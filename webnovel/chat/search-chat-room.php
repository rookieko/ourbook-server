<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
include_once INCLUDE_CHAT_QUERY;
include_once INCLUDE_ERROR;

header('Content-Type: application/json');
$jwtDecode = checkToken();

// 유저 아이디 
$uid = $jwtDecode[DATA_USER]["uid"];

if(!isset($_POST["query"])){
	exit;
}
$searchQuery = $_POST["query"];

// query 진행 sql 코드 진행 
$resultOthers = searchOtherChatRoom($uid,$searchQuery);

$resultMy =  searchMyChatRoom($uid,$searchQuery);

if($resultMy === false && $resultOthers === false){
	http_response_code(205);
	echo json_encode(getResponseArray(400,false,"excute error"));
	exit;
}
$result['message'] = "성공하였음";
$result['my'] = $resultMy;
$result['others'] = $resultOthers;

echo json_encode($result);

?>