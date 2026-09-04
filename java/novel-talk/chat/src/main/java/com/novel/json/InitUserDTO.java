package com.novel.json;

import java.util.Map;

import com.squareup.moshi.Json;

public class InitUserDTO {
	@Json(name = "uid")
	private Integer uid ; // 유저 고유 아이디

	@Json(name = "uei")
	private String uei ; // 유저 이메일 

	@Json(name = "uni")
	private String uni ; // 유저 닉네임

	@Json(name = "exp")
	private long exp ;	// 기한 

	@Json(name = "iat")
	private long iat ;

	private boolean isVerify;

	public Integer getUid() {
		return uid;
	}

	public String getUei() {
		return uei;
	}

	public String getUni() {
		return uni;
	}

	public long getExp() {
		return exp;
	}

	public long getIat() {
		return iat;
	}

	
	public boolean isVerify() {
		return isVerify;
	}


	public InitUserDTO(Map<String,?> requestMap, boolean b) {

		isVerify = b;

		for (String keytString : requestMap.keySet()) {
			switch (keytString.toString()) {
				case "uid":
				this.uid = (Integer) requestMap.get(keytString);
					break;
				case "uei":
				this.uei = (String) requestMap.get(keytString);
					break;
				case "uni":
				this.uni = (String) requestMap.get(keytString);
					break;
				case "exp":
				this.exp = (long) requestMap.get(keytString);
					break;
				case "iat":
				this.iat = (long) requestMap.get(keytString);
					break;
				default:
				System.out.println(" else in here"+ keytString + " : " + requestMap.get(keytString));
					break;
			}
		}
		// this.uid = uid;
		// this.uei = uei;
		// this.uni = uni;
		// this.exp = exp;
		// this.iat = iat;
	}

	
	
	
}
