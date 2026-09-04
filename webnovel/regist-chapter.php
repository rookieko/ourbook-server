<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
// include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
// 제목 title , 내용 content , 회차 번호  num 을 각각 입력 받음 

header('Content-Type: application/json');

$jwtDecode = checkToken();

$uid = $jwtDecode[DATA_USER]["uid"];

$title_input = $_POST["title"];
$content_input = $_POST["content"];
$word_length = $_POST["word_length"];
$text_length = $_POST["text_length"];
$wid = (int)$_POST["wid"];

// 소유권 검사 : 본인 작품에만 회차를 추가할 수 있다
if (!isWebnovelOwner($wid, $uid)) {
	echo json_encode(getResponseArray(403, false, "본인의 작품이 아닙니다"));
	return;
}

// print_r($_POST[]);
if(isset($_POST['num'])){
	$num = $_POST['num'];
}else{
	$num = getLastChapterNum($wid) +1;
}
$result = registerChapter($wid,$title_input,$content_input,$num,$word_length,$text_length);

if($result['success']){
	if(setBookTotalChapter($wid,1)){ // 회차 정보 , 총 회차수 +1;
		echo json_encode($result);
	}else{
		echo json_encode(getResponseArray(405,false,"회차 등록 성공 , total_num update fail"));
	}
}else{
	echo json_encode($result);
}
// print_r($result);


?>
