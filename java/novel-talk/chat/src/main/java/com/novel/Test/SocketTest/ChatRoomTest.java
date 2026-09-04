package com.novel.Test.SocketTest;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.net.ServerSocket;
import java.net.Socket;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.List;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.CopyOnWriteArrayList;
import java.util.concurrent.atomic.AtomicInteger;

// import com.novel.json.ChatMessage;
import com.novel.json.ChatMessageDTO;
import com.squareup.moshi.JsonAdapter;
import com.squareup.moshi.Moshi;

public class ChatRoomTest {
	
	Moshi moshi = new Moshi.Builder().build();


	JsonAdapter<ChatMessageDTO> jsonAdapter = moshi.adapter(ChatMessageDTO.class);

	// out , input stream
	// PrintWriter , InputStreamReader - 공부

	// 채팅방을 생성하고 , 본인이 입력한 경우 나를 나타내는 text를 추가로 나타냈으면 좋겠음

	/*
	 * ConcurrentHashMap은 동시성이 높은 환경에서 키-값 쌍을 관리할 때 사용됩니다.
	 * AtomicInteger는 멀티스레드 환경에서 카운터나 순차 ID 생성 등의 용도로 적합합니다.
	 * CopyOnWriteArrayList는 읽기 연산이 매우 많고, 쓰기 연산이 상대적으로 적은 시나리오에서 유용합니다.
	 */

	private static final int PORT = 6080;
	// private static List<ClientHandler> clients = new CopyOnWriteArrayList<>();
	private static Map<String, ChatRoom> chatRooms = new ConcurrentHashMap<>();
	private static AtomicInteger chatRoomIdGenerator = new AtomicInteger();

	public static void main(String[] args) throws IOException {
		ServerSocket serverSocket = new ServerSocket(PORT);
		System.out.println("Server is listening on port " + PORT);

		try {
			while (true) {
				Socket socket = serverSocket.accept();
				System.out.println("채팅방 연결됨"); // 서버
				ClientHandler clientHandler = new ClientHandler(socket); // 서버가 인식하는 client
				// clients.add(clientHandler);
				new Thread(clientHandler).start();
			}
		} finally {
			serverSocket.close();
		}
	}

	private static class ChatRoom {
		private String id;
		private String name;
		private List<ClientHandler> clients = new CopyOnWriteArrayList<>();

		public ChatRoom(String name) {
			this.id = "Room" + chatRoomIdGenerator.incrementAndGet();
			this.name = name;
		}

		public void addClient(ClientHandler clientHandler) {
			clients.add(clientHandler);
			broadcastMessage(clientHandler.nickName + " 이 채팅방을 입장하였습니다.");
		}

		public void removeClient(ClientHandler clientHandler) {
			clients.remove(clientHandler);
			broadcastMessage(clientHandler.nickName + " 이 채팅방을 떠났습니다.");
		}

		public void broadcastMessage(String message) { // 메세지에 차별을 두려면 여기에 입력해야 겠구나..
			for (ClientHandler client : clients) {
				if (client.loginOk) {
					client.sendMessage(message);
				}
			}
		}

		public void broadcastNormalMessage(String message, ClientHandler sender) { // 메세지에 차별을 두려면 여기에 입력해야 겠구나..
			for (ClientHandler client : clients) {
				if (client.loginOk) {
					// 현재 날짜 구하기 (시스템 시계, 시스템 타임존)
					LocalDateTime now = LocalDateTime.now();
					String formatedNow = now.format(DateTimeFormatter.ofPattern("yyyy년 MM월 dd일 HH시 mm분 ss초"));
					if (client == sender) { // 작성자 와 나의 객체가 같은 경우
						// client.sendMessage(message);
						client.sendMessage(
								"작성시간 : " + formatedNow + "\n 닉네임 : " + sender.nickName + " ( 나 )" + "\n 메세지 : " + message);

					} else { // 내가 아닌 경우
						client.sendMessage("작성시간 : " + formatedNow + "\n 닉네임 : " + sender.nickName + "\n 메세지 : " + message);

					}
				}
			}
		}
	}

	private static class ClientHandler implements Runnable {
		private boolean loginOk;
		private int uid;
		private Socket socket;
		private PrintWriter out;
		private BufferedReader in;
		private String nickName;
		private ChatRoom currentRoom;

		public ClientHandler(Socket socket) throws IOException {
			this.socket = socket;
			out = new PrintWriter(socket.getOutputStream(), true);
			in = new BufferedReader(new InputStreamReader(socket.getInputStream()));
		}

		@Override
		public void run() {
			try {
				// Setup nickname
				out.println("닉네임을 입력하세요 ");
				this.nickName = in.readLine();
				System.out.println(nickName + " 채팅방 입장");
				out.println("닉네임이 " + this.nickName + " 으로 설정 되었습니다.");

				if (nickName != null) {
					// currentRoom.broadcastMessage(nickName + " 가 채팅방에 입장하였습니다.");
					// 로그인 ok 설정
					this.loginOk = true;
				}
				// 닉네임 설정

				String inputLine;

				out.println("채팅방 1.생성 , 2.참가 , 3.나가기 선택하세요");
				while ((inputLine = in.readLine()) != null) {
					// // 현재 날짜 구하기 (시스템 시계, 시스템 타임존)
					// LocalDateTime now = LocalDateTime.now();
					// String formatedNow = now.format(DateTimeFormatter.ofPattern("yyyy년 MM월 dd일
					// HH시 mm분 ss초"));

					// broadcastMessage("작성시간 : "+ formatedNow + "\n 닉네임 : "+ nickName + "\n 메세지 : "
					// + inputLine,this);
					if (inputLine.startsWith("생성")) {
						out.println("생성할 채팅방 이름을 입력하세요");
						String roomName = in.readLine();
						if (roomName != null) {
							ChatRoom room = new ChatRoom(roomName);
							chatRooms.put(room.id, room);
							switchRoom(room);
							out.println("채팅방의 코드 :" + room.id);
						}
					} else if (inputLine.startsWith("참가")) {
						out.println("입장할 채팅방 코드을 입력하세요");
						String roomName = in.readLine();
						if (roomName != null) {
							ChatRoom room = chatRooms.get(String.valueOf(roomName));
							if (room != null) {
								switchRoom(room);
								out.println(room.name + " 채팅방에 입장하였습니다.");
							} else {
								out.println("입력하신 코드의 채팅방을 찾지 못했습니다.");
							}
						}

					} else if (inputLine.startsWith("나가기")) {
						if (currentRoom != null) {
							currentRoom.removeClient(this);
							currentRoom = null;
						}
					} else if (inputLine.startsWith("삭제")) {

					} else {
						// 일반 메세지
						if (currentRoom != null) {
							currentRoom.broadcastNormalMessage(inputLine, this);
						} else {
							// 메세지가 존재 하지 않는 경우
							out.println("메세지를 보내기 전에 채팅방에 입장하세요");
							return;
						}
					}

				}
			} catch (IOException e) {
				System.out.println("Error in client handler: " + e.getMessage());
			} finally {
				try {
					in.close();
					out.close();
					socket.close();
				} catch (IOException e) {
					e.printStackTrace();
				}
				currentRoom.removeClient(this);
				// currentRoom.broadcastMessage(nickName + " 이 채팅방을 떠났습니다.",this);
			}
		}

		private void switchRoom(ChatRoom newRoom) {
			if (currentRoom != null) {
				currentRoom.removeClient(this);
			}
			currentRoom = newRoom;
			newRoom.addClient(this);
		}

		public void sendMessage(String message) {
			out.println(message);
		}
	}
}

