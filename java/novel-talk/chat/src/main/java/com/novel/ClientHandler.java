package com.novel;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.net.Socket;
import java.sql.*;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Optional;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.atomic.AtomicBoolean;
import java.util.concurrent.atomic.AtomicInteger;

import org.apache.commons.logging.Log;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.novel.FCM.FcmDAO;
import com.novel.json.ChatMessageDTO;
import com.novel.json.ChatRoomUserDTO;
import com.novel.json.DefaultDTO;
import com.novel.json.InitUserDTO;
import com.novel.json.JsonConverter;
import com.squareup.moshi.JsonAdapter;
import com.squareup.moshi.Moshi;

// import org.mariadb.jdbc.Connection;

public class ClientHandler extends Thread {
	private static final Logger logger = LoggerFactory.getLogger(ClientHandler.class);
	NotificationService notificationService = new NotificationService();

	// 채팅방-유저 의  Data (last or start)_chat_id  를 저장 key 은 chat_room_User_id 로 설정 
	// 0 = startChatId , 1= lastChatId  
	private Map<Integer,Integer[]> chatRoomDataMap = new ConcurrentHashMap<>(); // 주의
	
	private String fcmToken;
	private Integer m_uid = 0 ; // 초기값 0  , 실제 0 이면 오류 

	private AtomicInteger retryInteger = new AtomicInteger(0);

	private InitUserDTO mUserDTO; // jwt  페이로드 dto 
	private Socket socket;
	private PrintWriter out;
	private BufferedReader in;
	private  AtomicBoolean initOk = new AtomicBoolean(false); // 인증 성공 , DB를 통해서 채팅방 구현이 완료되고 true 로 전환
	public Moshi moshi = new Moshi.Builder().build();

	// public JsonAdapter<ChatMessageDTO> messageJsonAdapter =
	// moshi.adapter(ChatMessageDTO.class);
	// public JsonAdapter<InitUserDTO> initJsonAdapter =
	// moshi.adapter(InitUserDTO.class);
	public JsonConverter jsonConverter;
	
	private Connection conn;

	public ClientHandler(Socket socket) throws IOException {
		this.socket = socket;
		this.jsonConverter = new JsonConverter();
		out = new PrintWriter(socket.getOutputStream(), true);
		in = new BufferedReader(new InputStreamReader(socket.getInputStream()));
	}

	@Override
	public void run() {
		// String dbURL = "jdbc:mysql://your_database_url";
		// String dbUser = "your_db_user";
		// String dbPassword = "your_db_password";
		// 반복문을 통해서 클라이언트에서 메세지를 보내는 경우 json 파싱을 하고 그 결과에 따라 
		// 1. 메세지 전송 , 2. 다른 메서드 ( 정보 갱신 , 채팅방 가입 , 탈퇴 등 ) 의 처리를 진행한다. 
		while (retryInteger.get() < 4) {

			while (!initOk.get()) {
				try {
					// 처음 메세지
					String messageString = in.readLine();
					logger.debug(" client Handler 첫 message :{}", messageString);
					// System.out.println(" client Handler 첫 message :" + messageString);

					// 수신 메세지 String Check 
					if (messageString == null || messageString.isEmpty()) 
						{
						// System.out.println("null string message ");
						logger.warn("messageString == null or empty");
						break;
					}
					Optional<DefaultDTO> dOptional = jsonConverter.fromJsonDefaultDTO(messageString);
					
					// 객체 Parsing Check
					if (dOptional.isEmpty()) {
						// System.out.println("parsing fail dOptional");
						logger.warn("parsing fail dOptional");
						continue;
					}else if (dOptional.get().getMethod().contentEquals("init")&&!dOptional.get().getFcmToken().isEmpty()) {
						
					}
					
					Optional<InitUserDTO> initUserOptional = TokenCheck.checkToDTO(dOptional.get().getJwtToken());

					// Parsing 한 객체에서 jwt String 을 추출 , jwt Check 
					// jwt Token 에 문제가 있는 경우 여기서 처리 할 수 있음 
					if (initUserOptional.isEmpty()) {
						// System.out.println("parsing fail initUserOptional");
						logger.warn("parsing fail initUserOptional");
						continue;
					}

					InitUserDTO initUserDTO = initUserOptional.get();
					if(initUserDTO == null){
						// System.out.println("initUserDTO == null ");
						logger.warn("initUserDTO == null ");
						continue;
					}
					initClient(initUserDTO.getUid());
						mUserDTO = initUserDTO;
						// System.out.println("초기화 성공 ? : " + initOk);
						logger.debug("초기화 성공 ? : {}",initOk);

						if(initOk.get()){
							// uid 가 초기화 된 이후에 fcm을 저장하는 method 실행 
							setFcmToken(initUserDTO.getUid(),dOptional.get().getFcmToken());							
							// fcm Tocken 저장 
							logger.debug("FCM token"+ getFcmToken());
						}

						break;
					

				} catch (IOException e) {
					// System.out.println("catch 1 IOException");
					logger.error("catch 1 IOException");
					logger.error(e.getMessage());
					// TODO Auto-generated catch block
					e.printStackTrace();
					break;
				} catch (Exception e) {
					logger.error("catch 2 IOException");
					logger.error(e.getMessage());
					// System.out.println("catch 2 Exception " + e.getMessage());
					// TODO Auto-generated catch block
					e.printStackTrace();
					break;
				}

			}
			while (initOk.get()) {
				// System.out.println("init OK Loop start");
				logger.trace("init OK Loop start");
				// 인증 성공 이후 메세지
				String messageString;

				try {
					messageString = in.readLine();
					// System.out.println("message :" + messageString);
					logger.debug("message :{}",messageString);
					if (messageString == null || messageString.isEmpty()) {
						// System.out.println("after initOk , null string message ");
						logger.debug("after initOk , null string message ");
						if(socket.isClosed()){
							// System.out.println(" socket closed aftert initOk , null string message ");
							logger.debug(" socket closed aftert initOk , null string message ");
							break;
						}else{
							// System.out.println(" socket not closed ");
							logger.debug(" socket not closed ");
							break;
							
							// continue;
						}
					}

					Optional<DefaultDTO> dOptional = jsonConverter.fromJsonDefaultDTO(messageString);
					if (messageString.isEmpty()) {
						// System.out.println("init ok but message null");
						logger.debug("init ok but message null");
						continue;
					} else {
						logger.debug("message isEmpty() not empty messge :{}",messageString);
						// System.out.println("message isEmpty() not empty :" + messageString);
					}

					if (dOptional.isEmpty()) {
						// System.out.println("after initOk parsing fail dOptional");
						logger.debug(messageString);
						continue;
					}

					// System.out.println("");

					if (dOptional.get().getMethod().contentEquals("send")) {
						sendMethod(dOptional);
						// System.out.println("send 실행 ");

						// // json parsing to send
						// ChatMessageDTO mChatMessageDTO = dOptional.get().getChatMessage();
						// DefaultDTO defaultDTO = new DefaultDTO();
						// defaultDTO.setChatMessage(mChatMessageDTO);
						// defaultDTO.setMethod("send");

						// String jsonMessage = jsonConverter.getDefaultJsonAdapter().toJson(defaultDTO);

						// SocketMain.chatRoomMap.get(mChatMessageDTO.chatRoomId)
						// 		.broadCastMessage(mChatMessageDTO, jsonMessage , this);
								continue;
					} else if (dOptional.get().getMethod().contentEquals("update_chat_id")) {
						// System.out.println("refresh 실행 ");
						logger.debug("update_chat_id 실행 ");
						// TODO 여기까지 진행하였음 , 채팅방의 인원드르이 정보 start ,
						// last chat id 정보를 갱신하게 설정 
						updateChatIdMethod(dOptional);
						continue;
					} else if (dOptional.get().getMethod().contentEquals("refresh_chat_id")) {
						logger.debug("refresh_chat_id 실행 ");
						String jsons = refreshChatId(dOptional);
						
						continue;
					}
				} catch (IOException e) {
					// TODO Auto-generated catch block
					// System.out.println("exception IOException " + e.getMessage());
					logger.error("exception IOException message {}", e.getMessage());
					e.printStackTrace();
					break;
				}

			}
			// System.out.println("문제 발생 재시도 "+retryInteger);
			logger.debug("문제 발생 재시도 {}", retryInteger);
			if (retryInteger.incrementAndGet() > 3) {
				// System.out.println(retryInteger.get() + " 횟수 3 초과 달성");
				logger.debug(" 횟수 3 초과 달성 횟수 : {}", retryInteger.get());
				// continue;ß
			} else {
				// System.out.println(retryInteger.get() + "횟수 3 이하 ");
				logger.debug(" 횟수 3 이하 달성 횟수 : {}", retryInteger.get());
				
				// continue;
			}
		}

	}
	
	// 사용자가 DefaulDTO 에 method 를 send 로 설정하고 message 객체가 parsing 된 경우 해당 메서드를 실행
	// DB 에 채팅메세지를 저장하고 입력했던 id 를 사용하여 DB를 불러와서 다른 사용자들에게 메세지를 전송 
	public void sendMethod(Optional<DefaultDTO> dOptional){
		// System.out.println("send 실행 ");
		logger.debug("send 실행 ");
		ChatMessageDAO chatMessageDAO = new ChatMessageDAO();
		ChatMessageDTO mChatMessageDTO = dOptional.get().getChatMessage();
		

		Integer chatId = chatMessageDAO.insertChatMessage(mChatMessageDTO);
						// json parsing to send
		if(chatId == 0){
			logger.error("sendMethod 실패 chatId long == 0");
			//TODO : 메세지 전송 실패 처리 과정 추가
			return;
		}
		ChatMessageDTO rChatMessageDTO   = chatMessageDAO.getChatMessageDTO(chatId);
		mChatMessageDTO.chatId = chatId;

		DefaultDTO defaultDTO = new DefaultDTO("send");
		defaultDTO.message = "서버에서 보내기 sendMethod 실행됨";
		
		defaultDTO.setChatMessage(rChatMessageDTO);
		// defaultDTO.setMethod("send");

		String jsonMessage = jsonConverter.getDefaultJsonAdapter().toJson(defaultDTO);

		SocketMain.chatRoomMap.get(mChatMessageDTO.chatRoomId)
				.broadCastMessage(/* mChatMessageDTO, */ jsonMessage , this);
		// SocketMain.chatRoomMap.get(mChatMessageDTO.chatRoomId).broadCastNotification(this, defaultDTO, jsonMessage);
		// SocketMain.chatRoomMap.get(mChatMessageDTO.chatRoomId).broadCastChatFcm(mChatMessageDTO.chatRoomId,rChatMessageDTO.userName, rChatMessageDTO.content);
		SocketMain.chatRoomMap.get(mChatMessageDTO.chatRoomId).broadCastDataFcm(mChatMessageDTO.chatRoomId, mChatMessageDTO.userName, jsonMessage);
		
	}
	/* 
	 * 기존 사용자가 마지막으로 읽은 채팅id 를 update 하기 위해서 사용함 
	 */
	public void updateChatIdMethod(Optional<DefaultDTO> dOptional){
			ChatRoomUserDTO mChatRoomUserDTO = dOptional.get().getChatRoomUserDTO();
			if(mChatRoomUserDTO == null){
				logger.error("null in ChatRoomUser updateChatIdMethod");
				return;
			}
			ChatRoomUserDAO chatRoomUserDAO = new ChatRoomUserDAO();
			
			int result = chatRoomUserDAO.updateLastChatId(mChatRoomUserDTO.chatRoomUserId, mChatRoomUserDTO.lastReadChatId);

			logger.debug("updateChatId row 1 이상시 성공 : {}", result);
			
			refreshChatId(dOptional);
	}

	/* 
	 * 사용자가 읽음 처리를 위해서 채팅방 사용자들의 start , last chat id 를 요청 한 경우 
	 * TODO 여기서 chatRoomDataMap를 사용해야 하는 것이 아닌가 생각됨 
	 */
	public String refreshChatId(Optional<DefaultDTO> dOptional){
		
		ChatRoomUserDTO mChatRoomUserDTO = dOptional.get().getChatRoomUserDTO();
		if(mChatRoomUserDTO == null){
			logger.error("null in ChatRoomUser refreshChatId");
			// return null;
			return null;
		}
		ChatRoomUserDAO chatRoomUserDAO = new ChatRoomUserDAO();
		List<ChatRoomUserDTO> chatUserlist = chatRoomUserDAO.selectUserChatReadData(mChatRoomUserDTO.chatRoomId);
		// method 와 message 를 설정  client 에서 json parsing -> 사용
		DefaultDTO defaultDTO = new DefaultDTO("refresh_chat_id");
		defaultDTO.message = "refresh_chat_id Data from Server";
		// list ChatRoomUser Data 를 입력
		defaultDTO.setListChatRoomUserDTOs(chatUserlist);
		Map<String,Object> dataMap = new HashMap<>();
		dataMap.put("chat_room_id", mChatRoomUserDTO.chatRoomId);
		defaultDTO.setData(dataMap);
		
		String JsonString = jsonConverter.getDefaultJsonAdapter().toJson(defaultDTO);
		// return JsonString;

		logger.debug("refreshChatId 실행 json : {}",JsonString);
		// sendText(JsonString);
		SocketMain.chatRoomMap.get(mChatRoomUserDTO.chatRoomId)
		.broadCastMessage(/* mChatMessageDTO, */ JsonString , this);
		return JsonString;

	}

	/**
	 * 오래됨
	 *  처음 초기화를 목적으로 사용 DB 에서 사용자의 채팅 정보(시작, 마지막 채팅 id ) 를 불러와서 객체화한다.
	 * @param uid 사용자의 user table id
	  */
	public void initClient(int uid) {
		try {

			// 초기 값 설정 
			m_uid = uid;
			// 데이터베이스 연결
			conn = DriverManager.getConnection(DBConstant.dbURL, DBConstant.user, DBConstant.password);

			
			String sqlString = """
					SELECT chat_room_id , start_chat_id , last_chat_id , class		
					FROM chat_room_user 
					WHERE uid = ?
					""";
			// "SELECT chat_room_id , start_chat_id , last_chat_id , class" +
			// "FROM chat_room_user "+
			// "WHERE uid = ?";

			// 클라이언트의 소켓에서 userID를 얻는 로직을 작성해야 합니다.
			// 예시에서는 userID를 상수로 설정합니다.
			// Integer userID = 0;

			// chat_room_user 테이블에서 해당 userID의 정보를 조회합니다.
			PreparedStatement pst = conn.prepareStatement(sqlString);
			pst.setInt(1, uid);
			ResultSet rs = pst.executeQuery();

			
			// uid to Object from DB 결과 처리
			while (rs.next()) {
				Integer chatRoomId = rs.getInt("chat_room_id");
				// System.out.println("client 연결 반복문 진행 " + chatRoomId);
				logger.debug("client 연결 반복문 진행 chat_room_id : {}",chatRoomId);
				// 채팅방객체 ( ChatRoom ) 가 존재 하지 않는 경우 
				if (SocketMain.chatRoomMap.get(chatRoomId) == null) {
					ChatRoom chatRoom = new ChatRoom();
					SocketMain.chatRoomMap.put(chatRoomId, chatRoom);
					SocketMain.chatRoomMap.get(chatRoomId).addClient(this);

					// 
				} else {
					SocketMain.chatRoomMap.get(chatRoomId).addClient(this);

				};
				Integer startChatId = rs.getInt("start_chat_id");
				Integer lastChatId = rs.getInt("last_chat_id");

				initUserChatRoomData(chatRoomId, startChatId, lastChatId);

				// if(chatRoomMap.)

			}
			initOk.set(true);
			// TODO  여기서  start chat id  와  last chat id 를 왜 쓰지 ? 
			for (Integer chatId : chatRoomDataMap.keySet()) { //debuging
				Integer[] impArray = chatRoomDataMap.get(chatId); //debuging
				logger.debug("chat_room_id : {} ", chatId);
				logger.debug("start_chat_id : {} , last_chat_id : {}",impArray[0],impArray[1]);
				
			}
			// logger.debug(sqlString, rs);
		} catch (SQLException e) {
			logger.error("initClienterror SQL error {}", e.getMessage());
			e.printStackTrace();
		}
	}

	private void initUserChatRoomData(Integer chatRoomId , Integer startChatId , Integer lastChatId){
		if(this.chatRoomDataMap.get(chatRoomId) == null){
			logger.debug("(정상) user client 에 chat_id  data 가 없어 초기화 chatroom id :{}",chatRoomId);
			this.chatRoomDataMap.put(chatRoomId,new Integer[]{startChatId,lastChatId});
		}else{
			logger.debug("(비정상) user client 에 chat_id data 가 존재 하지 않음 ");
		}

	}

	public void sendMessage(String jString, ChatMessageDTO requestDTO) {
		// 필수 data 메세지를 보내는 chatRoom id
		//
		ChatMessageDTO chatMessageDTO = requestDTO;
		if (chatMessageDTO == null) {
			// System.out.println("null sendMessage");
			logger.warn("null sendMessage");
			return;
		}
		SocketMain.chatRoomMap.get(chatMessageDTO.chatRoomId).broadCastMessage(/* chatMessageDTO, */ chatMessageDTO.content ,this);
		out.write(jString);

	};

	public void sendText(String messageString) {
		// out.write(messageString);
		out.println(messageString);
		out.println();
		// NotificationService notificationService = new NotificationService();
		// notificationService.init();
		// notificationService.sendNotification(fcmToken, messageString, messageString);
	}

	// public void sendNotify(String title , String content){
	// 	String mcontent = content.trim();
	// 	String mtitle = title.trim();
	// 	notificationService.sendNotification(fcmToken, mtitle, mcontent);
		
	// };
	// /* 기존 notification 으로 json 을 보내려고 하는데 느리고 String 길이가 정해져 있다고 해서 수정함 */
	// public void sendFcmData(String fcmTokens , String methods , String title , String jsons){
	// 	notificationService.sendDataMessage( fcmTokens , methods , title , jsons );
	// }

	public String getFcmToken() {
		return fcmToken;
	}

	public void setFcmToken(Integer uid , String fcmToken) {
		this.fcmToken = fcmToken;
		FcmDAO fcmDAO = new FcmDAO();
		fcmDAO.setTokenToDB(uid, fcmToken);
	}
	

}