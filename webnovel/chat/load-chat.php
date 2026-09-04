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

// $chatRoomUserId = $_POST['chat_room_user_id'];
$chatRoomId = $_POST['chat_room_id'];
// $startChatId = $_POST['startChatId'];

$result = getListChat($uid,$chatRoomId);

if($result === false){
	http_response_code(301);
	exit;
}

echo json_encode($result);

?>