<?php
/**
 * User: NaeemM
 * Date: 22/11/13
 */


class dmtService {
    const SUCCESS   =   200;

    private $url_base;
    private $url_proxy;

    private $dmt_provider;
    private $url_transform;
    private $dmt_batch_title;
    private $url_fetch_record;
    private $url_status_record;


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
            CURLOPT_URL => $this->url_base.'/'.$this->dmt_provider.'/'.$this->dmt_batch_title.
                           '/'.$this->url_status_record.'?request_id='.$requestId
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
            CURLOPT_URL => $this->url_base.'/'.$this->dmt_provider.'/'.$this->dmt_batch_title.
                '/'.$this->url_fetch_record.'?request_id='.$requestId
        ));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($httpCode === self::SUCCESS){
            return array('success' => true, 'response' => $response);
        }
        else
            return array('success' => false, 'response' => $response);
    }

    public function loadLibisCodeConfigurations($conf_file_path){
        $o_config = Configuration::load($conf_file_path);

        $this->url_proxy = $o_config->get('url_proxy');
        $this->url_base = $o_config->get('dmt_url_base');
        $this->dmt_provider = $o_config->get('dmt_provider');
        $this->url_transform = $o_config->get('url_transform');
        $this->dmt_batch_title = $o_config->get('dmt_batch_title');
        $this->url_fetch_record = $o_config->get('url_fetch_record');
        $this->url_status_record = $o_config->get('url_status_record');
    }

}