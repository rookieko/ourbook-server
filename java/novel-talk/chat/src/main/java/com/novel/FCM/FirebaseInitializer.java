package com.novel.FCM;

import java.io.FileInputStream;

import com.google.auth.oauth2.GoogleCredentials;
import com.google.firebase.FirebaseApp;
import com.google.firebase.FirebaseOptions;

import io.jsonwebtoken.io.IOException;

public class FirebaseInitializer {
    // 초기화 부분 
	public static void initializeFirebaseApp() throws IOException, java.io.IOException {
        FileInputStream serviceAccount = new FileInputStream(System.getenv().getOrDefault("GOOGLE_APPLICATION_CREDENTIALS", "firebase-service-account.json"));  // 원본은 상대경로 하드코딩이었다. cwd 가 /var/www/html 이어야 했다

        // FirebaseOptions options = new FirebaseOptions.Builder()
        //     .setCredentials(GoogleCredentials.fromStream(serviceAccount))
        //     .setDatabaseUrl("https://your-project-id.firebaseio.com") //?? 이부분 확인 TODO 
        //     .build();

        FirebaseOptions options =  FirebaseOptions.builder()
        .setCredentials(GoogleCredentials.fromStream(serviceAccount))
        .setServiceAccountId("firebase-adminsdk-XXXXX@your-project.iam.gserviceaccount.com")
        // .setDatabaseUrl("https://your-project-id.firebaseio.com") //?? 이부분 확인 TODO 
        .build();

        FirebaseApp.initializeApp(options);
    }
    

}
