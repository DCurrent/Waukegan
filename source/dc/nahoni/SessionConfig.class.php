<?php

namespace dc\nahoni;

require_once('config.php');

// Structure of parameters used for database connection attempt.
interface iSessionConfig
{	
	function get_database();			// Return database object.
	function get_life();				// Return lifespan of session data.
	function get_sp_clean();			// Get SP name - removes all expired sessions.
	function get_sp_destroy();			// Get SP name - remove single session.
	function get_sp_get();				// Get SP name - read session data.
	function get_sp_prefix();			// Get prefix for SP names.
	function get_sp_set();				// Get SP name - set session data.	
	
	function set_database($value);		// Return database object.
	function set_life($value);			// Return lifespan of session data.	
	function set_sp_clean($value);		// Set SP name - removes all expired sessions.
	function set_sp_destroy($value);	// Set SP name - remove single session.
	function set_sp_get($value);		// Set SP name - read session data.
	function set_sp_prefix($value);		// Set prefix for SP names.
	function set_sp_set($value);		// Set SP name - set session data.
}

class SessionConfig implements iSessionConfig
{
	private
		$database	= NULL,
		$life		= NULL,
		$sp_prefix	= NULL,
		$sp_clean	= NULL,
		$sp_destroy	= NULL,
		$sp_get		= NULL,
		$sp_set		= NULL;
	
	public function __construct()
	{
		$this->life 		= DEFAULTS::LIFE;
		$this->sp_prefix	= DEFAULTS::SP_PREFIX;
		$this->sp_clean		= DEFAULTS::SP_CLEAN;
		$this->sp_destroy	= DEFAULTS::SP_DESTROY;
		$this->sp_get 		= DEFAULTS::SP_GET;
		$this->sp_set 		= DEFAULTS::SP_SET;
	}
	
	// Accessors
	public function get_database()
	{
		return $this->database;
	}
	
	public function get_life()
	{
		return $this->life;
	}
	
	function get_sp_clean()
	{
		return $this->sp_clean;
	}
	
	function get_sp_destroy()
	{
		return $this->sp_destroy;
	}
	
	function get_sp_get()
	{
		return $this->sp_get;	
	}
	
	function get_sp_prefix()
	{
		return $this->sp_prefix;	
	}
	
	function get_sp_set()
	{
		return $this->sp_set;
	}
	
	// Mutators
	public function set_database($value)
	{
		$this->database = $value;
	}
	
	public function set_life($value)
	{
		$this->life = $value;
	}	
	
	function set_sp_clean($value)
	{
		$this->sp_clean = $value;
	}
	
	function set_sp_destroy($value)
	{
		$this->sp_destroy = $value;
	}
	
	function set_sp_get($value)
	{
		$this->sp_get = $value;	
	}
	
	function set_sp_prefix($value)
	{
		$this->sp_prefix = $value;	
	}
	
	function set_sp_set($value)
	{
		$this->sp_set = $value;
	}
}

?>