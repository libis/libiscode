<?php	
	require_once(__CA_BASE_DIR__."/app/plugins/contentDeliveryMenu/helpers/eckCoreService.php");	

	$eckCoreService = new eckService;
	echo _t("<h1>Libis Content Delivery System</h1>\n");
	echo _t("<h2>ECK-Core Service</h2>\n");

    echo "<center><br><b>"."ECK-Core"."</b><br></center>";

//  echo "<br><b>"."Supported Languages"."</b><br>";
//	echo $eckCoreService -> eckLanguageSupport();
	
    echo "<br><b>"."Available Functions"."</b><br>";
	echo $eckCoreService -> eckFeatureList();

    echo "<br><b>"."Record by ECK Id"."</b><br>";
    echo $eckCoreService -> eckCore(3,"/KIPersistence/persistence","lookupRecordByEckId");
	
    echo "<br><b>"."Record by CMS Id"."</b><br>";
	echo $eckCoreService ->eckCore(123,"/KIPersistence/persistence","lookupRecordByCmsId");
	
    echo "<br><b>"."Record by Persistent Id"."</b><br>";
	echo $eckCoreService ->eckCore(456,"/KIPersistence/persistence","lookupRecordByPersistentId");	

//    echo "<br><b>"."Insert Record"."</b><br>";
//    echo $eckCoreService -> eckInsertRecord();


