<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
include_once INCLUDE_MEMBER_QUERY;
//유저 닉네임 변경

header('Content-Type: application/json');  

$result_tokenCheck = checkToken();
if(!$result_tokenCheck[RESULT_JWT][SUCCESS]){
    // token 오류 변조 
    return;
}
// 사용자 정보를 담는 userInfo assoc_array 
$user_info = $result_tokenCheck[DATA_USER];


// 사용자가 입력한 userName

//ToDO 여기까지 진행이됨 , 현재 이메일을 사용하는데 uid를 사용으로 교체하고 
//토큰에 존재하는 클라이언트에서도 uid르 사용해서 사용자 정보를 불러온다.
$userName_input = $_POST['userName'];

$uid = $user_info['uid'];
if($uid == null){
    // 변조  ,  token 오류
	echo json_encode($result_tokenCheck);
	return;
}
$response_array = array();

//



// $result_array['User-Info'] = ["email" => $decode->uei , "userName" => $decode->uni , "uid" => $decode->uid];

// 닉네임 중복 확인 응답 코드 메세지 반환
// getArrayUserNameDuplicateInfo 유저 이메일 중복 응답  count , 조회 값 
$result[RESULT_NAMEDUPLICATE] = isUserNameDuplicate($userName_input); //return mysqli_object


if($result[RESULT_NAMEDUPLICATE] === false){
    // 성공 변경 가능 
    $response_array[RESULT_NAMEDUPLICATE] = getResponseArray(200,true,"사용 가능한 닉네임");  
		$response_array[RESULT_UNAMECHANGE] = updateUserName($userName_input , $uid);
		if($response_array[RESULT_UNAMECHANGE][SUCCESS]){
			// $response_array['Ojwt-Token'] = createToken($user_email,$userName_input);
			$response_array[DATA_USER] = ["userName" => $userName_input ]; // 바뀐 이름인데 사용 하면 안됨 ...
		}
		
    // $validSignInName = true;   
}else if($result[RESULT_NAMEDUPLICATE]===true) {
    // 실패 - 중복
    $response_array[RESULT_NAMEDUPLICATE] = getResponseArrayWithData(400,false,"이미 사용중인 닉네임",$arr = ["fail post input" => $userName_input]);     
    // $validSignInName = false;   
}else{
    // 실패 쿼리 에러
    
    $response_array[RESULT_NAMEDUPLICATE] = getResponseArrayWithData(500,false,"query 에러 발생",$arr = ["fail post input" => $userName_input , "status" => $result[RESULT_NAMEDUPLICATE]]);    
    // $validSignInName = false;   
};

echo json_encode($response_array);

?>