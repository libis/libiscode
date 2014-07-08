<?php
/**
 * Created by JetBrains PhpStorm.
 * User: NaeemM
 * Date: 6/06/13
 * Time: 13:01
 */

require_once(__CA_LIB_DIR__.'/vendor/autoload.php');

class validationService {

    private $url_proxy;
    private $profile_name;
    private $provider_name;
    private $url_validation;
    private $validation_host;
    private $url_validation_alternate;
    private $validation_alternate_host;


    # Constructor
    public function __construct()
    {
        $this->loadValidationConfigurations(dirname(__FILE__).'/config/libiscode.conf');
    }

    function validateRecords($recordFile){

        $fields = array(
            'record' => '@'.$recordFile
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_PROXY => $this->url_proxy,
                CURLOPT_URL => $this->url_validation.'/'.$this->provider_name.'/single/validate/'.$this->profile_name,
                CURLOPT_HTTPHEADER, array('Content-type: application/xml')
            )
        );
        curl_setopt($curl,CURLOPT_POSTFIELDS, $fields);
        $response = curl_exec($curl);

        return array('response_code' => curl_getinfo($curl, CURLINFO_HTTP_CODE), 'response_body' => json_decode($response, true));
    }

    function validateRecordsInBatch($recordFile){

        $fields = array(
            'record' => '@'.$recordFile
        );

        $set_name = $this->provider_name.round(microtime(true) * 1000);
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_PROXY => $this->url_proxy,
                CURLOPT_URL => $this->url_validation.'/'.$this->provider_name.'/'.$set_name.'/validate/'.$this->profile_name,
                CURLOPT_HTTPHEADER, array('Content-type: application/xml')
            )
        );
        curl_setopt($curl,CURLOPT_POSTFIELDS, $fields);
        $response = curl_exec($curl);

        return array('response_code' => curl_getinfo($curl, CURLINFO_HTTP_CODE), 'response_body' => json_decode($response, true));
    }


    public function loadValidationConfigurations($conf_file_path){
        $o_config = Configuration::load($conf_file_path);

        $this->url_validation = $o_config->get('url_validation');
        $this->url_validation_alternate = $o_config->get('url_validation_alternate');
        $this->validation_host = $o_config->get('validation_host');
        $this->validation_alternate_host = $o_config->get('validation_alternate_host');
        $this->url_proxy = $o_config->get('url_proxy');

        $this->profile_name = $o_config->get('profileName');
        $this->provider_name = $o_config->get('provider');

    }

}