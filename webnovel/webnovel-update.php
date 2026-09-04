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

// 소유권 검사 : 본인 작품이 아니면 어떤 변경도 허용하지 않는다
if (!isWebnovelOwner($wid, $uid)) {
	echo json_encode(getResponseArray(403, false, "본인의 작품이 아닙니다"));
	return;
}

global $mysqli;
$stmt = null;

switch ($_POST['change']) {
	case 'title':
		# code...
		$title = $_POST['title'];
		$stmt = $mysqli->prepare("UPDATE `webnovel` SET `title` = ? WHERE id = ?");
		$stmt->bind_param('si', $title, $wid);
		break;
	
	case 'category':
		$category_id = (int)$_POST['category_id'];
		$stmt = $mysqli->prepare("UPDATE `webnovel` SET `category_id` = ? WHERE id = ?");
		$stmt->bind_param('ii', $category_id, $wid);
		break;

	case 'summary':
		$summary = $_POST['summary'];
		$stmt = $mysqli->prepare("UPDATE `webnovel` SET `summary` = ? WHERE id = ?");
		$stmt->bind_param('si', $summary, $wid);
		break;

	case 'delete':
		$stmt = $mysqli->prepare("DELETE FROM webnovel WHERE `webnovel`.`id` = ?");
		$stmt->bind_param('i', $wid);
		break;

	default:
	exit;
		# code...
		break;
}

if($stmt->execute()){
	echo json_encode(getResponseArray(200,true,"수정 성공"));
}else{
	echo json_encode(getResponseArray(400,false,"실패 다시 시도해주세요"));

}


?>
