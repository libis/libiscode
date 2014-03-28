<?php
    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/dataPushService.php");
    require_once(__CA_LIB_DIR__."/core/Logging/Eventlog.php");


    echo "<h1>Libis Content Delivery System</h1>\n";
    echo "<h2>Data Push Service</h2>\n";

    $dataPush = new dataPushService();
    $e_log = new Eventlog();

    $filesDirectory = __CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/files';
    $searchString = 'edm';

    $it = new RecursiveDirectoryIterator($filesDirectory);
    $display = Array ( 'xml','zip' );

    echo '<div>';
    $formAction = dirname($_SERVER['SCRIPT_NAME'])."/index.php/contentDeliveryMenu/ContentDelivery/Index/universe/Data Push";
    echo "<form action='$formAction' method='post'>";
    echo "<table border='0' width='100%' style='border-width: 1px;
                                           border-color:#000000; border-style: solid;'>";
    echo "<tr style='text-align: center'>";
        echo "<td align='center' colspan='2'> <input type='submit' value='Push Data' name='dataPush'> </td>";
    echo "</tr>";

    foreach(new RecursiveIteratorIterator($it) as $file)
    {
        if (in_array(strtolower(array_pop(explode('.', $file))), $display)){
            if (strpos($file,$searchString) !== false) {
                echo "<tr style='text-align: center; border: 1px'>";
                echo '<td style="text-align: left; width:1%;">';
                echo "<input type='checkbox' value=' . $file .' name='edmFiles[]'  >";
                echo '</td>';

                echo '<td style="text-align: left">';
                echo basename($file);
                echo '</td>';
                echo "</tr>";
            }
        }
    }

    echo "</table>";
    echo '</form>';
    echo '</div>';

    if(isset($_POST['edmFiles'])){
        if($dataPush->initDataPushOutputFile($dataPush->dataPushOutputLogFile)===TRUE){
            foreach($_POST['edmFiles'] as $item){
                $edmFileLocation = explode('files', $item);
                if(isset($edmFileLocation[1])){
                    $edmFile = $filesDirectory.trim(str_replace('\\', '/', $edmFileLocation[1]));
                    $e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'datapush',
                        'MESSAGE' => _t('EDM File: %1', $edmFile)
                    ));
                    $isZip = new ZipArchive;
                    if ($isZip->open($edmFile) === true) {
                        $zipFilePath = explode('.zip', $edmFileLocation[1]);
                        $dataPush->pushData($edmFile, 'application/zip');
                        echo $edmFile;
                        $isZip->close();
                    }else
                    {
                        $dataPush->pushData($edmFile, 'application/xml');
                        echo $edmFile;
                    }
                }
            }
            echo '<br>';
            echo "Data push request for selected EDM Record(s)has been made. <br>";
            echo "<a href='".dirname($_SERVER['SCRIPT_NAME'])."/index.php/contentDeliveryMenu/ContentDelivery/Index/universe/Datapush Result?output="
                .strstr(basename($dataPush->dataPushOutputLogFile), '.', true)."'>See Results</a><br>";
            echo '<br>';
        }
        else
            echo '<br>Error in creating datapush output file. Please try again.<br>';

    }

?>

