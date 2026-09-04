package com.novel;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.auth.FirebaseAuthException;
import com.google.firebase.auth.FirebaseToken;
import com.google.firebase.messaging.AndroidConfig;
import com.google.firebase.messaging.FirebaseMessaging;
import com.google.firebase.messaging.FirebaseMessagingException;
import com.google.firebase.messaging.Message;
import com.google.firebase.messaging.Notification;

public class NotificationService {
		private static final Logger logger = LoggerFactory.getLogger(NotificationService.class);

    public void sendNotification(String registrationToken, String title, String body) {
        Notification notification = Notification.builder()
            .setTitle(title)
            .setBody(body)
            .build();

        Message message = Message.builder()
            .setAndroidConfig(
                AndroidConfig.builder()
                .setPriority(AndroidConfig.Priority.HIGH)
                .build()
            )
            .setNotification(notification)
            .setToken(registrationToken)
            .build();
        
        try {

            String response = FirebaseMessaging.getInstance().send(message);
            // System.out.println("Successfully sent message: " + response);
            
            logger.debug("sendNotification() response success message : {}",response);
        } catch (FirebaseMessagingException e) {
            logger.error("sendNotification() error message :{} Token : {}", e.getMessage() , registrationToken);
            logger.error("sendNotification() detail error code : {}  getHttpResponse.statusCode : {}, ", e.getErrorCode(), e.getHttpResponse().getStatusCode());
            logger.error("sendNotification()  getHttpResponse getContent : {} ", e.getHttpResponse().getContent() );
            
            
            try {
                FirebaseToken decodedToken = FirebaseAuth.getInstance().verifyIdToken(registrationToken);
                String uid = decodedToken.getUid();
                logger.error("error sendNotification() uid check {}",  uid);
                logger.error("error sendNotification() getIssuer check {}",  decodedToken.getIssuer());
            } catch (FirebaseAuthException es) {
                logger.error("sendNotification() error in verify Auth Token {}",  es.getMessage());
            }

            
        }
    }

    //uid 는 사용자의 id 
    public void sendDataMessage(String token , String method, String title ,String jsonPayload , int chatRoomUserId){
        Message message = Message.builder()
        .setAndroidConfig(
                AndroidConfig.builder()
                .setPriority(AndroidConfig.Priority.HIGH)
                .build()
            )
        .putData("json", jsonPayload)
        .putData("method", method)
        .putData("title", title)
        .putData("cruid", String.valueOf(chatRoomUserId))
        .setToken(token)
        .build();

    try {
        String response = FirebaseMessaging.getInstance().send(message);
        logger.debug("sendDataMessage() response success message : {}",response);
            
    } catch (FirebaseMessagingException e) {
        logger.error("sendDataMessage() Error sending message: {} " ,e.getMessage());

        try {
            FirebaseToken decodedToken = FirebaseAuth.getInstance().verifyIdToken(token);
            String uid = decodedToken.getUid();
            logger.error("error sendDataMessage() uid check {}",  uid);
            logger.error("error sendDataMessage() getIssuer check {}",  decodedToken.getIssuer());
        } catch (FirebaseAuthException es) {
            logger.error("sendDataMessage() error in verify Auth Token {}",  es.getMessage());
        }
    }
    }
}


