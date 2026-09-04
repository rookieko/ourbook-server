<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_TOKEN;
include_once INCLUDE_ERROR;

$jwtDecode = checkToken();
// 유저 아이디 
$uid = $jwtDecode[DATA_USER]["uid"];
if(!isset($_POST['title'])){
	echo json_encode(getResponseArray(500,false,"서버 전송 실패"));
	exit;
}

$title = $_POST['title'];
$category_id = $_POST['category'];

$response = registerBook($uid,$title,$category_id);


echo json_encode($response);
?>