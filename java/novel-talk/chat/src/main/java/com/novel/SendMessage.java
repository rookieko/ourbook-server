package com.novel;

public class SendMessage {
	private String driver = "org.mariadb.jdbc.Driver";
	private String dbURL = "jdbc:mariadb://localhost:3307/ourbook";
	private String user = "your_db_user";
	private String password = "your_db_password";

	public void SendChatToDB(String content , int chat_room_id,int chat_room_user_id,int option, int uid ){
	/* 메세지의 종류를 나타내기 위해 사용 
			0 = 일반 text , 
			1 = 알림 메세지 ,
			2 = 이미지 , 
			3 = ? 영상 , */

			// not use this 
		String query = "INSERT INTO chat ( chat_room_user_id , chat_room_id , content , option )";
	}

}
