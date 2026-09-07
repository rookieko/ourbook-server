# OurBook — Server

웹소설 플랫폼 OurBook의 서버입니다. **PHP REST API**와, WebSocket 라이브러리 없이 직접 설계한 **Java Raw TCP 채팅 서버**로 이루어져 있습니다.

**클라이언트 저장소:** [ourbook-android](https://github.com/rookieko/ourbook-android)

| | |
|---|---|
| 역할 | 개인 프로젝트 (서버·클라이언트 전부 직접 구현) |
| 최초 개발 | 2023-12 ~ 2024-02 |
| 현재 상태 | **복구·정리 완료 (2026-09).** 실제 서버에서 동작을 재확인함 |
| 성격 | production-ready 서비스가 아니라 **복구·학습 프로젝트**입니다. 알려진 보안 부채가 남아 있고, 아래 회고에 그대로 적어 두었습니다 |

---

## 이 프로젝트에서 가장 볼 만한 것 — Raw TCP 채팅 서버

신입 포트폴리오에서 실시간 채팅은 대개 Socket.IO나 STOMP 같은 **라이브러리 위**에서 만듭니다. 이 프로젝트는 그 아래를 직접 짰습니다.

```text
프로토콜   개행으로 구분된 JSON (newline-delimited JSON)
전송       Raw TCP, 포트 6080
메서드     init · send · update_chat_id · refresh_chat_id
인증       접속 직후 JWT 검증. PHP REST 와 같은 HS256 대칭키를 공유한다
```

**설계에서 실제로 풀어야 했던 문제들**

- **메시지 경계(framing).** TCP는 스트림이라 `send` 한 번이 `recv` 한 번과 대응하지 않습니다. 개행 구분자를 프레임 경계로 정하고 `BufferedReader.readLine()` 으로 잘라 읽습니다
- **접속별 스레드와 브로드캐스트.** `ClientHandler` 가 소켓 하나를 맡고, `ChatRoom` 이 참여자 목록을 들고 있다가 같은 방의 다른 핸들러로 밀어 넣습니다
- **오프라인 폴백.** 소켓이 붙어 있지 않은 참여자에게는 FCM 푸시로 대신 보냅니다 (`FcmManager`)
- **읽음 동기화.** `update_chat_id` / `refresh_chat_id` 로 마지막 읽은 메시지 ID를 맞춥니다

DTO는 Android 쪽과 **의도적으로 복제**되어 있습니다. 공유 모듈을 두지 않은 대신 필드를 바꿀 때 양쪽을 같이 고쳐야 하는 부담을 졌습니다. 지금이라면 스키마를 한 곳에 두고 생성하는 쪽을 택하겠습니다.

---

## 구성

```text
common/        설정 (config.example.php 를 config.php 로 복사해 사용)
db-connect.php mysqli 연결
member/        회원 · 인증 · 프로필     (JWT 발급/검증, 이메일 인증)
webnovel/      작품 · 회차 · 리뷰 · 채팅방 · 탐색 · 검색
upload/        프로필/표지 이미지 (내용은 저장소에 포함하지 않음)
java/novel-talk/chat/   Java Raw TCP 채팅 서버
db/schema.sql  스키마 · 트리거 6개 · 프로시저
```

| 항목 | 수 | 산정 기준 |
|---|---|---|
| PHP 파일 | 57 | `vendor/` 제외 전체. 엔드포인트 외 설정·헬퍼 포함 |
| 채팅 Java 소스 | 28 | `novel-talk/chat/src/main` 아래 `.java` |
| DB 테이블 | 22 | `schema.sql` |
| 트리거 | 6 | 좋아요 점수·조회수·채팅방 인원 자동 갱신 ([표](#트리거-6개)) |

---

## CRUD 로 본 기능

엔드포인트를 나열하면 "많다" 는 것밖에 안 보입니다. **기능별로 CRUD 어디까지 있는지**
세워 보면 이 프로젝트가 실제로 무엇이고 무엇이 아닌지가 드러납니다. 빈 칸도 그대로 둡니다.

| 기능 | C | R | U | D |
|---|---|---|---|---|
| 회원 | `sign-up` | `getUserInfo` | `updateUserName` · `updateUserPW` · `profileImageEdit` | `deleteUser` |
| 작품 | `regist-webnovel` | `get-one-book-data` · `explore/*` · `search/*` | `webnovel-update` (`change=title\|category\|summary`) | `webnovel-update` (`change=delete`) |
| 회차 | `regist-chapter` | `item-webnovel-chapter` · `get-own-chapter` | `chapter-update` (`change=change`) | `chapter-update` (그 외) |
| 리뷰 | `regis-review` | `item-webnovel-comment` · `get-best-review` · `get-own-review` · `get-review-statics` | `regis-review` (`ON DUPLICATE KEY UPDATE`) | **없음** |
| 리뷰 좋아요 | `regis-review-like` | 리뷰 목록 응답에 포함 | — | `regis-review-like` (`like_or_not`) |
| 읽기 위치 | `upsert-read-data` | `get-chapter-read` | `upsert-read-data` | — |
| 채팅방 | `regis-chat-room` | `load-chat-room` · `load-chat-room-detail` · `search-chat-room` | `join-chat-room` | 소프트 삭제 (`class` 컬럼) |
| 채팅 메시지 | **Raw TCP `send`** | `load-chat` | — | — |
| 관심목록 | **없음** | `LEFT JOIN likes` | — | **없음** |

**CRUD 가 완비된 것은 회원 하나뿐입니다.**

### 규약 — REST 동사 대신 액션 파라미터

표를 보면 U 와 D 가 같은 파일에 반복해서 나옵니다. 우연이 아니라 규약이었습니다.

```php
// webnovel-update.php
switch ($_POST['change']) {
    case 'title':    … UPDATE webnovel SET title = ? …
    case 'category': … case 'summary': …
    case 'delete':   … DELETE FROM webnovel …
}
```

`chapter-update.php` 는 `change == "change"` 면 UPDATE, 아니면 DELETE.
`regis-review-like.php` 는 `like_or_not` 으로 등록과 해제를 가릅니다.
읽기 위치는 이름부터 `upsert-read-data` 이고, 리뷰는 `ON DUPLICATE KEY UPDATE` 로
C 와 U 를 합쳤습니다.

`webnovel-update.php` 의 필드별 `case` 는 사실상 **PATCH** 를 엔드포인트 하나로 표현한 것입니다.

지금이라면 자원별 경로에 HTTP 동사를 매핑하겠습니다. 다만 이건 취향 문제가 아니라
**실제로 손해가 있습니다** — 액션 이름이 본문 안에 있으니 URL 만 봐서는 무엇을 하는지 알 수 없고,
클라이언트가 오타를 내면 `switch` 의 어느 `case` 에도 안 걸려 **조용히 아무 일도 일어나지 않습니다.**

### 규약에서 빠진 곳

- **리뷰에 삭제가 없습니다.** 규약대로였다면 `regis-review.php` 에 `change=delete` 가 있었을 자리입니다.
  스키마에는 의도가 남아 있습니다 — `novel_rating.score` 주석이 *"만약 0 이면 평점 삭제"* 인데
  `score == 0` 을 처리하는 코드가 없습니다
- **삭제만 소프트/하드가 갈립니다.** 채팅방은 `class` 컬럼을 바꾸는 소프트 삭제인데
  나머지는 하드 `DELETE` 입니다. 트리거는 하드 삭제 기준이라 어긋납니다 — 아래 *회고* 4번
- **관심목록은 읽기만 됩니다.** 서버에 `INSERT INTO likes` · `DELETE FROM likes` 가 없고,
  `LEFT JOIN likes` 로 현재 상태를 내려 주기만 합니다. 클라이언트 쪽 상황은
  [앱 저장소의 *알려진 한계*](https://github.com/rookieko/ourbook-android#관심목록은-표시만-되고-등록해제가-안-됩니다)에 적었습니다

---

## 실행 방법

비밀값은 저장소에 없습니다. **아래를 채우지 않으면 동작하지 않습니다.**

### 1. PHP API

```bash
cp common/config.example.php common/config.php
# DB_USERNAME · DB_PASSWORD · JWT_PUBLICKKEY 를 채운다
mysql -u <user> -p ourbook < db/schema.sql
```

`vendor/` 는 포함하지 않았습니다. JWT 라이브러리가 필요합니다 (`member/memberToken.php` 의 require 참조).

### 2. Java 채팅 서버

```bash
cd java/novel-talk/chat
mvn dependency:build-classpath -DincludeScope=runtime -Dmdep.outputFile=target/cp.txt
mvn package
java -cp "target/classes:$(cat target/cp.txt)" com.novel.SocketMain
```

**주의 두 가지 — 둘 다 실제로 겪은 함정입니다.**

- `pom.xml` 에 shade/assembly 플러그인이 없어 **plain jar 에는 의존성이 들어가지 않습니다.** classpath 에 의존성을 직접 넣어야 `LoggerFactory` 로딩에서 죽지 않습니다
- `FirebaseInitializer` 가 자격증명 파일을 **상대경로**로 엽니다. 원본은 웹 루트에서 실행하는 것을 전제했습니다. 이 저장소에서는 `GOOGLE_APPLICATION_CREDENTIALS` 환경변수로 바꿔 두었습니다. **Firebase 초기화 예외는 catch 되므로 소켓은 떠도 FCM만 조용히 죽어 있을 수 있습니다**

`JWT_PUBLICKKEY`(PHP)와 `TokenCheck.java` 의 키는 **같은 값이어야** 합니다. 다르면 REST 로그인은 되는데 채팅 접속만 거부됩니다.

---

## 리뷰·평점 — 다축 평가와 트리거 집계

채팅 다음으로 볼 만한 곳입니다. `webnovel/review/` 에 엔드포인트 6개가 있습니다.

| 파일 | 줄 | 하는 일 |
|---|---:|---|
| `regis-review.php` | 44 | 리뷰 등록 · 재평가 |
| `item-webnovel-comment.php` | 44 | 작품별 리뷰 목록 (정렬 옵션) |
| `regis-review-like.php` | 38 | 리뷰 좋아요 (= 댓글 평가) |
| `get-review-statics.php` | 37 | 축별 평점 통계 |
| `get-own-review.php` | 30 | 리뷰 단건 조회 |
| `get-best-review.php` | 23 | 베스트 리뷰 |

### `registerReview()` — 이 저장소의 트랜잭션 참조 구현

`webnovel/webnovelQuery.php:304`. 리뷰 하나를 등록하는 일이 **두 테이블에 걸쳐 있습니다.**
다섯 축 점수는 `novel_rating` 에 `type_id` 로 나뉘어 들어가고, 본문은 `comment` 로 갑니다.
둘 중 하나만 남으면 "점수는 있는데 글이 없는" 리뷰가 생깁니다.

- `begin_transaction()` 으로 묶고, 예외에서 `rollback()`
- 전 구간 prepared statement — `bind_param("iiid")` / `bind_param("iis")`
- 재평가는 `INSERT … ON DUPLICATE KEY UPDATE` 로 **덮어쓰기**. 중복 행이 쌓이지 않습니다

이 프로젝트의 다른 곳에는 문자열 연결로 만든 SQL 이 남아 있습니다(아래 *회고* 2번 참고).
그래서 **여기를 기준으로 삼고 나머지를 이쪽으로 옮기는 중**이라고 읽는 편이 정확합니다.

### 좋아요 집계는 애플리케이션이 아니라 트리거가 한다

`:531` `registerReviewLike()` / `:561` `unregisterReviewLike()` 는 `comment_rating` 에
`INSERT IGNORE` / `DELETE` 만 합니다. `comment.like_score` 의 증감은 **AFTER INSERT ·
AFTER DELETE 트리거**가 처리합니다. 앱이 집계를 직접 더하지 않으므로 동시 요청에도 어긋나지 않습니다.

### 그런데 평균 점수는 트리거가 만들지 않습니다

**좋아요는 평균 점수를 바꾸지 않습니다.** 위 트리거가 건드리는 것은 `comment.like_score`,
즉 그 리뷰가 받은 좋아요 수 하나뿐입니다. 평균이 움직이는 계기는 리뷰 등록과 재평가뿐입니다.

그리고 그 평균은 **저장돼 있지 않습니다.** 조회할 때마다 다시 집계합니다.

```sql
-- getSimpleReveiwData(): 작품 상세에 뜨는 종합 점수
SELECT AVG(novel_rating.score) FROM novel_rating
WHERE novel_rating.wid = ? AND novel_rating.type_id = 0;

-- getReviewStaticData(): 리뷰 목록 상단의 축별 통계
SELECT type_id, AVG(novel_rating.score) FROM novel_rating
WHERE novel_rating.wid = ? GROUP BY novel_rating.type_id;
```

### 같은 프로젝트 안에 비정규화 두 방식이 있습니다

| | 방식 | 갱신 주체 |
|---|---|---|
| 좋아요 수 | 컬럼에 저장 (`comment.like_score`) | **트리거** |
| 조회수 | 컬럼에 저장 (`webnovel.total_views`) | **트리거** |
| 평균 점수 | 저장 안 함 | 조회할 때마다 `AVG()` |

각각 이유는 있습니다. 좋아요와 조회수는 쓰기가 잦고 읽기는 더 잦아 캐시가 이득이고,
평점은 작품당 리뷰가 몇 건이라 매번 집계해도 쌉니다.

**문제는 그 판단이 코드 어디에도 안 적혀 있다는 것입니다.** 게다가 `webnovel` 테이블에는
`average_rating` 컬럼이 **있습니다.** 주석까지 달려 있습니다 —
*"평균 별점, 사용자가 Rating DB 에 insert, update 할 때 마다 조회 하게 설정"*.

그런데 **아무도 그 컬럼을 읽지도 쓰지도 않습니다. 34행 전부 NULL 입니다.**
클라이언트에는 대응 필드(`SearchItemDTO.average_rating`)가 있어 항상 null 을 받습니다.

캐시 컬럼을 설계해 두고 채우지 않은 채, 실제로는 매번 집계하는 쪽으로 굴러온 것입니다.
동작에는 문제가 없지만 **읽는 사람에게는 미완성으로 보입니다.** 지금 고른다면 컬럼을 지워
"매번 집계한다" 는 의도를 분명히 하겠습니다.

### 트리거 6개

`db/schema.sql` 에 있는 전부입니다.

| 트리거 | 시점 | 하는 일 |
|---|---|---|
| `after_chat_insert` | `chat` INSERT | 채팅 저장 후처리 |
| `after_user_joins_chat_room` | `chat_room_user` INSERT | 채팅방 인원수 +1 |
| `after_user_exits_chat_room` | `chat_room_user` DELETE | 채팅방 인원수 −1 |
| `update_like_score_after_insert` | `comment_rating` INSERT | `comment.like_score` +1 |
| `update_like_score_after_delete` | `comment_rating` DELETE | `comment.like_score` −1 |
| `increase_views` | `history_view` INSERT | `webnovel.total_views` +1 |

**`after_user_exits_chat_room` 만 앱 동작과 어긋납니다** — 앱은 하드 삭제를 쓰지 않습니다.
아래 *회고* 4번을 보십시오.

### 평가 축은 5개인데 스키마 주석은 4개입니다

`novel_rating.type_id` 주석은 `0 = 총점 , 1 = 세계관 , 2 = 캐릭터 , 3 = 스토리` 로 멈춰 있습니다.
실제 데이터는 `type_id` 0~5 이고, 행 수가 이력을 그대로 보여줍니다 —
**0·1·2·3 은 6행씩, 4·5 는 2행씩.** 글쓰기 품질과 업데이트 안정성이 나중에 추가됐고
주석은 따라가지 않았습니다. 스키마 주석을 신뢰할 수 없는 예로 남겨 둡니다.

---

## 회고 — 스스로 찾아 고친 것

2026년에 이 프로젝트를 다시 열어 직접 감사하고 고쳤습니다. **결함을 숨기지 않고, 왜 여기까지만 했는지도 함께 적습니다.**

### 1. 작가 모드 3개 엔드포인트에 인가 검사가 없었다 (IDOR)

- **문제** — `webnovel-update.php`, `chapter-update.php`, `regist-chapter.php` 가 JWT로 *인증*만 하고 **작품 소유자인지 확인하지 않았습니다.** 로그인한 사용자면 누구나 `wid` 만 바꿔서 남의 작품을 수정·삭제할 수 있었습니다
- **확인 방법** — 세 파일의 쿼리에 `uid` 조건이 있는지 전수 확인
- **선택** — 공통 소유권 헬퍼 `isWebnovelOwner($wid, $uid)` 를 `webnovelQuery.php` 에 두고 세 엔드포인트 진입부에서 차단
- **검증** — `php -l` 통과, 세 파일 모두 헬퍼 호출 확인
- **남긴 한계** — 다른 엔드포인트의 인가는 이번 범위에 넣지 않았습니다

### 2. 문자열 연결로 만든 SQL

- **문제** — `chapter-update.php` 등이 요청 값을 문자열로 이어 붙여 쿼리를 만들었습니다
- **선택** — 해당 파일들을 prepared statement 로 이관하고, 삭제 시 `WHERE id = ? AND wid = ?` 로 범위를 좁힌 뒤 `affected_rows` 를 확인하고 나서야 회차 수를 감소시키도록 바꿨습니다
- **남긴 한계** — **전체 PHP 를 prepared statement 로 이관하지는 않았습니다.** 위험이 큰 곳부터 고치고 멈췄습니다

### 3. 비밀번호가 응답에 실려 나갔다

- **문제** — `getUserData()` 가 `SELECT *` 결과를 그대로 반환해 **평문 비밀번호가 회원 정보 응답에 포함**됐습니다
- **선택** — 반환 직전 `unset($row['password'])`
- **남긴 한계** — 근본 해결은 `SELECT` 컬럼 명시입니다. 주석으로 남겨 두었습니다

### 4. 트리거 설계가 앱 동작과 어긋난다 (미해결)

`after_user_exits_chat_room` 트리거는 `chat_room_user` 의 **하드 DELETE** 에 반응하는데, 앱은 `class` 컬럼을 바꾸는 **소프트 삭제**를 씁니다. 따라서 **채팅방 인원수가 줄어들지 않습니다.**

고치려면 트리거를 UPDATE 기반으로 바꾸거나 앱을 하드 삭제로 바꿔야 하는데, 어느 쪽이든 기존 데이터 정합성을 다시 봐야 해서 **이번에는 기록만 하고 두었습니다.**

### 5. 리뷰 통계 엔드포인트만 인증이 꺼져 있었다

- **문제** — `webnovel/review/get-review-statics.php` 만 `checkToken()` 이 **주석 처리**돼 있었고,
  `$wid` 도 `(int)` 캐스팅 없이 `getReviewStaticData()` 로 넘어갔습니다. 그 함수는 값을
  쿼리 문자열에 그대로 넣습니다 (`WHERE novel_rating.wid = $wid`).
  즉 **인증 없이 호출 가능하고, 값이 그대로 SQL 에 들어가는** 경로였습니다
- **어떻게 찾았나** — 평균 점수가 어디서 계산되는지 문서에 쓰려고 호출 사슬을 따라가다 발견했습니다.
  기능은 정상 동작하고 있었으므로 화면만 봐서는 드러나지 않습니다
- **선택** — 그 파일 두 줄로 닫았습니다. `checkToken()` 을 되살리고 `$wid = (int)$_GET["wid"];`.
  같은 폴더의 나머지 5개는 원래 `checkToken()` 이 살아 있었고,
  `get-one-book-data.php` 는 캐스팅까지 하고 있었습니다 — **이 파일만 둘 다 빠져 있었습니다**
- **범위** — `webnovelQuery.php` 의 문자열 보간은 그대로 둡니다. 회고 2번과 같은 부채이고,
  전면 이관은 이번 정리의 범위 밖으로 정했습니다. 진입점에서 좁히는 것으로 끝냅니다
- **검증** — `php -l` 통과. 클라이언트(`ReviewService.getReviewStatisticsData`)는
  원래부터 JWT 헤더를 보내고 있어 인증 복구로 깨지는 곳이 없습니다

### 알려진 보안 부채 (그대로 남아 있음)

| 항목 | 상태 |
|---|---|
| 비밀번호 평문 저장·평문 비교 | 미해결. 해시로 바꾸면 기존 계정이 전부 무효가 됨 |
| MariaDB 3307 포트 외부 노출 | 미해결. 토이 프로젝트 서버라 방치했고, 회고 자산으로 남김 |
| `error-show.php` 가 `display_errors` 를 켠다 | 미해결. 오류 노출 위험 |
| `checkToken()` 이 JWT 헤더 부재 시 401 이 아니라 500 | 미해결. `memberToken.php` 가 `isset` 없이 헤더를 읽어 `JWT::decode(null, …)` 이 TypeError 로 죽습니다. `display_errors` 와 겹치면 스택 트레이스까지 나갑니다. 공통 인증 코드라 전 엔드포인트에 영향이 가서 이번에는 손대지 않았습니다 |
| 쿼리 헬퍼가 값을 문자열로 이어 붙인다 | 부분 해결. 진입점에서 `(int)` 로 좁히지만 `webnovelQuery.php` 자체는 그대로입니다 |

---

## 이 저장소에 없는 것

- `common/config.php` — `config.example.php` 를 복사해 만드세요
- Firebase 서비스 계정 JSON
- `vendor/` (Composer 의존성)
- `upload/` 의 실제 사용자 이미지 — 개인정보라 제외했습니다

---

## 동작 실증 (2026-09-04)

실제 서버에 채팅 서버를 띄우고 Android 에뮬레이터에서 왕복을 확인했습니다.

```text
6080 LISTEN · java RSS 약 110MB (t2.micro 949MB 중)
앱에서 전송 → 서버 처리 → DB 저장(id 부여) → FCM 팬아웃까지 성공
```

클라이언트 로그에서 서버가 되돌려준 페이로드를 그대로 확인했습니다.

```json
{"chat_message":{"chat_room_id":6,"content":"RawTCP-live-test","id":396,
  "send_date":"2026-09-04 21:59:48","uid":22},
 "message":"서버에서 보내기 sendMethod 실행됨","method":"send"}
```

### 터미널을 두 번째 참여자로 세운 데모 (2026-09-07)

폰 두 대를 붙이면 "채팅이 된다"만 보입니다. **프로토콜을 직접 설계했다는 주장을 증명하려면
클라이언트가 앱이 아니어도 된다는 걸 보여야 합니다.**

그래서 두 번째 참여자를 터미널로 세웠습니다. `nc` 수준의 소켓 하나로 붙어
개행 JSON 을 한 줄씩 던지면, 앱이 그걸 그냥 받습니다.

```text
$ python3 tcp_client.py <server> 6080      # Raw TCP · 개행 구분 JSON

--> {"method":"init","jwt":"<마스킹>","fcm_token":"demo-terminal-client"}

--> {"method":"send","chat_message":{
       "chat_room_id":6,"chat_room_user_id":20,"username":"팀노바",
       "content":"터미널에서 보낸 Raw TCP 메시지",
       "new_date":1788746037242,"option":0}}

<-- {"method":"send","message":"서버에서 보내기 sendMethod 실행됨",
     "chat_message":{"id":397,"uid":40,"send_date":"2026-09-07 10:53:57"}}

<-- {"method":"refresh_chat_id","list_chat_room_user":[ … 참여자 3명, last_read_chat_id=397 ]}
```

던진 줄이 앱 화면에 뜨는 장면입니다. 앱은 다른 계정(`uid 22`)으로 로그인해 있어
터미널이 보낸 메시지를 **상대방 말풍선**으로 그립니다.

![Raw TCP 채팅 데모](docs/demo/chat_raw_tcp.gif)

`send` 한 번에 서버가 하는 일이 응답에 다 드러납니다 — DB 에 저장하며 `id` 를 부여하고(397),
방 참여자 전원에게 브로드캐스트하고, 이어서 `refresh_chat_id` 로 각자의 읽음 위치를 갱신합니다.

**띄울 때 실제로 걸린 함정** — `~/.m2` 에 guava 16.0.1 이 함께 있어 classpath 앞쪽에 잡히면
Firebase 가 `NoSuchMethodError: MoreExecutors.directExecutor()` 로 죽습니다
(`directExecutor` 는 guava 18부터). **소켓은 정상적으로 뜨고 FCM 만 죽기 때문에 놓치기 쉽습니다.**
