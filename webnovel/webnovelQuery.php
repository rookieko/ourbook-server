<?php

use PhpMyAdmin\Sql;

include_once $_SERVER['DOCUMENT_ROOT'] . '/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_TOKEN;

/* 
bind param ! 
i	corresponding variable has type int
d	corresponding variable has type float
s	corresponding variable has type string
b	corresponding variable is a blob and will be sent in packets */

// 책 제목 등록을 위한 중복 유무
function isWebnovelTitleDuplicate($title)
{
	global $mysqli;
	$sql_query = "SELECT count(*) AS count FROM webnovel WHERE title = ? ";
	$stmt = $mysqli->prepare($sql_query);
	$stmt->bind_param('s', $title);
	$stmt->execute();
	$result = $stmt->get_result();
	$assoc_row = $result->fetch_assoc();
	if ($assoc_row['count'] > 0) {
		return true;
	} else if ($assoc_row['count'] < 1) {
		return false;
	} else {
		// echo "querry 에러 발생";
		return STATUS_FAIL;
		// return $assoc_row['count'];
	}
};


// 작가 모드 요청의 소유권 검사 — 남의 작품을 수정/삭제하지 못하게 한다
function isWebnovelOwner($wid, $uid)
{
	global $mysqli;
	$sql_query = "SELECT count(*) AS count FROM webnovel WHERE id = ? AND uid = ? ";
	$stmt = $mysqli->prepare($sql_query);
	$stmt->bind_param('ii', $wid, $uid);
	$stmt->execute();
	$result = $stmt->get_result();
	$assoc_row = $result->fetch_assoc();
	return $assoc_row['count'] > 0;
};


/* 글쓴이가 요청하는 책의 정보 from uid */
function getWriterBookData($uid)
{
	global $mysqli;
	$sql_query = "SELECT * FROM webnovel WHERE uid = {$uid} ";
	if ($result = $mysqli->query($sql_query)) {
		return $result;
	} else {
		return null;
	};
};
function get_WriteChapterData($wid)
{
	global $mysqli;
	// 내림차순 정렬 , 회차 순 
	$sql_query = "SELECT * FROM chapter WHERE wid = {$wid} ORDER BY num DESC";
	if ($result = $mysqli->query($sql_query)) {
		return $result;
	} else {
		return null;
	};
};

/* wid 에서 책 데이터 가져요기 */

function getSimpleBookData($wid)
// 웹소설 정보 호출
{
	global $mysqli;

	// 첫 화 보기 chapter_id , 카테고리 등 정보 
	$sql_query = <<<SQL
	SELECT user.username AS writer_name,
	 category_name.name AS category ,
	 chapter.id AS first_chapter_id ,
	 webnovel.category_id AS category_id,
	 webnovel.* 
	FROM webnovel 
	LEFT JOIN user ON user.id = webnovel.uid 
	LEFT JOIN category_name ON category_name.id = webnovel.category_id 
	LEFT JOIN chapter ON chapter.wid = $wid AND chapter.num = 1
	WHERE webnovel.id = $wid 
	SQL;
	if ($result = $mysqli->query($sql_query)) {
		$result_fetch = $result->fetch_assoc();
		return $result_fetch;
	} else {
		return null;
	};
};

function getConstBookDataWithUser($wid, $uid)
{
	global $mysqli;
	$sql_query = "SELECT rating.score , history.chapter_idre  FROM webnovel";
}
//  0 = 인기순 , 1  = 최신순 , 2 = 등록순 , 3 = 선호 작품 등록순 ?
function getMaineBookList()
{
	global $mysqli;
	$sql_query =
		"SELECT user.username AS writer_name, category_name.name AS category , webnovel.* 
	FROM webnovel 
	LEFT JOIN user ON user.id = webnovel.uid 
	LEFT JOIN category_name ON category_name.id = webnovel.category_id 
	ORDER BY total_views DESC LIMIT 10  ";
	if ($result = $mysqli->query($sql_query)) {
		// $result_fetch = $result->fetch_assoc();
		$data = [];
		while ($row = $result->fetch_assoc()) {
			$num = array_push($data, $row);
		}
		return $data;
	} else {
		return null;
	};
};

/* 탐색- 독자 cid (category id ) 에서 책 데이터 가져오기  */
function getCidToBookData($cid, $page, $option)
{
	global $mysqli;

	// 한 페이지당 몇 개의 항목을 보여줄 것인지 설정
	$itemsPerPage = 10;

	// 현재 페이지에 대한 OFFSET 계산
	if ($page > 0) {
		$offset = ($page) * $itemsPerPage;
	} else {
		$offset  = 0;
	}

	$sql_query = "SELECT user.username AS writer_name, category_name.name AS category , webnovel.* 
	FROM webnovel 
	LEFT JOIN user ON user.id = webnovel.uid 
	LEFT JOIN category_name ON category_name.id = webnovel.category_id  
	WHERE webnovel.category_id = {$cid}
	 {$option} 
	LIMIT {$itemsPerPage} 
	OFFSET {$offset}";

	// 결과 처리 및 반환 로직 (예시)
	$result = $mysqli->query($sql_query);
	$data = [];
	$num = 0;
	while ($row = $result->fetch_assoc()) {
		$num = array_push($data, $row);
	}
	if ($num == 0) {
		return 0;
	};
	return $data;
};

/* 탐색- 독자 wid 에서 회차 데이터 가져오기  */
//  0 = 인기순 , 1  = 최신순 , 2 = 등록순 , 3 = 선호 작품 등록순 ?
function getWidToChapterData($wid, $page, $option, $uid)
{
	// 	function get_WriteChapterData($wid)
	// {
	// 	global $mysqli;
	// 	$sql_query = "SELECT * FROM chapter WHERE wid = {$wid} ";
	// 	if ($result = $mysqli->query($sql_query)) {
	// 		return $result;
	// 	} else {
	// 		return null;
	// 	};
	// };
	// 한 페이지당 몇 개의 항목을 보여줄 것인지 설정
	$itemsPerPage = 14;

	// 현재 페이지에 대한 OFFSET 계산
	if ($page > 0) {
		$offset = ($page) * $itemsPerPage;
	} else {
		$offset  = 0;
	}
	//
	global $mysqli;
	$sql_query = "SELECT * FROM chapter WHERE wid = {$wid} {$option} LIMIT {$itemsPerPage} OFFSET {$offset}";
	// 결과 처리 및 반환 로직 (예시)
	$result = $mysqli->query($sql_query);
	$data = [];
	$num = 0;
	while ($row = $result->fetch_assoc()) {
		$num = array_push($data, $row);
	};
	if ($num == 0) {
		return 0;
	};
	return $data;
};




/* 책의 카테고리 정보 가져오기  */
function getCategoryInfo()
{
	global $mysqli;
	$sql_query = "SELECT * FROM category_name";
	$result = $mysqli->query($sql_query);

	return $result;
}

/* INSERT 웹소설, 회차 등록.. */

// 웹소설 등록 메서드
function registerBook($uid, $title, $category_id)
{
	global $mysqli;
	$sql_query = "INSERT INTO webnovel (title , uid , category_id) VALUES (?,?,?)";
	$stmt = $mysqli->prepare($sql_query);
	$stmt->bind_param('sii', $title, $uid, $category_id);
	if ($stmt->execute()) {
		return getResponseArray(200, true, "성공");
	} else {
		return getResponseArray(400, false, "실패 중복 query ");
	}
}

// 회차 등록 메서드
function registerChapter($wid, $chapter_title, $chapter_content, $num, $word_length, $text_length)
{
	global $mysqli;
	$sql_query = "INSERT INTO chapter (wid , chapter_name , chapter_content, num , word_length ,text_length ) VALUES (?,?,?,?,?,?)";
	$stmt = $mysqli->prepare($sql_query);
	$stmt->bind_param('issiii', $wid, $chapter_title, $chapter_content, $num, $word_length, $text_length);
	if ($stmt->execute()) {
		return getResponseArray(200, true, "성공");
	} else {
		return getResponseArray(400, false, "실패 중복 query ");
	}
}

/* UPDATE  */

// 책의 이미지 등록 

function setBookCoverImage(int $wid, String $imagePath)
{
	global $mysqli;
	$query_string = "UPDATE webnovel SET coverImage = ? WHERE id = ?";
	$stmt = $mysqli->prepare($query_string);
	$stmt->bind_param('si', $imagePath, $wid);
	if ($stmt->execute()) {
		return getResponseArray(200, true, "프로필 이미지 저장 성공");
	} else {
		return getResponseArray(400, false, "프로필 이미지 DB 저장 실패");
	}
}
// 총 회차수 정보 수정 
function setBookTotalChapter(int $wid, int $TotalNum)
{
	global $mysqli;
	$query_string = " UPDATE webnovel SET total_num = total_num+? WHERE id = ? ";
	$stmt = $mysqli->prepare($query_string);
	$stmt->bind_param('ii', $TotalNum, $wid);
	if ($stmt->execute()) {
		return true;
	} else {
		return false;
	}
}


/* 마지막 회차 번호 출력 */
function getLastChapterNum(int $wid)
{
	global $mysqli;
	$query_string = "SELECT MAX(num) AS num FROM chapter WHERE wid = ?";
	$stmt = $mysqli->prepare($query_string);
	$stmt->bind_param('i', $wid);
	if ($stmt->execute()) {
		$result = $stmt->get_result();
		if ($fetch = $result->fetch_assoc()) {
			return $fetch['num'];
		} else {
			return 0;
		}
	}
}

/* 리뷰  관련 쿼리 */
/* 리뷰 등록, 관련 정보 가져오기  */
function getUserBookReview($wid, $uid)
{
}

/* 리뷰 - INSERT , update , unique key 로 설정된  type_id ( 세계관 , 총점 , 스토리 등 항목에 따른  type ) , uid , wid 를 통해서 확인 , 중복인 경우 update 로 진행한다.  */
function registerReview($wid, $uid, $score_arr,  float $score_world, float $score_story, float $score_character, float $score_total, string $content)
{
	global $mysqli;


	$mysqli->begin_transaction();

	try {
		/*  score 배열을 차례 대로 input  */
		foreach ($score_arr as $type => $score) {
			# code...
			$query_string =
				"INSERT INTO novel_rating (wid , uid , type_id , score ) VALUES (?,?,?,?) 
			ON DUPLICATE KEY 
			UPDATE 
			score = VALUES(score)"; //ON DUPLICATE KEY UPDATE score = VALUES(score)
			$stmt = $mysqli->prepare($query_string);
			$stmt->bind_param("iiid", $wid, $uid, $type, $score);
			if (!$stmt->execute()) {
				throw new Exception("Insert rating failed by query");
			}
		} // 리뷰 삽입
		$query_string2 =
			"INSERT INTO comment (wid, uid, comment_content,type) 
		VALUES (?, ?, ? ,1)
		ON DUPLICATE KEY
		UPDATE
		comment_content = VALUES(comment_content) ,
		createDate = NOW()"; //type 1 = 일반 리뷰 , 2 ~ 댓글 3 대댓글
		$stmt = $mysqli->prepare($query_string2);
		$stmt->bind_param("iis", $wid, $uid, $content);
		if (!$stmt->execute()) {
			throw new Exception("Insert comment failed by query");
		}

		// 모든 작업 성공, 트랜잭션 커밋
		$mysqli->commit();
		return true;
	} catch (Exception $e) {
		// 오류 발생 시 롤백
		$mysqli->rollback();
		return $e->getMessage();
	}

	/* $query_string_world = "INSERT INTO rating ( wid , uid , type_id , score ) VALUES( {$wid},{$uid},1,{$score_world})";
	$query_string_charcter = "INSERT INTO rating ( wid , uid , type_id , score ) VALUES( {$wid},{$uid},2,{$score_character})";
	$query_string_story = "INSERT INTO rating ( wid , uid , type_id , score ) VALUES( {$wid},{$uid},3,{$score_story})";
	$query_string_total = "INSERT INTO rating ( wid , uid , type_id , score ) VALUES( {$wid},{$uid},0,{$score_total})";
	// 리뷰 내용 contnet 는 `` 가 포함 될 수 있으니 prepare 를 사용한다
	$mysqli->query($query_string_world);
	$mysqli->query($query_string_charcter);
	$mysqli->query($query_string_story);
	$mysqli->query($query_string_total);
	

	$query_string_comment = "INSERT INTO comment ( wid , uid , comment_content ) VALUES ( ? , ? , ? )";


	$stmt = $mysqli->prepare($query_string_comment);
	$stmt->bind_param("iis",$wid,$uid , $content);
	if(	$stmt->execute()){
		return true;
	}else return false; */
}
/* 리뷰 - comment 의 갯수 정보  */
function getReviewCount($wid)
{
	global $mysqli;
	$query_string = <<<SQL
	SELECT 
	COUNT(*) AS review_count
	FROM comment
	WHERE  wid = $wid
	SQL;
	if ($result = $mysqli->query($query_string)) {
		$rs = $result->fetch_assoc();
		return $rs["review_count"];
	} else return false;
}
/* 리뷰 - 책 정보 페이지 에서 보여질 최대 5개의 리뷰 정보  */
function getReviewBest($wid, $uid)
{
	global $mysqli;
	// uid , wid 로 부터 data 를 가져온다
	$sql_query =
		// comment , comment
		"SELECT 
	comment.* ,
	user.username AS user_name,
	user.profileImagePath AS profile_image ,
	novel_rating.score AS score ,
	novel_rating.wid AS rating_wid ,
	EXISTS (
			SELECT 1 FROM comment_rating 
			WHERE comment_rating.comment_id = comment.id 
			AND comment_rating.uid = $uid
		) AS is_like
	FROM 
		comment 
	LEFT JOIN user ON user.id = comment.uid 
	LEFT JOIN novel_rating ON comment.wid = novel_rating.wid
	AND novel_rating.uid = comment.uid
	AND novel_rating.type_id = 0
	WHERE comment.wid = {$wid}
	AND comment.like_score > 0
	ORDER BY comment.like_score DESC
	LIMIT 5";
	// 결과 처리 및 반환 로직 (예시)
	$result = $mysqli->query($sql_query);
	$data = [];
	$num = 0;
	while ($row = $result->fetch_assoc()) {
		$num = array_push($data, $row);
	}
	if ($num == 0) {
		return 0;
	};
	return $data;
}
/* 리뷰 - 탐색 wid 에서 불러온 comment Data  */
function getReviewList($wid, $page, $option, $uid)
{
	// wid , option ( 정렬 ) 를 통해서 불러온 리뷰 data 들을 불러오고 
	// 나머지는 
	$itemsPerPage = 14;

	// 현재 페이지에 대한 OFFSET 계산
	if ($page > 0) {
		$offset = ($page) * $itemsPerPage;
	} else {
		$offset  = 0;
	}
	//
	global $mysqli;
	$sql_query =
		// comment , comment
		"SELECT 
		comment.* ,
		user.username AS user_name,
		user.profileImagePath AS profile_image ,
		novel_rating.score AS score ,
		novel_rating.wid AS rating_wid ,
		EXISTS (
			SELECT 1 FROM comment_rating 
			WHERE comment_rating.comment_id = comment.id 
			AND comment_rating.uid = $uid
		) AS is_like
	FROM 
		comment 
	LEFT JOIN user ON user.id = comment.uid 
	LEFT JOIN novel_rating ON comment.wid = novel_rating.wid
	 AND novel_rating.uid = comment.uid
	 AND novel_rating.type_id = 0
	WHERE comment.wid = {$wid} 
	{$option}
	LIMIT {$itemsPerPage} 
	OFFSET {$offset}";
	// 결과 처리 및 반환 로직 (예시)
	$result = $mysqli->query($sql_query);
	$data = [];
	$num = 0;
	while ($row = $result->fetch_assoc()) {
		$num = array_push($data, $row);
	}
	if ($num == 0) {
		return 0;
	};
	return $data;
}
/* 리뷰 데이터 하나 ,from comment id  */
function getReviewData($comment_id)
{
	global $mysqli;
	$query_string = <<<SQL
	SELECT comment.* 
	FROM comment
	WHERE comment.id = $comment_id
	SQL;
	if ($result = $mysqli->query($query_string)) {
		$row = $result->fetch_assoc();
		return $row;
	} else {
		return getResponseArray(405, false, "getReviewData query error");
	}
}
/* 리뷰 데이터 하나 with novel_rating ,from comment id  */
function getReviewWithRatingData($comment_id, $uid)
{
	global $mysqli;
	// 1 대 다 table 의 join 을 좀 더 공부해 봐야겠다  매번 MAX 로 연산하는게 맞나 싶음
	// 마지막 두 개  quality , update 는 마지막 추가한 값이라 기존에 있던 데이터인 경우 존재하지 않아 반환 값이 null인 경우가 있음 
	// 그래서 null 이 아닌 0으로 반환 
	$query_string = <<<SQL
		SELECT
			c.*, 
			MAX(CASE WHEN n.type_id  = 0 THEN n.score  ELSE NULL END) AS total_score,
			MAX(CASE WHEN n.type_id = 1 THEN n.score  ELSE NULL END) AS world_score,
			MAX(CASE WHEN n.type_id  = 2 THEN n.score  ELSE NULL END) AS character_score,
			MAX(CASE WHEN n.type_id  = 3 THEN n.score  ELSE NULL END) AS story_score,
			MAX(CASE WHEN n.type_id  = 4 THEN n.score  ELSE 0 END) AS quality_score,
			MAX(CASE WHEN n.type_id  = 5 THEN n.score  ELSE 0 END) AS update_score,
			u.username AS username,
			u.profileImagePath AS profileImagePath,
			EXISTS (
			SELECT 1 FROM comment_rating 
			WHERE comment_rating.comment_id = c.id 
			AND comment_rating.uid = $uid
		) AS is_like
		FROM
			comment c
		LEFT JOIN
			novel_rating n ON c.wid = n.wid AND c.uid = n.uid
		LEFT JOIN 
			user u ON c.uid = u.id 
		WHERE c.id = $comment_id
		GROUP BY
			c.id
	SQL;
	if ($result = $mysqli->query($query_string)) {
		$row = $result->fetch_assoc();
		return $row;
	} else {
		return false;
		// getResponseArray(405,false,"getReviewData query error");
	}
}
/* 리뷰 좋아요 data */
function registerReviewLike($uid, $comment_id)
{
	// 리뷰에 좋아요를 설정한다 
	/* 좋아요를 등록하기 위해서 해당 1.  리뷰의 id , 2. 중복 방지를 위해 user id
		3.. (선택 wid )  */
	global $mysqli;
	// insert ignore 오 triger(after insert) 를 사용하여 comment like score 처리
	/*  참고 트리거

	CREATE DEFINER=`your_db_user`@`%` TRIGGER update_like_score_after_insert
AFTER INSERT
ON comment_rating FOR EACH ROW
BEGIN 
	UPDATE comment 
	SET like_score = like_score + 1 
	WHERE id = NEW.comment_id;
END

	 */
	$query_string = <<<SQL
	INSERT IGNORE INTO comment_rating (uid,comment_id,point) 
	VALUES($uid,$comment_id,1)
	SQL;
	if ($mysqli->query($query_string)) {
		return getReviewData($comment_id);
	} else {
		return false;
	}
}
/*  리뷰 좋아요 해제  */
function unregisterReviewLike($uid, $comment_id)
{
	/**
	 * 참고 트리거 registerReviewLike 와 마찬가지로 after delete를 사용하여 삭제 
	 * CREATE DEFINER=`your_db_user`@`%` TRIGGER update_like_score_after_delete
	 * AFTER DELETE 
	 * 	 ON comment_rating FOR EACH ROW
	 * 	 BEGIN 
	 * 	 	UPDATE comment SET like_score = like_score -1
	 * 		WHERE id = OLD.comment_id;
	 * END
	 */
	global $mysqli;
	$query_string = <<<SQL
DELETE FROM comment_rating
WHERE comment_id = $comment_id 
AND uid = $uid
SQL;
	if ($mysqli->query($query_string)) {
		// $mysqli->affected_rows;

		return getReviewData($comment_id);
	} else {
		return false;
	}
}
/* 리뷰 관련 simpleData 가져오기  for book info */
function getSimpleReveiwData($wid)
{
	global $mysqli;
	$query_string = <<<SQL
	SELECT AVG(novel_rating.score) AS score
	FROM novel_rating 
	WHERE novel_rating.wid = $wid 
	AND novel_rating.type_id = 0 ;
	SQL;
	if ($result = $mysqli->query($query_string)) {
		$rs = $result->fetch_assoc();
		if (isset($rs["score"])) {
			return $rs["score"];
		} else {
			return 0;
		}
	} else return false;
}
/* 리뷰 통계 data 가져오기 */
function getReviewStaticData($wid)
{
	global $mysqli;
	$count = getReviewCount($wid); // 총 리뷰 갯수
	$query_string = <<<SQL
	SELECT novel_rating.type_id  AS type_id ,
	novel_rating.wid AS wid,
	AVG(novel_rating.score) AS score
	FROM novel_rating
	WHERE novel_rating.wid  = $wid
	GROUP BY novel_rating.type_id ;
	SQL;
	if (
		$result = $mysqli->query($query_string)
	) {
		$result_array = [];
		$result_array["count"] = $count; // 총 갯수 표현용
		if ($count == 0) {  // 결과가 0 인 경우
			return getResponseArray(203, false, "등록된 리뷰가 없음");
		}

		while ($rs = $result->fetch_assoc()) {
			// var_dump($rs);
			/* 각 type_id 에 따라 다른 key 값을 넣음 */
			switch ($rs["type_id"]) { // type_id 에 따라서 
				case 0:
					$result_array["total_score"] = $rs["score"];
				case 1:
					$result_array["world_score"] = $rs["score"];
					// echo $rs["score"];
					break;
				case 2:
					$result_array["character_score"] = $rs["score"];
					break;
				case 3:
					$result_array["story_score"] = $rs["score"];
					break;
				case 4:
					$result_array["quality_score"] = $rs["score"];
					break;
				case 5:
					$result_array["update_score"] = $rs["score"];
					break;
				default:
					// echo " key = " . $key . " value =	" .$value;
					break;
			}
			// foreach ($rs as $key => $value) { } // 무의미 해보여서 제외함
		}
		return getResponseArrayWithData(200, true, " 조회 성공 , 결과 존재 ", $result_array);
	} else {
		return getResponseArray(401, false, "query 실패");;
	}
}

/* 사용자의 정보 책에 관한 설정 정보  */
function getUserBookOneData($uid, $wid)
{
	//TODO 별점 관련 정보도 가져온다.
	global $mysqli;
	$query_string =
		"SELECT 
		novel_rating.score AS my_score,
		comment.id AS my_comment_id,
		history_view.chapter_id AS recent_view_chapter, 
		history_view.position AS recent_view_locate,
		likes.option AS likes
	FROM 
		user
	LEFT JOIN novel_rating ON user.id = novel_rating.uid AND novel_rating.wid = {$wid} AND novel_rating.type_id = 0
	LEFT JOIN comment ON user.id = comment.uid AND comment.wid = {$wid}
	LEFT JOIN history_view ON user.id = history_view.uid AND history_view.wid = {$wid} 
	LEFT JOIN likes ON user.id = likes.uid AND likes.wid = {$wid}
	WHERE 
		user.id = {$uid}
	ORDER BY 
		history_view.readDate DESC
	LIMIT 1 ";
	if ($result = $mysqli->query($query_string)) {

		return $result->fetch_assoc();
	} else {
		return false;
	}
}

/* read , 회차 정보  */
// * 첫 회차 정보  조회 , 가져오기  
function getFirstChapter($wid)
{
	// 첫 회차의 정보 wid에서 가지고 있는 첫번째 회차 정보를 가져온다 "num" 
	global $mysqli;

	$query_string = <<<SQL
	SELECT * 
	FROM 
		chapter 
	WHERE 
		wid = $wid
	ORDER BY
		num ASC
	LIMIT 1	
	SQL;
	// cid 를 모르는 경우가 있을 까봐 정렬을 하였는데 그럴 필요가 있을까 싶음
	if ($result = $mysqli->query($query_string)) {
		// 하나의 결과  반복문 사용 안해도 됨 
		$rs = $result->fetch_assoc();
		return $rs;
	} else {
		return false;
	}
}

/* chapter id  read , history check  */
function getReadChapterData($chapter_id, $uid)
{
	// chapter id 로 부터 회차의 정보를 가져온다 , chapter_id 포함
	global $mysqli;

	$query_string = <<<SQL
	SELECT chapter.*,
		history_view.id AS history_id ,
		history_view.percent,
		history_view.position,
		history_view.readDate AS history_date
	FROM 
		chapter 
	LEFT JOIN history_view ON chapter.wid = history_view.wid AND history_view.uid = $uid AND history_view.chapter_id = chapter.id
	WHERE 
		chapter.id = $chapter_id
	SQL;
	if ($result = $mysqli->query($query_string)) {
		// 하나의 결과  반복문 사용 안해도 됨 
		$rs = $result->fetch_assoc();
		return $rs;
	} else {
		return false;
	}
}
/* history_view 관련 처리 */
function upsertHistoryView($uid, $wid, $chapter_id, $position, $percent)
{
	// uid 와 chapter_id 를 유니크 constraint 설정으로 duplicate update를 활용한다.
	// 신규,업데이트 모두 여기서 처리
	global $mysqli;
	$query_string = <<<SQL
	INSERT INTO history_view 
		(uid,wid,chapter_id,position,percent)
	VALUES($uid,$wid,$chapter_id,$position,$percent)
	ON DUPLICATE KEY 
	UPDATE 
	position = VALUES(position) ,
	percent = VALUES(percent) , 
	readDate = NOW()
SQL;
	if ($result = $mysqli->query($query_string)) {
		// 하나의 결과  반복문 사용 안해도 됨 
		return true;
	} else {
		return false;
	}
}
