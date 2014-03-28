<?php
require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/parallelism/asyncDataPush.php");
require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/parallelism/Stacking.php");
require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/parallelism/Pooling.php");
require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/dataPushService.php");
require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/pheanstalk/pheanstalk_init.php");
require_once(__CA_LIB_DIR__."/core/Logging/Eventlog.php");



echo _t("<h1>Libis Content Delivery System</h1>\n");
echo _t("<h2>Data Push Service</h2>\n");

$dataPush = new dataPushService();
$threadPool = new Pool(10);
$e_log = new Eventlog();

$pheanstalk = new Pheanstalk_Pheanstalk('127.0.0.1');

$filesDirectory = __CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/files';
$searchString = 'edm';

$it = new RecursiveDirectoryIterator($filesDirectory);
$display = Array ( 'xml','zip' );

echo '<div>';
echo '<form action="/providence/index.php/contentDeliveryMenu/ContentDelivery/Index/universe/Data Push" method="post" name="dataForm">';
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
$pheanstalk->useTube('testtube');
    useTube('testtube')->put("job payload goes here\n");

if(isset($_POST['edmFiles'])){
    if($dataPush->initDataPushOutputFile($dataPush->dataPushOutputLogFile)===TRUE){
        $work = array();
        $workStack = array();
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
                    $work [] = $threadPool->submit(new dataPushWork($dataPush->pushData($edmFile, 'application/zip')));
                    $isZip->close();
                }else
                {
                    $work [] = $threadPool->submit(new dataPushWork($dataPush->pushData($edmFile, 'application/xml')));
                }
            }
        }
        echo '<br>';
        echo "Data push request for selected EDM Record(s)has been made. <br>";
        echo "<a href='/providence/index.php/contentDeliveryMenu/ContentDelivery/Index/universe/Datapush Result?output="
            .strstr(basename($dataPush->dataPushOutputLogFile), '.', true)."'>See Results</a><br>";
        echo '<br>';
    }
    else
        echo '<br>Error in creating datapush output file. Please try again.<br>';

    $threadPool->shutdown();
}

?>

