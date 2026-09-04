package com.novel;

public class ChatRoomUser {
	// 채팅방 마다 사용자의 start , last  - chat id 가 다르고 , 자주 사용할 것 같아서 사용함
	private ClientHandler clientHandler;
	private Integer start_chat_id ; 
	private Integer last_chat_id ;


	
	@Deprecated
	public ChatRoomUser(ClientHandler clientHandler, Integer start_chat_id, Integer last_chat_id) {
		this.clientHandler = clientHandler;
		this.start_chat_id = start_chat_id;
		this.last_chat_id = last_chat_id;
	}


	public Integer getStart_chat_id() {
		return start_chat_id;
	}
	public void setStart_chat_id(Integer start_chat_id) {
		this.start_chat_id = start_chat_id;
	}
	public Integer getLast_chat_id() {
		return last_chat_id;
	}
	public void setLast_chat_id(Integer last_chat_id) {
		this.last_chat_id = last_chat_id;
	} 


	




}
