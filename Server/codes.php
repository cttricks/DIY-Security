<?php
$SecretKey = "Niotron";
$headers = getallheaders();

/*Default Response*/
$response = array("status"=>false,"msg"=>"unknown device location");

/*Get Client time zone*/
$timeZone = (isset($headers["X-Region"]) && $headers["X-Region"] !== "")? $headers["X-Region"] : "Asia/Kolkata";
$zoneList = timezone_identifiers_list();

/*Check User Agent*/
$response["msg"] = "The client request is not from app.";
if(isset($headers["User-Agent"]) && strpos($headers["User-Agent"], 'Dalvik') !== false){
    if(in_array($timeZone, $zoneList)){
		/*Change default time zone*/
        date_default_timezone_set($timeZone);
        $time = time();
        $response["msg"] = "invalid credentials, check and try again.";
        
        /*check for auth token*/
        if(isset($headers["Authorization"]) && $headers["Authorization"] !== ""){
            $token = str_replace("Basic ","", $headers["Authorization"]);
            $decoded_token = base64_decode($token);
            
            /*Validate decoded token*/
            $Str1 = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $decoded_token);
            if($Str1!=$decoded_token || $Str1 == ''){
                $response["msg"] = "invalid or broken token, try again.";
                die(json_encode($response));
            }
            
			/*Get Index*/
            $index = substr($decoded_token,(strlen($decoded_token) -2),2);
			
			/*Ganarate Server Token*/
            $serverToken = sha1($index . date("j/m/Y", $time) . $SecretKey . date("h:i", $time)). $index;
            
			/*Compare Token*/
            if($decoded_token !== $serverToken){
                $response["msg"] = "invalid token, try again.";
                die(json_encode($response));
            }
            
			/*Success- Do any action and return respose.*/
            $response["status"] = true;
            $response["msg"] = "Token Verified Successfully";
        }
    }
}


echo json_encode($response);

?>
