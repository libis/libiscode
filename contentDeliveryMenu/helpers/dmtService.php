<?php
/**
 * User: NaeemM
 * Date: 22/11/13
 */


class dmtService {
    const SUCCESS   =   200;

    protected $url_base;
    protected $url_proxy;

    protected $url_transform;
    protected $url_fetch_record;
    protected $url_status_record;


    # Constructor
    public function __construct()
    {
        $this->loadLibisCodeConfigurations(dirname(__FILE__).'/config/libiscode.conf');
    }

    public function mappingSingleFile($recordFile, $mappingFile, $sourceFormat, $targetFormat){
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
            CURLOPT_PROXY => $this->url_proxy,
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

    public  function requestStatus($requestId){
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_PROXY => $this->url_proxy,
            CURLOPT_URL => $this->url_base.$this->url_status_record.'?request_id='.$requestId
        ));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseData = json_decode($response);

        if($httpCode === self::SUCCESS){
            return $responseData->{'status_code'};
        }
    }

    public  function fetchRecord($requestId){
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_PROXY => $this->url_proxy,
            CURLOPT_URL => $this->url_base.$this->url_fetch_record.'?request_id='.$requestId
        ));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($httpCode === self::SUCCESS){
           return $response;
        }
    }

    public function loadLibisCodeConfigurations($conf_file_path){
        $o_config = Configuration::load($conf_file_path);

        $this->url_base = $o_config->get('dmt_url_base');
        $this->url_proxy = $o_config->get('url_proxy');

        $this->url_transform = $o_config->get('url_transform');
        $this->url_fetch_record = $o_config->get('url_fetch_record');
        $this->url_status_record = $o_config->get('url_status_record');
    }

}