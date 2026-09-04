<?php
include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/db-connect.php";
include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/member/memberQuery.php";

global $mysqli;
$auth_number;
$result_array = array();

header('Content-Type: application/json');

$authInput = $_POST['authCode'];
$email = $_POST['email'];

$sqlCheckAuthCode = "SELECT * FROM emailAuth WHERE email = '$email'";
$result = $mysqli->query($sqlCheckAuthCode);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $auth_code = $row["auth_code"];
        
    }
    if($auth_code == $authInput){
        $result_array['AuthCheck'] =  getResponseArray(200,true,"이메일 인증 성공 회원 가입을 축하드립니다.");
        $sqlAuthOk = "UPDATE emailAuth SET auth_ok = 1 WHERE email = '$email'";
        $sqlStatusOk = "UPDATE user SET status = 1 WHERE email = '$email'";
        $mysqli->query($sqlAuthOk);
        $mysqli->query($sqlStatusOk);
    }else{
        $result_array['AuthCheck'] = getResponseArray(400,false,"이메일 인증 코드가 틀림");

    }
}
  echo json_encode($result_array);

    $mysqli ->close();
?>