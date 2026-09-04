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

$chat_room_id = $_POST["chat_room_id"];


$result = getChatRoomDetail($chat_room_id,$uid);

if($result != null){
	http_response_code(200);
	echo json_encode($result);
}else{
	http_response_code(301);
};
