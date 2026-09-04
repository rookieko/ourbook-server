<?php

use PhpMyAdmin\SqlParser\Utils\Query;

include_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
include_once INCLUDE_DB;
include_once INCLUDE_MEMBER_QUERY;
include_once INCLUDE_TOKEN;
// include_once INCLUDE_ERROR;

if (!$_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_FILES['imageKey'])){
    echo json_encode(getResponseArray(500,false,"fail by HTTP METHOD")) ;
    return;
}
$jwtDecode = checkToken();
// 유저 아이디 
$uid = $jwtDecode[DATA_USER]["uid"];


if ($uid != null){
        $result = getUserData($uid);
	if($result == false){// 유저 정보가 없을 때 
		echo json_encode(getResponseArray(500,false,"UID 에 맞는 회원 정보가 없습니다."));
		exit;
	}
	// 유저 정보가 있을 때 -> 불필요한 응답이라는 생각으로 삭제 
	// echo json_encode(getResponseArrayWithData(200,true,"유저 정보 획득 성공",$result));

}
// echo print_r($_FILES['imageKey']);
// echo $_FILES['imageKey']['error'];
// 업로드 파일 크기가 큰 경우 error 코드 1 이 발생  php에 설정된 최대 크기 보다 업로드 파일의
// 크기가 크기 떄문에 발생 , php.ini 에서 max upload file size , post 파일 사이즈 변경 , 재부팅 으로 해결

// 원 파일의 이미지 
$orgin_profileImage = $result[DB_USER_PROFILEIMAGE];

// 업로드 디렉토리 설정
$uploadDir = "/var/www/html/OurBook/upload/profileImage/";

// $DBUrl = "/OurBook/upload/profileImage/"; // DB 저장을 위한 URL , 
// 그냥 DB 에는 파일명만 저장하고 클라이언트에서 경로를 붙여서 사용하는 것으로 변경 DB 리소스 낭비

// 파일이 존재하는지 확인

//imageKey 는 클라이언트에서 formdata 를 만들때 설정 , 테스트겸 예제와 다르게 imageKey 로 설정 하였음..
if (isset($_FILES['imageKey'])) {
    $uploadFile = $uploadDir . basename($_FILES['imageKey']['name']);
    $imageExtension = pathinfo($_FILES['imageKey']['name'], PATHINFO_EXTENSION); // 1.

    $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION)); // 2. 두개 차이가 뭔지..

    // 파일 타입 및 크기 검증
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        echo json_encode(getResponseArray(400,false,"파일 타입을 지원하지 않습니다."));
        exit;
    }

// TODO 
     // 현재 시간을 기반으로 하는 파일 이름 생성
     $timestamp = time(); // 현재 시간의 타임스탬프
     $newFileName = $result['username'] . '_' . $timestamp . '.' . $imageExtension; // 예: 'user123_1652923325.jpg'

     $target_file = $uploadDir . $newFileName;

    // 파일을 업로드 디렉토리에 저장
    if (move_uploaded_file($_FILES['imageKey']['tmp_name'], $target_file)) {
        // echo "The file ". htmlspecialchars(basename($_FILES['image']['name'])) . " has been uploaded.";
        echo json_encode(getResponseArrayWithData(200,true,"이미지 업로드 성공",$arr= ["imagePath" => $newFileName]));

        // 이미 이미지가 있는 경우 파일 삭제 
        if(isset($orgin_profileImage)){
            if(!unlink($uploadDir.$orgin_profileImage)){
                    echo json_encode(getResponseArray(300,false,"원 이미지 삭제 실패"));
                }else{
                    // setProfileImage($result['id'],"");
                }
        }

        // 데이터베이스에 파일 정보 저장 user_id -> update DB_USER_PROFILEIMAGE
        setProfileImage($result['id'],$newFileName);
        
    } else {
        echo json_encode(getResponseArray(401,false,"이미지 업로드 실패"));
        // echo "Sorry, there was an error uploading your file.";
    }
} else {
    echo json_encode(getResponseArray(402,false,"업로드한 파일이 존재하지 않음"));
    // echo "No file was uploaded.";
}


?>


