<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_ERROR;

header('Content-Type: application/json');

$jwtDecode = checkToken();
// mysqli_set_charset($mysqli, 'utf16');
// 유저 아이디 
$uid = $jwtDecode[DATA_USER]["uid"];
$wid = $_POST[REQUSET_WID];
$chapter_id = $_POST[REQUSET_CHAPTER_ID];
$position = $_POST[REQUSET_HISTORY_POSITION];
$percent = (float)$_POST[REQUSET_HISTORY_PERCENT];

$result = upsertHistoryView($uid,$wid,$chapter_id,$position,$percent);

if($result){
	$result_data = getReadChapterData($chapter_id,$uid);
	if($result_data){
		echo json_encode(getResponseArrayWithData(200,true,"정보 불러오기 성공",$result_data));

	}else{
		echo json_encode(getResponseArrayWithData(408,false,"query 조회 select 확인 실패",array()));

	}
}else {
	echo json_encode(getResponseArrayWithData(404,false,"query 조회 실패",array()));
};

?>
