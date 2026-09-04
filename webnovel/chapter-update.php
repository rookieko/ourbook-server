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

$wid_string = $_POST["wid"];
$wid = (int)$wid_string;
$cid_string = $_POST["chapter_id"];
$cid =(int)$cid_string;
$change = $_POST["change"];
$isDelete = false; // 삭제인지 , 업데이트인지 . true 인 경우 총 회차수 -1; update 인 경우   no action 

// 소유권 검사 : 본인 작품의 회차가 아니면 수정/삭제를 허용하지 않는다
if (!isWebnovelOwner($wid, $uid)) {
	echo json_encode(getResponseArray(403, false, "본인의 작품이 아닙니다"));
	return;
}

global $mysqli;
$stmt = null;

// WHERE 에 wid 를 함께 두어 다른 작품의 회차 id 가 넘어와도 걸리지 않게 한다
if( $change == "change"){
$isDelete = false;
$title = $_POST['title'];
$content = $_POST['content'];

$stmt = $mysqli->prepare("UPDATE chapter SET chapter_name = ? , chapter_content = ? WHERE id = ? AND wid = ?");
$stmt->bind_param('ssii', $title, $content, $cid, $wid);
}else {
	$isDelete = true;
	$stmt = $mysqli->prepare("DELETE FROM chapter WHERE `chapter`.`id` = ? AND `chapter`.`wid` = ?");
	$stmt->bind_param('ii', $cid, $wid);
}

if($stmt->execute()){
	if($isDelete){
		if($stmt->affected_rows < 1){
			// 실제로 지워진 회차가 없으면 total_num 을 건드리지 않는다
			echo json_encode(getResponseArray(404,false,"해당 회차를 찾을 수 없습니다"));
			return;
		}
		if(setBookTotalChapter($wid,-1)){ 
			// update chapter table , update webnovel table 's total_num success ;
			echo json_encode(getResponseArray(200,true,"삭제 성공"));
		}else{
		echo json_encode(getResponseArray(405,false," chapter 수정 성공 , total_num update fail , 실패 다시 시도해주세요"));
		}
	}else{
		echo json_encode(getResponseArray(200,true,"수정 성공"));
	}

}else{
	echo json_encode(getResponseArray(400,false,"실패 다시 시도해주세요"));
}



?>
