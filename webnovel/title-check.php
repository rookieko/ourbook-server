<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_TOKEN;
include_once INCLUDE_ERROR;

// mysqli_set_charset($mysqli, 'utf8');
header('Content-Type: application/json');

$jwtDecode = checkToken();
// 유저 아이디 
$uid = $jwtDecode[DATA_USER]["uid"];

$title_input = $_GET[REQUSET_WEBNOVEL_TITLE];

if(!isset($title_input)){
	echo json_encode(getResponseArray(500,false,"데이더 송신 오류"));
	exit;
}

$isDuplicate = isWebnovelTitleDuplicate($title_input);
if($isDuplicate === false ){
	// 중복 x  사용 가능한 title
	echo json_encode(getResponseArrayWithData(200,true,"사용 가능한 제목 입니다.",['title' => $title_input]));

}else if ($isDuplicate === true){
	echo json_encode(getResponseArray(400,false,"이미 사용중인 제목 입니다."));
}else{
	echo json_encode(getResponseArray(404,false,"query 오류 발생"));
}

// 먼저 token 을 확인하고 assoc_array ,
// book DB table 에 중복 확인  
	
?>