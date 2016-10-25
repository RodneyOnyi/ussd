<?php
 require'condb.php';
 require'func.php';
 
 error_reporting(0);
	$sessionId   = $_POST["sessionId"];
	$serviceCode = $_POST["serviceCode"];
	$phoneNumber = $_POST["phoneNumber"];
	$text = $_POST["text"]; 
 
 $input = array();
 $input=explode("*",$text);
 $lastInput = end($input);
 
	$level = getSessionMenu($phoneNumber, $sessionId);
 
	$selectquery = mysqli_query($con,"SELECT * FROM `customers` WHERE `phone` LIKE '$phoneNumber' AND isActive='1' LIMIT 1 ");
	$resultCount = mysqli_num_rows($selectquery);

	if($resultCount>0) {
		while($row = mysqli_fetch_assoc($selectquery)){
			$name = $row['FName'];
			$pin = $row['pin'];
			$user_id = $row['Id'];
		}
		if($level==0){		
			// This is the first request. Note how we start the response with CON
		    $response = menu_login($name,0);            
		    updateSessionMenu($phoneNumber, $sessionId,"1");
		}elseif($level==1) {
							
			if($lastInput==$pin){
				if((strlen($lastInput))==(strlen($pin))){				
					$response = menu_home();
					updateSessionMenu($phoneNumber, $sessionId,"2");
				}else{
					$response = menu_login($name,1);
				}
			}else{
				$response = menu_login($name,1);			
			}		
		}
	}else{
		
	//Case Select Using Menus for Registration
		
	switch($level){
		case '':	
			$insertCustomer = mysqli_query($con,"INSERT INTO `customers` (`Phone`) VALUES ('$phoneNumber')");
			// This is the first request. Note how we start the response with CON			
			$response = menu_register();
			updateSessionMenu($phoneNumber, $sessionId,"1");      
		break;
		case '0':
	        // This is the first request. Note how we start the response with CON
			$response = menu_register();
			updateSessionMenu($phoneNumber, $sessionId,"1");
		break;
		//------------------------------------------------------------------------
		case '1':
			$updateSession =mysqli_query($con,"UPDATE customers SET FName = '$lastInput' WHERE phone='$phoneNumber' ");
			$response ="CON Please enter your Last Name to enroll \n";
			updateSessionMenu($phoneNumber, $sessionId,"2");
		break;
		case '2':		
			$updateSession =mysqli_query($con,"UPDATE customers SET LName = '$lastInput' WHERE phone='$phoneNumber' ");
			
			$response ="CON Provide Your Staff ID Number eg. 27222222 \n"; 	
			updateSessionMenu($phoneNumber, $sessionId,"3");	
		break;
		//------------------------------------------------------------------------
		case '3':
			$updateSession =mysqli_query($con,"UPDATE customers SET staffNo = '$lastInput' WHERE phone='$phoneNumber' ");
			
			$response = "CON What is your date of birth e.g 1980-09-01 \n";
			updateSessionMenu($phoneNumber, $sessionId,"4");
		break;
		//------------------------------------------------------------------------
		case '4':
			if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",end($input))){
				$updateSession =mysqli_query($con,"UPDATE customers SET dob = '$lastInput' WHERE phone='$phoneNumber' ");
				
				$response = "CON What Gender are you? Select by typing 1 or 2 \n";	
				$response.="1.Male \n";
				$response.="2.Female \n";
				updateSessionMenu($phoneNumber, $sessionId,"5");
			}else{
				
				$response = "CON Invalid input, try again\n What is your date of birth e.g 1980-09-01 \n";	
			}
			
		//--------------------------------------------------------------------//	
		break;
		//------------------------------------------------------------------------
		case '5':
			if($lastInput==1||$lastInput==2){
				$updateSession =mysqli_query($con,"UPDATE customers SET gender = '$lastInput' WHERE phone='$phoneNumber' ");
				
				$response = company();
				updateSessionMenu($phoneNumber, $sessionId,"6");
			}else{
				$response = gender();					
			}
			
		break;
		//------------------------------------------------------------------------
		case '6':		
			$company = search_company($lastInput);	
			if($company==0){
				$response = company();
			}else{
				$updateSession =mysqli_query($con,"UPDATE customers SET cID = '$company' WHERE phone='$phoneNumber' ");
				
				$selectquery = mysqli_query($con,"SELECT * FROM customers JOIN companies ON customers.cID=companies.Id WHERE customers.phone LIKE '$phoneNumber' LIMIT 1 ");
					while($row = mysqli_fetch_assoc($selectquery)){
						$name = $row['FName'];
						$gender = $row['gender'];
						$comp = $row['Name'];
						$staff_id = $row['staffNo'];
						$dob = $row['dob'];
				}
				
				$response = "CON Confirm Details \n\n";	
				$response.="Name:  $name \n";
				$response.="DOB: $dob \n";
				$response.="Identification: $staff_id \n";
				$response.="Gender: $gender \n";
				$response.="Company: $comp \n";
				$response.="Is this correct? Y/N\n";
				$response.="1. Yes\n";
				$response.="2. No";	
				
				updateSessionMenu($phoneNumber, $sessionId,"7");
			}
		break;
		//--------------------------------------------------------------------------
		case '7':
			switch ($lastInput){
				case '1':	
					$r = str_repeat('0123456789',4);
					$new_pin = substr(str_shuffle($r),0,4);
					$updateSession =mysqli_query($con,"UPDATE customers SET pin ='$new_pin' WHERE phone='$phoneNumber' ");
					$updateSession =mysqli_query($con,"UPDATE customers SET isActive =1 WHERE phone='$phoneNumber' ");
					$response = "CON Thank for registering. \n";
					$response.="Your PIN $new_pin is Want to Login?\n";
					$response.="1. Yes \n";
					$response.="2. No";
					updateSessionMenu($phoneNumber, $sessionId,"0");
				break;
				case '2':
					$response = "CON Do you want to edit some details?\n";
					$response.="1. Yes\n";
					$response.="2. No";
					updateSessionMenu($phoneNumber, $sessionId,"0");
				break;
				default:
					$response = "END Sorry, response not recognised. \n";
				break;	
			}				
		break;
		//------------------------------------------------------------------------
		default:
			$response = "END The Application Experienced an Unexpected Error";
		break;
			
	}
}
	

header('Content-type: text/plain');
echo $response;	
}

?>