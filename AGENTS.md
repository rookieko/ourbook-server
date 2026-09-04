# 에이전트를 위한 안내

이 저장소를 clone 해서 작업할 때 막히기 쉬운 지점만 적는다.
프로젝트 설명·프로토콜·회고는 `README.md` 에 있다.

## 설정이 없으면 아무것도 동작하지 않는다

비밀값은 저장소에 없다. 먼저 채워야 한다.

```bash
cp common/config.example.php common/config.php
# DB_USERNAME · DB_PASSWORD · JWT_PUBLICKKEY 를 채운다
mysql -u <user> -p ourbook < db/schema.sql
```

**`JWT_PUBLICKKEY`(PHP)와 `TokenCheck.java` 의 키는 같은 값이어야 한다.**
다르면 REST 로그인은 되는데 채팅 접속만 거부되어 원인을 찾기 어렵다.

Java 쪽 DB 접속 정보는 `DBConstant.java`, Firebase 자격증명 경로는
`GOOGLE_APPLICATION_CREDENTIALS` 환경변수로 지정한다.

## 채팅 서버를 띄울 때 걸리는 것

- `pom.xml` 에 shade·assembly 플러그인이 없다. **plain jar 에는 의존성이 들어가지 않으므로**
  classpath 에 직접 넣거나 fat jar 를 만들어야 한다. 아니면 `LoggerFactory` 로딩에서 죽는다
- classpath 에 오래된 guava 가 섞이면 Firebase 가 `MoreExecutors.directExecutor()` 를 못 찾는다.
  **소켓은 정상적으로 뜨고 FCM 만 죽어서 놓치기 쉽다**
- Firebase 초기화 예외는 catch 되므로 로그를 직접 확인해야 한다

## 알아둘 것

- 클라이언트는 별도 저장소다 — [ourbook-android](https://github.com/rookieko/ourbook-android)
- `vendor/` 는 포함하지 않았다. JWT 라이브러리가 필요하다 (`member/memberToken.php` 참조)
- `upload/` 의 실제 이미지는 개인정보라 제외했다. 디렉터리만 있다
- **production-ready 가 아니다.** 남아 있는 보안 부채는 `README.md` 의 회고에 명시해 두었다.
  결함으로 보이는 것을 고치기 전에 먼저 읽을 것
