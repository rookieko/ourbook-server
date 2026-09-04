<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;
// include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_WEBNOVEL_QUERY;
include_once INCLUDE_ERROR;
// 제목 title , 내용 content , 회차 번호  num 을 각각 입력 받음 

header('Content-Type: application/json');

$jwtDecode = checkToken();

$uid = $jwtDecode[DATA_USER]["uid"];
// 스코어 값 불라오기 		 
$score_world = $_POST[REQUSET_REVIEW_SCORE_World];
$score_story = $_POST[REQUSET_REVIEW_SCORE_Story];
$score_character = $_POST[REQUSET_REVIEW_SCORE_Character];
$score_quality = $_POST[REQUSET_REVIEW_SCORE_Quality];
$socre_update = $_POST[REQUSET_REVIEW_SCORE_Update];
$review_content = $_POST[REQUSET_REVIEW_CONTENT];
$wid = $_POST[REQUSET_WID];

/* TODO query 에서 등록에 관한 메서드 작성 , 이전에 선행 DB 설계  */

// var_dump($score_world);
// var_dump($score_story);
// var_dump($score_character);
// var_dump($review_content);
// var_dump($wid);

/*  평균값 계산  */
$score_total = ($score_story + $score_world + $score_character +$score_quality + $socre_update) / 5; 
/*  upsert 전에 배열에 값을 넣음 숫자 index 는 실제 type_id */
$score_arr = [0 => $score_total, 1 => $score_world, 2 => $score_character, 3 => $score_story , 4 => $score_quality , 5 => $socre_update];
if (
	($message = registerReview($wid, $uid, $score_arr, $score_world, $score_story, $score_character, $score_total, $review_content)) === true
) {
	//성공
	echo json_encode(getResponseArray(200,true,"성공"));
} else {
	echo json_encode(getResponseArray(400,false,$message));
	// 실패
}
