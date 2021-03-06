<?php
    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/pidService.php");
    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/pidStorage.php");
    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/libisCodeUtils.php");
    require_once(__CA_LIB_DIR__."/core/Logging/Eventlog.php");

	echo _t("<h1>Libis Content Delivery System</h1>\n");
	echo _t("<h2>Persistent Identifiers (PIDs) Service</h2>\n");

    $pidService = new pidService();
    $pidStorage = new pidStorage();
    $utils      = new libiscodeUtils();
    $e_log = new Eventlog();

    $filesDirectory = __CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/files';
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
                            echo '<a target = "_blank" style = "text-decoration:none;" href='.dirname($_SERVER['SCRIPT_NAME']).
                                "/app/plugins/contentDeliveryMenu/files".end(explode('files', $file)).'>'
                                .basename($file);
                            echo '</a>';
                            echo '</td>';
                        echo "</tr>";
                    }
                }
            }

            echo "<tr style='text-align: center'>";
                echo "<td align='center' colspan='2'> <input type='submit' value='Generate Pid' name='pidGeneration'> </td>";
                echo "<td align='center' colspan='2'> <input type='submit' value='Remove Records' name='pidRemoveRecords'> </td>";
            echo "</tr>";
        echo '</form>';
        echo "</table>";
    echo '<br>';
    echo '</div>';


    if(isset($_POST['edmFiles'])){
        if(isset($_POST['pidRemoveRecords'])){
            foreach($_POST['edmFiles'] as $item){
                $path_parts = pathinfo($item);
                $recordDirecory = trim(trim(trim($path_parts['dirname']), '.'));
                if (file_exists($recordDirecory)){
                    if($utils->removeDirectory($recordDirecory) === TRUE)
                        $e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation',
                            'MESSAGE' => _t('%1 successfully deleted.', $path_parts['filename'])
                        ));
                    else
                        $e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation',
                            'MESSAGE' => _t('%1 could not be deleted.', $path_parts['filename'])
                        ));
                }
            }
            header('Location: '.$_SERVER['PHP_SELF']);
        }
        else{
            foreach($_POST['edmFiles'] as $item){
                $edmFileLocation = explode('files', $item);

                if(isset($edmFileLocation[1])){
                    $edmFile = $filesDirectory.trim(str_replace('\\', '/', $edmFileLocation[1]));
                    $edmFile = trim(trim($edmFile, '.'));
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

                        $pidZipFile = $fileExtractionLocation.'.zip';
                        echo '<br>EDM Zip File: '.basename($edmFile);
                        echo '<br>EDM Zip File With PID: ';
                        echo '<a target = "_blank" href='.dirname($_SERVER['SCRIPT_NAME']).
                            "/app/plugins/contentDeliveryMenu/files".end(explode('files', $pidZipFile)).
                            '>'.basename($pidZipFile).'<br>'.'</a>';

                        for($i = 0; $i < $isZip->numFiles; $i++) {
                            $isZip->extractTo($fileExtractionLocation, array($isZip->getNameIndex($i)));
                            $edmFileInZip = $fileExtractionLocation.'/'.$isZip->getNameIndex($i);

                            $result = $pidStorage->generateRecordPID($edmFileInZip);
                            echo '<br>-->EDM File: '.basename($edmFileInZip).'<br>';
                            foreach($result as $value){
                                echo '--> Record Identifier: '.$value['recordidentifier'].'<br>';
                                echo '----->Record PID: '.$value['pid'].'<br>';
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
                        $result = $pidStorage->generateRecordPID($edmFile);

                        echo '<br>EDM File: ';
                        echo '<a target = "_blank" href='.dirname($_SERVER['SCRIPT_NAME']).
                            "/app/plugins/contentDeliveryMenu/files".end(explode('files', $edmFile)).
                            $fileName.'>'.basename($edmFile).'<br>'.'</a>';

                        foreach($result as $value){
                            $e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'pid_generation',
                                'MESSAGE' => sprintf('Record Identifier: %s,  Record PID: %s, Stored PID: %s', $value['recordidentifier'],
                                    $value['generatedpid'], $value['storedpid'])
                            ));

                            echo '--> Record Identifier: '.$value['recordidentifier'].'<br>';
                            echo '----->Record PID: '.$value['pid'].'<br>';
                        }
                    }

                }

            }
        }
    }

?>

