<?php
/**
 * Created by JetBrains PhpStorm.
 * User: NaeemM
 * Date: 6/06/13
 * Time: 13:01
 */

class validationService {
    private $urlValidation = 'http://euinside.semantika.si/validation/validate';
    private $urlValidation2 = 'http://app.asp.hunteka.hu:5080/eck-validation-module/profiles/lido/validate/';
    private $urlProfileList = 'http://euinside.semantika.si/validation/profiles';

    function validate(){

        $fields = array(
            'Name' => 'MYRecord',
            //'XmlDocument' => '@C:/Kaam/Projecten/Europeana/EuropeanaInside/REST Services/validationrecords/lido1.xml'
            'XmlDocument' => 'a'
        );
        $requestParameter = json_encode($fields);

        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->urlValidation)
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Host: euinside.semantika.si',
                'User-Agent: Fiddler',
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

    function validate2(){

        $fields = array(
            'Record' => '@C:/Kaam/Projecten/Europeana/EuropeanaInside/REST Services/validationrecords/lido1.xml'
            //'Record' => 'test'
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->urlValidation2)
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: text/xml')
        );

        curl_setopt($curl,CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($responseCode != 200)
            return $responseCode;
        else
            return $result;
    }

    function getProfiles(){
        $htmlResult="";
        $url = $this->urlProfileList;
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url)
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

    function getProfileByName($profileName){
        $htmlResult="";
        $url = $this->urlProfileList."/".$profileName;
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url)
        );
        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($responseCode != 200)
            return $responseCode;
        else
            return json_decode($result);
    }


}