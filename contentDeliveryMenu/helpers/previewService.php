<?php
/**
 * User: NaeemM
 * Date: 10/06/13
 */

class previewService {
    private $url_preview;
    private $url_proxy;
    private $edm_profile_name;
    private $edm_provider_name;

    # Constructor
    public function __construct()
    {
        $this->loadPreviewConfigurations(dirname(__FILE__).'/config/libiscode.conf');
    }

    ////Multiple records in a single xml file, preview at once
    ////Currently this is being used
    function previewBatchRecords($recordFile){

        $file_path_info = pathinfo($recordFile);
        $preview_file = $file_path_info['dirname'].'/'.'preview_'.$file_path_info['basename'];
        $preview_file = str_replace('.xml', '.html', $preview_file);
        $preview_file = str_replace('edm', '', $preview_file);

        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_PROXY => $this->url_proxy,
                CURLOPT_URL => $this->url_preview.'/'.$this->provider_name.'/'.$this->edm_provider_name.'/single/preview/'.$this->edm_profile_name,
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

    function previewSingleRecord($record_content){

        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_PROXY => $this->url_proxy,
                CURLOPT_URL => $this->url_preview.'/'.$this->provider_name.'/'.$this->edm_provider_name.'/single/preview/'.$this->edm_profile_name,
                CURLOPT_HTTPHEADER => array('Content-Type: application/xml; charset=UTF-8', 'Accept: text/html')
            )
        );

        curl_setopt($curl,CURLOPT_POSTFIELDS, $record_content);
        $response = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($responseCode == 200)
            return $response;
        else
            return 'Error ('.$responseCode. ') in preview service, please try again.<br>';

    }

    ////Multiple records in a single xml file, each record is extracted from the xml and its preview is get
    public function previewRecords($recordFile){

        $domDoc = new DOMDocument();
        $domDoc->formatOutput = true;
        $domDoc->preserveWhiteSpace = false;
        $domDoc->load($recordFile);
        $params = $domDoc->getElementsByTagName('RDF');
        $counter = 1;
        $record_preview ="";
        foreach ($params as $param) {

            $param_id = $param->getElementsByTagName('identifier');
            $value = ($param_id->length > 0) ? $param_id->item(0)->nodeValue : '';
            $record_number = 'Record Number '.$counter.'. Identifier:'.$value.'<br>';

            $newDoc = new DOMDocument;
            $rdfNode = $newDoc->importNode($param, true);
            $newDoc->appendChild($rdfNode);
            $record_content = $newDoc->saveXML();
            $record_preview .=$record_number.'<br>'.$this->previewSingleRecord($record_content).'<br>';
            $newDoc = null;

            $counter++;
        }

        $file_path_info = pathinfo($recordFile);
        $preview_file = $file_path_info['dirname'].'/'.'preview_'.$file_path_info['basename'];
        $preview_file = str_replace('.xml', '.html', $preview_file);
        $preview_file = str_replace('edm', '', $preview_file);         //should be replaced for edm

        file_put_contents($preview_file,$record_preview."\n");
        $url = $_SERVER['REQUEST_URI'];
        $root_dir_path = current(explode('index.php', $url));

        $preview_file_url = 'http://'.$_SERVER['HTTP_HOST'].$root_dir_path.'app'.end(explode('app',$preview_file));
        $response = '<a  target=\'_blank\'  href="'.$preview_file_url.'">Click</a> to see the preview of '.basename($recordFile).'<br>';
        return $response;


    }

    public function loadPreviewConfigurations($conf_file_path){
        $o_config = Configuration::load($conf_file_path);

        $this->url_preview = $o_config->get('url_preview');
        $this->url_proxy = $o_config->get('url_proxy');
        $this->edm_profile_name = $o_config->get('edmProfileName');
        $this->edm_provider_name = $o_config->get('edmProvider');
    }

}