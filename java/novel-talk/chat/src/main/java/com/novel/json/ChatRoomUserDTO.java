package com.novel.json;

import com.squareup.moshi.Json;

public class ChatRoomUserDTO {


		@Json(name = "class")
		public Integer classInt;

		@Json(name = "chat_room_id")
		public Integer chatRoomId;
		
    @Json(name = "chat_room_user_id")
    public Integer chatRoomUserId;

    @Json(name = "start_read_chat_id")
    public Integer startReadChatId;

    @Json(name = "last_read_chat_id")
    public Integer lastReadChatId;

    @Json(name = "profile_image")
    public String profileImage;

    @Json(name = "user_name")
    public String userName;

    @Json(name = "uid")
    public Integer uid;

	
}
