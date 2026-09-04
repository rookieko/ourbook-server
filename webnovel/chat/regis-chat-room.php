<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_CHAT_QUERY;
include_once INCLUDE_ERROR;

header('Content-Type: application/json');
$jwtDecode = checkToken();

// 유저 아이디 
$uid = $jwtDecode[DATA_USER]["uid"];
// wid , uid , 
// 채팅방 생성과 , 유저 등록 까지 실행한다 .  첫 유저는 방장  
$wid = $_POST['wid'];
$name = $_POST['name'];
$introduce = $_POST['introduce']??'';
$password_check = $_POST['password_setting']??0;
$password_check = (bool)$password_check;
$password = $_POST['password'] ?? null;
// if($password_check){
// 	// password 설정됨
// 	$password = $_POST['password'];
// 	if(!isset($password)){
// 		$password = "";
// 	};
// }else{
// 	// password 가 설정 안됨
// }
// if(!isset($introduce)){
// 	$introduce = "";
// };


$result = regisChatRoom($uid,$wid,$name,$introduce,$password_check,$password);

$total_user_number = 1;

$start_chat_id = 0;
$user_class = 0; // super = 0 , normal = 3;

$result_array = [];
$result_array['chat_room_id'] = (int)$result;

if( $result != 300 || $result !== false ){
	echo json_encode(getResponseArrayWithData(200,true , " 성공 하였습니다.",$result_array));
}else{
	http_response_code(400);
	echo json_encode(getResponseArrayWithData(400,false,"실패하였습니다.",$result_array));
};



?>