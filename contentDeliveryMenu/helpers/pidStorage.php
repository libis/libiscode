<?php
/**
 * User: NaeemM
 * Date: 3/12/13
 */

require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/pidService.php");
require_once(__CA_LIB_DIR__."/core/Logging/Eventlog.php");
require_once(__CA_APP_DIR__.'/models/ca_attributes.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');

define('PID_ELEMENT', 'eckPid');

class pidStorage {
    private $pidService;
    private $attribute;
    private $pid_field;
    private $c_object;
    private $c_locale;
    private $e_log;
    private $db;


    # Constructor
    public function __construct()
    {
        self::__set('pidService', new pidService());
        self::__set('attribute', new ca_attributes());
        self::__set('c_object', new ca_objects());
        self::__set('c_locale', new ca_locales());
        self::__set('e_log', new Eventlog());
        self::__set('db', $this->attribute->getDb());

        $this->loadPIDConfigurations(dirname(__FILE__).'/config/libiscode.conf');
    }

    # Setter
    public function __set($name, $value)
    {
        switch ($name)
        {
            case 'pidService':
                $this->pidService = $value;
                break;

            case 'attribute':
                $this->attribute = $value;
                break;

            case 'c_locale':
                $this->c_locale = $value;
                break;

            case 'c_object':
                $this->c_object = $value;
                break;

            case 'e_log':
                $this->e_log = $value;
                break;

            case 'db':
                $this->db = $value;
                break;
        }
    }

    # Getter
    public function __get($name)
    {
        if (in_array($name, array('pidService')))
            return $this->$name;

        if (in_array($name, array('attribute')))
            return $this->$name;

        if (in_array($name, array('c_locale')))
            return $this->$name;

        if (in_array($name, array('c_object')))
            return $this->$name;

        if (in_array($name, array('e_log')))
            return $this->$name;

        if (in_array($name, array('db')))
            return $this->$name;
    }

    function findElement($elementCode){
        $qr_res = $this->db->query("
					SELECT *
					FROM ca_metadata_elements
					WHERE element_code = ?
				", $elementCode);
        if(isset($qr_res)){
            $qr_res->nextRow();
            return $qr_res->get('element_id');
        }
        else
            return null;
    }

    function findAttribute($elementId, $rowId){
        $qr_res = $this->db->query("
					SELECT *
					FROM ca_attributes
					WHERE element_id = ?
					AND row_id = ?
				", $elementId, $rowId);
        if(isset($qr_res)){
            $qr_res->nextRow();
            return $qr_res->get('attribute_id');
        }
        else
            return null;

    }

    function addAttribute($elementId, $rowId){
        $locale = 1;
        $table_number = 57;
        $qr_res = $this->db->query("
					INSERT INTO ca_attributes
					(element_id, locale_id, table_num, row_id )
					VALUES (?, ?, ?, ? )
				", $elementId, $locale, $table_number, $rowId);
        return $this->findAttribute($elementId, $rowId);
    }

    function findObject($idNo){
        $qr_res = $this->db->query("
					SELECT *
					FROM ca_objects
					WHERE idno = ?
				", $idNo);
        if(isset($qr_res)){
            $qr_res->nextRow();
            return $qr_res->get('object_id');
        }
        else
            return null;

    }

    function addPid($elementId, $attributeId, $pidValue){
        $existingPid = $this->getPid($elementId, $attributeId);
        if(isset($existingPid))                         //update pid value if it already exists
        {
            $this->db->query("
					UPDATE ca_attribute_values
					SET value_longtext1 = ?
					WHERE element_id = ?
					AND attribute_id = ?
				", $pidValue, $elementId, $attributeId);
        }
        else{                                           //insert pid value if it does not exists
            $this->db->query("
					INSERT INTO ca_attribute_values
					(element_id, attribute_id, value_longtext1)
					VALUES (?, ?, ?)
				", $elementId, $attributeId, $pidValue);
        }

    }

    function getPid($elementId, $attributeId){
        $qr_res = $this->db->query("
					SELECT *
					FROM ca_attribute_values
					WHERE element_id = ?
					AND attribute_id = ?
				", $elementId, $attributeId);
        if(isset($qr_res)){
            $qr_res->nextRow();
            return $qr_res->get('value_longtext1');
        }
        else
            return null;
    }

    function storePid($pidCode, $pidValue, $idNo){
        $objectId = $this->findObject($idNo);

        $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation_storage',
            'MESSAGE' => sprintf('Object Id: %s', $objectId)));

        if(isset($objectId)){
            $pidElementId = $this->findElement($pidCode);

            $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation_storage',
                'MESSAGE' => sprintf('Element Id: %s', $pidElementId)));

            if($pidElementId){
                $attributeId = $this->findAttribute($pidElementId, $objectId);

                $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation_storage',
                    'MESSAGE' => sprintf('Attribute Id: %s', $attributeId)));

                if(!isset($attributeId))
                    $attributeId = $this->addAttribute($pidElementId, $objectId);

                $this->addPid($pidElementId, $attributeId, $pidValue);


                return $this->getPid($pidElementId, $attributeId);
            }
        }
        else
            return 'idno('.$idNo.') does not exists.';
    }

    function generateRecordPID($edmRecordFile){

        $domDoc = new DOMDocument();
        $domDoc->formatOutput = true;
        $domDoc->preserveWhiteSpace = false;

        $domDoc->load($edmRecordFile);

        $value = array();
        $result = array();

        $params = $domDoc->getElementsByTagName('ProvidedCHO');
        foreach ($params as $param) {
            $subElements = $param->getElementsByTagName('identifier');
            foreach($subElements as $element){
                $recrodIdentifier = $element->nodeValue;

                $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation_storage',
                    'MESSAGE' => sprintf('Record Identifier: %s', $recrodIdentifier)));

                $pidResponse = $this->pidService->generatePID('libis.kuleuven.be' , 'object', $recrodIdentifier);

                if(isset($pidResponse['response_code']) && $pidResponse['response_code'] == 200){
                    $pid =  $pidResponse['pid'];

                    $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation_storage',
                        'MESSAGE' => sprintf('PID: %s', $pid)));

                    $value['recordidentifier'] = $recrodIdentifier;
                    $value['generatedpid'] = $pid;

                    //add in edm xml file
                    $childNode = $domDoc->createElement('dc:pid');
                    $nodeValue = $domDoc->createTextNode($pid);
                    $child = $param->appendChild($childNode);            //add newley created node to root or the given node
                    $child->appendChild($nodeValue);                    //assign value to the newly created node element

                    //add in collectiveaccess database
                    $isPIDAdded = $this->storePid($this->pid_field, $pid, $recrodIdentifier);

//                    $isPIDAdded = $this->addPIDInDb($recrodIdentifier, $this->pid_field, $pid);

                    if($isPIDAdded){
                        $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation_storage',
                            'MESSAGE' => sprintf('PID(%s) for record %s successfully stored.', $pid, $recrodIdentifier)));
                        $value['storedpid'] = $isPIDAdded;
                    }

                    else
                        $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation_storage',
                            'MESSAGE' => sprintf('PID(%s) for record %s could not be added stored.', $pid, $recrodIdentifier)));

                    $result[]= $value;

                }else
                {
                    $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation_storage',
                        'MESSAGE' => sprintf('PID for record %s could not be created.
                        PID service returned with response code: %s',$recrodIdentifier, $pidResponse['response_code'])));

                    $result[] = array(
                        'recordidentifier' => $recrodIdentifier,
                        'generatedpid'     => 'PID for this record could not be created.
                                               PID service returned with response code: '.
                                               $pidResponse['response_code']
                    );
                }



            }
        }
        $domDoc->save($edmRecordFile);


        return $result;
    }

    function addPIDInDb($idNo, $pidCode, $pid){

        $objectId = $this->findObject($idNo);

        $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation_storage',
            'MESSAGE' => sprintf('object_id: %s.', $objectId)));

        $this->c_object->setMode(ACCESS_WRITE);
        $this->c_object->load($objectId);
        $this->c_object->getPrimaryKey();
        $localeId = $this->c_locale->getDefaultCataloguingLocaleID();

        $this->c_object->addAttribute(array(
            'eckPid' => $pid,
            'locale_id' => $localeId
        ), $pidCode);

        return $this->c_object->update();
    }

    public function loadPIDConfigurations($conf_file_path){
        $o_config = Configuration::load($conf_file_path);

        $this->pid_field= $o_config->get('pid_field');
    }
} 