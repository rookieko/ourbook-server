<?php

// 이메일 인증 코드 전송을 function으로 수정 , 오직 이메일을 보내기 위한 php 파일 
include_once $_SERVER['DOCUMENT_ROOT']."/OurBook/db-connect.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";



    // $email_code = rand(000000,999999);
    // $email_input = $_GET['email'];

    //이메일 이증 번호 생성

    
    /**
     * 이메일 인증 번호 저장 함수 , 폐쇄
     * @param mixed $uid emailAuth table 에 저장 될 user tabel 의 id
     * @param mixed $email emailAuth table 에 저장 될 email
     * @param mixed $authCode emailAuth table 에 저장 될 인증 번호 auth_number
     * @return void 
     */
    function save_emailAuthCode_pure($uid, $email ,$authCode){
            //이메일 인증코드 db에 저장
        global $mysqli;

            $sqlSaverEmailCode = "INSERT INTO emailAuth (uid,email,auth_code) VALUES('$uid','$email','$authCode')";
        if ($mysqli->query($sqlSaverEmailCode) === TRUE) {
            $resultArray["status"] = 200;
            $resultArray["message"] = "이메일 인증 코드 DB 저장 성공";
            
        } else {
            $resultArray["status"] = 401;
            $resultArray["message"] = "이메일 인증 코드 DB 저장 실패 ";

        };
    };


    // sendEmail_custom($title_post, $content_post,$response_array);
    // save_emailAuthCode($uid,$email_code,$email_input,$response_array);

    /**
     * php 인증 코드 확인용 이메일 전송 function
     * @param mixed $to 보낼 이메일 주소
     * @param mixed $authCode 이메일 주소로 보낼 인증 번호 
     * @return (string|int)[] 상태코드(status) , 메세지 (message) return
     * @throws Exception 
     */
    function sendAuthEmail_pure($to, $authCode ){
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; 
        $mail->Host        = 'smtp.example.com';
        $mail->SMTPAuth    = true;
        $mail->Username    = 'your_smtp_user';
        $mail->Password    = 'your_smtp_password';
        $mail->SMTPSecure  = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port        = 587;
        $mail->setFrom('noreply@example.com', 'OurBook');
        $mail->addAddress($to, 'TO');
        $mail->isHTML(true);
      
        
        $title = "아워북 인증번호 발송 메세지 입니다.";
        $content = <<<HTML
        <div>
        <div style="text-align: left;padding: 0 0 20px 0;font-size: 14px;line-height: 1.5;width: 80%;">아워북 이메일 인증을 위해 다음 코드를 입력해 로그인하세요:</div>
        <div style="background:#FAF9FA; border:1px solid #DAD8DE;text-align: center;padding: 5px;margin: 0 0 5px 0;font-size: 24px;line-height: 1.5;width: 80%;"> $authCode </div>
        </div>
        HTML;

        $mail->Subject     = $title;
        $mail->Body        = $content;
        if (!$mail->send()) {
            $resultArray["message"] = '이메일 전송실패 상세 메세지 : ' . $mail->ErrorInfo;
            $resultArray["status"] = 400;
            $resultArray["success"] = false;
            return $resultArray;
            // echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            // echo 'Message sent';
            $resultArray["message"] = '이메일 전송 성공';
            $resultArray["status"] = 200;
            $resultArray["success"] = true;
            return $resultArray;

        };

    };
?>