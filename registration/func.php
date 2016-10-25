<?php
	require'condb.php';	
	$inputx = array();
	
	function updateSessionMenu($phoneNumber, $sessionFromGateway,$sessionMenu) {
	  global $con;

	  $selectSession =mysqli_query($con,"SELECT * FROM ussd_sessions WHERE phoneNumber = '$phoneNumber' LIMIT 1");
	  $rowcount=mysqli_num_rows($selectSession);
	  if($rowcount>0){
		  while ($row = mysqli_fetch_assoc($selectSession)){
		  $sessionIdInDB = $row['session_id'];

		  if ($sessionIdInDB != $sessionFromGateway) {
			  $updateSession =mysqli_query($con,"UPDATE ussd_sessions SET session_id = '$sessionFromGateway' WHERE phoneNumber='$phoneNumber' ");
		  }
			$updateSession =mysqli_query($con,"UPDATE ussd_sessions SET sessionMenu = '$sessionMenu' WHERE phoneNumber='$phoneNumber' ");
			return $sessionMenu;
		  
				  
		}
	  }else{
		$selectSession =mysqli_query($con,"INSERT INTO `ussd_sessions` (`session_id`, `sessionMenu`, `phoneNumber`) VALUES ('$sessionFromGateway', '1', '$phoneNumber')");
		return '1';
	  }
	}
	
	function getSessionMenu($phoneNumber, $sessionFromGateway) {
		global $con;
		
		$selectSession =mysqli_query($con,"SELECT * FROM ussd_sessions WHERE phoneNumber = '$phoneNumber' ");
		$rowcount=mysqli_num_rows($selectSession);
		if($rowcount>0){
		  while ($row = mysqli_fetch_assoc($selectSession)){
			$level = $row['sessionMenu'];
		  }
		}
		return $level;
	}
		
	function menu_register(){
		$response  = "CON Welcome \n";
	    $response .="Please enter your First Name to enroll \n";
		
		return $response;
	}
	
	function gender(){
		$response = "CON Invalid Input \n\n";	
		$response.= "What Gender are you? \n";
		$response.="1.Male \n";
		$response.="2.Female \n";	
		
		return $response;
	}
	
	function company(){
		$response = "CON Search Company \n";
		$response .="Enter Company Name \n"; 
		
		return $response;				
	}
	
	function menu_login($name,$error){
		
		$response  = "CON Welcome, $name to your QOOZI Account \n\n";
		if($error==1){
			$response .="Incorrect Pin, Try Again \n";
		}
		$response .="Please enter your 4-digit PIN \n";	
		
		return $response;
	}
	
	function menu_home(){
		
		$response  = "CON Account \n";
		
		$response .="1. Redeem \n";	
		$response .="2. View Balance \n";
		$response .="3. Change PIN \n";		
		$response .="0. Exit \n";
		
		return $response;
	}
		
	function pinChange($pin,$user_id){
		global $con;
		
		$updatePIN = mysqli_query($con, "UPDATE customers SET pin='$pin' WHERE Id ='$user_id' ");
		if($updatePIN){
			$response = "You PIN has been successfully changed.";
		}else{
			$response = "You PIN could not be changed at this time.";
		}
		
		return $response;
	}
		
?>