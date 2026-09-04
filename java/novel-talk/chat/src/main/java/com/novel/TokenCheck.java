package com.novel;

import io.jsonwebtoken.SignatureAlgorithm;

import java.util.Map;
import java.util.Optional;

import javax.crypto.spec.SecretKeySpec;

import com.novel.json.InitUserDTO;

import io.jsonwebtoken.Jwt;
import io.jsonwebtoken.JwtParser;
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.SignatureAlgorithm;

public class TokenCheck {
	private static String publicKey ="CHANGE_ME_shared_HS256_secret";  // common/config.php 의 JWT_PUBLICKKEY 와 반드시 동일해야 한다
	
	public static Optional<InitUserDTO> checkToDTO(String tokeString) throws Exception{
		Optional<InitUserDTO> resultOptional;
		SignatureAlgorithm sa = SignatureAlgorithm.HS256;
		SecretKeySpec secretKeySpec = new SecretKeySpec(publicKey.getBytes(), sa.getJcaName());

        JwtParser jwtParser = Jwts.parser()
                .verifyWith(secretKeySpec)
                .build();

								try {
            Jwt<?, ?> jwt= jwtParser.parse(tokeString);
            
            Map<String,Object> map = (Map<String, Object>) jwt.getPayload();
						if(map == null){
							System.out.println("null 발생  TokenCheck . check  map");
							return resultOptional = Optional.ofNullable(null);
						}
						// return 할 객체를 생성해준다.
						InitUserDTO initUserDTO = new InitUserDTO(map, jwtParser.isSigned(tokeString));
						return Optional.ofNullable(initUserDTO);
            
        } catch (Exception e) {
            throw new Exception("Could not verify JWT token integrity!", e);
        }

        // System.out.println(jwtParser.isSigned(tokeString));
    }
	
}
