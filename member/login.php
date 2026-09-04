<?php
include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/db-connect.php";

error_reporting( E_ALL );
ini_set( "display_errors", 1 );


$result_array = array();

header('Content-Type: application/json');

$password = $_POST['password'];
$email = $_POST['email'];

$sqlLogin = "SELECT * FROM user WHERE email = '$email' and password = '$password'";
$result = $mysqli->query($sqlLogin);
if ($result->num_rows > 0) {
    //로그인 성공
    include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/member/memberToken.php";
    $result_array['LoginCheck'] = 200;
    $row = mysqli_fetch_assoc($result);
    // 응답에 들어갈 데이터 주입
    $result_array['email'] = $row["email"];
    $result_array['userName'] = $row["username"];
    $result_array['uid'] = $row['id'];
    // 로그인 토큰 생성 , 전달 OBJWToken
    $result_array['OBJWToken'] = createToken($email,$row['username'],$row['id']);
    }else{// 로그인 실패
        $result_array['LoginCheck'] = 400;
    };

echo json_encode($result_array);

?>