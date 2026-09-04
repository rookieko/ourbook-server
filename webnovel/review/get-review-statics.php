<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
// include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_ERROR;

header('Content-Type: application/json');

// $jwtDecode = checkToken();

// $uid = $jwtDecode[DATA_USER]["uid"];

// $wid = $_GET["wid"];

if( !isset($_GET["wid"]) ){ 
	http_response_code(401);
	echo json_encode(getResponseArray(401,false,"실패 wid 가 없음 다시 실행 "));
	exit; 
}


// webNovel id 
$wid = $_GET["wid"];

// count , world_score , story_score , character_score 를 받는다.
$result = getReviewStaticData($wid);
if($result[SUCCESS]){ // 성공 여부에 따라 , query , 값 존재 
	// var_dump($result);
	echo json_encode($result);
}else{
	echo json_encode(getResponseArray(300,false,$result[MESSAGE]));
}



?>