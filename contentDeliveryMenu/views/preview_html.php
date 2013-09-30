<?php
    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/previewService.php");


	echo _t("<h1>Libis Content Delivery System</h1>\n");
	echo _t("<h2>Preview Service</h2>\n");



    $previewService = new previewService;

    echo "<center><br><b>"."Preview"."</b><br></center>";
    echo "<br><b>"."Preview Record"."</b><br>";
    echo "Response :".$previewService -> getTemplates();

?>

