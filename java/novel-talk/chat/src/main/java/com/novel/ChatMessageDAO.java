package com.novel;


import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.List;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.novel.FCM.FcmDAO;
import com.novel.json.ChatMessageDTO;

public class ChatMessageDAO {
		private final static Logger logger = LoggerFactory.getLogger(ChatMessageDAO.class);

	
	// TODO  채팅 메세지의 DB 를 관리 하는 ChatMessageDAT  insertChatMessage 를  ChatMessage 의  broadCastMessgae 안에서 실행이 되게하고  boolean 값에 따라 달라지게 처리 한다.

	// 채팅 메세지를 불러오기 위해 사용하는 read chatMessage  List 를  반환한다. 
	public List<ChatMessageDTO> readListChatMessage(){
		return null;
	}
	

	// Socket 을 통해 채팅 메세지를 보내기 전에 DB 에 저장하기 위해서 사용되는 메서드 
	public Integer insertChatMessage(ChatMessageDTO chatMessageDTO){
		// Connection conn = null;
		// PreparedStatement pstmt = null;
		Integer result  = 0;
		String queryString = "INSERT INTO chat ( chat_room_user_id , chat_room_id , content , option ) VALUES(?,?,?,?)";
		try (
			Connection conn = DriverManager.getConnection(DBConstant.dbURL, DBConstant.user, DBConstant.password);
			PreparedStatement pstmt = conn.prepareStatement(queryString,PreparedStatement.RETURN_GENERATED_KEYS);
			){
			
			pstmt.setInt(1, chatMessageDTO.chatRoomUserId);
			pstmt.setInt(2, chatMessageDTO.chatRoomId);
			pstmt.setString(3, chatMessageDTO.content);
			pstmt.setInt(4, chatMessageDTO.option);
	
			int affectedRows = pstmt.executeUpdate();

            if (affectedRows > 0) {
                try (ResultSet rs = pstmt.getGeneratedKeys()) {
                    if (rs.next()) {
												result = rs.getInt(1); // '1'은 첫 번째 열을 의미합니다.
                        // System.out.println("Inserted ID: " + result);
												logger.debug("Inserted ID: {}" , result);
                    }
                }
            }

		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
			logger.error(" error in insertChatMessage  message : {}",e.getMessage());
		}


			if(result != 0 ){
				// System.out.println(" messagae DB 저장 성공 ");
				logger.debug("messagae DB 저장 성공");
			}else{ 
				// System.out.println(" messagae DB 저장 실패  ");
				logger.debug(" messagae DB 저장 실패  ");
			}
				return result;
		
		


	}
	public ChatMessageDTO getChatMessageDTO(Integer chatId){
		ChatMessageDTO chatMessageDTO = null;
		String queryString = """
				SELECT
				chat.* ,
				chat_room_user.class,
				user.username ,
				user.id AS uid,
				user.profileImagePath AS profile_image
				FROM chat
				LEFT JOIN chat_room_user ON chat_room_user.id = chat.chat_room_user_id
				LEFT JOIN user ON user.id = chat_room_user.uid
				WHERE chat.id = ?;
				""";
		try (
			Connection conn = DriverManager.getConnection(DBConstant.dbURL, DBConstant.user, DBConstant.password);
			PreparedStatement pstmt = conn.prepareStatement(queryString,PreparedStatement.RETURN_GENERATED_KEYS);
			){
				 chatMessageDTO = new ChatMessageDTO();
				pstmt.setInt(1, chatId);
				try (ResultSet rs = pstmt.executeQuery()) {
					if(rs.next()){
						chatMessageDTO.chatId = chatId;
						chatMessageDTO.chatRoomId = rs.getInt("chat_room_id");
						chatMessageDTO.chatRoomUserId = rs.getInt("chat_room_user_id");
						chatMessageDTO.option = rs.getInt("option");
						chatMessageDTO.date = rs.getString("send_date");
						chatMessageDTO.newDate = rs.getInt("new_date");
						chatMessageDTO.content = rs.getString("content");
						chatMessageDTO.uid = rs.getInt("uid");
						chatMessageDTO.userName = rs.getString("username");
						chatMessageDTO.profileImage = rs.getString("profile_image");
						
					}
				} catch (Exception e) {
					logger.error("getChatMessageDTO() Exception error {}",e.getMessage());
				}

			}catch(SQLException e){
				e.printStackTrace();
				logger.error("getChatMessageDTO() SQLException error {}",e.getMessage());

			}
			return chatMessageDTO;

		
	}
	public boolean insertChatMessage2(ChatMessageDTO chatMessageDTO){
		String queryString = "INSERT INTO chat ( chat_room_user_id , chat_room_id , content , option ) VALUES(?,?,?,?)";
		int result = 0;
			try (
				Connection connection = DriverManager.getConnection(DBConstant.dbURL, DBConstant.user, DBConstant.password);
				PreparedStatement pstmt = connection.prepareStatement(queryString);
			) {
				pstmt.setInt(1, chatMessageDTO.chatRoomUserId);
				pstmt.setInt(2, chatMessageDTO.chatRoomId);
				pstmt.setString(3, chatMessageDTO.content);
				pstmt.setInt(4, chatMessageDTO.option);
				 result = pstmt.executeUpdate();



				
			} catch (Exception e) {
				// TODO: handle exception
				e.printStackTrace();

			}

			if(result == 1 ){
				System.out.println(" messagae DB 저장 성공 ");
				return true;
			}else{ 
				System.out.println(" messagae DB 저장 실패  ");

				return false;}
		
	}

}
