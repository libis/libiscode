<?php
/**
 * Created by JetBrains PhpStorm.
 * User: NaeemM
 * Date: 6/06/13
 * Time: 13:01
 */

class validationService {
    private $urlValidationMonguz = 'http://euinside.asp.monguz.hu/eck-validation-servlet/validation';
    private $monguzHost = 'euinside.asp.monguz.hu';

    private  $urlValidationSemnatika = 'http://euinside.semantika.si';
    private  $semantikaHost = 'euinside.semantika.si';

    function validateRecords($recordFile, $provider, $requestTitle, $profileName){
        $fields = array(
            'Name'          => $requestTitle,
            'record'        => '@'.$recordFile,
            'provider'      => $provider,
            'profileName'   => $profileName
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->urlValidationMonguz)
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Host:'.$this->monguzHost,
                'Content-Type: 	multipart/form-data')
        );
        curl_setopt($curl,CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($responseCode != 200)
            return $responseCode;
        else
            return $result;

    }

    function validateRecordsSemantika($recordFile, $provider, $requestTitle, $profileName){

        $fields = array(
            'Name'          => $requestTitle,
            'xmldoc'        => '@'.$recordFile,
            'XmlDocument'      => '@'.$recordFile
//            'Source'   => $profileName
        );

        $requestParameter = json_encode($fields);

        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->urlValidationSemnatika.'/validation/validate')
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Host: localhost:13987',
                'User-Agent: Fiddler',
                'Content-Type: application/json; charset=utf-8')
        );
        curl_setopt($curl,CURLOPT_POSTFIELDS, $requestParameter);

        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        return $responseCode;

//        if($responseCode != 200)
//            return $responseCode;
//        else
//            return $result;

    }




}