<?php
/**
 * User: NaeemM
 * Date: 10/06/13
 */

class previewService {

    private $urlPreviewMonguz = 'http://euinside.asp.monguz.hu/eck-preview-module/Preview';

    function getTemplates($provider){

        $url = $this->urlPreviewMonguz.'/'.$provider.'/templates';
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url)
        );
        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($responseCode == 200){
            return $result;
        }
        else
            return $responseCode;
    }


}