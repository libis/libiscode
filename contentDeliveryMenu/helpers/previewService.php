<?php
/**
 * Created by JetBrains PhpStorm.
 * User: NaeemM
 * Date: 10/06/13
 * Time: 12:01
 * To change this template use File | Settings | File Templates.
 */

class previewService {

    private $urlTemplateList = 'http://app.asp.hunteka.hu:5080/eck-preview-module/templates/';

    function getTemplates(){
        $htmlResult="";
        $url = $this->urlTemplateList;
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url)
        );
        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($responseCode == 200){
            $responseData = json_decode($result);
//            if(is_null($responseData)){
//                $htmlResult .= "null<br>";
//            }else{
//                foreach ($responseData as $name => $value) {
//                    $htmlResult .= $name . ':'.$value."<br>";
//                }
//            }

            $jsonIterator = new RecursiveIteratorIterator(	new RecursiveArrayIterator(json_decode($result, TRUE)), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($jsonIterator as $key => $val) {
                if(is_array($val)) {
                    $htmlResult .= "$key:<br>";
                } else {
                    $htmlResult .= "$key => $val,<br>";
                }
            }
            return $htmlResult;
        }
        else{
            return $responseCode;
        }

    }

}