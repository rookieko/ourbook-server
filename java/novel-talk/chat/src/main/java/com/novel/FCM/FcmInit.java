package com.novel.FCM;

import java.io.FileInputStream;
import java.io.IOException;

import com.google.auth.oauth2.GoogleCredentials;
import com.google.firebase.FirebaseApp;
import com.google.firebase.FirebaseOptions;

public class FcmInit {
	
	@Deprecated //사용하지 않은 FirebaseInitializer 가 대체해서 사용 중
	public void init(){

		FirebaseOptions options;
		try {
			options = FirebaseOptions.builder()
			.setCredentials(GoogleCredentials.getApplicationDefault())
			// .setDatabaseUrl("https://<DATABASE_NAME>.firebaseio.com/") // ??
				.build();
				FirebaseApp.initializeApp(options);
		} catch (IOException e) {
			e.printStackTrace();
		}
	
	}
	// public void sdkInit(){
	// 	try (FileInputStream serviceAccount = new FileInputStream("OurBook/java/novel-talk/chat/src/main/java/com/novel/FCM/FcmInit.java")) {
	// 		FirebaseOptions options = new FirebaseOptions.Builder()
	// 		  .setCredentials(GoogleCredentials.fromStream(serviceAccount))
	// 		  .build();
			
	// 		FirebaseApp.initializeApp(options);
	// 	} catch (IOException e) {
	// 		e.printStackTrace();
	// 	}

	// };
	
}
