<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
// include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_ERROR;

// 사용자가 가지고 있는 책 관련 정보 data 
//1. 리뷰 관련 ( 리뷰 점수 , 존재 여부  ) 2. 회차 기록 저장 정보 , 마지막 날짜의 회차 기록정보 readDate 3. 
//4. 관심 목록 설정 정보 
// likes , novel_rating , 


header('Content-Type: application/json');


$jwtDecode = checkToken();
// mysqli_set_charset($mysqli, 'utf16');
// 유저 아이디 
$uid = $jwtDecode[DATA_USER]["uid"];
$wid = $_POST["wid"];

$result = getUserBookOneData($uid,$wid);


if($result){
	echo json_encode(getResponseArrayWithData(200,true,"정보 불러오기 성공",$result));
}else {
	echo json_encode(getResponseArrayWithData(404,false,"query 조회 실패",array()));
};




?>