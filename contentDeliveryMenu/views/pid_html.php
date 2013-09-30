<?php
    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/pidService.php");

	echo _t("<h1>Libis Content Delivery System</h1>\n");
	echo _t("<h2>Persistent Identifiers (PIDs) Services</h2>\n");


    $pidService = new pidService;

    echo "<center><br><b>"."PID"."</b><br></center>";

    echo "<br><b>"."PID Generate"."</b><br>";
    echo "".$pidService->generatePID('libis.kuleuven.be' , 'object', 12345678);
    echo "<br>";

    echo "<br><b>"."PID Lookup"."</b><br>";
    echo "".$pidService->lookupPID('libis.kuleuven.be_object_12345678');

?>

