package com.novel;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.List;
import java.util.concurrent.CopyOnWriteArrayList;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.novel.FCM.FcmManager;
import com.novel.json.ChatMessageDTO;
import com.novel.json.DefaultDTO;
import com.novel.json.JsonConverter;

public class ChatRoom {
		private final static Logger logger = LoggerFactory.getLogger(ChatRoom.class);

	
	String chatRoomName;
	List<ClientHandler> clientHandlerList;

	public ChatRoom() {
		clientHandlerList = new CopyOnWriteArrayList<>();
	}

	public void addClient(ClientHandler clientHandler) {
		clientHandlerList.add(clientHandler);
	}
	
	public void removeClient(ClientHandler clientHandler){
		clientHandlerList.remove(clientHandler);
	}

	// 같은 ChatRoom 에 존재하는 Socket 들에게 보여줄 Data 
	public void broadCastMessage(String messageString , ClientHandler sender ) {
		if (!clientHandlerList.isEmpty()) {

		// ChatMessageDAO chatMessageDAO = new ChatMessageDAO();
		// long isChatSaved = chatMessageDAO.insertChatMessage(chatMessageDTO);
		// 0 이상이면 성공 chatId임

		// if( isChatSaved > 0){
			for (ClientHandler clientHandler : clientHandlerList) {
				// clientHandler.sendTest(messageString);
				clientHandler.sendText(messageString);
			}
			
		// }else{
		// 	System.out.println("채팅 저장 실패");
		// 	sender.sendText("fail broadCastMessage");
		// 	return;
		// }
		}
	}
	// public void broadCastNotification(ClientHandler sender ,DefaultDTO defaultDTO ,String title){
	// 	if (!clientHandlerList.isEmpty()) {

	// 			for (ClientHandler clientHandler : clientHandlerList) {
	// 				clientHandler.sendNotify(title,defaultDTO.message);
	// 			};
				
	// 		}
	// 	}
		
		// TODO!!! 여기서 부터 실행 notification 정상적으로 실행이 되는지 확인하기
		public void broadCastChatFcm(Integer chat_room_id ,String name, String content){
		new Thread(new Runnable() {

			@Override
			public void run() {
				FcmManager fcmManager = new FcmManager();
				ChatRoomUserDAO chatRoomUserDao = new ChatRoomUserDAO();
				List<Integer> uidList = chatRoomUserDao.selectUidChatRoom(chat_room_id);
				if(uidList == null || uidList.isEmpty()){
					logger.debug("broadCastChatFcm() null or empty in Fcm");
					return;
				};
				// 주석! 그냥 param 에서 부터 String 으로 메세지 json 을 받음 
				// JsonConverter jsonConverter = new JsonConverter();
				// String chatMessageDTOString = jsonConverter.getMessageJsonAdapter().toJson(chatMessageDTO);
				fcmManager.sendChatFcm(uidList, name , content);
			}
			
		}).start();
	}
	public void broadCastDataFcm(Integer chat_room_id ,String name, String content){
		new Thread(new Runnable() {

			@Override
			public void run() {
				logger.debug("fcm Thread 시작 ");
				FcmManager fcmManager = new FcmManager();
				ChatRoomUserDAO chatRoomUserDao = new ChatRoomUserDAO();
				List<Integer> uidList = chatRoomUserDao.selectUidChatRoom(chat_room_id);
				if(uidList == null || uidList.isEmpty()){
					logger.debug("broadCastDataFcm() null or empty in Fcm");
					return;
				};
				// 주석! 그냥 param 에서 부터 String 으로 메세지 json 을 받음 
				// JsonConverter jsonConverter = new JsonConverter();
				// String chatMessageDTOString = jsonConverter.getMessageJsonAdapter().toJson(chatMessageDTO);
				fcmManager.sendDataFcm(uidList,chat_room_id, name, content);
			}
			
		}).start();
	}

	// socket 을 통해
	// public void SendChatToDB(ChatMessageDTO chatMessageDTO) throws SQLException {
	// 	// Connection conn = DriverManager.getConnection(DBConstant.dbURL, DBConstant.user, DBConstant.password);
		
	// 	// String queryString = "INSERT INTO chat ( chat_room_user_id , chat_room_id , content , option ) VALUES(?,?,?,?)";
	// 	// PreparedStatement pstmt = conn.prepareStatement(queryString);
		


	// }

}
