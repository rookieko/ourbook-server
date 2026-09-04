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
$chatRoomId = $_POST['chat_room_id']?? false; // 비어있거나 null 인 경우 false

if($chatRoomId ===false){
	echo json_encode("error chat room id not posted ");
		exit;
}


$result = joinUserRoom($uid,$chatRoomId,2); // 0 = 방장 , 1 = 부반장 ?  2 = 일반 유저

if($result === false ){
	http_response_code(301);
	echo json_encode(getResponseArray(400,false,"실패하였습니다."));
	exit;
}else{

}
http_response_code(200);
echo json_encode(getResponseArrayWithData(200,true,"성공하였습니다.", array('chat_room_user_id'=> $result) ));
?>