<?php
    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/previewService.php");

	echo _t("<h1>Libis Content Delivery System</h1>\n");
	echo _t("<h2>Preview Service</h2>\n");



    $previewService = new previewService;
    $filesDirectory = __CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/files';
    $searchString = 'lido';

    $it = new RecursiveDirectoryIterator($filesDirectory);
    $display = Array ( 'xml','zip' );

    echo '<div>';
    echo "<table border='0' width='100%' style='border-width: 1px;
                                                   border-color:#000000; border-style: solid;'>";
    $formAction = dirname($_SERVER['SCRIPT_NAME'])."/index.php/contentDeliveryMenu/ContentDelivery/Index/universe/Preview";
    echo "<form action='$formAction' method='post'>";
    foreach(new RecursiveIteratorIterator($it) as $file)
    {
        if (in_array(strtolower(array_pop(explode('.', $file))), $display)){
            if (strpos($file,$searchString) !== false) {
                echo "<tr style='text-align: center; border: 1px'>";
                echo '<td style="text-align: left; width:1%;">';
                echo "<input type='checkbox' value=' . $file .' name='previewFiles[]'>";
                echo '</td>';

                echo '<td style="text-align: left">';
                echo basename($file);
                echo '</td>';
                echo "</tr>";
            }
        }
    }

    echo "<tr style='text-align: center'>";
    echo "<td align='center' colspan='2'> <input type='submit' value='Preview Records' name='recordPreview'> </td>";
    echo "</tr>";
    echo '</form>';
    echo "</table>";
    echo '<br>';
    echo '</div>';

    if(isset($_POST['previewFiles'])){
        echo 'Preview Results<br>';
        echo '<br>';
        foreach($_POST['previewFiles'] as $item){
            $previewFileLocation = explode('files', $item);

            if(isset($previewFileLocation[1])){
                $previewFile = $filesDirectory.trim(str_replace('\\', '/', $previewFileLocation[1]));
                $previewFile = trim(trim($previewFile, '.'));
                $response = $previewService->previewRecords($previewFile);
                echo $response;
                echo '<br>';
            }
        }
    }

?>

