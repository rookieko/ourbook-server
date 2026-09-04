package com.novel.FCM;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import com.novel.DBConstant;
import com.novel.SocketMain;

public class FcmDAO {
	private final static Logger logger = LoggerFactory.getLogger(FcmDAO.class);
	// Fcm  메세지 관리를 위한 DAO , DB 관리도 실행 \
	
	/* fcm Token 을 서버로 부터 받아서 insert 를 진행  */
	public void setTokenToDB(int uid , String token){
		// user id 를 통해서 fcm_token DB 에  uid 와 token 을 저장 , upsert를 사용
		String sqlString = """
			INSERT INTO fcm_tokens (uid, token )
			VALUES ( ? ,	?  ) ON DUPLICATE KEY
			UPDATE token = ? , time = now() ;
		""";
		Integer result = 0;
		logger.debug("setTokenToDB() 서버에 fcm Token 저장 실행  1. uid : {}  2. token :{}",uid,token);
		try (
			Connection conn = DriverManager.getConnection(DBConstant.dbURL, DBConstant.user, DBConstant.password);
			PreparedStatement pstmt = conn.prepareStatement(sqlString,PreparedStatement.RETURN_GENERATED_KEYS);
		) {
			pstmt.setInt(1, uid); // uid ( unique Key )
			pstmt.setString(2, token);// insert
			pstmt.setString(3, token);// update
			int affectedRows = pstmt.executeUpdate();

			if (affectedRows > 0) {
                try (ResultSet rs = pstmt.getGeneratedKeys()) {
                    if (rs.next()) {
												result = rs.getInt(1); // '1'은 첫 번째 열을 의미합니다.
                        // System.out.println("Inserted ID: " + result);
												logger.debug("setTokenToDB() Inserted ID: {}" , result);
                    }
                }
            }
		} catch (SQLException e) {
			// TODO: handle exception
			logger.error(e.getMessage());
		}
	};
		
		// 사용자의 uid 를 통해서 token을 가져온다. 기본적으로 다수의 사용자에게 ( 채팅방 ) list 로 Token 을 보내도록 실행
		// 근데 그렇게 되면 혹시 중간에 문제가 생기는 경우 나머지 유저들의 데이터를 불러올 때 문제가 생기지 않을까? , List -> One String 으로 진행 
		public String getFcmToken(int uid){
			String sqlString = """
					SELECT
					*
					FROM fcm_tokens
					WHERE uid = ? ;
				""";
			try (
				Connection conn = DriverManager.getConnection(DBConstant.dbURL, DBConstant.user, DBConstant.password);
				PreparedStatement pstmt = conn.prepareStatement(sqlString,PreparedStatement.NO_GENERATED_KEYS);
			) {
				pstmt.setInt(1, uid);
				try ( ResultSet rs = pstmt.executeQuery()) {
					// query 진행 이후의 과정 실해
					if(rs.next()){
						String token = rs.getString("token"); // 이거 만약에 빈 값이라면 어떻게 처리해야 하지 ? 
						logger.debug("getFcmToken() Token 확인 {}",token);
						return token;

					}else{
						return "null";
						
					}

				} catch (Exception e) {
					logger.error("getFcmToken() in excuteQuery() , {}" ,e.getMessage());
				}

			} catch (SQLException e) {
				logger.error("getFcmToken() error SQL , {}" , e.getMessage());
			}
			return "null";
		

	};


}
