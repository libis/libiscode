<?php
    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/pidService.php");
    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/pidStorage.php");
    require_once(__CA_LIB_DIR__."/core/Logging/Eventlog.php");

	echo _t("<h1>Libis Content Delivery System</h1>\n");
	echo _t("<h2>Persistent Identifiers (PIDs) Service</h2>\n");

    $pidService = new pidService();
    $pidStorage = new pidStorage();
    $e_log = new Eventlog();

    $filesDirectory = __CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/files';
    $pidCode = 'eckPid';
    $searchString = 'edm';

    $it = new RecursiveDirectoryIterator($filesDirectory);
    $display = Array ( 'xml','zip' );

    echo '<div>';
        echo "<table border='0' width='100%' style='border-width: 1px;
                                       border-color:#000000; border-style: solid;'>";
        $formAction = dirname($_SERVER['SCRIPT_NAME'])."/index.php/contentDeliveryMenu/ContentDelivery/Index/universe/PID Generation";
        echo "<form action='$formAction' method='post'>";
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
                echo "<td align='center' colspan='2'> <input type='submit' value='Generate Pid' name='pidGeneration'> </td>";
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
                $isZip = new ZipArchive;
                if ($isZip->open($edmFile) === true) {
                    echo '<br>';
                    $zipFilePath = explode('.zip', $edmFileLocation[1]);
                    $fileExtractionLocation = $filesDirectory.trim(str_replace('\\', '/', $zipFilePath[0])).'pid';

                    if (file_exists($fileExtractionLocation)){
                        foreach (glob($fileExtractionLocation.'/*') as $file)
                            unlink($file);
                    }
                    else
                        mkdir($fileExtractionLocation);
                    echo '<br>EDM Zip File: '.basename($edmFile);
                    for($i = 0; $i < $isZip->numFiles; $i++) {
                        $isZip->extractTo($fileExtractionLocation, array($isZip->getNameIndex($i)));
                        $edmFileInZip = $fileExtractionLocation.'/'.$isZip->getNameIndex($i);

                        $result = $pidStorage->generateRecordPID($edmFileInZip, $pidCode);
                        echo '<br>-->EDM File: '.basename($edmFileInZip).'<br>';
                        foreach($result as $value){
                            echo '----> Record Identifier: '.$value['recordidentifier'].'<br>';
                            echo '------->Record PID: '.$value['generatedpid'].'<br>';
                            echo '-------------> Stored PID: '.$value['storedpid'].'<br>';
                        }
                    }
                    $isZip->close();

                    $zipWithPid = new ZipArchive;
                    if ($zipWithPid->open( $fileExtractionLocation.'.zip', ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE )){
                        foreach (glob($fileExtractionLocation.'/*') as $file)
                            $zipWithPid->addFile($file, basename($file));
                        $zipWithPid->close();
                    }
                    //remove extracted files and the directory
                    foreach (glob($fileExtractionLocation.'/*') as $file)
                        unlink($file);
                    rmdir($fileExtractionLocation);

                    echo '<br>';
                }else
                {
                    $e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation',
                        'MESSAGE' => _t('EDM File: %1', $edmFile)
                    ));

                    $result = $pidStorage->generateRecordPID($edmFile, $pidCode);

//                    $result = $pidStorage->generateRecordsPID($edmFile, $pidCode);
                    echo '<br>EDM File: '.basename($edmFile).'<br>';
                    foreach($result as $value){
                        $e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation',
                            'MESSAGE' => sprintf('Record Identifier: %s,  Record PID: %s, Stored PID: %s', $value['recordidentifier'],
                                $value['generatedpid'], $value['storedpid'])
                        ));

                        echo '--> Record Identifier: '.$value['recordidentifier'].'<br>';
                        echo '----->Record PID: '.$value['generatedpid'].'<br>';
                        echo '-----------> Stored PID: '.$value['storedpid'].'<br>';
                    }
                }

            }


        }
    }

?>

