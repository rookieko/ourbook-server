<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_ERROR;

header('Content-Type: application/json');


$jwtDecode = checkToken();

$uid = $jwtDecode[DATA_USER]["uid"];

if(!isset($_GET["query"])){
	exit;
}
$searchQuery = $_GET["query"];

global $mysqli;

// SQL 쿼리 준비
$sql = 
"SELECT 
webnovel.* ,
user.username AS username ,
user.profileImagePath AS profile_image ,
cn.name AS category_name
FROM webnovel
LEFT JOIN user ON webnovel.uid = user.id
LEFT JOIN category_name cn ON webnovel.category_id = cn.id 
WHERE webnovel.title 
LIKE ? OR webnovel.summary LIKE ? ";

// 쿼리 준비
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
		http_response_code(400);
    die('Query Preparation failed: (' . $mysqli->errno . ') ' . $mysqli->error);
}

// 검색어에 와일드카드 추가
$searchTerm = "%$searchQuery%";
$i = 0;

// 바인드 파라미터
$stmt->bind_param('ss', $searchTerm, $searchTerm);

// 쿼리 실행
$stmt->execute();

// 결과 받기
$result = $stmt->get_result();

// 결과를 배열로 변환
$results = [];
while ($row = $result->fetch_assoc()) {
    $results[] = $row;
}
echo json_encode($results);

?>