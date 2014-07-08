<?php
    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/validationService.php");
    echo _t("<b>Libis Content Delivery System</b><br>");
    echo _t("<b>Validation Service</b><br>");

    $url = $_SERVER['REQUEST_URI'];
    $root_dir_path = current(explode('index.php', $url));

    $validationService = new validationService;
    $filesDirectory = __CA_BASE_DIR__.'/app/plugins/contentDeliveryMenu/files';
    $searchString = 'edm';

    $it = new RecursiveDirectoryIterator($filesDirectory);
    $display = Array ( 'xml','zip' );


    echo '<div>';
    echo "<table border='0' width='100%' style='border-width: 1px;
                                                   border-color:#000000; border-style: solid;'>";
    $formAction = dirname($_SERVER['SCRIPT_NAME'])."/index.php/contentDeliveryMenu/ContentDelivery/Index/universe/Validation";
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
    echo "<td align='center' colspan='2'> <input type='submit' value='Validate Records' name='recordValidation'> </td>";
    echo "</tr>";
    echo '</form>';
    echo "</table>";
    echo '<br>';
    echo '</div>';

    if(isset($_POST['edmFiles'])){
        echo 'Validation Results<br>';
        echo '<br>';
        $str_js_files = "";
        foreach($_POST['edmFiles'] as $item){
            $edmFileLocation = explode('files', $item);
            if(isset($edmFileLocation[1])){
                $edmFile = $filesDirectory.trim(str_replace('\\', '/', $edmFileLocation[1]));

                $isZip = zip_open($edmFile);
                if (is_resource($isZip)) {
                    zip_close($isZip);
                    echo $edmFile. '<br>';
                    $result_batch = $validationService->validateRecordsInBatch($edmFile);
                    print_r($result_batch);
                }else
                {
                    $edmFile = trim(trim($edmFile, '.'));
                    $result = $validationService->validateRecords($edmFile);

                    if(isset($result['response_code'])){
                        switch($result['response_code']){
                            case 200:
                                echo basename($edmFile).' --> Validation successful'.'<br>';
                                break;

                            case 412:       //validation errors
                                $response_body = $result['response_body'];
                                $records = $response_body['validationresult']['record'];

                                 $str_js_records = '{
                                    label:\''.basename($edmFile).'\',
                                    children:[';

                                $str_children = "";
                                $counter = 1;
                                foreach($records as $record){
                                    if(isset($record['id']))
                                        $str_children.='{label:\''.$record['id'].'\'';
                                    else
                                        $str_children.='{label:\' - \'';

                                    $str_js_errors = "";
                                    if(isset($record['error']) && sizeof($record['error'] > 0)){
                                        $str_js_errors = "children:[";
                                        $str_error = "";
                                        $counter_error = 1;
                                        foreach($record['error'] as $error){

                                            if(isset($error['content'])){
                                                $text = $error['content'];
                                                $text = str_replace(array("'", "\n", "\r"), array("", "\\n", "\\r" ),$text);
                                                $str_error.='{label:\''.$counter_error.'-> '. $text.'\'';

                                            }
                                            else
                                                $str_error.='{label:\' - \'';

                                            $str_error .= '}';
                                            if($counter_error !== sizeof($record['error']))
                                                $str_error.=',';
                                            $counter_error++;

                                        }

                                        $str_js_errors .= $str_error.']';
                                        $str_children .= ','.$str_js_errors;
                                    }

                                    $str_children .= '}';
                                    if($counter !== sizeof($records))
                                        $str_children.=',';
                                    $counter++;
                                }

                                $str_js_records .= $str_children.']}';

                                if (strpos($str_js_files,'label') !== false) {
                                    $str_js_files = $str_js_files . ',' . $str_js_records;
                                }
                                else
                                    $str_js_files = $str_js_records;

                                break;

                            default:
                                basename($edmFile).' --> Service temporarily unavailable'.'<br>';
                        }
                    }

                }
            }

        }
    }

    ?>

    <!doctype html>
    <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="http://<?php echo $_SERVER['HTTP_HOST'].$root_dir_path; ?>app/plugins/contentDeliveryMenu/helpers/jqtree/extra/bower_components/bootstrap/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="http://<?php echo $_SERVER['HTTP_HOST'].$root_dir_path; ?>app/plugins/contentDeliveryMenu/helpers/jqtree/jqtree.css">

            <script src="http://<?php echo $_SERVER['HTTP_HOST'].$root_dir_path; ?>app/plugins/contentDeliveryMenu/helpers/jqtree/tree.jquery.js"></script>
            <script>
                $(function() {
                    var data = [<?php echo $str_js_files; ?>];

                    $('#tree1').tree({
                        data: data
                    });
                });
            </script>
            <script>
                $.mockjax({
                    url: '*',
                    responseTime: 1000,
                    response: function(options) {
                        if (options.data && options.data.node) {
                            this.responseText = ExampleData.getChildrenOfNode(options.data.node);
                        }
                        else {
                            this.responseText = ExampleData.getFirstLevelData();
                        }
                    }
                });

                $(function() {
                    var $tree = $('#tree1');

                    $tree.tree({
                        dragAndDrop: true
                    });
                });
            </script>

        </head>
        <body>
            <div class="container">
                <div id="tree1" style="width: 650px"></div>
            </div>
        </body>
    </html>