<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
date_default_timezone_set('Asia/Seoul');

// $hostname="your-server-host.example.com";
/* $hostname="localhost";

$dbuserid="your_db_user";

$dbpasswd="your_db_password";

$dbname="ourbook";
 */


$mysqli = new mysqli(DB_HOST,DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_error) {

    echo "MariaDB 접속 실패 !!", "<br>";	// 출력
    // mysqli_connet_error() 내장함수는 DB서버 연결 오류 원인을 반환해주는 함수
	   echo "오류 원인 : ", mysqli_connect_error();	
       die('Connect Error: '.$mysqli->connect_error);
	   exit();


}else{
    mysqli_set_charset($mysqli, "utf8mb4");
    // echo "성공";
    // mysqli_set_charset($mysqli, 'utf8');
}


?>