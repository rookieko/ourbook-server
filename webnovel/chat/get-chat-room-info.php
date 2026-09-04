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

if(!isset($_POST['chat_room_id'])){
	echo json_encode("error chat room id not posted ");
		exit;
}

$chatRoomId = $_POST['chat_room_id'];

$result_room = getChatRoomInfo($uid,$chatRoomId);

// 성공 여부 처리  false query 실패 
if($result_room === false){
	http_response_code(300);
	echo json_encode(getResponseArray(401,false,"실패하였음"));
	exit;
}
//성공 
// $result = getResponseArrayWithData(200,true,"채팅방 목록 호출을 성공",$result_roomList);
http_response_code(200);
echo json_encode($result_room);

?>