<?php
include_once include_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';

include_once INCLUDE_DB;
include_once INCLUDE_MEMBER_QUERY;

header('Content-Type: application/json');  

$email_input = $_POST[REQUSET_EMAIL];
$result['EmailDuplicate'] = isEmailDuplicate($email_input); //return mysqli_object
$authCode = rand(000000,999999);

if($result['EmailDuplicate'] == false){
	// 이메일이 일치하는 유저 정보가 없음 
	$validSignInEmail =true;
	$response_array[RESULT_EMAILDUPLICATE] = getResponseArray(400,false,"회원정보가 존재 하지 않습니다.");     
}else if($result['EmailDuplicate'] == true) {
	// 이메일이 일치하는 정보가 존재함
	$response_array[RESULT_EMAILDUPLICATE] = getResponseArray(200,true,"존재하는 회원정보");          
	$validSignInEmail = false;
	
}else{
	// 실패 쿼리 에러
	$response_array[RESULT_EMAILDUPLICATE] = getResponseArray(500,false,"query 에러 발생");    
	$validSignInEmail = false;

}  

?>