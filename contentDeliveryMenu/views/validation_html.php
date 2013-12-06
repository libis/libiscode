<?php
    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/validationService.php");

	echo _t("<h1>Libis Content Delivery System</h1>\n");
	echo _t("<h2>Validation Service</h2>\n");


    $validationService = new validationService;
    $filesDirectory = __CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/files';
    $searchString = 'edm';

    $it = new RecursiveDirectoryIterator($filesDirectory);
    $display = Array ( 'xml','zip' );

    echo '<div>';
        echo "<table border='0' width='100%' style='border-width: 1px;
                                               border-color:#000000; border-style: solid;'>";
        echo '<form action="/providence/index.php/contentDeliveryMenu/ContentDelivery/Index/universe/Validation" method="post">';

            foreach(new RecursiveIteratorIterator($it) as $file)
            {
                if (in_array(strtolower(array_pop(explode('.', $file))), $display)){
                    if (strpos($file,$searchString) !== false) {
                        echo "<tr style='text-align: center; border: 1px'>";
                            echo '<td style="text-align: left; width:1%;">';
                                echo "<input type='checkbox' value=' . $file .' name='edmFiles[]'>";
                            echo '</td>';

                            echo '<td style="text-align: left">';
                                echo basename($file);
                            echo '</td>';
                        echo "</tr>";
                    }
                }
            }

            echo "<tr style='text-align: center'>";
                echo "<td align='center' colspan='2'> <input type='submit' value='Validate Records' name='recordValidation'> </td>";
            echo "</tr>";
        echo '</form>';
        echo "</table>";
        echo '<br>';
    echo '</div>';

if(isset($_POST['edmFiles'])){
    foreach($_POST['edmFiles'] as $item){
        $edmFileLocation = explode('files', $item);
        if(isset($edmFileLocation[1])){
            $edmFile = $filesDirectory.trim(str_replace('\\', '/', $edmFileLocation[1]));

            $isZip = zip_open($edmFile);
            if (is_resource($isZip)) {
                zip_close($isZip);
                $response = $validationService->validateRecords($edmFile,'KULeuven', basename($edmFile), 'marc');
                echo basename($edmFile). ' => '. $response. '<br>';
            }else
            {
                $response = $validationService->validateRecords($edmFile,'KULeuven', basename($edmFile), 'marc');
                echo basename($edmFile). ' => '. $response. '<br>';
            }
        }

    }
}