<?php
	/*
	 * ECK  Service Class
	 *
	 */
	 
	 Class eckService{

         private $url_eck_record_add;
         private $url_eck_functions;
         private $url_eck_languages;
         private $url_eck_base;
         private $url_proxy;

         # Constructor
         public function __construct()
         {
             $this->loadECKCoreConfigurations(dirname(__FILE__).'/config/libiscode.conf');
         }

		/**
		 * Fuctions
		 */	 
		function eckCore($id,$module,$function) {
			$idType = "";
			if($function == "lookupRecordByEckId") $idType = "eckId";
			if($function == "lookupRecordByCmsId") $idType = "cmsId";
			if($function == "lookupRecordByPersistentId") $idType = "persistentId";
			if($function == "lookupRecordsAnyIdType") $idType = "id";

			$requestParameters = "?module=".$module."&function=".$function."&args={".$idType.":".$id."}";

			$htmlResult="";
			$ch = curl_init();

            curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_PROXY => $this->url_proxy,
                    CURLOPT_URL => $this->url_eck_base.$requestParameters)
            );

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
			$ch = curl_init();
            curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_PROXY => $this->url_proxy,
                    CURLOPT_URL => $this->url_eck_functions)
            );

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
			$ch = curl_init();
            curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_PROXY => $this->url_proxy,
                    CURLOPT_URL => $this->url_eck_languages)
            );

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
			$fields = array(
									'cmsId' => 88888,
									'persistentId' => 55555,
									'metadataFile' =>'c:\log.txt'
							);

			//url-ify the data for POST
            $fields_string = '';
			foreach($fields as $key=>$value){
                $fields_string .= $key.'='.$value.'&';
            }
			rtrim($fields_string, '&');	
			
			$ch = curl_init();

            curl_setopt_array($ch, array(
                    CURLOPT_POST => count($fields),
                    CURLOPT_POSTFIELDS => $fields_string,
                    CURLOPT_PROXY => $this->url_proxy,
                    CURLOPT_URL => $this->url_eck_record_add)
            );

			$result = curl_exec($ch);

			curl_close($ch);
			
			return $this->url_eck_record_add.$fields_string;
		}

         public function loadECKCoreConfigurations($conf_file_path){
             $o_config = Configuration::load($conf_file_path);

             $this->url_eck_record_add = $o_config->get('url_eck_core_record_add');
             $this->url_eck_functions = $o_config->get('url_eck_core_functions');
             $this->url_eck_languages = $o_config->get('url_eck_core_languages');
             $this->url_eck_base = $o_config->get('url_eck_core_base');
             $this->url_proxy = $o_config->get('url_proxy');
         }
		
	 }
	 
	 



	

