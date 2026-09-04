<?php
// 헤더의 토큰으로 부터 사용자의 회원 정보를 응답 하기 위한 php 파일 
include_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
include_once INCLUDE_MEMBER_QUERY;
// include_once INCLUDE_ERROR;
header('Content-Type: application/json');  

// error_reporting( E_ALL );
// ini_set( "display_errors", 1 );

$headers = apache_request_headers(); 
// print_r($headers);
$jwtDecode = checkToken(); 

$uid = $jwtDecode[DATA_USER]["uid"];

if ($uid != null){
	$result = getUserData($uid);
	if($result == false){// 유저 정보가 없을 때 
		echo json_encode(getResponseArray(401,false,"UID 에 맞는 회원 정보가 없습니다."));
		return;
	}
	// 유저 정보가 있을 때 
	echo json_encode(getResponseArrayWithData(200,true,"유저 정보 획득 성공",$result));
}else{
	echo json_encode(getResponseArray(401,false,"유저 정보 획득 실패"));
}



// echo $jwt;
// print_r(checkToken());
// echo json_encode(checkToken());


?>