<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
use Firebase\JWT\ExpiredException;// try catch 에서 ExpiredException 을 잡기 위해 사용 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";
include_once INCLUDE_MEMBER_QUERY;

	// $JwtKey = "CHANGE_ME_shared_HS256_secret";

	class ExpiredExceptionCatch extends ExpiredException{}; // try catch 에서 ExpiredException 페쇄 use 로는 상속이 안되거나

/* jwt 토큰 생성 , jwt 로 로그인 할 토큰을 생성 
	OBJWToken
	결과 성공  return_array[RESULT_JWT][status == 200 , success == true]
	payLoad = [
	exp = 만료 기한
	iat = 토큰 생성 시간
	uei = 유저 이메일 주소
	uni = 유저 이름 ( 닉네임 )
	uid = 유저 아이디 , user db 의 primary key
	( 임시 ) ust = 유저 status , 추후 유저 정보 ( 관리자 , 작가 , 임시 등 ) 나타내게 구현 
	]
	생성 조건
	1. 회원의 로그인 정보를 확인 하고 나서  or  2. jwt의 토큰의 유효기간 확인 하고 나서
	클라이언트로 보낼 jwt 토큰을 String 을 return , 이후 client 로 jwt response
	토큰 기한 설정 , User-Info 유저 정보 받아서 결과 배열에 
	Ojwt-Token = 새로 발급한 JWT , 갱신은 시간만
*/
function checkToken(){
	// global $JwtKey; // 공개 키
	$JHeader = new stdClass(); // 요청 jwt decode header  , alg 확인
	$headers = apache_request_headers(); 
	// print_r($headers);
	$jwt = $headers[HEADER_JWT]; // 'OBJWToken ' =  jwt가 들어있는 요청 header key , $jwt 는 요청 jwt
	
	try {
		//code...
		$decode = JWT::decode($jwt,new Key(JWT_PUBLICKKEY, 'HS256'),$JHeader); // jwt decode 
		// header 검증 
		if ($JHeader->alg != 'HS256'){
			$result_array[RESULT_JWT] = getResponseArray(405,false,"토큰 Header 정보가 잘못 되었습니다.");
			return $result_array;

			// return false;
		}else{
			// echo "head 통과";
		};
		// 가장 중요한 uid 확인 null 이면 오류로 토큰에 문제가 있다고 봄 , 발급시 거의 존재
		if($decode->uid == null){
			return $result_array[RESULT_JWT] = getResponseArray(STATUS_FAIL,false,"토큰의 정보 오류 uid 값 존재하지 않음");
		}

		// 시간 검증 현재 시간이 만료 시간 보다 높은 경우 ( 만료  = > 재발급 )
		if(time() > $decode->exp){ // jwt Firebase 는 exp 를 자동으로 인식해서 유효 기간을 확인해 주고 에러 코드를 발생시킨다 방법1. exp 가 아닌 다른 이름으로 , 2.catch 에서 특정 에러를 잡는 코드를 입력
			//수정 해야 함  jwt 생성 , 반환 
			// 현재 실행이 안됨 firbase 에서 에러를 자동으로 잡기 때문
			$result_array[RESULT_JWT] = getResponseArray(301,true,"토큰의 기한이 지났으면 다시 재 발급 합니다.");
			$result_array[DATA_USER] = ["email" => $decode->uei , "userName" => $decode->uni , "uid" => $decode->uid];
			$result_array[DATA_JWT]= createToken($decode->uei , $decode->uni , $decode->uid);
			return $result_array;		
		}else{ // 모두 만족 jwt 유효
			// 로그인 성공 
			$result_array[RESULT_JWT] = getResponseArray(STATUS_SUCCESS,true,"토큰이 기한이 검사 적합 , 다시 재 발급 합니다.");
			$result_array[DATA_USER] = ["email" => $decode->uei , "userName" => $decode->uni , "uid" => $decode->uid];
			$result_array[DATA_JWT]= createToken($decode->uei , $decode->uni , $decode->uid);
			return $result_array;
		}
	}catch(ExpiredException $ex) { 
		// TODO : 꼼수 재발급 코드 수정 해야함..보안 목적 
		// jwt Firebase 는 exp 를 자동으로 인식해서 유효 기간을 확인해 주고 에러 코드를 발생시킨다 
		// 방법 1. exp 가 아닌 다른 이름으로 , 
		// 방법 2.catch 에서 특정 에러를 잡는 코드를 입력
		$result_array[RESULT_JWT] = getResponseArray(302,true,"try catch 토큰의 기한이 지났습니다 다시 재발급 해야 합니다.");
		// 꼼수 재발급 코드 수정 해야함..보안 목적 
		list($jwtHead ,$payload , $signature) = explode('.',$jwt); // 나누어 payload 구하고
		$jsonObject = json_decode(base64_decode($payload));  // parsing
		// print_r($jsonObject); // 확인 용 삭제
		$result_array[DATA_USER] = ["email" => $jsonObject->uei , "userName" => $jsonObject->uni , "uid" => $decode->uid];
		$result_array[DATA_JWT]= createToken($decode->uei , $decode->uni , $decode->uid);
		return $result_array;
	} 
	catch (Exception $e) {
		//throw $th;
		// echo  "checkToken 오류 ".$e->getMessage();
		$result_array[RESULT_JWT] = getResponseArray(404,false,"토큰 정보가 잘못 되었습니다. checkToken 오류: ".$e->getMessage());
		return $result_array;
	}
	
};


/* jwtToken 생성 */
function createToken($userEmail , $userName ,int $uid ){
	// global $JwtKey;
	$now = new DateTimeImmutable();
	$nowTimeStamp = $now->getTimestamp(); // 현재 시간 스탬프 iat 에 저장 
	$setTimeStamp =  $now->add(new DateInterval('P1M'))->getTimestamp(); // 토큰 만료 시간  형재 20분 설정
	//
	
	$payload = [
		'exp' => $setTimeStamp,
		'iat' => $nowTimeStamp,
		'uei' => $userEmail,
		'uni' => $userName,
		'uid' => $uid
		
	];

	$jwt = JWT::encode($payload, JWT_PUBLICKKEY, 'HS256');

	return $jwt;

}



?>