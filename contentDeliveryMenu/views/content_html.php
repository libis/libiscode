<?php
/* $va_statistics 	= $this->getVar('statistics_listing');
echo "value=".$va_statistics;

$s_universe=$va_statistics ->universe;
print "<h2>".strtoupper($s_universe)."</h2>";
print _t("<h1>Libis Content Delivery plugin</h1>\n");
print _t("<h2>List of Content</h2>\n");


print "<br/><div class=\"clear\"><!--empty--></div>\n".
	  "<div class=\"editorBottomPadding\"><!-- empty --></div>\n" .
	  "<div class=\"clear\"><!--empty--></div>\n"; */


require_once(__CA_MODELS_DIR__.'/ca_sets.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_MODELS_DIR__.'/ca_lists.php');
require_once(__CA_MODELS_DIR__.'/ca_list_items.php');
require_once(__CA_LIB_DIR__.'/core/BaseModelWithAttributes.php');
require_once(__CA_MODELS_DIR__."/ca_metadata_elements.php");
require_once(__CA_LIB_DIR__."/ca/ConfigurationExporter.php");
require_once(__CA_LIB_DIR__.'/ca/Attributes/Attribute.php');
require_once(__CA_APP_DIR__.'/helpers/htmlFormHelpers.php');

require_once(__CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/helpers/marcTransformation.php');
require_once(__CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/helpers/KLogger.php');

$marcTransformer = new transformation();
$log = KLogger::instance(__CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/logging/', 7);


$t_set = new ca_sets();
$availableSets = $t_set->getSets();
$availableItems = $t_set->getFirstItemsFromSets(array(1));
$setIds  = array();
foreach($availableItems as $key => $value){
    $setIds[] = $key; //set ids are available as 'keys', for further information about sets, 'value' can be parsed
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>Libis Content Delivery System</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />

        <script type="text/javascript">
            // initialize CA Utils
            function setSelected(sel) {
                var value = sel.options[sel.selectedIndex].value;
                alert(value);
            }

        </script>
    </head>
    <body>
        <div>
            <h1>Libis Content Delivery System</h1>
            <h2 align="center" style="font: bold">Control Board</h2>
        </div>

        <div align="center">
                <div id="recordSelection">
                    <table border="0" align="center" cellspacing="10" width="100%" style="border-width: 1px;
                           border-color:#000000; border-style: solid;">
                        <tr>
                            <td align="center">
                                <select id="selectSet" onchange="setSelected(this)">
                                    <option value="">Select LIBIScode set</option>
                                    <?php
                                        foreach($availableItems as $key => $value){
                                        $setIds[] = $key; //set ids are available as 'keys', for further information about sets, 'value' can be parsed
                                        echo "<option value='".$key."'> Set ".$key."</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td align="center">
                                <select id="selectFormat" ">
                                    <option value="">Select a Format</option>
                                </select>
                            </td>
                        </tr>
                        <tr></tr>
                        <tr>
                            <td align="center">
                                Import Set: <input type="text" name="importSet" ><br>
                            </td>
                            <td align="center">
                                Open Existing Set: <input type="text" name="openSet" ><br>
                            </td>
                        </tr>
                    </table>
               </div>
        </div


    </body>
</html>

<?php

$objectId =1;

$t_object = new ca_objects($objectId);
$o_data = new Db();

$elements = array();
$prefferedLabel = $t_object->get('ca_objects.preferred_labels.name');
$idNo = $t_object->get('idno');
//print_r($t_object->get('ca_objects.preferred_labels.name'));
//print_r($t_object->get('idno'));

$vn_row_id = $t_object->getPrimaryKey();
$va_element_ids = $t_object->getApplicableElementCodes(null, false, false);
$pa_options=null;
$va_attributes = ca_attributes::getAttributes($t_object->getDb(), $t_object->tableNum(), $vn_row_id, array_keys($va_element_ids), $pa_options);
$va_attributes_without_element_ids = array();
$t_element = new ca_metadata_elements();


foreach($va_attributes as $vn_element_id => $va_values) {
    if (!is_array($va_values)) { continue; }
    $va_attributes_without_element_ids = array_merge($va_attributes_without_element_ids, $va_values);
}
foreach($va_attributes_without_element_ids as $ids){
    $idValue = $ids->getValues();
    foreach($idValue as $val){
        $element_id = $t_element->load($val->getElementID());
        $element_code = $val->getElementCode();
        $element_value = $val->getDisplayValue();
        $elements[$element_code] = $element_value;
    }
}

print '<pre>';
print_r($elements);
print '</pre>';






/*
//FIND THE CORRECT LIST ID TO EXTRACT RIGHT IDNO OF AN ITEM
$list = new ca_lists();

$item1 = $list->getItemFromListByItemID(39,145);
print '<pre>';
print_r($item1['idno']);
print '</pre>';



foreach($va_attributes_without_element_ids as $ids){
    $idValue = $ids->getValues();
    foreach($idValue as $val){
        $listId = $val->getDisplayValue();
        $listItemId = $val->getElementID();

        if(isset($listItemId)){
            echo $listItemId. ', '.$listId. '<br>';

            $item = $list->getItemFromListByItemID(39, $listId);
           echo 'idno= '.$item['idno'].'<br>';
            print '<pre>';
           // print_r($val);
            print '</pre>';
        }


//        echo $val->getDisplayValue().'<br>';
//        echo $val->getElementID();

    }
}

*/


//$ret = $marcTransformer->transformtoMarc($va_attributes);

$elements['marc001']=$idNo;
$elements['marc245a']=$prefferedLabel;
$ret = $marcTransformer->marcTransformation($elements);

?>