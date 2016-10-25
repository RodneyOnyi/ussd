<?php

require_once('ussd_connect.php');
require_once('AfricasTalkingGateway.php');

// Reads the variables sent via POST from our gateway
if(!empty($_POST)){
 $sessionId   = $_POST["sessionId"];
 $serviceCode = $_POST["serviceCode"];
 $phoneNumber = $_POST["phoneNumber"];
 $text = $_POST["text"];
 $input = array();
 $input=explode("*",$text);
 $level = count($input);
		
	if($text=="") {
	
         // This is the first request. Note how we start the response with CON
        $response  = "CON Choose your Internet connection  \n";
        $response .="1. WiMAX (Wireless) \n";
	$response .="2. Fiber \n";
			
    	}elseif($level==1) {
        
	 	$response ="CON Where are you Located? \n";
		$response .="1. Nairobi \n";
		if($input[0]==1){
		$response .="2. Mombasa \n";
		}
		if($input[0]>2){
			$response ="END Option not valid \n";
		}
	 			
	 	  
	}elseif($level==2) {
		
		if($input[1]==1){
			if($input[0]==1){
				$response ="CON What area in Nairobi? \n";
				$response .="1. Kilimani \n";
				$response .="2. CBD \n";
			}elseif($input[0]==2){
				$response ="CON What Building in Nairobi? \n";
				$response .="1. Loita Building \n";
				$response .="2. I&M Building \n";
				}
		}elseif($input[1]==2){
			if($input[0]==1){
				$response ="CON What area in Mombasa? \n";
				$response .="1. Nyali \n";
				$response .="2. Bandari \n";
			}
		}elseif(($input[1]!=1)&&($input[1]!=2)){
			$response ="END Sorry Option not valid \n";
		}
		
				
	}elseif($level==3) {
		if(($input[2]!=1)&&($input[2]!=2)){
				$response ="END Sorry Option not valid \n";
		}else{
		$response ="CON What Problem are you experiencing?\n";
		$response .="1. Slow Speeds \n";
		$response .="2. No Internet \n";
		}
		
	
	}elseif($level==4){
		if(($input[3]!=1)&&($input[3]!=2)){
			$response ="END Sorry Option not valid \n";
		}else{	
		if($input[0]==1){
			$connection="WiMAX";
			if($input[3]==1){
				$problem="slow speed";
				if(($input[1]==1)&&($input[2]==1)){
					$region="Nairobi"; 
					$area="Kilimani"; 
				}elseif(($input[1]==1)&&($input[2]==2)){
					$region="Nairobi"; 
					$area="CBD";
				}elseif(($input[1]==2)&&($input[2]==1)){
					$region="Mombasa"; 
					$area="Nyali";
				}elseif(($input[1]==2)&&($input[2]==2)){
					$region="Mombasa"; 
					$area="Bandari";
				}
			}elseif($input[3]==2){
				$problem="no internet";
				if(($input[1]==1)&&($input[2]==1)){
					$region="Nairobi"; 
					$area="Kilimani"; 
				}elseif(($input[1]==1)&&($input[2]==2)){
					$region="Nairobi"; 
					$area="CBD";
				}elseif(($input[1]==2)&&($input[2]==1)){
					$region="Mombasa"; 
					$area="Nyali";
				}elseif(($input[1]==2)&&($input[2]==2)){
					$region="Mombasa"; 
					$area="Bandari";
				}
			}
			
		$selectquery = mysql_query("SELECT * FROM issuelog WHERE region='".$region."' AND area='".$area."' AND problem='".$problem."' AND connection='".$connection."' AND isActive=1 ORDER BY issue_id DESC LIMIT 1 ");	
		}elseif($input[0]==2){
			$connection="Fiber";
			if($input[3]==1){
				$problem="slow speed";
				if(($input[1]==1)&&(($input[2]==1)||($input[2]==2))){
					$region="Nairobi"; 
				}
			}elseif($input[3]==2){
				$problem="no internet";
				if(($input[1]==1)&&(($input[2]==1)||($input[2]==2))){
					$region="Nairobi"; 
				}
			}
		
		$selectquery = mysql_query("SELECT * FROM `issuelog` WHERE region='".$region."' AND area='".$area."' AND problem='".$problem."' AND connection='".$connection."' AND isActive=1 ORDER BY issue_id DESC LIMIT 1 ");
		}
		
		$resultCount = mysql_num_rows($selectquery);
				if ($resultCount>0){
					while ($row = mysql_fetch_assoc($selectquery)){
					$msg = $row['message'];
					}
				}else{
					if($problem=="slow speed"){
						$msg="Please visit www.speedtest.zuku.co.ke and email the results to support@simbanet.co.ke along with your account number";
					}else{
						$msg="Please ensure that your router is powered on otherwise contact 0205780030 for assistance";
					}
				}
		
		$response = "END Please wait, a message will be sent to you shortly \n";	
			// Specify your login credentials		
			$user_name = "rodneyo";
			$apikey = "e7ce46d517c3c63621d30fc3e184873e17f7e1b41af486a490ab648ba9569924";
			$recipients = $phoneNumber;
			// And of course we want our recipients to know what we really do
			$message="$msg";				
			// Create a new instance of our awesome gateway class
			$gateway    = new AfricasTalkingGateway($user_name, $apikey);
			// Any gateway error will be captured by our custom Exception class below, 
			// so wrap the call in a try-catch block
			try 
			{ 
			// Thats it, hit send and we'll take care of the rest. 
			$results = $gateway->sendMessage($recipients, $message);
			}
			catch ( AfricasTalkingGatewayException $e )
			{
			  echo "Encountered an error while sending: ".$e->getMessage();
			}
			// DONE!!! 
		}
	}
     }

header('Content-type: text/plain');
echo $response;

// DONE!!!
?>
