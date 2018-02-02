<?php

	require_once(__DIR__.'/settings.php');
	// Nahoni require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/session.php');			// Session class.
	require(__DIR__.'/navigation.php');
	require(__DIR__.'/source/dc/record_navigation/main.php');	// Record navigation.
	require(__DIR__.'/source/dc/sorting/main.php'); 		// Record sorting.
	require(__DIR__.'/source/dc/cache/main.php'); 		// Page cache.
	require(__DIR__.'/data_main.php');
	// Yukon require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/database/main.php'); 	// Database class.
	// Stoeckl require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/access/main.php');
	require(__DIR__.'/source/dc/url_query/main.php'); 	// URL builder (to include variables).

		
	// Replace default session handler.
	$session_handler = new class_session();
	session_set_save_handler($session_handler, TRUE);
		
?>