<?php
require_once(__CA_MODELS_DIR__.'/ca_metadata_elements.php');
require_once(__CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/helpers/KLogger.php');
require_once(__CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/helpers/libisCodeXML.php');

class marcXMLGeneration {
    private $log;
    private $xml;

    # Constructor
    public function __construct()
    {
        self::__set('log', KLogger::instance(__CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/logging/', KLogger::DEBUG));
        self::__set('xml', new libisCodeXML());

    }

    # Setter
    public function __set($name, $value)
    {
        switch ($name)
        {
            case 'log':
                $this->log = $value;
                break;

            case 'xml':
                $this->xml = $value;
                break;
        }
    }

    # Getter
    public function __get($name)
    {
        if (in_array($name, array('log')))
            return $this->$name;

        if (in_array($name, array('xml')))
            return $this->xml;
    }

    function marcGeneration($marcRecords){

        $directoryPath = __CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/files/'.round(microtime(true) * 1000);
        if (!file_exists($directoryPath))
            mkdir($directoryPath);

        $fileName = round(microtime(true) * 1000);

        $recordNumber = 1;

        $marcXML = $this->xml->createXML($fileName, $directoryPath);
        if(isset($marcXML)){
            $this->log->logInfo('Marc XML file creation successful: '. $marcXML);

            $this->xml->initXML($marcXML);      //init XML for Marc

            $this->xml->loadXMLFile($marcXML);

            foreach($marcRecords as $record){

                $records = $record;
                $recordIdNo = $records['marc001'];
                $this->initRecord($marcXML);        //init a record

                //add record's header fields
                $leaderString = $this->getLeaderValue($record);
                $controlFieldTag = $this->dataFieldParser('marc001');

                $this->xml->addNode('leader', $leaderString, $recordNumber);      //create leader node

                $this->xml->addNode('controlefield', $recordIdNo, $recordNumber, $controlFieldTag);  //create controlfield node

                //add record's subfields
                foreach($records as $element=>$value){

                        if(strpos($element, 'leader') === false && strpos($element, 'marc001') === false
                        && strpos($element, 'edm') === false)       //marc001 == controlefield, edm filters out edm records
                        {
                            if(is_array($value)){
                                foreach($value as $subValue)
                                    $subField = $this->processMarcElement( $element, $subValue, $marcXML, $recordNumber);           //create record sub nodes
                            }
                            else
                                $subField = $this->processMarcElement( $element, $value, $marcXML, $recordNumber);           //create record sub nodes
                        }


                    if(!isset($subField))
                        $this->log->logError('Element[ Code:'.$element. ', Value:'.$value.'] could not be transformed' );
                    else
                        $this->log->logInfo('Element[ Code:'.$element. ', Value:'.$value.'] successfuly transformed' );
                }
                $recordNumber++;
            }

            $this->xml->saveXMLFile($marcXML);
        }
        else{
            $this->log->logInfo('Marc XML file creation failed');
        }
        return array($directoryPath, $fileName);
    }

    function processMarcElement( $elementCode, $elementValue, $marcXML, $recordNumber){

        $dataFieldTag = $this->dataFieldParser($elementCode);
        $subFieldTags = $this->subFieldParser($elementCode);
        if(isset($subFieldTags[0]))
            $subFieldTag = $subFieldTags[0];
        else
            $subFieldTag =null;

        $nodeName = 'datafield';

        $addedNode =  $this->xml->addNode($nodeName, $elementValue, $recordNumber, $dataFieldTag, $subFieldTag);
        return $addedNode;
    }

    function dataFieldParser($elementCode){
        $dataFieldTag = '';
        $element =  explode('marc', $elementCode);
        if(isset($element[1]))
            $dataFieldTag = substr($element[1], 0, 3);
        return $dataFieldTag;
    }

    function subFieldParser($elementCode){
        $subFieldTags = '';
        $element =  explode('marc', $elementCode);
        if(isset($element[1]))
            $subFieldTags = substr($element[1],3);
        return str_split($subFieldTags);        //if there are multiple tags (e.g. abc) they will be put in an array
    }

    function initRecord($marcXML){
        $this->xml->addNode('record', null, null);
    }

    function getLeaderValue($record){
        $leaderCode = '';
        foreach(array_keys($record) as $key){
            if (strpos($key,'leader') !== false)
                $leaderCode = $key;
        }

        return isset($record[$leaderCode])? $record[$leaderCode] : ' ';

    }

}
