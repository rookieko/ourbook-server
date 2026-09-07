<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
// include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_ERROR;

header('Content-Type: application/json');

// 같은 폴더의 다른 엔드포인트와 달리 여기만 인증이 주석 처리돼 있었다.
// 클라이언트(ReviewService.getReviewStatisticsData)는 원래부터 JWT 헤더를 보내고 있었다.
$jwtDecode = checkToken();

if( !isset($_GET["wid"]) ){
	http_response_code(401);
	echo json_encode(getResponseArray(401,false,"실패 wid 가 없음 다시 실행 "));
	exit; 
}


// webNovel id
// getReviewStaticData() 는 값을 쿼리 문자열에 그대로 넣는다 → 여기서 int 로 좁힌다.
// (get-one-book-data.php 가 쓰는 방식과 같다)
$wid = (int)$_GET["wid"];

// count , world_score , story_score , character_score 를 받는다.
$result = getReviewStaticData($wid);
if($result[SUCCESS]){ // 성공 여부에 따라 , query , 값 존재 
	// var_dump($result);
	echo json_encode($result);
}else{
	echo json_encode(getResponseArray(300,false,$result[MESSAGE]));
}



?>