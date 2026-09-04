package com.novel.FCM;

import java.util.List;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.novel.ChatRoomUserDAO;
import com.novel.NotificationService;
import com.novel.SocketMain;
import com.novel.json.ChatMessageDTO;

public class FcmManager {
		private final static Logger logger = LoggerFactory.getLogger(FcmManager.class);

	// fcm manager 를 통해서 fcm 을 보낸다 notification 
	// 가장 먼저 1. 채팅방 id ->  2. ChatRoomUser DB 접근 -> 3. user_id ( ChatRoomUser DB ) List -> 4.  fcm Token( fcm_tokens ) List  
	// 근데 그냥 처음 부터 join 을 사용하면 되잖아 ?  .. 개별로 따로 처리할 수 있다는 장점은 있겠지만 join 이 더 간결할 꺼 같음 
	public void sendChatFcm(List<Integer> uidList ,String name , String content  ){
		if( uidList == null || uidList.isEmpty()){
			logger.error("sendChatFcm() 앞 broadCastChatFcm 점검 필요 여기서 실행시 문제 null or is empty");
			return;
		}
		FcmDAO fcmDAO = new FcmDAO();
		// 앞에서 broadCastChatFcm 에서 uidList 는 확인 하였음
		for (Integer uid : uidList) {
			logger.debug("sendChatFcm() uid : {}",uid);
			String fcmToken = fcmDAO.getFcmToken(uid);
			if(fcmToken.contains("null")){
				logger.debug("sendChatFcm() fcmToken 이 없음 uid : {}", uid);
			}else{
				//  fcmToken 이 존재할때 
				logger.debug("sendChatFcm() fcmToken 이 있음 uid : {} , fcm_token : {}",uid,fcmToken);
				NotificationService notificationService = new NotificationService();
				notificationService.sendNotification(fcmToken, name, content);
			}
		}
	}

	public void sendDataFcm(List<Integer> uidList ,Integer chatRoomId ,String name , String content  ){
		if( uidList == null || uidList.isEmpty()){
			logger.error("sendDataFcm() 앞 broadCastChatFcm 점검 필요 여기서 실행시 문제 null or is empty");
			return;
		}
		FcmDAO fcmDAO = new FcmDAO();
		// 앞에서 broadCastChatFcm 에서 uidList 는 확인 하였음
		for (Integer uid : uidList) {
			logger.debug("sendDataFcm() uid : {}",uid);
			String fcmToken = fcmDAO.getFcmToken(uid);
			if(fcmToken.contains("null")){
				logger.debug("sendDataFcm() fcmToken 이 없음 uid : {}", uid);
			}else{
				//  fcmToken 이 존재할때 

				//chatRoomUserId 발급  ( 알림 클릭시 사용 목적 )
				ChatRoomUserDAO chatRoomUserDAO =  new ChatRoomUserDAO();
				Integer chatRoomUserId = chatRoomUserDAO.selectChatRoomUserId(uid, chatRoomId);
				logger.debug("sendDataFcm() fcmToken 이 있음 uid : {} , fcm_token : {}",uid,fcmToken);
				NotificationService notificationService = new NotificationService();
				notificationService.sendDataMessage(fcmToken, "chat", name, content , chatRoomUserId);
			}
		}
	}

	
}
