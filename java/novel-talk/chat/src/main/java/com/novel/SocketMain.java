package com.novel;

import java.io.IOException;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.atomic.AtomicInteger;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.novel.FCM.FirebaseInitializer;
import com.novel.json.ChatMessageDTO;
import com.squareup.moshi.JsonAdapter;
import com.squareup.moshi.Moshi;

public class SocketMain {
	private final static Logger logger = LoggerFactory.getLogger(SocketMain.class);
	
	// private ServerSocket serverSocket ;
	public static Map<Integer, ChatRoom > chatRoomMap = new ConcurrentHashMap<>();
	public  static final Integer PORT = 6080;

	public Moshi moshi = new Moshi.Builder().build();

	public JsonAdapter<ChatMessageDTO> jsonAdapter = moshi.adapter(ChatMessageDTO.class);

	
	
	public static void main(String[] args) throws IOException {
		// ServerSocketHandler serverSocketHandler = new ServerSocketHandler();
		ServerSocket serverSocket = new ServerSocket(PORT);
		try {
			FirebaseInitializer.initializeFirebaseApp();
		} catch (Exception e) {
			// TODO: handle exception
			logger.warn(e.getMessage());
		}
		// System.out.println("Server is listening on port " + PORT);
		logger.debug("Server is listening on port :{}",PORT);
		try {
				while (true) {
						Socket socket = serverSocket.accept();
						// System.out.println("채팅방 연결됨"); //서버
						logger.debug("채팅방 연결됨");
						ClientHandler clientHandler = new ClientHandler(socket); // 서버가 인식하는 client
						
//                clients.add(clientHandler);
						new Thread(clientHandler).start();
				}
		} finally {
				serverSocket.close();
		}
    }
		
	}
	//  class ServerSocketHandler implements Runnable{
	// 	private final Integer PORT = 6080;
	// 	public ServerSocket serverSocket ;
	// 	public ServerSocketHandler() {
	// 		this.serverSocket = new ServerSocket(PORT);
	// 	}
	// 	@Override
	// 	public void run() {
	// 		while (true) {
	// 			Socket socket = serverSocket.accept();
	// 			System.out.println("채팅방 연결됨"); //서버
	// 			ClientHandler clientHandler = new ClientHandler(socket);
	// 			new 
	// 		}
	// 		throw new UnsupportedOperationException("Unimplemented method 'run'");
	// 	}
		
		
	// }

// }
