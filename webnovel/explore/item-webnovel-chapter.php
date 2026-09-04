<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_ERROR;

header('Content-Type: application/json');

$jwtDecode = checkToken();
// 유저 아이디 
$uid = $jwtDecode[DATA_USER]["uid"];

$wid = $_GET["wid"];
$page = $_GET['page'];
$option = $_GET['option'];//  0 = 인기순 , 1  = 최신순 , 2 = 등록순 , 3 = 선호 작품 등록순 ?

$opt; // opt 를 sql 문 limit 절 앞에 넣어 정렬 순서를 바꾼다 // 그런데 chapter 까지 합치는 경우 동작이 안됨 
if ($option == 0 ){
 $opt = "ORDER BY views DESC";
}elseif($option == 1){
	$opt = "ORDER BY num DESC ";
}elseif($option == 2 ){
	$opt = "ORDER BY num ASC ";
}elseif($option == 3){

};

$data = getWidToChapterData($wid,$page,$opt,$uid); //

if( $data == 0){
	http_response_code(201);
	echo json_encode( $arrayName = []);
 exit;
}
http_response_code(200);

echo json_encode($data);

?>