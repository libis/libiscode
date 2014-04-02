<?php
/**
 * User: NaeemM
 * Date: 17/01/14
 */
require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/datapush/swordappclient.php");
//require_once(__CA_LIB_DIR__."/core/Logging/Eventlog.php");

class dataPushService {

    private $swordAppClient;

    private $urlDeposit;

    private $packageFormat;
    private $acceptedFormats = array();

    public  $dataPushOutputLogFile;
    private $dataPushOutputHeaders = array();
    private $dataPushOutput = array();

    private $url_sword_service_doc;
    private $datapush_on_behalf_of;
    private $datapush_password;
    private $collection_title;
    private $datapush_user;

    private $e_log;

    public function __construct(){
        $this->dataPushOutputLogFile = __CA_BASE_DIR__.
            "/app/plugins/contentDeliveryMenu/files/datapushoutput/datapushoutput".round(microtime(true) * 1000).".csv";
        $this->swordAppClient = new SWORDAPPClient();
        $this->dataPushOutputHeaders = array('Data File', 'Resource Id', 'Resource URL');

        $this->loadDataPushConfigurations(dirname(__FILE__).'/config/libiscode.conf');
    }


    function initDataPushOutputFile($dataPushOutPutFile){
        if (($dataPushFileHandle = fopen($dataPushOutPutFile, "w")) === FALSE) {
//            $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'datapush',
//                'MESSAGE' => _t('Error in initializing datapush output file(%1)', $this->dataPushOutputLogFile)
//            ));
            return false;
        }
        fputcsv($dataPushFileHandle, $this->dataPushOutputHeaders);
        return true;
    }

    function pushData($dataFiletoPush, $dataFormat){
        $dataFiletoPush = trim(trim($dataFiletoPush, '.'));
        $this->getServiceDocument();

        unset($this->dataPushOutput);

        if (($dataPushFileHandle = fopen($this->dataPushOutputLogFile, "a")) === FALSE) {
//            $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'datapush',
//                'MESSAGE' => _t('Error in opening datapush output file(%1)', $this->dataPushOutputLogFile)
//            ));
            return;
        }
        $this->dataPushOutput [] = rtrim(basename($dataFiletoPush), '.');
        if(isset($this->urlDeposit, $this->datapush_user, $this->datapush_password, $dataFiletoPush,$dataFormat)){

            if (in_array($dataFormat, $this->acceptedFormats))
            {
                $depositResponse = $this->swordAppClient->deposit(
                    $this->urlDeposit, $this->datapush_user, $this->datapush_password, $this->datapush_on_behalf_of, $dataFiletoPush, $this->packageFormat, $dataFormat);
                if ($depositResponse->sac_status == 201) {  //successfully created on sword server

                    $resourceId='';
                    $sec_id = explode('/dpp/',$depositResponse->sac_id);
                    if(isset($sec_id[1]))
                        $resourceId = $sec_id[1];
                    $this->dataPushOutput [] = $resourceId;
                    $this->dataPushOutput [] = $depositResponse->sac_id;
                }

                else{
                    $this->dataPushOutput [] = $depositResponse->sac_status;
                    $this->dataPushOutput [] = $depositResponse->sac_statusmessage;
                }
            }
            else
            {
                $supportedFormats = '';
                if(isset($this->acceptedFormats)){
                    foreach($this->acceptedFormats as $value)
                        $supportedFormats .= $value.', ';
                }
                $this->dataPushOutput [] = $dataFormat. ' format is not supported.';
                $this->dataPushOutput [] = 'Supported Formats: '.$supportedFormats;
            }


        }
        else
            $this->dataPushOutput [] = 'No deposit url available.';

        fputcsv($dataPushFileHandle, $this->dataPushOutput);
        fclose($dataPushFileHandle);
//        $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'dataPushService',
//            'MESSAGE' => _t('Datapush output: %1', implode(',' , $this->dataPushOutput))));
    }

    function getServiceDocument(){
        $swordAppClient = new SWORDAPPClient();

        $swordServiceDocument = $swordAppClient->servicedocument($this->url_sword_service_doc, $this->datapush_user, $this->datapush_password, $this->datapush_on_behalf_of);

        if ($swordServiceDocument->sac_status == 200) {
            foreach ($swordServiceDocument->sac_workspaces as $workspace) {
                $collections = $workspace->sac_collections;
                foreach ($collections as $collection) {
                    $ctitle = $collection->sac_colltitle;
                    if($ctitle === $this->collection_title){
                        $this->urlDeposit = $collection->sac_href;
                        if (count($collection->sac_accept) > 0) {
                            foreach ($collection->sac_accept as $accept) {
                                $this->acceptedFormats[] = "".$accept;
                            }
                        }
                        if (count($collection->sac_acceptpackaging) > 0) {
                            foreach ($collection->sac_acceptpackaging as $acceptpackaging => $q) {
                                $this->packageFormat = $acceptpackaging . " (q=" . $q . ")\n";
                            }
                        }

                    }

                }
            }

        }

    }

    public function loadDataPushConfigurations($conf_file_path){
        $o_config = Configuration::load($conf_file_path);

        $this->datapush_user = $o_config->get('datapush_user');
        $this->datapush_password = $o_config->get('datapush_password');
        $this->datapush_on_behalf_of = $o_config->get('datapush_on_behalf_of');
        $this->url_sword_service_doc = $o_config->get('url_sword_service_doc');
        $this->collection_title = $o_config->get('collection_title');
    }

}

