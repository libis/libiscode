<?php
/**
 * A class to make PID Service requests.
 * User: Naeem Muhammad
 * Date: 6/06/13
 */

class pidService {

    private $url_pid_generation;
    private $url_pid_lookup;
    private $url_proxy;
    private $pid_host;

    # Constructor
    public function __construct()
    {
        $this->loadPIDConfigurations(dirname(__FILE__).'/config/libiscode.conf');
    }

    function generatePID($institutionUrl, $recordType, $accessionNumber ){
        $array = array('InstitutionUrl' => $institutionUrl,
            'RecordType' => $recordType, 'AccessionNumber' => $accessionNumber);

        $requestParameter = json_encode($array);
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_PROXY => $this->url_proxy,
                CURLOPT_URL => $this->url_pid_generation)
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Host: '.$this->pid_host,
                'Content-Type: application/json; charset=utf-8')
        );
        curl_setopt($curl,CURLOPT_POSTFIELDS, $requestParameter);

        $pid = '';
        $result = '';
        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $contentType = explode(';',curl_getinfo($curl, CURLINFO_CONTENT_TYPE));
        if($contentType[0] === 'application/xml' && strlen($result) > 0){
            $doc = new DOMDocument;
            $doc->loadXML($result);
            //pid is returned as xml string in a node called 'string'
            $pid = $doc->getElementsByTagName('string')->item(0)->nodeValue;
        }

        return array('response_code' => $responseCode, 'pid' => trim($pid, '"'));
    }

    function lookupPID($pid){
        $htmlResult="";

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_PROXY => $this->url_proxy,
        CURLOPT_URL => $this->url_pid_lookup.'/'.$pid)
        );
        $result = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($responseCode == 200){
            $responseData = json_decode($result);
            if(is_null($responseData)){
                $htmlResult .= "null<br>";
            }else{
                foreach ($responseData as $name => $value) {
                    $htmlResult .= $name . ':'.$value."<br>";
                }
            }
        }
        else{
            $htmlResult .= "Response Code = ".$responseCode."<br>";
            if(is_null($result))
                $htmlResult .= "null"."<br>";
            else
                $htmlResult .= "Request Unsuccessful: ".$result."<br>";
        }

        curl_close($curl);

       return $htmlResult;
    }

    public function loadPIDConfigurations($conf_file_path){
        $o_config = Configuration::load($conf_file_path);

        $this->url_pid_generation = $o_config->get('url_pid_generation');
        $this->url_pid_lookup = $o_config->get('url_pid_lookup');
        $this->url_proxy = $o_config->get('url_proxy');
        $this->pid_host = $o_config->get('pid_host');


    }

}