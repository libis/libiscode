<?php
require_once(__CA_LIB_DIR__."/core/Logging/Eventlog.php");


echo _t("<h1>Libis Content Delivery System</h1>\n");
echo _t("<h2>Data Push Result</h2>\n");

$e_log = new Eventlog();

$dataPushFilesDirectory = __CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/files/datapushoutput';

if(isset($_GET['output'])){

    $outputFile = $dataPushFilesDirectory.'/'.$_GET['output'].'.csv';
    if (($dataPushLogFileHandle = fopen($outputFile, "r")) === FALSE) {
        $this->e_log->log(array('CODE' => 'LIBC', 'SOURCE' => 'datapush',
            'MESSAGE' => _t('Datapush output file(%1) does not exist', $outputFile)
        ));

        echo 'Error in reading data push output. Please try again.';
        return;
    }
    else{
        $header = NULL;
        echo '<div>';
        echo "<table border='1' width='100%' style='border-width: 1px;
                                       border-color:#000000; border-style: solid;'>";

        $header = true;
        $rowCount = 0;
        while (($row = fgetcsv($dataPushLogFileHandle, 1000, ',')) !== FALSE)
        {
            echo "<tr style='text-align: center'>";
                if(!$header)
                    echo "<td align='left'> $rowCount </td>";
            else
                echo "<th align='center'> Index </th>";

                $columnCount = 1;
                foreach($row as $column){
                    if($header)
                        echo "<th align='center'> $column </th>";
                    else{
                        if($columnCount === 3){
                            $columnLink = str_replace('dpp', 'dpp/resource', $column);
                            echo "<td align='left' >
                            <a href='$columnLink'> $column </a> </td>";
                        }
                        else
                            echo "<td align='left' > $column </td>";
                    }
                    $columnCount++;
                }
            echo "</tr>";
            $header = false;
            $rowCount++;
        }
        echo "</table>";

        echo "<table border='0' width='100%' style='border-width: 0px;
                                       border-color:#000000; border-style: solid;'>";
            echo "<tr style='text-align: right'>";
                echo "<td align='right' colspan='6'>
                    <a href='".dirname($_SERVER['SCRIPT_NAME'])."/index.php/contentDeliveryMenu/ContentDelivery/Index/universe/Data Push'> Back to Data Push</a>
                 </td>";
            echo "</tr>";
        echo "</table>";
        echo '</div>';

        fclose($dataPushLogFileHandle);
    }

}

?>

