<?php
//include_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
/* 상수 정의 php  */

define('OURBOOKDIR',$_SERVER['DOCUMENT_ROOT'].'/OurBook/');
define('INCLUDE_MEMBER_QUERY',$_SERVER['DOCUMENT_ROOT']."/OurBook/member/memberQuery.php");
define('INCLUDE_WEBNOVEL_QUERY',$_SERVER['DOCUMENT_ROOT']."/OurBook/webnovel/webnovelQuery.php");
define('INCLUDE_CHAT_QUERY',$_SERVER['DOCUMENT_ROOT']."/OurBook/webnovel/chat/chat-query.php");

define('INCLUDE_TOKEN',$_SERVER['DOCUMENT_ROOT']."/OurBook/member/memberToken.php");
define('INCLUDE_ERROR',$_SERVER['DOCUMENT_ROOT']."/OurBook/error-show.php");
/* DB 상수 설정  */

define('INCLUDE_DB',$_SERVER['DOCUMENT_ROOT']."/OurBook/db-connect.php");
define('DB_USERNAME', 'your_db_user');
define('DB_PASSWORD', 'your_db_password');
define('DB_NAME','ourbook');
define('DB_HOST','localhost');

define('DB_USER_PROFILEIMAGE' , 'profileImagePath');
define('DB_USER_ID','id');

/* 서버 - 경로 설정  */


/* 변수명 설정  */
define('JWT_PUBLICKKEY', 'CHANGE_ME_shared_HS256_secret_min_32_bytes');  // Java TokenCheck.java 와 동일한 값을 써야 한다

/* 요청 값  설정 */
define('HEADER_JWT','Ojwt-Token'); // Header 안의 jwt 의 KEY
define('REQUEST_USERNAME','userName'); // 요청 post 유저 닉네임
define('REQUSET_EMAIL','email'); // 요청 post 유저 이메일 
define('REQUEST_PASSWORD','password'); // 요청 post 유저 비밀번호 
define('REQUSET_WEBNOVEL_TITLE','title'); // 요청  BOOK , 제목
define('REQUSET_WEBNOVEL_CONTENT','content');
define('REQUSET_REVIEW_SCORE_World','score_world');
define('REQUSET_REVIEW_SCORE_Story','score_story');
define('REQUSET_REVIEW_SCORE_Character','score_character');
define('REQUSET_REVIEW_SCORE_Quality','score_quality');
define('REQUSET_REVIEW_SCORE_Update','score_update');
define('REQUSET_REVIEW_CONTENT','content');
define('REQUSET_REVIEW_','');
define('REQUSET_WID','wid');
define('REQUSET_UID','uid');
define('REQUSET_CHAPTER_ID','chapter_id');
define('REQUSET_HISTORY_POSITION','position');
define('REQUSET_HISTORY_PERCENT','percent');
define('REQUSET__','');

/* 정렬 페이징  */
define('OPTION_Popular',0); // 인기순
define('OPTION_New',1); // 최신순 
define('OPTION_Old',2); // 등록순 

define('OPTION_Main',5); // 메인 화면 item  갯수 5개 정도


/* 결과 값  설정  */
// 공통 결과값 변수 
define('SUCCESS','success');
define('MESSAGE','message');
define('STATUS','status');
define('DATA','data');
// status int 조회
define('STATUS_SUCCESS',200);
define('STATUS_FAIL',400);

// 조회 결과값 status , success , message 을 가지는 result 의 이름  
define('RESULT_NAMEDUPLICATE','nameDuplicate');//유저 이름 중복 조회 
define('RESULT_EMAILDUPLICATE','EmailDuplicate');//유저 이메일 중복 조회
define('RESULT_JWT','jwtResult');// JWT 인증 결과 이후 data에 새로운 인증 키를 넣으면 ? 
define('RESULT_UNAMECHANGE','updateUserName');//유저 이름 변경
define('RESULT_AUTHCODESAVE','authCodeSave');// 회원 인증 번호 DB에 저장 
define('RESULT_AUTHCODESEND','authCodeSend');// 회원 인증 번호 이메일 발송

// custom 응답 


// DATA 를 담을 배열의 KEY 이름 
define('DATA_USER','User-Info'); //"email" => $decode->uei , "userName" => $decode->uni , "uid" => $decode->uid
define('DATA_JWT','Ojwt-Token'); // STRING

?>