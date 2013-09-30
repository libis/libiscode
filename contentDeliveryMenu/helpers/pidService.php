<?php
/**
 * A class to make PID Service requests.
 * User: Naeem Muhammad
 * Date: 6/06/13
 */

class pidService {
    private $urlPIDLookup = 'http://euinside.semantika.si/pid/lookup/';
    private $urlPIDGeneration = 'http://euinside.semantika.si/pid/generate';


    function generatePID($institutionUrl, $recordType, $accessionNumber ){
        $array = array('InstitutionUrl' => $institutionUrl,
            'RecordType' => $recordType, 'AccessionNumber' => $accessionNumber);

        $requestParameter = json_encode($array);
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->urlPIDGeneration)
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Host: euinside.semantika.si',
                'Content-Type: application/json; charset=utf-8')
        );
        curl_setopt($curl,CURLOPT_POSTFIELDS, $requestParameter);

        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($responseCode != 200)
            return $responseCode;
        else
            return $result;

    }

    function lookupPID($pid){
        $htmlResult="";

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $this->urlPIDLookup.$pid)
        );
        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

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

        curl_close($curl);

       return $htmlResult;
    }



}