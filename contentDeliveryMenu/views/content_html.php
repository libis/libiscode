<?php
require_once(__CA_MODELS_DIR__.'/ca_sets.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/helpers/dmtService.php');
require_once(__CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/helpers/KLogger.php');
require_once(__CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/helpers/marcXMLGeneration.php');


ini_set('memory_limit','2048M');
set_time_limit(600000);

define('CA_OBJECT_TABLE_NUMBER', 57);
$log = KLogger::instance(__CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/logging/', 7);
$marcXMLFile = '';

$t_set = new ca_sets();
$availableSets = $t_set->getSets();
$o_db = $t_set->getDb();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>Libis Content Delivery System</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />

        <script src="js/ajaxsbmt.js" type="text/javascript"></script>

    </head>
    <body>
        <div>
            <h1>Libis Content Delivery System</h1>
            <h2 align="center" style="font: bold">Control Board</h2>
        </div>

        <div align = center>
            <table border="0" align="center" cellspacing="10" width="100%" style="border-width: 1px;
                           border-color:#000000; border-style: solid;">
                <tr>
                    <form action="<?php echo dirname($_SERVER['SCRIPT_NAME'])."/index.php/contentDeliveryMenu/ContentDelivery/Index/universe/Content List"; ?>" method="post">
                    <td align="left">
                        <strong style="font-size: 10px;">SET:</strong>
                        <select name="selectSet"">
                            <?php
                            foreach($availableSets as $sets){
                                foreach($sets as $set){
                                    echo '<option value = '.$set['set_id'].'>'.$set['name'].'</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td align="left">
                        <strong style="font-size: 10px; text-align: left">Target Format:</strong>
                        <select name="selectGenerationFormat" ">
                        <option value="marc">MARC</option>
                        <option value="lido">LIDO</option>
                        </select>
                    </td>
                    <td align="right">
                        <input type="submit" value="Generate XML" name="xmlGeneration">
                    </td>
                    </form>
                </tr>
                <tr>

                </tr>
            </table>
            <br>
        </div>

    </body>
</html>

<?php

if(isset($_POST['selectSet']) && $_POST['selectGenerationFormat']){

    $log->logInfo('Set ID: '.$_POST['selectSet'].'   Format: '.$_POST['selectFormat']);

    $set_id = $_POST['selectSet'];
    $marcGenerator = new marcXMLGeneration();
    $recordElements = array();

    $qr_res = $o_db->query("
					SELECT *
					FROM ca_set_items
					WHERE set_id = ?
					AND table_num = ?
				", $set_id, CA_OBJECT_TABLE_NUMBER);
    $recordCounter = 0;

    $total_records = $qr_res->numRows();
    $t_start = round(microtime(true) * 1000);

    while($qr_res->nextRow()) {
        $t_object = new ca_objects($qr_res->get('row_id'));
        $idNo = $t_object->get('idno');
        $prefferedLabel = $t_object->get('ca_objects.preferred_labels.name');
        $nonprefferedLabel = $t_object->get('ca_objects.nonpreferred_labels', array('returnAsArray' => true));
        $object_id = $t_object->get('object_id');

        $va_element_ids = $t_object->getApplicableElementCodes(null, false, false);
        $va_attributes = ca_attributes::getAttributes($t_object->getDb(), $t_object->tableNum(),
                            $t_object->getPrimaryKey(), array_keys($va_element_ids), null);

        $va_attributes_without_element_ids = array();
        $t_element = new ca_metadata_elements();
        $elements = array();

        foreach($va_attributes as $vn_element_id => $va_values) {
            if (!is_array($va_values)) { continue; }
            $va_attributes_without_element_ids = array_merge($va_attributes_without_element_ids, $va_values);
        }

        $counter = 0;
        foreach($va_attributes_without_element_ids as $ids){
            $idValue = $ids->getValues();
            foreach($idValue as $val){
                $element_id = $t_element->load($val->getElementID());
                $element_code = $val->getElementCode();

                if (strpos($element_code,'marc') !== false || strpos($element_code,'leader') !== false) {
                    $element_value = $val->getDisplayValue();

                    // list items
                    $element_type = ca_metadata_elements::getElementDatatype($element_code);
                    if($element_type == 3){
                        $t_list_item = new ca_list_items($element_value);
                        $item_value = $t_list_item->getListName();

                        isset($item_value)? $element_value = $item_value :$element_value = '';
                    }
                    if(strlen($element_value) > 0){
                        if(strpos($element_code,'leader') !== false)
                            $elements[$element_code] = $element_value;
                        else{
                            $elements[$element_code][$counter] = $element_value;
                            $counter++;
                        }
                    }

                }elseif($element_code === 'edm_provider_aggr'){
                    $element_value = $val->getDisplayValue();
                    $element_type = ca_metadata_elements::getElementDatatype($element_code);
                    if($element_type == 3){
                        $t_list_item = new ca_list_items($element_value);
                        $item_value = $t_list_item->getListName();
                        isset($item_value)? $element_value = $item_value :$element_value = '';
                    }
                    $elements['marc999e'] = $element_value;         // marc999e is defined against edm_provider_aggr (provider)
                }
            }
        }

        // relationships
        $relationships = $t_object->hasRelationships();
        foreach($relationships as $key => $value){
            if (strpos($key,'_x_') !== false) {
                if (strpos($key,'vocabulary_terms') !== false) {
                    $sql_vc_terms = "SELECT ca_objects_x_vocabulary_terms.item_id, ca_list_items.item_value,ca_lists.list_code
                    FROM ca_objects_x_vocabulary_terms
                    INNER JOIN ca_list_items ON ca_objects_x_vocabulary_terms.item_id = ca_list_items.item_id
                    INNER JOIN ca_lists ON ca_list_items.list_id = ca_lists.list_id
                    WHERE object_id = $object_id";
                    $qr_vc_terms = $o_db->query($sql_vc_terms);
                    $vc_fields = $qr_vc_terms->getAllRows();
                    if(sizeof($vc_fields) > 0){
                        foreach($vc_fields as $list_item){
                            $item_value = $list_item['item_value'];
                            $list_code = current(explode('_',$list_item['list_code']));
                            if(array_key_exists($list_code, $elements)){
                                $key_count = sizeof($elements[$list_code]);
                                $elements[$list_code][$key_count] = $item_value;
                            }
                            else
                                $elements[$list_code][] = $item_value;
                        }
                    }
                }
                else{
                    $related_items = $t_object->getRelatedItems('ca' . end(explode('x',$key)));
                    if(is_array($related_items)){
                       $counter = 0;
                        foreach($related_items as $item){
                            if(strlen($item['label']) > 0){
                                $elements[$item['relationship_type_code']][$counter] = $item['label'];
                                $counter++;
                            }
                        }
                    }
                }
            }
        }

        $elements['marc001']=$idNo;
        $elements['marc245a']=$prefferedLabel;
        $npLabelCounter = 0;
        foreach($nonprefferedLabel as $key => $item){
            foreach($item as $value){
                if(strlen($value['name']) > 0){
                    $elements['marc245b'][$npLabelCounter]=$value['name'];
                    $npLabelCounter++;
                }
            }
        }

        $recordCounter ++;
        $recordElements[] = $elements;
    }

    $log->logInfo('Total records: '.$total_records);

    $marcResult = $marcGenerator->marcGeneration($recordElements);
    $marcFilePath =$marcResult[0].'/'.$marcResult[1].'.xml';

    $t_end = round(microtime(true) * 1000);
    $log->logInfo('Marc XML generation time: '.(($t_end-$t_start)/1000).' sec');


    echo '<div>';
        echo "<table border='0' width='100%' style='border-width: 1px;
                               border-color:#000000; border-style: solid;'>";
        $formAction = dirname($_SERVER['SCRIPT_NAME'])."/index.php/contentDeliveryMenu/ContentDelivery/Index/universe/Content List";
        echo "<form action='$formAction' method='post'>";
            echo "<tr style='text-align: center'>";
                echo '<td style="text-align: left">';
                    echo '<strong style="font-size: 10px;">Generated '.strtoupper($_POST['selectGenerationFormat']).' XML File: </strong>';
                    echo '<a target = "_blank" href='.dirname($_SERVER['SCRIPT_NAME']).
                        "/app/plugins/contentDeliveryMenu/files".end(explode('files', $marcResult[0])).
                        "/".$marcResult[1].'.xml'.'>'.$marcResult[1].'.xml'.'</a>';
                    echo '<input type ="hidden" name="generatedXMLFile" value='.$marcFilePath.'>';
                    echo '<input type ="hidden" name="sourceFormat" value='.$_POST['selectGenerationFormat'].'>';
                    echo '<input type ="hidden" name="dataDirectory" value='.$marcResult[0].'>';
                echo '</td>';

                echo '<td style="text-align: left">';
                    echo '<strong style="font-size: 10px; text-align: left">Target Format:</strong>';
                    echo '<select name="selectTransformFormat" ">';
                        echo '<option value="edm">EDM</option>';
                        echo '<option value="lido">LIDO</option>';
                    echo '</select>';
                echo '</td>';

                echo "<td align='right'> <input type='submit' value='Transform XML' name='xmlTransformation'> </td>";
            echo "</tr>";
        echo '</form>';
        echo "</table>";
        echo '<br>';
    echo '</div>';

}

if(isset($_POST['selectTransformFormat']) && $_POST['generatedXMLFile']){
    $dmtService = new dmtService();
    $mappingFile = __CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/helpers/marcmappingrules.csv';
    echo '<div>';
    $requestId = $dmtService->mappingSingleFile($_POST['generatedXMLFile'], $mappingFile, $_POST['sourceFormat'], $_POST['selectTransformFormat']);
    if(isset($requestId)){
        echo '-> Request Id: '.$requestId.'<br>';
        $statusResponse = $dmtService->requestStatus($requestId);
        if(isset($statusResponse)){
            if($statusResponse === '2'){        //status=2    record(s) have been transformed/mapped
                echo '-> Record is ready to be fetch. <br>';
                $fileInfo = explode('Transformed_', $requestId);
                if(isset($fileInfo[1]))
                    $fileName = $_POST['selectTransformFormat'].$fileInfo[1];
                else
                    $fileName = $_POST['selectTransformFormat'].'temp.xml';

                $transformedResult = $dmtService->fetchRecord($requestId);
                $dataDirectory = $_POST['dataDirectory'].'/';
                echo '-> Retrieved Record(s) File: ';

                if(($transformedResult['success']) === true){
                    echo '<a target = "_blank" href='.dirname($_SERVER['SCRIPT_NAME']).
                        "/app/plugins/contentDeliveryMenu/files".end(explode('files', $dataDirectory)).
                        $fileName.'>'.$fileName.'</a>';
                    file_put_contents($dataDirectory.$fileName,$transformedResult['response']);
                }
                else
                    echo 'File could not be fetch. Service returned with response: '.$transformedResult['response'];

            }
            else
                echo 'Request('.$requestId.') yet to be processed.';
        }
    }
    else{
        echo 'Error in transformation request, please try again';
            echo '<div>';
            echo "<table border='0' width='100%' style='border-width: 1px;
                                   border-color:#000000; border-style: solid;'>";
            $formAction = dirname($_SERVER['SCRIPT_NAME'])."/index.php/contentDeliveryMenu/ContentDelivery/Index/universe/Content List";
                echo "<form action='$formAction' method='post'>";
                echo "<tr style='text-align: center'>";
                    echo '<td style="text-align: left">';
                        echo '<strong style="font-size: 10px;">Generated '.strtoupper($_POST['sourceFormat']).' XML File: </strong>';
                        echo '<a target = "_blank" href='.dirname($_SERVER['SCRIPT_NAME']).
                            "/app/plugins/contentDeliveryMenu/files".end(explode('files', $_POST['generatedXMLFile'])).'>'
                            .basename($_POST['generatedXMLFile']).'</a>';
                        echo '<input type ="hidden" name="generatedXMLFile" value='.$_POST['generatedXMLFile'].'>';
                        echo '<input type ="hidden" name="sourceFormat" value='.$_POST['sourceFormat'].'>';
                        echo '<input type ="hidden" name="dataDirectory" value='.$_POST['dataDirectory'].'>';
                    echo '</td>';

                    echo '<td style="text-align: left">';
                        echo '<strong style="font-size: 10px; text-align: left">Target Format:</strong>';
                        echo '<select name="selectTransformFormat" ">';
                        echo '<option value="edm">EDM</option>';
                        echo '<option value="lido">LIDO</option>';
                        echo '</select>';
                    echo '</td>';

                    echo "<td align='right'> <input type='submit' value='Transform XML' name='xmlTransformation'> </td>";
                echo "</tr>";
                echo '</form>';
            echo "</table>";
        echo '</div>';
    }

    echo '</div>';
}

?>



