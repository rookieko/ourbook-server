<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
// include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;

header('Content-Type: application/json');
// 

$jwtDecode = checkToken();
// 유저 아이디 
$uid = $jwtDecode[DATA_USER]["uid"];
$result = getWriterBookData($uid);

if(!isset($result)){
	echo json_encode(getResponseArray(500,false,"책이 존재 하지 않음"));
	exit;
}
$resultData= array();
$rs = array();
while($result_fetch = $result->fetch_assoc()){
	$rs[] = $result_fetch;
}
$resultData['book'] = $rs;
// print_r($resultData);

// print_r($result_fetch);
// echo json_encode($result_fetch);

// echo $uid;

echo json_encode(getResponseArrayWithData(200,true,"책을 불러옵니다.",$resultData));

?>