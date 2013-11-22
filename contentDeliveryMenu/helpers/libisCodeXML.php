<?php
require_once(__CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/helpers/KLogger.php');

class libisCodeXML {

    private $log;

    # Constructor
    public function __construct()
    {
        self::__set('log', KLogger::instance(__CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/logging/', KLogger::DEBUG));

    }

    # Setter
    public function __set($name, $value)
    {
        switch ($name)
        {
            case 'log':
                $this->log = $value;
                break;
        }
    }

    # Getter
    public function __get($name)
    {
        if (in_array($name, array('log')))
            return $this->$name;
    }



    function createXML($name, $path){
        $xmlFile = $path.'/'.$name.'.xml';
        libxml_use_internal_errors(true);
        $domDoc = new DOMDocument('1.0', 'UTF-8');
        if($domDoc->save($xmlFile) == false)
            return null;
        else
            return $xmlFile;
    }

    function initXML($xmlFile){

        $domDoc = new DOMDocument();
        $domDoc->formatOutput = true;
        $domDoc->preserveWhiteSpace = false;
        $domDoc->load($xmlFile);

        $rootElt = $domDoc->createElement('collection');

        $domDoc->appendChild($rootElt);
        $domDoc->save($xmlFile);
    }


    function addNode($xmlFile, $elementtoAdd, $value, $recordNumber, $tagValue=null,$subNodeTag=null){
        $domDoc = new DOMDocument();
        $domDoc->formatOutput = true;
        $domDoc->preserveWhiteSpace = false;
        $domDoc->load($xmlFile);
        $elementAddto = '';

        if(!isset($recordNumber))
            $elementAddto = $domDoc->documentElement;       //add to root node if no recordnumber given
        else{                                               //find record to add either its header or subfields
            $params = $domDoc->getElementsByTagName('record');
            $elementAddto = $params->item($recordNumber-1);
        }

        if(isset($elementAddto)){
            $node = $domDoc->createElement($elementtoAdd);

            if(isset($tagValue)){
                $tagAttribute = $domDoc->createAttribute('tag');
                $tagAttribute->value = $tagValue;
                $node->appendChild($tagAttribute);
            }

            $chidNode = $elementAddto->appendChild($node);

            //create empty indicators
            if($elementtoAdd === 'datafield'){
                $ind1Attribute = $domDoc->createAttribute('ind1');
                $ind1Attribute->value = ' ';
                $node->appendChild($ind1Attribute);

                $ind2Attribute = $domDoc->createAttribute('ind2');
                $ind2Attribute->value = ' ';
                $node->appendChild($ind2Attribute);
            }

            //add controlfield value
            if(isset($value)){
                if($elementtoAdd === 'controlefield'){
                    $ctrlNodeValue = $domDoc->createTextNode($value);
                    $chidNode->appendChild($ctrlNodeValue);
                }

                if(($elementtoAdd === 'leader')){
                    $leaderNodeValue = $domDoc->createTextNode($value);
                    $chidNode->appendChild($leaderNodeValue);
                }

            }

            if(isset($subNodeTag)){
                $subFieldNode = $domDoc->createElement('subfield');

                $subFieldTagAttr = $domDoc->createAttribute('code');
                $subFieldTagAttr->value = $subNodeTag;
                $subFieldNode->appendChild($subFieldTagAttr);

                $subNode = $node->appendChild($subFieldNode);
                if(isset($value)){
                    $nodeValue = $domDoc->createTextNode($value);
                    $subNode->appendChild($nodeValue);
                }
            }

            $domDoc->save($xmlFile);
            return $chidNode;
        }
        else
            return null;

    }

    function addRecordControlField($xmlFile, $recordNumber, $controlValue){

        $domDoc = new DOMDocument();
        $domDoc->formatOutput = true;
        $domDoc->preserveWhiteSpace = false;
        $domDoc->load($xmlFile);

        $params = $domDoc->getElementsByTagName('record');
        $recordNode = $params->item($recordNumber-1);
        if(isset($recordNode)){
            $node = $domDoc->createElement('controlfield');
            $nodeValue = $domDoc->createTextNode($controlValue);
            $childNode = $recordNode->appendChild($node);
            $childNode->appendChild($nodeValue);
        }
        $domDoc->save($xmlFile);
        return $childNode;
    }

}