<?php

	namespace dc\record_navigation;

	//require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/url_query/main.php'); 	// Page cache.

	abstract class RECORD_NAV_COMMANDS
	{		
		const DELETE			= 1;
		const FIRST				= 2;
		const LAST				= 3;
		const LISTING			= 4;
		const NEW_BLANK			= 5;
		const NEW_COPY			= 6;
		const NEXT				= 7;
		const PREVIOUS			= 8;
		const SAVE				= 9;		
	}
?>