<?php
/**
 * User: NaeemM
 * Date: 10/06/13
 */

class previewService {
    private $url_preview;
    private $url_proxy;
    private $lido_profile_name;
    private $lido_provider_name;

    # Constructor
    public function __construct()
    {
        $this->loadPreviewConfigurations(dirname(__FILE__).'/config/libiscode.conf');
    }

    function previewRecords($recordFile){

        $file_path_info = pathinfo($recordFile);
        $preview_file = $file_path_info['dirname'].'/'.'preview_'.$file_path_info['basename'];
        $preview_file = str_replace('.xml', '.html', $preview_file);
        $preview_file = str_replace('lido', '', $preview_file);         //should be replaced for edm

        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_PROXY => $this->url_proxy,
                CURLOPT_URL => $this->url_preview.'/'.$this->provider_name.'/'.$this->lido_provider_name.'/single/preview/'.$this->lido_profile_name,
                CURLOPT_HTTPHEADER => array('Content-Type: application/xml; charset=UTF-8', 'Accept: text/html')
            )
        );

        curl_setopt($curl,CURLOPT_POSTFIELDS, file_get_contents($recordFile));
        $response = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($responseCode == 200){
            file_put_contents($preview_file,print_r($response, true));

            $url = $_SERVER['REQUEST_URI'];
            $root_dir_path = current(explode('index.php', $url));

            $preview_file_url = 'http://'.$_SERVER['HTTP_HOST'].$root_dir_path.'app'.end(explode('app',$preview_file));
            $response = '<a  target=\'_blank\'  href="'.$preview_file_url.'">Click</a> to see the preview of '.basename($recordFile).'<br>';
            return $response;
        }
        else
            return 'Error ('.$responseCode. ') in preview service, while previewing '.basename($recordFile).', please try again.<br>';

    }

    public function loadPreviewConfigurations($conf_file_path){
        $o_config = Configuration::load($conf_file_path);

        $this->url_preview = $o_config->get('url_preview');
        $this->url_proxy = $o_config->get('url_proxy');
        $this->lido_profile_name = $o_config->get('lidoProfileName');
        $this->lido_provider_name = $o_config->get('lidoProvider');
    }

}