<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
// include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_ERROR;

header('Content-Type: application/json');

$jwtDecode = checkToken();

$uid = $jwtDecode[DATA_USER]["uid"];

$comment_id = $_GET["comment_id"];
if($comment_id < 1){
	http_response_code(404);
	exit;
}

$result = getReviewWithRatingData($comment_id,$uid);

if($result){
	echo json_encode(getResponseArrayWithData(200,true,"성공 query",$result));

}else{
	echo json_encode(getResponseArray(400,false,"실패 result 가 없음"));
}


?>