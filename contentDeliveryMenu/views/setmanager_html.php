<?php

    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/setManagerService.php");

	echo _t("<h1>Libis Content Delivery System</h1>\n");
	echo _t("<h2>Set Manager Service</h2>\n");


    $setManagerService = new setManagerService;

    echo "<center><br><b>"."Set Manager"."</b><br></center>";

    echo "<br><b>"."List Working Set"."</b><br>";
    echo $setManagerService -> getList();
    echo "<br>";

    echo "<br><b>"."Commit a Set"."</b><br>";
    echo $setManagerService -> commit();
    echo "<br>";

    echo "<br><b>"."Commit Status"."</b><br>";
    echo $setManagerService -> getStatus();

?>

