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

$was_liked = (int)$_POST["like_or_not"]; 
$comment_id = $_POST["comment_id"];
// 1 인 경우 delete 로 0인 경우 insert 로 
// echo "was_liked = {$was_liked} comment_id = {$comment_id}";
if($was_liked == 1){
	// delete 로 
	$result = unregisterReviewLike($uid,$comment_id);
	$string = "do delete"; //확인용 로그
}else{
	// insert 로 
	$result = registerReviewLike($uid,$comment_id);
	$string = "do insert"; //확인용 로그
}//else
$array = array();
// 결과 처리 
if($result == false){
	echo json_encode(getResponseArrayWithData(400,false,$string." query 실패 was_liked = {$was_liked} comment_id = {$comment_id}",array()));
}else{
	// 성공
	echo json_encode(getResponseArrayWithData(200,true, $string." query 성공 data was_liked = {$was_liked} comment_id = {$comment_id}",$result));
}



?>