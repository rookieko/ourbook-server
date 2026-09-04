package com.novel.json;

import com.squareup.moshi.Json;

public class ChatMessageDTO {

    @Json(name = "id")
    public Integer chatId;

    @Json(name = "chat_room_id")
    public Integer chatRoomId;

    @Json(name = "chat_room_user_id")
    public Integer chatRoomUserId;

		@Json(name = "username")
    public String userName;

    @Json(name = "profile_image")
    public String profileImage;

		@Json(name = "uid")
    public Integer uid;

    @Json(name = "content")
    public String content;

    @Json(name = "send_date")
    public String date; // 예: "8:00"

    @Json(name = "new_date")
    public long newDate; 

    @Json(name = "option")
    public Integer option; //
    	/* 메세지의 종류를 나타내기 위해 사용 
			0 = 일반 text , 
			1 = 알림 메세지 ,
			2 = 이미지 , 
			3 = ? 영상 , */
}
