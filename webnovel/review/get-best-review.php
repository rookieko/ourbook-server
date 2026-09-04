<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_ERROR;


header('Content-Type: application/json');

$jwtDecode = checkToken();

// 유저 아이디  uid
$uid = $jwtDecode[DATA_USER]["uid"];

$wid = $_GET["wid"];

$result = getReviewBest($wid,$uid);

if($result > 0 ){
	echo json_encode($result);
}
?>