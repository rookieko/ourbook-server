<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
// include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_ERROR;

header('Content-Type: application/json');


$jwtDecode = checkToken();
// mysqli_set_charset($mysqli, 'utf16');
// 유저 아이디 
$uid = $jwtDecode[DATA_USER]["uid"];

// if ($uid != null){
// 	$result = getUserData($uid);
// if($result == false){// 유저 정보가 없을 때 
// echo json_encode(getResponseArray(500,false,"UID 에 맞는 회원 정보가 없습니다."));
// exit;
// 	}
// }
$wid_string = $_POST["wid"];
$wid = (int)$wid_string; // 웹소설 이미지를 저장할 웹소설 id  
// php 에 db 관련은 기본 int parsing 처리 해줘서 불필요함
$count  = getReviewCount($wid);
$total_score = getSimpleReveiwData($wid);
$bookData = getSimpleBookData($wid);
$bookData['review_count'] = $count;
$bookData['total_score'] = $total_score;


if($bookData == null){
	echo json_encode(getResponseArray(400,false,"실패 null 값 "));
	exit;
}
echo json_encode(getResponseArrayWithData(200,true," 성공 ",$bookData));



?>