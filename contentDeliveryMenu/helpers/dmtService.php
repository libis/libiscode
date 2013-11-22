<?php
/**
 * User: NaeemM
 * Date: 22/11/13
 */

class dmtService {
    const SUCCESS   =   200;

    protected $url_base  = 'http://localhost/euInside/dmt.php/DataMapping';
    protected $url_transform = '/Libis/my_tansfer/Transform';
    protected $url_fetch_record = '/Libis/my_tansfer/fetch';
    protected $url_status_record = '/Libis/my_tansfer/status';

    public  function mappingSingleFile($recordFile, $mappingFile, $sourceFormat, $targetFormat){
        $ch = curl_init();
        $fields = array(
            'record' => '@'.$recordFile,
            'mappingRulesFile' => '@'.$mappingFile,
            'sourceFormat' => $sourceFormat,
            'targetFormat' => $targetFormat
        );

        curl_setopt_array($ch, array(
            CURLOPT_POST => 1,
            CURLOPT_URL => $this->url_base.$this->url_transform,
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_RETURNTRANSFER => 1
        ));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseData = json_decode($response);
        if($httpCode ===  self::SUCCESS){
            return $responseData->{'request_id'};
        }
    }

}