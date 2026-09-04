package com.novel;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.novel.json.ChatRoomUserDTO;

public class ChatRoomUserDAO {

	/** 채팅방 유저의 DB 관련 CRUD 를 진행할 Data Access Object */

	private static final Logger logger = LoggerFactory.getLogger(ChatRoomUserDAO.class);

	/*
	 * 특정 사용자 ( ChatRoomUser id ) 의 마지막으로 읽은 채팅 id 를 update 하기 위해서 사용하는 method
	 * Client socket으로 부터 메세제 method에 refersh-update 라는 호출이 trigger 가 된다
	 * chatMessageDTO 참조
	 * 이미 등록된 chatId의 값이 존재하고 지금 입력하는 채팅 id 보다 큰 경우 무시하게끔 설정
	 */
	public Integer updateLastChatId(Integer chatRoomUserId, Integer chatId) {
		// Connection connection = null;
		// PreparedStatement pstmt = null;
		String queryString = """
				UPDATE chat_room_user
				SET last_chat_id = ?
				WHERE id = ?  AND last_chat_id < ?
				""";
		int result = 0;
		// try {
		try (Connection connection = DriverManager.getConnection(DBConstant.dbURL, DBConstant.user, DBConstant.password);
				PreparedStatement pstmt = connection.prepareStatement(queryString);) {
			// String queryString = "INSERT INTO chat ( chat_room_user_id , chat_room_id ,
			// content , option ) VALUES(?,?,?,?)";
			// 이후 채팅방 user 의 last chat id 를 update 혹은 upsert 하는 sql 문을 작성한다
			// chatRoomUserId 를 통해서 lastChatId 를 업데이트를 하는데 이미 등록된 LastChatId 가 큰 경우 무시하게끔 설정

			// pstmt = connection.prepareStatement(queryString); // -> try-catch 로 대체
			pstmt.setInt(1, chatId);
			pstmt.setInt(2, chatRoomUserId);
			pstmt.setInt(3, chatId);
			result = pstmt.executeUpdate();
			logger.debug(" query updateLastChatId effect row Count : {}", result);

			// 이후에 다른 사용자( socket 이 연결된 ) 에게 모두 data 를 select, 전달할지는 추후 고민하도록 하자 .
		} catch (SQLException e) {
			logger.error("SQl exception error in updateLasChatId message :{} ", e.getMessage());
			e.printStackTrace();
			// retrun result;
		}
		if (result == 0) {
			logger.debug("query error or LastChaId was Bigger Then New updateLasChatId() ");
		}

		return result;

	}

	/*
	 * 채팅방의 다른 사용자들의 start-last chat id 정보를 불러오기 위해서 사용
	 * null 인 경우는 오류
	 */
	public List<ChatRoomUserDTO> selectUserChatReadData(Integer mchatRoomId) {
		// Connection connection = null;
		// PreparedStatement pstmt = null;
		// ResultSet result = null;
		List<ChatRoomUserDTO> chatRoomUserList = null;
		String sqlString = """
				SELECT chat_room_user.*,
				user.username AS username,
				user.profileImagePath AS profile_image
				FROM chat_room_user
				LEFT JOIN user ON user.id = chat_room_user.uid
				WHERE chat_room_id = ?
				AND class <= 5
				""";
		try (
			Connection connection = DriverManager.getConnection(DBConstant.dbURL, DBConstant.user, DBConstant.password);
			PreparedStatement pstmt = connection.prepareStatement(sqlString)
			) {
			chatRoomUserList = new ArrayList<>();
			pstmt.setInt(1, mchatRoomId);
			try (ResultSet result = pstmt.executeQuery()) {
				while (result.next()) {
					Integer startChatId = result.getInt("start_chat_id");
					Integer lastChatId = result.getInt("last_chat_id");
					Integer uid = result.getInt("uid");
					Integer chatRoomId = result.getInt("chat_room_id");
					Integer classInt = result.getInt("class");
					Integer chatRoomUserId = result.getInt("id");
					String profileImagePath = result.getString("profile_image");
					String userName = result.getString("username");

					ChatRoomUserDTO chatRoomUserDTO = new ChatRoomUserDTO();
					chatRoomUserDTO.classInt = classInt;
					chatRoomUserDTO.chatRoomUserId = chatRoomUserId;
					chatRoomUserDTO.profileImage = profileImagePath;
					chatRoomUserDTO.userName = userName;
					chatRoomUserDTO.uid = uid;
					chatRoomUserDTO.chatRoomId = chatRoomId;
					chatRoomUserDTO.startReadChatId = startChatId;
					chatRoomUserDTO.lastReadChatId = lastChatId;

					chatRoomUserList.add(chatRoomUserDTO);
				}
			}
		} catch (SQLException e) {
			logger.error("selectUserChatReadData() SQL Exception 발생: {}", e.getMessage());
			// 추가적인 에러 핸들링 로직을 여기에 포함시킬 수 있습니다.
		}

		return chatRoomUserList;
		// return chatRoomUserList;
	}

	/* 사용자의 chat_room_id 로 부터 uid List를 얻음 , 
	 * 목적 - fcm_tokens 에서 token 을 불러와서 chat fcm 을 보내기 위해서 사용함 
	 */
	public List<Integer> selectUidChatRoom(int chat_room_id){
		String sqlString = """
			SELECT 
			chat_room_user.uid AS uid
			FROM chat_room_user
			WHERE chat_room_user.chat_room_id = ?
			AND class <= 5;
			""";
			List<Integer> uidList = null;
		try (
			Connection connection = DriverManager.getConnection(DBConstant.dbURL, DBConstant.user, DBConstant.password);
			PreparedStatement pstmt = connection.prepareStatement(sqlString);

		) {
			uidList  = new ArrayList<>();
			pstmt.setInt(1, chat_room_id);
			try (ResultSet resultSet = pstmt.executeQuery()) {
				while (resultSet.next()) {
					Integer uid = resultSet.getInt("uid");
					uidList.add(uid);
				}
				return uidList;

			} catch (Exception e) {
				logger.error("selectUidChatRoom() error {} ",e.getMessage());
			}
		} catch (SQLException e) {
			logger.error("selectUidChatRoom() error {}", e.getMessage());
		}
		return new ArrayList<>();
	}


	public Integer selectChatRoomUserId(Integer uid , Integer chatRoomid){
		Integer chatUserId = -1;
		
		String sqlString = """
			SELECT 
			chat_room_user.id AS chat_room_user_id
			FROM chat_room_user
			WHERE chat_room_user.chat_room_id = ? 
			AND chat_room_user.uid =  ? ;
			""";
			List<Integer> uidList = null;
		try (
			Connection connection = DriverManager.getConnection(DBConstant.dbURL, DBConstant.user, DBConstant.password);
			PreparedStatement pstmt = connection.prepareStatement(sqlString);

		) {
			uidList  = new ArrayList<>();
			pstmt.setInt(1, chatRoomid);
			pstmt.setInt(2, uid);
			try (ResultSet resultSet = pstmt.executeQuery()) {
				while (resultSet.next()) {
					chatUserId  = resultSet.getInt("chat_room_user_id");
				}
				return chatUserId;

			} catch (Exception e) {
				logger.error("selectUidChatRoom() error {} ",e.getMessage());
			}
		} catch (SQLException e) {
			logger.error("selectUidChatRoom() error {}", e.getMessage());
		}
		return -1;
	}



}
