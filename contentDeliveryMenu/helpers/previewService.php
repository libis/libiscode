<?php
/**
 * User: NaeemM
 * Date: 10/06/13
 */

class previewService {
    private $url_preview;
    private $url_proxy;

    # Constructor
    public function __construct()
    {
        $this->loadPreviewConfigurations(dirname(__FILE__).'/config/libiscode.conf');
    }

    function getTemplates($provider){
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_PROXY => $this->url_proxy,
                CURLOPT_URL => $this->url_preview.'/'.$provider.'/templates')
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

    public function loadPreviewConfigurations($conf_file_path){
        $o_config = Configuration::load($conf_file_path);

        $this->url_preview = $o_config->get('url_preview_monguz');
        $this->url_proxy = $o_config->get('url_proxy');
    }

}