<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_TOKEN;
include_once INCLUDE_ERROR;
/* 등록을 위한 카테고리 출력 */

// mysqli_set_charset($mysqli, 'utf8');
header('Content-Type: application/json');
mysqli_set_charset($mysqli, 'utf8');


$result = getCategoryInfo();

$response_data = array();
$data = array();

while($result_fetch = $result->fetch_assoc()){
	$response_data[] = $result_fetch;
	// echo $result_fetch["name"];
	// echo $result_fetch["id"];
};

// print_r($response_data);
$data['category']= $response_data;

echo json_encode($response_data);
// echo "한글 출력";
?>