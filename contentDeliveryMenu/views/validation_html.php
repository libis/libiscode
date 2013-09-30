<?php
    require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/validationService.php");

	echo _t("<h1>Libis Content Delivery System</h1>\n");
	echo _t("<h2>Validation Service</h2>\n");


    $validationService = new validationService;


    echo "<center><br><b>"."Validation"."</b><br></center>";
    echo "<br><b>"."Validate Record"."</b><br>";
    echo "Response (Semantika):".$validationService -> validate();
    echo "<br>";

    echo "Response (Monguz):".$validationService -> validate2();
    echo "<br>";

    echo "<br><b>"."Validation Profiles List"."</b><br>";
    echo "".$validationService->getProfiles();
    echo "<br>";

    echo "<br><b>"."Lido Profile"."</b><br>";
    echo "".$validationService->getProfileByName('Lido');
    echo "<br>";

    echo "<br><b>"."EDM Profile"."</b><br>";
    echo "".$validationService->getProfileByName('EDM');
    echo "<br>";


