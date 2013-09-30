<?php
	/*
	 * ECK  Service Class
	 *
	 */
	 
	 Class eckService{
	 
		/**
		 * Fuctions
		 */	 
		function eckCore($id,$module,$function) {
			$idType = "";
			if($function == "lookupRecordByEckId") $idType = "eckId";
			if($function == "lookupRecordByCmsId") $idType = "cmsId";
			if($function == "lookupRecordByPersistentId") $idType = "persistentId";
			if($function == "lookupRecordsAnyIdType") $idType = "id";
		
		
			$requestBaseUrl ="http://euinside.k-int.com/ECKCore/function/call.json";
			$requestParameters = "?module=".$module."&function=".$function."&args={".$idType.":".$id."}";
			//$requestParameters= "?module=/KIPersistence/persistence&function=lookupRecordByEckId&args={eckId:".$eckId."}";
			$requestUrl = $requestBaseUrl.$requestParameters;
			$htmlResult="";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			//curl_setopt($ch,CURLOPT_URL,$requestBaseUrl.$requestParameters);
			curl_setopt($ch,CURLOPT_URL,$requestUrl);

			$result = curl_exec($ch);
			$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($responseCode == 200){
				$responseData = json_decode($result);	
				if(is_null($responseData)){
					$htmlResult .= "null<br>";
				}else{
					foreach ($responseData as $name => $value) {
						$htmlResult .= $name . ':'.$value."<br>";
					}		
				}
			}
			else{
				$htmlResult .= "Response Code = ".$responseCode."<br>";
				if(is_null($result))
					$htmlResult .= "null"."<br>";
				else
					$htmlResult .= "Request Unsuccessful: ".$result."<br>";
			}
			curl_close($ch);
			
			return $htmlResult;						
		}	 

		function eckFeatureList(){
		
			$htmlResult = "";
			$requestUrl = "http://euinside.k-int.com/ECKCore/function/list.json";
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch,CURLOPT_URL,$requestUrl);

			$result = curl_exec($ch);
			$responseData = json_decode($result);
			$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			$jsonIterator = new RecursiveIteratorIterator(	new RecursiveArrayIterator(json_decode($result, TRUE)), RecursiveIteratorIterator::SELF_FIRST);
			foreach ($jsonIterator as $key => $val) {
				if(is_array($val)) {
					$htmlResult .= "$key:<br>";
				} else {
					$htmlResult .= "$key => $val,<br>";
				}
			}					
			
			curl_close($ch);
			
			return $htmlResult;	
		}
		
		function eckLanguageSupport(){
		
			$htmlResult = "";
			$requestUrl = "http://euinside.k-int.com/ECKDefinition/languages";
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch,CURLOPT_URL,$requestUrl);

			$result = curl_exec($ch);
			$responseData = json_decode($result);
			$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			$jsonIterator = new RecursiveIteratorIterator(	new RecursiveArrayIterator(json_decode($result, TRUE)), RecursiveIteratorIterator::SELF_FIRST);
			foreach ($jsonIterator as $key => $val) {
				if(is_array($val)) {
					$htmlResult .= "$key:<br>";
				} else {
					$htmlResult .= "$key => $val,<br>";
				}
			}					
			
			curl_close($ch);
			
			return $htmlResult;	
		}		
		
		function eckInsertRecord(){
		
			//set POST variables
			$url = 'http://euinside.k-int.com/ECKCore/import/save.json';
			$fields = array(
									'cmsId' => 88888,
									'persistentId' => 55555,
									'metadataFile' =>'c:\log.txt'
							);

			//url-ify the data for the POST
			foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			rtrim($fields_string, '&');	
			
			//open connection
			$ch = curl_init();

			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

			//execute post
			$result = curl_exec($ch);

			//close connection
			curl_close($ch);						
			
			return $url.$fields_string;
		}
		
	 }
	 
	 



	

