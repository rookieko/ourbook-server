<?php
include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/db-connect.php";
include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/member/memberQuery.php";


// $email = "user@example.com";// $_POST['email'];
// $username = "usernamse";//$_POST['username'];
// $password = "890890";//$_POST['password']; // 실제로는 비밀번호를 해싱 처리해야 함

header('Content-Type: application/json');  

$email_input = $_POST[REQUSET_EMAIL]; // 받은 이메일
$userName_input = $_POST[REQUEST_USERNAME];
$userPassword_input = $_POST[REQUEST_PASSWORD];
$validSignInName = false;
$validSignInEmail = false;
$authCode = rand(000000,999999);


// $uid = 5;
// email_send($email,$uid);

/**  응답 데이터 저장용 연관 배열로 마지막 Jsonencode 를 통해서 응답
RESULT_EMAILDUPLICATE = 이메일 중복 확인
RESULT_NAMEDUPLICATE = 닉네임 중복 확인
RESULT_AUTHCODESAVE = 이메일 인증 번호 , 이메일 DB 저장 확인
RESULT_AUTHCODESEND = 이메일 인증 번호 발송
 */
$response_array = array();

// 이메일 중복 검사

// 이메일 중복 확인 응답 코드 메세지 반환
// getArrayEmailDuplicateInfo 유저 이메일 중복 응답  count , 조회 값 
$result['EmailDuplicate'] = isEmailDuplicate($email_input); //return mysqli_object

if($result['EmailDuplicate'] == false){
    // 성공 
    $validSignInEmail =true;
    $response_array[RESULT_EMAILDUPLICATE] = getResponseArray(200,true,"사용 가능한 이메일");     
}else if($result['EmailDuplicate'] == true) {
    // 실패- 중복
    $response_array[RESULT_EMAILDUPLICATE] = getResponseArray(400,false,"이미 사용중인 이메일");          
    $validSignInEmail = false;
    
}else{
    // 실패 쿼리 에러
    $response_array[RESULT_EMAILDUPLICATE] = getResponseArray(500,false,"query 에러 발생");    
    $validSignInEmail = false;

}    


// 닉네임 중복 확인 응답 코드 메세지 반환
// getArrayUserNameDuplicateInfo 유저 이메일 중복 응답  count , 조회 값 
$result['UserNameDuplicate'] = isUserNameDuplicate($userName_input); //return mysqli_object


if($result['UserNameDuplicate'] == false){
    // 성공
    $response_array[RESULT_NAMEDUPLICATE] = getResponseArray(200,true,"사용 가능한 닉네임");  
    $validSignInName = true;   
}else if($result['userName'] == true) {
    // 실패 - 중복
    $response_array[RESULT_NAMEDUPLICATE] = getResponseArray(400,false,"이미 사용중인 닉네임");     
    $validSignInName = false;   
}else{
    // 실패 쿼리 에러
    $response_array[RESULT_NAMEDUPLICATE] = getResponseArray(500,false,"query 에러 발생");    
    $validSignInName = false;   
};


//-----------------------수정 이전 ---------------------------------//

if($validSignInEmail&&$validSignInName){
// signUpDataSave($uid,$userName,$email_input,$userPassword,$response_array);
    include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/member/register/emailAuthSend.php";
        // sendAuthEmail_custom($response_array);
        $response_array[RESULT_AUTHCODESAVE] = setSignUpUserDataBeforeAuth($userName_input,$email_input,$userPassword_input,$authCode);
        $response_array[RESULT_AUTHCODESEND] = sendAuthEmail_pure($email_input,$authCode);
        // save_emailAuthCode($uid,$response_array);
        // setSignUpUserDataBeforeAuth()

}else{
    // $response_array['authCodeSave'] = getResponseArray(300 , false , "중복 중지");
    // $response_array['authCodeSend'] = getResponseArray(300 , false , "중복 중지");
};
echo json_encode($response_array);
// include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/member/register/emailAuthSend.php";



$mysqli->close();
?>