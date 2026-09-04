-- MariaDB dump 10.19  Distrib 10.5.20-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: ourbook
-- ------------------------------------------------------
-- Server version	10.5.20-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `README`
--

DROP TABLE IF EXISTS `README`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `README` (
  `id` int(11) NOT NULL,
  `Message` text DEFAULT NULL,
  `Bitcoin_Address` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Test`
--

DROP TABLE IF EXISTS `Test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Test` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `string` varchar(100) DEFAULT NULL,
  `numbers` int(11) DEFAULT NULL,
  `longDate` date DEFAULT current_timestamp(),
  `longDateTime` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bookmark`
--

DROP TABLE IF EXISTS `bookmark`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookmark` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '북마크 id ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='기본적인 구조는 histroy_view 와 비슷하게 가져갈 예정..';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category_name`
--

DROP TABLE IF EXISTS `category_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '웹소설 카테고리 , 문자열 저장 장소의 PK',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT ' 카테고리 string 의 name , value 값  유저들에게 보여줄 값',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chapter`
--

DROP TABLE IF EXISTS `chapter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chapter` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '회차 , chapter 의 pk , AI',
  `wid` bigint(20) DEFAULT NULL COMMENT 'reference webNovel table''s pk , 1:n FK ',
  `chapter_name` varchar(100) NOT NULL COMMENT 'chapter 의 제목 ,  name',
  `chapter_content` mediumtext NOT NULL COMMENT 'chapter 의 실제 컨텐츠 ',
  `views` int(11) DEFAULT 0 COMMENT '조회수 회차 (chapter ) 를 조회 할 때 마다 1 씩 증가',
  `num` int(10) unsigned DEFAULT 0 COMMENT '회차 번호',
  `createTime` datetime DEFAULT current_timestamp() COMMENT '회차 작성 날짜 정보',
  `word_length` int(10) unsigned DEFAULT 0 COMMENT 'chapter 내용 의 단어의 길이 / 뷰어 page 계산에서 사용',
  `text_length` bigint(20) unsigned DEFAULT 0 COMMENT '회차 내용의 총 text 길이 , // 뷰어 page 처리를 위해 사용',
  PRIMARY KEY (`id`),
  KEY `chapter_webNovel_FK` (`wid`),
  CONSTRAINT `chapter_webNovel_FK` FOREIGN KEY (`wid`) REFERENCES `webnovel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'chat table 의 primary key',
  `content` text DEFAULT NULL COMMENT '채팅 메세지 , 실제 내용',
  `option` tinyint(4) DEFAULT 0 COMMENT '메세지의 종류를 나타내기 위해 사용 \n0 = 일반 text , \n1 = 알림 메세지 ,\n2 = 이미지 , \n3 = ? 영상 ,',
  `chat_file_id` bigint(20) unsigned DEFAULT NULL COMMENT 'chat file table의 id 외래키',
  `send_date` datetime NOT NULL DEFAULT current_timestamp() COMMENT '메세지를 보낸 시간',
  `chat_room_user_id` int(10) unsigned DEFAULT NULL COMMENT 'chat_room_user table 의 id ,  외래키',
  `chat_room_id` int(10) unsigned DEFAULT NULL COMMENT 'chat_room table 의 id  외래키',
  `new_date` bigint(20) unsigned DEFAULT unix_timestamp(current_timestamp()),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=394 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER after_chat_insert
AFTER INSERT
ON chat FOR EACH ROW
BEGIN 
	IF NEW.option = 1 THEN 
		UPDATE chat_room_user SET last_chat_id = NEW.id ,start_chat_id = NEW.id
		WHERE id = NEW.chat_room_user_id;
	ELSEIF NEW.option <= 3 THEN 
		UPDATE chat_room_user SET last_chat_id = NEW.id 
		WHERE id = NEW.chat_room_user_id;
	ELSE 
		SIGNAL SQLSTATE '45000'
    	SET MESSAGE_TEXT = 'you should change in after_chat_insert trigger.';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `chat_file`
--

DROP TABLE IF EXISTS `chat_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_file` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `file_path` text NOT NULL COMMENT '파일 경로',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chat_room`
--

DROP TABLE IF EXISTS `chat_room`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_room` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '채팅방 고유의 id',
  `chat_room_name` text NOT NULL COMMENT '채팅방 이름 , 검색에서 사용',
  `chat_room_wid` bigint(20) DEFAULT NULL COMMENT '채팅방에서 태그 되어있는 웹소설 id , wid',
  `create_date` datetime DEFAULT current_timestamp() COMMENT '채팅방 생성한 날짜',
  `total_user_number` int(10) unsigned DEFAULT 0 COMMENT '채팅방의 총 유저의 수',
  `total_chat_number` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '총 채팅의 숫자 , main 에서 보여줄 정보',
  `introduce` text DEFAULT NULL COMMENT '채팅방 소개말',
  `password` varchar(100) DEFAULT NULL COMMENT '채팅방 참여코드 , 암호',
  `last_chat_id` bigint(20) unsigned DEFAULT NULL COMMENT '마지막 채팅 id 이후 채팅방 가입시 chat room user 의. start chat id 에서 사용됨',
  PRIMARY KEY (`id`),
  KEY `chat_room_webnovel_FK_1` (`chat_room_wid`),
  CONSTRAINT `chat_room_webnovel_FK` FOREIGN KEY (`chat_room_wid`) REFERENCES `webnovel` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `chat_room_webnovel_FK_1` FOREIGN KEY (`chat_room_wid`) REFERENCES `webnovel` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chat_room_user`
--

DROP TABLE IF EXISTS `chat_room_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_room_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'chat room user table 의 primary key',
  `uid` bigint(20) NOT NULL COMMENT 'user table 의 id',
  `start_chat_id` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '들어왔을 때 처음 존재 했던 id',
  `last_chat_id` bigint(20) unsigned DEFAULT NULL COMMENT '사용자가 채팅방 종료전 마지막 불러온 chat id  , 읽음 수 구현을 위해 사용',
  `chat_room_id` int(10) unsigned NOT NULL COMMENT '외래키 chat_room_id 의. 외래키',
  `class` smallint(6) NOT NULL DEFAULT 2 COMMENT '권한을 나타내기 위해 사용 , 0 = 방장 , 1 = 부방장 , 2 = 일반 유저 On   , 5 = 일반 유저 Off  , 6. = 강퇴 유저 재가입 불가',
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_room_user_unique` (`uid`,`chat_room_id`),
  KEY `chat_room_user_chat_room_FK` (`chat_room_id`),
  CONSTRAINT `chat_room_user_chat_room_FK` FOREIGN KEY (`chat_room_id`) REFERENCES `chat_room` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER after_user_joins_chat_room
AFTER INSERT
ON chat_room_user FOR EACH ROW
BEGIN 
	UPDATE chat_room 
	SET chat_room.total_user_number = chat_room.total_user_number +1 
	WHERE chat_room.id = NEW.chat_room_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER after_user_exits_chat_room
AFTER DELETE 
ON chat_room_user FOR EACH ROW
BEGIN 
	UPDATE chat_room 
	SET chat_room.total_user_number = chat_room.total_user_number -1 
	WHERE chat_room.id = OLD.chat_room_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '댓글 table pk , AI',
  `uid` bigint(20) DEFAULT NULL COMMENT 'ref users''s id',
  `wid` bigint(20) DEFAULT NULL COMMENT 'ref webnovel''s id , fk',
  `comment_content` varchar(255) NOT NULL COMMENT '댓글 입력 , 내용 최대 255 글자 ',
  `type` smallint(6) NOT NULL DEFAULT 1 COMMENT 'comment 의 종류 , 1 = 기본 리뷰 , 2 = 기본 부모 리뷰 , 3 = 리뷰의 댓글  , 자식 comment 인지 , 부모 comment 인지',
  `createDate` datetime NOT NULL DEFAULT current_timestamp() COMMENT '리뷰 , 내용 작성 날짜',
  `parent_id` int(10) unsigned DEFAULT 0 COMMENT '부모 리뷰 type 이 3 인 경우만 존재 하게 설정',
  `like_score` int(11) DEFAULT 0 COMMENT 'like_score  좋아요 싫어요에 따라서  + - , 정렬 , webnovel info 화면에서 노출',
  PRIMARY KEY (`id`),
  UNIQUE KEY `comment_unique` (`uid`,`wid`),
  KEY `comment_webNovel_FK` (`wid`),
  CONSTRAINT `comment_user_FK` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `comment_webNovel_FK` FOREIGN KEY (`wid`) REFERENCES `webnovel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comment_rating`
--

DROP TABLE IF EXISTS `comment_rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment_rating` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `point` smallint(6) NOT NULL DEFAULT 0 COMMENT '1 = 긍정 , -1 부정',
  `uid` bigint(20) NOT NULL COMMENT 'user id 고유의 id',
  `comment_id` int(10) unsigned NOT NULL COMMENT '리뷰 id  이웃키',
  PRIMARY KEY (`id`),
  UNIQUE KEY `comment_rating_unique` (`uid`,`comment_id`),
  KEY `comment_rating_comment_FK` (`comment_id`),
  CONSTRAINT `comment_rating_comment_FK` FOREIGN KEY (`comment_id`) REFERENCES `comment` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='리뷰에 대한 유저 반응';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER update_like_score_after_insert
AFTER INSERT
ON comment_rating FOR EACH ROW
BEGIN 
	UPDATE comment 
	SET like_score = like_score + 1 
	WHERE id = NEW.comment_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER update_like_score_after_delete
AFTER DELETE 
ON comment_rating FOR EACH ROW
BEGIN 
	UPDATE comment SET like_score = like_score -1
	WHERE id = OLD.comment_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `emailAuth`
--

DROP TABLE IF EXISTS `emailAuth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailAuth` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'email 인증 테이블 key id AI',
  `uid` bigint(20) NOT NULL COMMENT 'user table''s id',
  `auth_ok` tinyint(1) DEFAULT 0 COMMENT 'email 인증 여부 기본 값 false 이메일 발송시 false, 인증번호 확인시 true',
  `send_time` datetime NOT NULL DEFAULT current_timestamp() COMMENT '이메일 인증번호 생성 시간',
  `auth_code` int(11) DEFAULT NULL COMMENT 'email 인증 확인 코드 ',
  `email` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid_auth` (`uid`),
  CONSTRAINT `uid_auth` FOREIGN KEY (`uid`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fcm_tokens`
--

DROP TABLE IF EXISTS `fcm_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fcm_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) DEFAULT NULL,
  `token` text DEFAULT NULL COMMENT 'fcm  사용자 고유의 token',
  `option` smallint(5) unsigned DEFAULT NULL COMMENT '알림 설정을 저장할 목적으로 사용 할 수 있을 것 같아서 만든 option',
  `time` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `fcm_tokens_unique` (`uid`),
  CONSTRAINT `fcm_tokens_user_FK` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history_view`
--

DROP TABLE IF EXISTS `history_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_view` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '시청 기록의 pk , AI',
  `uid` bigint(20) DEFAULT NULL COMMENT 'reference users''s id , fk ',
  `wid` bigint(20) DEFAULT NULL COMMENT 'reference webnovel''s id , fk ',
  `chapter_id` bigint(20) DEFAULT NULL COMMENT 'reference chapter''s id , fk ',
  `position` int(10) unsigned DEFAULT NULL COMMENT '마지막으로 회차의 본 위치 , percent 예정 ',
  `readDate` datetime NOT NULL DEFAULT current_timestamp() COMMENT '웹소설 회차를 조회한 날짜 , 등록된 날짜',
  `percent` float DEFAULT NULL COMMENT '위치 정보 , scrollview 의 전체의 퍼센트로 계산',
  PRIMARY KEY (`id`),
  UNIQUE KEY `history_view_unique` (`uid`,`chapter_id`),
  KEY `history_view_webNovel_FK` (`wid`),
  KEY `history_view_chapter_FK` (`chapter_id`),
  CONSTRAINT `history_view_chapter_FK` FOREIGN KEY (`chapter_id`) REFERENCES `chapter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `history_view_user_FK` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `history_view_webNovel_FK` FOREIGN KEY (`wid`) REFERENCES `webnovel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER increase_views
AFTER INSERT
ON history_view FOR EACH ROW
BEGIN 
	UPDATE webnovel
	SET total_views = total_views +1
	WHERE id = NEW.wid;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `likes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL COMMENT 'user id 이웃 키',
  `wid` bigint(20) NOT NULL COMMENT 'webnovel id , 이웃키',
  `regisDate` datetime NOT NULL DEFAULT current_timestamp() COMMENT '등록한 날짜',
  `option` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT '옵션 , 0 = 기본 , 1 = 알림 신청한 웹소설',
  PRIMARY KEY (`id`),
  UNIQUE KEY `likes_unique` (`uid`,`wid`,`option`),
  KEY `likes_webnovel_FK` (`wid`),
  CONSTRAINT `likes_user_FK` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `likes_webnovel_FK` FOREIGN KEY (`wid`) REFERENCES `webnovel` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='관심 목록 , 알림 설정 가능';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `novel_rating`
--

DROP TABLE IF EXISTS `novel_rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `novel_rating` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'rating, 별점 평가에 사용할 pk, AI',
  `uid` bigint(20) DEFAULT NULL COMMENT 'ref user''s id , fk',
  `wid` bigint(20) DEFAULT NULL COMMENT 'ref webnovel''s id , fk ',
  `score` float NOT NULL COMMENT 'rating score 최대 값 5 , 최소 값 1 ,  만약 0 이면 평점 삭제 ',
  `type_id` int(11) NOT NULL DEFAULT 0 COMMENT '평점 타입  0 = 총점 , 1 = 세계관 , 2= 캐릭터 , 3 = 스토리',
  `comment_id` int(10) unsigned DEFAULT NULL COMMENT 'comment table 의 id 해당 comment 에 연결되어있는 foreigner key',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rating_unique` (`uid`,`wid`,`type_id`),
  KEY `rating_webNovel_FK` (`wid`),
  KEY `rating_type_name_FK` (`type_id`),
  KEY `novel_rating_comment_FK` (`comment_id`),
  CONSTRAINT `novel_rating_comment_FK` FOREIGN KEY (`comment_id`) REFERENCES `comment` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `rating_type_name_FK` FOREIGN KEY (`type_id`) REFERENCES `type_name` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `rating_user_FK` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `rating_webNovel_FK` FOREIGN KEY (`wid`) REFERENCES `webnovel` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='별점 , 평가에 사용될 rating , 5 점 만점 점수 ,  기본적으로 comment 와 비슷한 구조';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `testTableA`
--

DROP TABLE IF EXISTS `testTableA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testTableA` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Column1` date DEFAULT current_timestamp(),
  `age` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='test table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `type_name`
--

DROP TABLE IF EXISTS `type_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_name` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='review rating 에 들어가게 되는 type 의 문자열 정보를 저장 하고 있는 table . join 필수';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '자동 증가 "user.id" ="uid"',
  `username` text NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `createDate` date NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '계정 상태 기본 값 = 0 , 이메일 인증 성공시 =1 , 기타 상태 = 2...',
  `profileImagePath` text NOT NULL COMMENT '프로필 이미지 ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`) USING HASH
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='유저 계정 정보 저장 ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `view`
--

DROP TABLE IF EXISTS `view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `view` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '조회수 id',
  `wid` bigint(20) DEFAULT NULL COMMENT 'webnovel''s id',
  `cid` bigint(20) DEFAULT NULL COMMENT 'chapter 테이블의 id , foregin key',
  `uid` bigint(20) DEFAULT NULL COMMENT '조회한 사용자의 primary key , user table 의 id',
  `view_date` date DEFAULT current_timestamp() COMMENT '회차를 조회한 날짜 정보 , 인기순 , 기간별 설정을 위해 사용',
  PRIMARY KEY (`id`),
  KEY `view_chapter_FK` (`cid`),
  KEY `view_user_FK` (`uid`),
  KEY `view_webnovel_FK` (`wid`),
  CONSTRAINT `view_chapter_FK` FOREIGN KEY (`cid`) REFERENCES `chapter` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `view_user_FK` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `view_webnovel_FK` FOREIGN KEY (`wid`) REFERENCES `webnovel` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='조회수 정보 저장 table  회차를 열람하게 되면 저장된다 ,책 정보에 기록 , 중복 조회 안되게 설정 ,  인기순 조회 목적으로 이용';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `webNovel_category`
--

DROP TABLE IF EXISTS `webNovel_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webNovel_category` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'category - webNovel 의 카테고리 정보 , 1 : N ',
  `wid` bigint(20) DEFAULT NULL COMMENT 'ref from webNovle'' id ,  foreign key, 1 : N',
  `category_name_id` int(11) DEFAULT NULL COMMENT 'reference category_strings id ,   fk , 1: n',
  PRIMARY KEY (`id`),
  KEY `webNovel_category_category_name_FK` (`category_name_id`),
  CONSTRAINT `webNovel_category_category_name_FK` FOREIGN KEY (`category_name_id`) REFERENCES `category_name` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `webnovel`
--

DROP TABLE IF EXISTS `webnovel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `webnovel` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'webNovel ''s primary key A.I',
  `title` varchar(100) NOT NULL COMMENT 'webNovel''s title non duplicate',
  `summary` text DEFAULT NULL COMMENT 'webNovel''s summary , 줄거리 ',
  `createTime` datetime DEFAULT current_timestamp() COMMENT 'webNovel''s create date time',
  `coverImage` text DEFAULT NULL COMMENT 'webNovel ''s book cover image Path',
  `uid` bigint(20) DEFAULT NULL COMMENT '작가의 uid , User table pk 와 동일 ,  delete 오류 방지로 차원에서 외래키 설정 안함',
  `average_rating` int(10) unsigned DEFAULT NULL COMMENT '평균 별점 , 사용자가 Rating DB 에 insert, update 할 때 마다 조회 하게 설정.',
  `category_id` int(11) DEFAULT NULL COMMENT '임시 category_name 의 id , 복수의 카테고리 설정이 안되어 있음 ',
  `total_num` int(10) unsigned DEFAULT 0 COMMENT '회차 수',
  `total_views` bigint(20) unsigned DEFAULT 0 COMMENT '조회수 회차별 조회수의 총합',
  PRIMARY KEY (`id`),
  UNIQUE KEY `webNovel_unique` (`title`),
  KEY `webNovel_user_FK` (`uid`),
  CONSTRAINT `webNovel_user_FK` FOREIGN KEY (`uid`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'ourbook'
--

--
-- Dumping routines for database 'ourbook'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `user_join_chat_room` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE  PROCEDURE `user_join_chat_room`(
IN mChatRoomUserId INT,
IN mChatRoomId INT
)
BEGIN
	INSERT INTO chat (option,chat_room_user_id,chat_room_id)
	VALUES(1,mChatRoomUserId,mChatRoomId);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-04 12:52:08
