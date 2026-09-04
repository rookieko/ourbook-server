package com.novel;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

public class DbTest {
    private String driver = "org.mariadb.jdbc.Driver";

    private String dbURL = "jdbc:mariadb://localhost:3307/ourbook";
    private String user = "your_db_user";
    private String password = "your_db_password";

    public List<String> test() {
        String query = "SELECT * FROM chat_room ";
        List<String> messages = new ArrayList<>();
        try (

                Connection conn = DriverManager.getConnection(dbURL, user, password);
                PreparedStatement pstmt = conn.prepareStatement(query)) {

            pstmt.setInt(1, 41);

            try (ResultSet rs = pstmt.executeQuery()) {
                while (rs.next()) {
                    // rs.getArray(0).;
                    String message = rs.getString("chat_room_name");
                    messages.add(message);
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return messages;
    }

    public static void main(String[] args) {
        try {
            Class.forName("org.mariadb.jdbc.Driver");
        } catch (ClassNotFoundException e) {
            // TODO Auto-generated catch block
            e.printStackTrace();
        }
        DbTest dbTest = new DbTest();
        List<String> result = dbTest.test();
        for (String message : result) {
            System.out.println(message);
        }
    }

}
