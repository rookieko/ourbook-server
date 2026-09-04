<?php
error_reporting( E_ALL );
ini_set( "display_errors", 1 );

include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/db-connect.php";
include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/member/memberQuery.php";
// 암호 변경
include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/member/memberToken.php";

$result_tokenCheck = checkToken();
if(!$result_tokenCheck['jwtResult']['success']){
    // token 오류 변조 
    return;
}


$userPassword_input = $_POST['password'];

// print_r($_POST);

$user_email = $result_tokenCheck[DATA_USER]['email'];
if($user_email == null){
	echo json_encode($user_result);
	return;
}
$response_array = array();

// 닉네임 중복 확인 응답 코드 메세지 반환
// getArrayUserNameDuplicateInfo 유저 이메일 중복 응답  count , 조회 값 
// $result['userName'] = getArrayUserNameDuplicateInfo($userName_input); //return mysqli_object
$response_array = updateUserPassword($user_email,$userPassword_input);

// if($result['userName']['count']== 0){
//     // 성공 변경 가능 
//     $response_array['nameDuplicate'] = getResponseArray(200,true,"사용 가능한 닉네임");  
// 		$response_array['updateUserName'] = updateUserName($userName_input , $user_email);
		
//     // $validSignInName = true;   
// }else if($result['userName']['count']> 0) {
//     // 실패 - 중복
//     $response_array['nameDuplicate'] = getResponseArray(400,false,"이미 사용중인 닉네임");     
//     // $validSignInName = false;   
// }else{
//     // 실패 쿼리 에러
//     $response_array['nameDuplicate'] = getResponseArray(500,false,"query 에러 발생");    
//     // $validSignInName = false;   
// };

echo json_encode($response_array);

?>