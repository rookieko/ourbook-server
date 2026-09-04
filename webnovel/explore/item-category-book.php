<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_TOKEN;
include_once INCLUDE_ERROR;

// mysqli_set_charset($mysqli, 'utf8');
header('Content-Type: application/json');
mysqli_set_charset($mysqli, 'utf8');


$jwtDecode = checkToken();
// 유저 아이디 
$uid = $jwtDecode[DATA_USER]["uid"];

$cid = $_GET['cid'];
$page = $_GET['page'];
$option = $_GET['option'];//  0 = 인기순 , 1  = 최신순 , 2 = 등록순 , 3 = 선호 작품 등록순 ?

$opt;
if ($option == 0 ){
	$opt = "ORDER BY total_views DESC";
}elseif($option == 1){
	$opt = "ORDER BY id DESC ";
}elseif($option == 2 ){
	$opt = "ORDER BY id ASC ";
}elseif($option == 3){

};

// echo "cid {$cid} page {$page} option {$option}";
$data = getCidToBookData($cid,$page,$opt);
if( $data == 0){
	http_response_code(201);
	echo json_encode( $arrayName = []);
 exit;
}
http_response_code(200);

echo json_encode($data);
?>