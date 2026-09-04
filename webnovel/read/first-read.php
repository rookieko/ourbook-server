<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
// include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_ERROR;
// 처음 조회하는 경우 

header('Content-Type: application/json');


$jwtDecode = checkToken();
// mysqli_set_charset($mysqli, 'utf16');
// 유저 아이디 
$uid = $jwtDecode[DATA_USER]["uid"];
$wid = $_POST["wid"];


$result =  getFirstChapter($wid);

if($result){
	echo json_encode(getResponseArrayWithData(200,true,"성공",$result));
}else {
	echo json_encode(getResponseArray(405,false," 실패 query "));
};
?> 