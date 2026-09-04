<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';

include_once INCLUDE_DB;


function getUserData($uid){
    global $mysqli;
    $query_string =  "SELECT * FROM user WHERE id = ? ";
    $stmt = $mysqli->prepare($query_string);
    $stmt->bind_param('i',$uid); 
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows ==1){
    $row = $result->fetch_assoc();
    // 비밀번호는 어떤 응답에도 실리면 안 된다.
    // schema.sql 확보 후에는 SELECT * 대신 필요한 컬럼만 나열하도록 바꾼다.
    unset($row['password']);
    return $row;
        }else return false;
};

// 프로필 이미지를 업로드에 성공 하는 경우 이미지의 파일명을 user DB 에 저장을 함
// 데이터베이스에 파일 정보 저장 user_id -> update DB_USER_PROFILEIMAGE
function setProfileImage(int $uid, String $imagePath){
    global $mysqli;
    $query_string = "UPDATE user SET profileImagePath = ? WHERE id = ?";
    $stmt = $mysqli->prepare($query_string);
    $stmt->bind_param('si',$imagePath,$uid);
    if($stmt->execute()){
        return getResponseArray(200,true,"프로필 이미지 저장 성공");
    }else{
        return getResponseArray(400,false,"프로필 이미지 DB 저장 실패");
   }
}

                


/**
 * 응답 어레이 return 용 
 * @param int $status = 상태 코드 200 통과 (사용 가능 ) | >= 300 비정상 (사용 불가)
 * @param string $message = 상태 메세지
 * @return array 
 */
function getResponseArray(int $status, bool $success , string $message){
    return $arr = [
        'status' => $status ,'success'=> $success,'message' => $message
         ] ;
};


/**
 *  응답 어레이 return 용 
 * @param int $status = 상태 코드 200 통과 (사용 가능 ) | >= 300 비정상 (사용 불가)
 * @param bool $success = boolean 정상일때 true
 * @param string $message  = 하고 싶은 메세지
 * @param array $data = array for give to client , ex) user Data
 * @return (int|bool|string|array)[] 
 */
function getResponseArrayWithData(int $status, bool $success , string $message , array $data){
    return $arr = [
        'status' => $status ,'success'=> $success,'message' => $message , 'data' => $data
         ] ;
} 




/**
 * @param mixed $email 이메일을 통해서 회원 정보 유무 조회 
 * @return mysqli_result|false 
 */
function isEmailDuplicate($email){
    global $mysqli;
    $return_array = array(); // return
    $query_string =  "SELECT count(*) as count FROM user WHERE email = ?";
    $stmt = $mysqli->prepare($query_string);
    $stmt->bind_param('s',$email); 
    $stmt->execute();
    $result = $stmt->get_result();
    $assoc_row = $result->fetch_assoc();
    if($assoc_row['count']> 0){
        return true;
        }else if($assoc_row['count']< 1){
            return false;
        }else{
            // echo "querry 에러 발생";
            return STATUS_FAIL;
        }  
/*     if($result->num_rows == 0){
        // $return_array["status"] = 200;
        // $return_array["message"] = "사용 가능한 이메일";
        return getResponseArray(200,"사용 가능한 이메일");     
    }else if($result->num_rows > 0) {
        // $return_array["status"] = 400;
        // $return_array["message"] = "이미 사용중인 이메일";
    return getResponseArray(400,"이미 사용중인 이메일");     
        return $return_array;
    }else{
        // $return_array["status"] = 500;
        // $return_array["message"] = "query 에러 발생";
        return getResponseArray(500,"query 에러 발생");    
        return $return_array;     
    }; */
};

/**
 * 유저 닉네임 , username 을 통해서 중복 확인  
 * userName 통해서 회원 정보 유무 조회 1 이상인 경우 응답 코드 > 300 설정
 * @param mixed $userName 
 * @return mysqli_result 
 */
function isUserNameDuplicate($userName){
    global $mysqli;
    $return_array = array(); // return
    $query_string =  "SELECT count(*) as count FROM user WHERE username = ?";
    $stmt = $mysqli->prepare($query_string);
    $stmt->bind_param('s',$userName); 
    $stmt->execute();
    $result = $stmt->get_result();
    $assoc_row = $result->fetch_assoc();
    if($assoc_row['count']> 0){
        return true;
        }else if($assoc_row['count']< 1){
            return false;
        }else{
            // echo "querry 에러 발생";
            return STATUS_FAIL;
            // return $assoc_row['count'];
        }

/*     if($result->num_rows == 0){
        // $return_array["status"] = 200;
        // $return_array["message"] = "사용 가능한 이메일";
        return getResponseArray(200,"사용 가능한 닉네임");     
    }else if($result->num_rows > 0) {
        // $return_array["status"] = 400;
        // $return_array["message"] = "이미 사용중인 이메일";
        return getResponseArray(400,"이미 사용중인 닉네임");     
        return $return_array;     
    }else{
        // $return_array["status"] = 500;
        // $return_array["message"] = "query 에러 발생";
        return getResponseArray(500,"query 에러 발생");    
        return $return_array;     
    }; */
};



/** 유저 정보 저장 인증 번호 , 이메일 저장 
 * @param mixed $userName 유저 이름 , 닉네임
 * @param mixed $email 이메일 발송 할
 * @param mixed $password 암호 
 * @param mixed $email_code 이메일 인증 코드
 * @return array|void 응답 연관 배열 [ status  200 = 성공 , success , message ]
 */
function setSignUpUserDataBeforeAuth($userName ,$email , $password, $email_code ){
    global $mysqli;
    // global $email_input;

    // $email = $email_input;
     // 유저 id 이후 이메일 인증 코드 저장 목적
    $sqlIntoUser = "INSERT INTO user (email, username, password , status ) VALUES (?,?,?,0)";
    $stmt['sqlIntoUser'] =  $mysqli->prepare($sqlIntoUser);
    $stmt['sqlIntoUser']->bind_param('sss',$email,$userName,$password); 

    if ($stmt['sqlIntoUser']->execute()) {
        $UID =  $stmt['sqlIntoUser']->insert_id; // 유저 id insert 이후 반환 값 
        // $result['sqlIntoUser'] = $stmt['sqlIntoUser']->get_result(); // ! get_result = selecet 에서 
        if($stmt['sqlIntoUser']->errno == 0){ // 에러 미 발생 
            $sqlIntoAuth = "INSERT INTO emailAuth (uid,auth_code,email,auth_ok) VALUES(?,?,?,0)";
            $stmt['sqlIntoAuth'] =  $mysqli->prepare($sqlIntoAuth);
            $stmt['sqlIntoAuth']->bind_param('iis',$UID,$email_code,$email); 
            if($stmt['sqlIntoAuth']->execute()){
                if($stmt['sqlIntoAuth']->errno ==0){ // 성공
                    return getResponseArray (200 ,true, "성공 ");
                }else{ //실패 최종 emailAuth table ,sqlIntoAuth
                    return getResponseArray(401, false ,$stmt['sqlIntoAuth']->errno );
                }
            }else{// 실패 user table ,sqlIntoUser
                return getResponseArray(402 , false ,$stmt['sqlIntoUser']->errno );

            }
        };
/* 
        $sql_getUID ="SELECT * FROM user WHERE email = '$email'";
        
        // echo "User registered successfully";
        //그냥 join으로 한번에 입력 가능하게 수정 가능 할 꺼 같음 TODO
        $sql_getUID ="SELECT * FROM user WHERE email = '$email'";
        $result = $mysqli->query($sql_getUID);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $uid = $row["id"];
            }
        } else {
            $uid = 999;
        }
        
        // email_send($email,$uid)
        // $email_input = $email;
        // include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/member/register/emailAuthSend.php";
        // sendEmail_custom($title_post, $content_post,$response_array,$email_input);
        // save_emailAuthCode($uid,$email_code,$email,$response_array);
        $response_array["signUpDataSaveResult"] = 200; 
    } else {
        $response_array["signUpDataSaveResult"] = 403; 

    }; */
    };
}
    // 유저 정보 , 이름을 업데이트 
    function updateUserName($userName , int $uid){
        global $mysqli;
        $sqlUserNameUpdate = "UPDATE user SET username = ? WHERE id = ? ";
        $stmt = $mysqli->prepare($sqlUserNameUpdate);
        $stmt->bind_param('si',$userName,$uid);
        if($stmt->execute()){
            return getResponseArray(200,true,"이름 변경 완료");
        }else {
            return getResponseArray(400,false,"이름 변경 실패");

        }
    }
    function updateUserPassword($email , $password){
        global $mysqli;
        $sqlPasswordUpdate = "UPDATE user SET password = ? WHERE email = ? ";
        $stmt = $mysqli->prepare($sqlPasswordUpdate);
        $stmt->bind_param('ss',$password,$email);
        if($stmt->execute()){
            return getResponseArray(200,true,"비밀번호 변경 완료");
        }else {
            return getResponseArray(400,false,"비밀번호 변경 실패");

        }
    }

    // 비밀 번호 변경전 현재 비밀 번호로 입력한 것이 맞나 확인한다
    function checkPassword( $uid, $password){
        global $mysqli;
        $result_array = array();
        // 비밀번호 확인용 sql
        $sqlPasswordCheck = "SELECT * FROM user WHERE id = ? and password = ?";
        $stmt = $mysqli->prepare($sqlPasswordCheck);
        $stmt->bind_param('is',$uid,$password);
        $stmt->execute();
        
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            //결과 값이 존재한다 , 회원 정보 조회 성공 
       
            $result_array['checkPassword'] = true;
            $row = $result->fetch_assoc();
            // 응답에 들어갈 데이터 주입
            $result_array['email'] = $row["email"];
            $result_array['userName'] = $row["username"];
            // 로그인 토큰 생성 , 전달 OBJWToken
            $result_array['OBJWToken'] = createToken( 
                $row['email'] , $row['username'] , $row['id']
            );
            }else{// 회원 정보 조회 실패 
                $result_array['checkPassword'] = false;
            };
            return $result_array;
    }

?>