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
	private	$database	= NULL;
	private	$life		= NULL;
	private	$sp_prefix	= NULL;
	private	$sp_clean	= NULL;
	private	$sp_destroy	= NULL;
	private	$sp_get		= NULL;
	private	$sp_set		= NULL;
	
	public function __construct($config_file = NULL)
	{
		$this->life 		= DEFAULTS::LIFE;
		$this->sp_prefix	= DEFAULTS::SP_PREFIX;
		$this->sp_clean		= DEFAULTS::SP_CLEAN;
		$this->sp_destroy	= DEFAULTS::SP_DESTROY;
		$this->sp_get 		= DEFAULTS::SP_GET;
		$this->sp_set 		= DEFAULTS::SP_SET;
	
		/*
		* If config file is supplied, use it to
		* populate member values.
		*/
		if($config_file)
		{
			$this->populate_config($config_file);
		}
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
	
	/*
	* Populates member data from supplied 
	* config file. 
	* 
	* 1. Reads config file secion matched to 
	* full class name (including namepsace).
	*
	* 2. Values in config are sent to matched
	* mutator. Example: 
	*
	* Config: user_name = "John Doe"
	* Method: $this->set_user_name($value);
	*/
	public function populate_config($config_file)
	{
		/*
		* If any part of this code fails we need to
		* consider it fatal and stop execution.
		* Throw an exception for any kind of notice 
		* or warning so we can catch and handle it. 
		*/
		set_error_handler(function ($severity, $message, $file, $line) {
    	throw new \ErrorException($message, $severity, $severity, $file, $line);
		});
		
		/*
		* Parse config into array, get class specfic 
		* section and pass values into members.
		*/		
		try
		{			
			$config_array = parse_ini_file($config_file, TRUE);
			$section_array = $config_array[__CLASS__];	
			
			// Interate through each class method.
			foreach(get_class_methods($this) as $method) 
			{		
				$key = str_replace('set_', '', $method);
				
				/*
				* If there is an array element with key matching
				* current method name, then the current method 
				* is a set mutator for the element. Populate 
				* the set method with the element's value.
				*/
				if(isset($section_array[$key]))
				{					
					$this->$method($section_array[$key]);					
				}
			}
		}
		catch(\Exception $exception)
		{			
			error_log(__CLASS__.' Fatal Error: '.$exception->getMessage());
			die(__NAMESPACE__.' Fatal Error: Failed to read values from config file. Please contact administrator.');
		}		
	}	
}

?>