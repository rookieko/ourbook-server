<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/OurBook/common/config.php';
	include_once INCLUDE_DB;
	include_once INCLUDE_MEMBER_QUERY;
	include_once INCLUDE_TOKEN;

	$jwtDecode = checkToken(); 
	$uid = $jwtDecode[DATA_USER]["uid"];


	$query2 = "DELETE FROM emailAuth WHERE uid = '$uid' ";

	$query1 = "DELETE FROM user WHERE id = '$uid' ";
	if($uid == null){ exit; }

	if($mysqli->query($query2)){

		if($mysqli->query($query1)){

			echo json_encode(getResponseArray(200,true,"삭제 성공"));
		}else{
			echo json_encode(getResponseArray(400,false,"삭제 실패"));

		}
	}


?>