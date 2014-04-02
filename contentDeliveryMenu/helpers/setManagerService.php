<?php
/**
 * Created by JetBrains PhpStorm.
 * User: NaeemM
 * Date: 10/06/13
 * Time: 12:20
 * To change this template use File | Settings | File Templates.
 */

class setManagerService {

    private $url_set_manager_list;
    private $url_set_manager_commit;
    private $url_set_manager_status;
    private $url_test_record_file;      //only for test purposes

    # Constructor
    public function __construct()
    {
        $this->loadSetManagerConfigurations(dirname(__FILE__).'/config/libiscode.conf');
    }


    function getList(){
        $htmlResult="";
        $curl = curl_init();

        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_PROXY => $this->url_proxy,
                CURLOPT_URL => $this->url_set_manager_list)
        );

        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($responseCode == 200){
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

    function commit(){
        $fields = array(
            'records' => '@'.$this->url_test_record_file,
            'setDescription' => 'A lido set from Libis');
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POSTFIELDS => $fields,
                CURLOPT_PROXY => $this->url_proxy,
                CURLOPT_URL => $this->url_set_manager_commit)
        );

        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($responseCode == 202)
            return 'Record has been submitted successfully for processing(Response Code = '.$responseCode.'). ';
        else
            return 'Response Code = '.$responseCode;

    }

    function getStatus(){
        $htmlResult="";
        $curl = curl_init();

        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_PROXY => $this->url_proxy,
                CURLOPT_URL => $this->url_set_manager_status)
        );

        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($responseCode == 200){
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

    public function loadSetManagerConfigurations($conf_file_path){
        $o_config = Configuration::load($conf_file_path);

        $this->url_set_manager_list = $o_config->get('url_set_manager_list');
        $this->url_set_manager_commit = $o_config->get('url_set_manager_commit');
        $this->url_set_manager_status = $o_config->get('url_set_manager_status');
        $this->url_proxy = $o_config->get('url_proxy');

        $this->url_test_record_file = $o_config->get('test_record_set_manager');
    }

}