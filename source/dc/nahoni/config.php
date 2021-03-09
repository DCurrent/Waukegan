<?php

namespace dc\nahoni;

abstract class DEFAULTS	
{
	const 	LIFE		= NULL;					// Default session lifetime.
			
			// Stored procedure default names.
	const	SP_PREFIX	= NULL,					// Prefix for all otehr SP names.
			SP_CLEAN	= 'session_clean',		// SP to remove all expired sessions.
			SP_DESTROY	= 'session_destroy',	// SP to remove single session on command.
			SP_GET		= 'session_get',		// SP to read session data.
			SP_SET		= 'session_set';		// SP to write session data.
}

?>

