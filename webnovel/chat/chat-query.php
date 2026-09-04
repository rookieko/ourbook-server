<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;


function getUserChatRoomList($uid)
{
	/* 사용자가 들어가있는 채팅방 목록의 정보를 DB 로 부터 가져온다 
	uid 확인 처리는 별도로 처리 */
	global $mysqli;
	/* 필요한 정보 
	1. 웹소설 정보 ( 제목 , 커버 이미지 , wid ( not show ) )
	2. 채팅방 정보 ( 채팅방 제목 , 생성 날짜 , 총 유저의 수 , !( 사용자가 읽지 않은 메세지 정보 - 갯수 , )) 
	3. 보류 TODO 개별 유저 정보 - COUNT ( where chat_id > CRU.last_read_chat.id )
	 */
	// 수정 1차
	// $query_string = <<<SQL
	// SELECT 
	// 	chat_room.chat_room_name AS chat_room_name ,
	// 	chat_room.create_date AS room_create_date ,
	// 	chat_room.total_user_number AS total_user_number,
	// 	chat_room_user.id AS chat_room_user_id,
	// 	chat_room_user.last_chat_id AS user_last_chat_id ,
	// 	chat_room_user.start_chat_id AS user_start_chat_id ,
	// 	chat_room_user.class AS user_class,
	// 	chat_room_user.chat_room_id AS chat_room_id , 
	// 	webnovel.coverImage AS coverImage ,
	// 	webnovel.title AS novel_title,
	// 	webnovel.id AS wid 
	// FROM chat_room_user 
	// LEFT JOIN chat_room ON chat_room.id = chat_room_user.chat_room_id
	// LEFT JOIN webnovel ON webnovel.id = chat_room.chat_room_wid
	// WHERE chat_room_user.uid = ? AND chat_room_user.class < 5
	// 수정 2차
	// 	 SELECT 
	// 	chat_room.chat_room_name AS chat_room_name ,
	// 	chat_room.create_date AS room_create_date ,
	// 	chat_room.total_user_number AS total_user_number,
	// 	chat_room_user.id AS chat_room_user_id,
	// 	chat_room_user.last_chat_id AS user_last_chat_id ,
	// 	chat_room_user.start_chat_id AS user_start_chat_id ,
	// 	chat_room_user.class AS user_class,
	// 	chat_room_user.chat_room_id AS chat_room_id , 
	// 	webnovel.coverImage AS coverImage ,
	// 	webnovel.title AS novel_title,
	// 	webnovel.id AS wid ,
	// 	COUNT( chat.id ) AS n_read_chat_num, 
	// 			(SELECT content 
	// 				FROM chat 
	// 				WHERE chat_room_id = chat_room_user.chat_room_id AND id >= chat_room_user.last_chat_id ORDER BY id DESC LIMIT 1 ) AS last_content
	// FROM chat_room_user 
	// LEFT JOIN chat_room ON chat_room.id = chat_room_user.chat_room_id
	// LEFT JOIN webnovel ON webnovel.id = chat_room.chat_room_wid
	// LEFT JOIN chat ON chat.chat_room_id = chat_room_user.chat_room_id AND chat.id > chat_room_user.last_chat_id
	// WHERE chat_room_user.uid = 22 AND chat_room_user.class < 5
	// GROUP BY chat_room_user.chat_room_id; ;

		
	// SQL;
	$query_string2 = <<<SQL
			SELECT 
				chat_room.id AS chat_room_id,
				chat_room.chat_room_name AS chat_room_name,
				chat_room.create_date AS room_create_date,
				chat_room.total_user_number AS total_user_number,
				chat_room_user.id AS chat_room_user_id,
				chat_room_user.last_chat_id AS user_last_chat_id,
				chat_room_user.start_chat_id AS user_start_chat_id,
				chat_room_user.class AS user_class,
				chat_room_user.chat_room_id AS chat_room_id, 
				webnovel.coverImage AS coverImage,
				webnovel.title AS novel_title,
				webnovel.id AS wid,
				COUNT(chat.id) AS n_read_chat_num,
				(SELECT COUNT(*)
				FROM chat_room_user AS cru
				WHERE cru.chat_room_id = chat_room_user.chat_room_id) AS total_users_in_room,  -- 추가된 집계
				(SELECT content 
				FROM chat 
				WHERE chat_room_id = chat_room_user.chat_room_id AND id >= chat_room_user.last_chat_id 
				ORDER BY id DESC LIMIT 1) AS last_content ,
				(SELECT new_date
				FROM chat 
				WHERE chat_room_id = chat_room_user.chat_room_id
				ORDER BY id DESC LIMIT 1) AS last_chat_time
		FROM chat_room_user 
		LEFT JOIN chat_room ON chat_room.id = chat_room_user.chat_room_id
		LEFT JOIN webnovel ON webnovel.id = chat_room.chat_room_wid
		LEFT JOIN chat ON chat.chat_room_id = chat_room_user.chat_room_id AND chat.id > chat_room_user.last_chat_id
		WHERE chat_room_user.uid = ? AND chat_room_user.class < 5
		GROUP BY chat_room_user.chat_room_id
		ORDER BY new_date DESC;
	SQL;

	$stmt =$mysqli->prepare($query_string2);
	$stmt->bind_param("i",$uid);
	$result_array =[];
	if( $stmt->execute()){
		$result = $stmt->get_result();
		while($row = $result->fetch_assoc()){
			array_push($result_array,$row);
		};
		return $result_array;
	}else{
		return false;
	};
	

}
function getChatRoomDetail($chat_room_id , $uid ){
	global $mysqli;
	/* 필요한 정보 
	1. 채팅방에서 사용자가 읽지 않은 메세지의 갯수 
	2. 채팅방에서 마지막 메세지 내용
 */
$query_string = <<<SQL
		SELECT 
		COUNT( c.id ) AS unread_messages_count, 
				(SELECT content 
					FROM chat 
					WHERE chat_room_id = cru.chat_room_id AND id >= cru.last_chat_id ORDER BY id DESC LIMIT 1 ) AS last_chat_content
		FROM chat AS c
		JOIN chat_room_user AS cru ON c.chat_room_id = cru.chat_room_id
		WHERE cru.uid = ?
		AND cru.chat_room_id = ?
		AND c.id > cru.last_chat_id;

	SQL;
$stmt = $mysqli->prepare($query_string);
$stmt-> bind_param("ii", $uid, $chat_room_id);
if($stmt->execute()){
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	return $row;
}else{
	return null;
}

};
function userExitChatRoom(){
	/* 채팅방 나가도 사용자의 정보들이 계속 존재하는 것을 확인하였음. 
	CRU 의 class 정보를 수정하여 나갔다고 다른 로직에서 처리하면 구현할 수 있을 듯함 
	class  - 0 = 방장 , 1 = 부방장 , 2 = 일반 유저 On   , 5 = 일반 유저 Off  , 6. = 강퇴 유저 재가입 불가 */

	
}

function userUpdataChatRoom(){}

function regisChatRoom($uid , $wid , $name , $introduce ,$password_option , $password ){
	global $mysqli;
// 준비된 문장 생성
if($password_option){
	// 비밀번호 설정에 따라서 값을 변경 

	$stmt = $mysqli->prepare("INSERT INTO chat_room (chat_room_name, chat_room_wid, introduce, password) VALUES (?, ?, TRIM(?), TRIM(?))");
	
	// 파라미터 바인딩
	$stmt->bind_param("siss", $name, $wid, $introduce, $password);
}else{
	$stmt = $mysqli->prepare(
		"INSERT INTO chat_room 
	(chat_room_name, chat_room_wid, introduce) 
	VALUES (?, ?, TRIM(?))");
	// 파라미터 바인딩
	$stmt->bind_param("sis", $name, $wid, $introduce);
}
	if ($result = $stmt->execute()) {
		// 하나의 결과  반복문 사용 안해도 됨 
		// 마지막으로 삽입된 ID 가져오기
		$chat_room_id = $mysqli->insert_id; // 마지막으로 입력한 id  
		$result = joinUserRoom($uid,$chat_room_id,0);
		if( $result){
			return $chat_room_id;
		}else{
			return 300;
		}
	} else {
		return false;
	}
}

function joinUserRoom($uid, $chat_room_id , $user_class){
	global $mysqli;
	try{
	$mysqli->begin_transaction();
	$query_string = <<<SQL
	 INSERT INTO chat_room_user ( uid , chat_room_id , class) 
	 VALUES  ( $uid , $chat_room_id, $user_class)
	SQL;

	if ( $mysqli->query($query_string) === true) {
		// 하나의 결과  반복문 사용 안해도 됨 
		$chatRoomUserId = $mysqli->insert_id;
		$query_string2 = <<<SQL
		CALL user_join_chat_room($chatRoomUserId,$chat_room_id);
		SQL;
		if($mysqli->query($query_string2)){

			// 성공 
			$mysqli->commit();
			return $chatRoomUserId;
		}else{
			$mysqli->rollback();
			error_log('insert was ok but error in insert chat WHERE : joinUserROOM WHO userId:'.$uid);
			return false;
		}
	} else {
		// 실패
		error_log('insert fail chatRoomUser error WHERE : joinUserRoom',0);
		$mysqli->rollback();
		return false;
	}
}catch(mysqli_sql_exception $e){
	$mysqli->rollback();
	error_log('Database error: user id :'.$uid .'message:'. $e->getMessage());
}
	
}


function checkChatRoomPW($chat_room_id,$password){
	
}

// 사용자의 채팅 data를 불러온다 start_chat_id 를 사용하여 사용자가 들어온 시점 부터의 채팅 정보를 불러오게 된다
function getListChat($uid,$chat_room_id){
	global $mysqli;
	$query_string = <<<SQL
	SELECT
	chat.* ,
	chat_room_user.class,
	user.username ,
	user.profileImagePath AS profile_image
	FROM chat
	LEFT JOIN chat_room_user ON chat_room_user.id = chat.chat_room_user_id
	LEFT JOIN user ON user.id = chat_room_user.uid
	WHERE chat.chat_room_id =  ? 
	AND chat.id >= ( SELECT start_chat_id FROM chat_room_user WHERE uid = ? AND chat_room_id = ? )
	SQL;

	$stmt =$mysqli->prepare($query_string);
	$stmt->bind_param("iii",$chat_room_id,$uid,$chat_room_id);
	if($stmt->execute()){
		$result = $stmt->get_result();
		$returnArray = [];
		while($row = $result->fetch_assoc()){
			array_push($returnArray,$row);
		}
		return $returnArray;
	}else{
		return false;
	}
}
function searchOtherChatRoom($uid , $query){
	/* 사용자가 들어가있는 채팅방 목록의 정보를 DB 로 부터 가져온다 
	uid 확인 처리는 별도로 처리 */
	global $mysqli;
	// 검색  , 내가 들어가 있지 않은 채팅방 
	/* 필요한 정보 
	1. 웹소설 정보 ( 제목 , 커버 이미지 , wid ( not show ) )
	2. 채팅방 정보 ( 채팅방 제목 , 생성 날짜 , 총 유저의 수 , !( 사용자가 읽지 않은 메세지 정보 - 갯수 , )) 
	3. 보류 TODO 개별 유저 정보 - COUNT ( where chat_id > CRU.last_read_chat.id )
	 */

	$query_string = <<<SQL
	SELECT 
		chat_room.id AS chat_room_id,
		chat_room.chat_room_name AS chat_room_name ,
		chat_room.create_date AS room_create_date ,
		chat_room.introduce AS chat_room_intro ,
		IF(chat_room.password IS NOT NULL, 1, 0) AS room_lock ,
		chat_room.total_user_number AS total_user_number,
		webnovel.coverImage AS coverImage ,
		webnovel.title AS novel_title,
		(SELECT COUNT(*)
				FROM chat_room_user AS cru
				WHERE cru.chat_room_id = chat_room_user.chat_room_id) AS total_users_in_room,
		webnovel.id AS wid 
	FROM chat_room_user 
	LEFT JOIN chat_room ON chat_room.id = chat_room_user.chat_room_id
	LEFT JOIN webnovel ON webnovel.id = chat_room.chat_room_wid
	WHERE chat_room_user.uid != ? 
	AND ( chat_room.chat_room_name LIKE ? 
	OR webnovel.title LIKE ? ) 
	GROUP BY chat_room.id ;
	SQL;
	// 와일드 카드 
	$searchTerm = "%$query%";
	$stmt =$mysqli->prepare($query_string);
	$stmt->bind_param("iss",$uid,$searchTerm,$searchTerm);
	if($stmt->execute()){
		$result = $stmt->get_result();
		$returnArray = [];
		while($row = $result->fetch_assoc()){
			array_push($returnArray,$row);
		}
		return $returnArray;
	}else{
		return false;
	}
};
function searchMyChatRoom($uid , $query){
	/* 사용자가 들어가있는 채팅방 목록의 정보를 DB 로 부터 가져온다 
	uid 확인 처리는 별도로 처리 */
	global $mysqli;
	// 검색  , 내가 들어가 있는 채팅방 
	/* 필요한 정보 
	1. 웹소설 정보 ( 제목 , 커버 이미지 , wid ( not show ) )
	2. 채팅방 정보 ( 채팅방 제목 , 생성 날짜 , 총 유저의 수 , !( 사용자가 읽지 않은 메세지 정보 - 갯수 , )) 
	3. 보류 TODO 개별 유저 정보 - COUNT ( where chat_id > CRU.last_read_chat.id )
	 */

	$query_string = <<<SQL
	SELECT 
	  chat_room.id AS chat_room_id,
		chat_room.chat_room_name AS chat_room_name ,
		chat_room.create_date AS room_create_date ,
		chat_room.total_user_number AS total_user_number,
		chat_room_user.id AS chat_room_user_id,
		chat_room_user.last_chat_id AS user_last_chat_id ,
		chat_room_user.start_chat_id AS user_start_chat_id ,
		chat_room_user.class AS user_class, 
		webnovel.coverImage AS coverImage ,
		webnovel.title AS novel_title,
		webnovel.id AS wid 
	FROM chat_room_user 
	LEFT JOIN chat_room ON chat_room.id = chat_room_user.chat_room_id
	LEFT JOIN webnovel ON webnovel.id = chat_room.chat_room_wid
	WHERE chat_room_user.uid = ? 
	AND ( chat_room.chat_room_name LIKE ? 
	OR webnovel.title LIKE ? ) ;
	SQL;
	// 와일드 카드 
	$searchTerm = "%$query%";
	$stmt =$mysqli->prepare($query_string);
	$stmt->bind_param("iss",$uid,$searchTerm,$searchTerm);
	if($stmt->execute()){
		$result = $stmt->get_result();
		$returnArray = [];
		while($row = $result->fetch_assoc()){
			array_push($returnArray,$row);
		}
		return $returnArray;
	}else{
		return false;
	}
};

function getChatRoomInfo($uid , $chatRoomId){
	/* 사용자가 들어가있는 채팅방 목록의 정보를 DB 로 부터 가져온다 
	uid 확인 처리는 별도로 처리 */
	global $mysqli;
	// 검색  , 내가 들어가 있지 않은 채팅방 
	/* 필요한 정보 
	1. 웹소설 정보 ( 제목 , 커버 이미지 , wid ( not show ) )
	2. 채팅방 정보 ( 채팅방 제목 , 생성 날짜 , 총 유저의 수 , !( 사용자가 읽지 않은 메세지 정보 - 갯수 , )) 
	3. 보류 TODO 개별 유저 정보 - COUNT ( where chat_id > CRU.last_read_chat.id )
	 */

	$query_string = <<<SQL
	SELECT 
		chat_room.chat_room_name AS chat_room_name ,
		chat_room.create_date AS room_create_date ,
		chat_room.introduce AS chat_room_intro ,
		IF(chat_room.password IS NOT NULL, 1, 0) AS room_lock ,
		IF(chat_room_user.uid = ? , 1 , 0 ) AS in_room ,
		
		chat_room.total_user_number AS total_user_number,
		chat_room_user.last_chat_id AS user_last_chat_id ,
		chat_room_user.start_chat_id AS user_start_chat_id ,
		chat_room_user.class AS user_class,
		chat_room_user.chat_room_id AS chat_room_id , 
		webnovel.coverImage AS coverImage ,
		webnovel.title AS novle_title,
		webnovel.id AS wid 
	FROM chat_room_user 
	LEFT JOIN chat_room ON chat_room.id = chat_room_user.chat_room_id
	LEFT JOIN webnovel ON webnovel.id = chat_room.chat_room_wid
	WHERE chat_room.id = ? ;
	SQL;

	$stmt =$mysqli->prepare($query_string);
	$stmt->bind_param("ii",$uid,$chatRoomId);
	if($stmt->execute()){
		$result = $stmt->get_result();
		// $returnArray = [];
		if($row = $result->fetch_assoc()){
			// array_push($returnArray,$row);
			return $row;
		}
	}else{
		return false;
	}
}


?>