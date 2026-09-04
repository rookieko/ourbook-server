package com.novel;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class LoadChat {
    // String host = "localhost";
    // String user = "your_db_user";
    // String password = ""
    private String driver = "org.mariadb.jdbc.Driver";
    private String dbURL = "jdbc:mariadb://localhost:3307/ourbook";
    private String user = "your_db_user";
    private String password = "your_db_password";

    public List<String> loadMessages(int chatRoomId) {
        List<String> messages = new ArrayList<>();
        String query = "SELECT message FROM chat WHERE chat_room_id = ? ORDER BY send_date ASC";
        try (Connection conn = DriverManager.getConnection(dbURL, user, password);
             PreparedStatement pstmt = conn.prepareStatement(query)) {

            pstmt.setInt(1, chatRoomId);

            try (ResultSet rs = pstmt.executeQuery()) {
                while (rs.next()) {
                    String message = rs.getString("message");
                    messages.add(message);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return messages;
    }

    // public static void main(String[] args) {
    //     ChatService service = new ChatService();
    //     int chatRoomId = 1; // 예시 채팅방 ID, 실제 채팅방 ID로 교체 필요
    //     List<String> messages = service.loadMessages(chatRoomId);

    //     System.out.println("채팅방 메시지:");
    //     for (String message : messages) {
    //         System.out.println(message);
    //     }
    // }
}
