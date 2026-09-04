package com.novel.Test.SocketTest;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.List;
import java.util.concurrent.CopyOnWriteArrayList;


import com.novel.json.ChatMessageDTO;
import com.squareup.moshi.JsonAdapter;
import com.squareup.moshi.Moshi;

public class ChatRoomTest2 {
	private static List<ClientHandler> clients = new CopyOnWriteArrayList<>();
	public Moshi moshi = new Moshi.Builder().build();

	public JsonAdapter<ChatMessageDTO> jsonAdapter = moshi.adapter(ChatMessageDTO.class);
	private static final int PORT = 6080;

	public static void main(String[] args) throws IOException {
		ServerSocket serverSocket = new ServerSocket(PORT);
		System.out.println("Server is listening on port " + PORT);

		try {
			while (true) {
				Socket socket = serverSocket.accept();
				System.out.println("채팅방 연결됨"); // 서버
				ClientHandler clientHandler = new ClientHandler(socket); // 서버가 인식하는 client
				clients.add(clientHandler);
				new Thread(clientHandler).start();
			}
		} finally {
			serverSocket.close();
		}
	}

	public static void broadcastMessage(String message) { // 메세지에 차별을 두려면 여기에 입력해야 겠구나..
		for (ClientHandler client : clients) {
			// if (client.loginOk) {
				client.sendMessage(message);
			// }
		}
	}

	private static class ClientHandler implements Runnable {
		private boolean loginOk;
		private int uid;
		private Socket socket;
		private PrintWriter out;
		private BufferedReader in;
		private String nickName;
		// private ChatRoom currentRoom;

		public ClientHandler(Socket socket) throws IOException {
			this.socket = socket;
			out = new PrintWriter(socket.getOutputStream(), true);
			in = new BufferedReader(new InputStreamReader(socket.getInputStream()));
		}

		@Override
		public void run() {
			
				// Setup nickname
				try {
					while (true) {
				// out.println("닉네임을 입력하세요 ");
					String messageString = in.readLine();
					// System.out.println(nickName + " 채팅방 입장");
					// out.println("닉네임이 " + this.nickName + " 으로 설정 되었습니다.");
					
						broadcastMessage(messageString);
						
					}
					
				} catch (IOException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
		
	}

		public void sendMessage(String message) {
			System.out.println("보낼 message" + message);
			out.println(message);
			out.println();
		}
	}
}
	
