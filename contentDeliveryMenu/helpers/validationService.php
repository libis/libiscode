<?php
/**
 * Created by JetBrains PhpStorm.
 * User: NaeemM
 * Date: 6/06/13
 * Time: 13:01
 */

class validationService {

    private $url_validation_monguz;
    private $url_validation_semantika;
    private $validation_host_monguz;
    private $validation_host_semantika;
    private $url_proxy;

    # Constructor
    public function __construct()
    {
        $this->loadValidationConfigurations(dirname(__FILE__).'/config/libiscode.conf');
    }

    function validateRecordsMonguz($recordFile, $provider, $requestTitle, $profileName){
        $fields = array(
            'Name'          => $requestTitle,
            'record'        => '@'.$recordFile,
            'provider'      => $provider,
            'profileName'   => $profileName
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_PROXY => $this->url_proxy,
                CURLOPT_URL => $this->url_validation_monguz)
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Host:'.$this->validation_host_monguz,
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
                CURLOPT_PROXY => $this->url_proxy,
                CURLOPT_URL => $this->url_validation_semantika.'/validation/validate',
                CURLOPT_POSTFIELDS => $requestParameter,
                CURLOPT_HTTPHEADER => array(
                    'Host:'.$this->validation_host_semantika,
                    'User-Agent: Fiddler',
                    'Content-Type: application/json; charset=utf-8'))
        );

        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($responseCode != 200)
            return $responseCode;
        else
            return $result;

    }

    public function loadValidationConfigurations($conf_file_path){
        $o_config = Configuration::load($conf_file_path);

        $this->url_validation_monguz = $o_config->get('url_validation_monguz');
        $this->url_validation_semantika = $o_config->get('url_validation_semantika');
        $this->validation_host_monguz = $o_config->get('validation_host_monguz');
        $this->validation_host_semantika = $o_config->get('validation_host_semantika');
        $this->url_proxy = $o_config->get('url_proxy');

    }

}