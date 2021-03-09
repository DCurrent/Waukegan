<?php

namespace dc\nahoni;

require_once('config.php');

interface iSession
{
	function get_config();
	function set_config(SessionConfig $value);
}

// Override PHP's default session handling to store data in an MSSQL table. 
class Session implements \SessionHandlerInterface, iSession
{    
	
	private
		$config			= NULL;	// Config options.

	function __construct(SessionConfig $config = NULL)
	{
		// Set connection parameters member. If no argument
		// is provided, then create a blank connection
		// parameter instance.
		if($config)
		{
			$this->set_config($config);
		}
		else
		{
			$this->set_config(new SessionConfig);
		}
	}
	
	function __destruct()
	{
		// echo '__destruct';		
	}
	
	// Accessors
	public function get_config()
	{
		return $this->config;
	}
	
	// Mutators
	public function set_config(SessionConfig $value)
	{
		$this->config = $value;
	}
    
	// Called by PHP to open session. Construct does all the work
	// we'd normally put here, so leave it blank.
   	public function open($savePath, $sessionName)
    {	
		// echo 'open';
		
		
		
		// $savePath: 		Path to locate session file. Unused.
		// $sessionName:	Name of session file. Unused.

		// Return TRUE.
        return TRUE;
    }
	
	// Called by PHP to close session.
    public function close()
    {	
		// echo 'close';
		
		
		
		// Return TRUE.
        return TRUE;
    }

	// Locate and read session data from database.
    public function read($id)
    {		
		// echo 'read';        

		// Dereference database object.
		$iDatabase = $this->config->get_database();
		
		// String - call stored procedure.
		$iDatabase->set_sql('{call '.$this->config->get_sp_prefix().$this->config->get_sp_get().'(@id = ?)}');				

		// Prepare parameter array.
		$params = array(array(&$id, SQLSRV_PARAM_IN));
		
		// Bind parameters and execute query.
		$iDatabase->set_param_array($params);				
		$iDatabase->query_run();		
		
		if($iDatabase->get_row_exists())
		{
			// Set class and acquire object.
			$iDatabase->get_line_config()->set_class_name(__NAMESPACE__.'\Data');
			$result = $iDatabase->get_line_object();	
		}
		else
		{
			$result = new Data();
		}
		
		// Return session data.
		return $result->get_session_data();		
    }

	// Update or insert session data. Note that only ID and Session Data are 
	// required. Other data is to aid in debugging.
    public function write($id, $data)
    {
		// echo 'write';
						
		$source	= $_SERVER['PHP_SELF'];		// Current file.
		$ip		= $_SERVER['REMOTE_ADDR'];	// Client IP address.					
		
		// Time value so we know when the session has expired. While we have the option
		// to set a time here, for consistency we'll normally just send NULL, and 
		// and let the stored procedure add in the database engine's own current time.
		$time	= NULL; //date(DATE_ATOM);
		
		// Ensure IP string is <= 15. Anything over is a MAC or unexpected (and useless) value. */
		$ip = substr($ip, 0, 15);
		
		// Dereference database object.
		$iDatabase = $this->config->get_database();
		
		// SQL string - call stored procedure.
		$iDatabase->set_sql('{call '.$this->config->get_sp_prefix().$this->config->get_sp_set().'(@id 			= ?,
											@data 			= ?,											
											@source 		= ?,
											@ip 			= ?)}');				

		// Prepare parameter array.
		$params = array(array($id, SQLSRV_PARAM_IN),
						array($data, SQLSRV_PARAM_IN),
						array($source, SQLSRV_PARAM_IN),
						array($ip, SQLSRV_PARAM_IN));
						
		// Bind parameters and execute query.
		$iDatabase->set_param_array($params);				
		$iDatabase->query_run();		
					
		// Return TRUE. 
		return TRUE;
    }

	// Delete current session.
    public function destroy($id)
    {	
		// echo 'Destroy';

		// Dereference database object.
		$iDatabase = $this->config->get_database();
		
		// SQL string - call stored procedure.
		$iDatabase->set_sql('{call '.$this->config->get_sp_prefix().$this->config->get_sp_destroy().'(@id = ?)}');				

		// Prepare parameter array.
		$params = array(array(&$id, SQLSRV_PARAM_IN));
		
		// Bind parameters and execute query.
		$iDatabase->set_param_array($params);				
		$iDatabase->query_run();		
		
		// Return TRUE.
		return TRUE;
    }

	// Delete expired session data.
		
	//	$life_max: Maximum lifetime of a session, in seconds. This is
	//	passed from the php.ini session.gc_maxlifetime setting.
    public function gc($life_max)
    {
		// echo 'gc';
		
		// If local setting isn't NULL, use it to override the default
		// lifetime value.
		if($this->config->get_life() != NULL)
		{
			$life_max = $this->config->get_life();
		}
		
		// Dereference database object.
		$iDatabase = $this->config->get_database();
		
		// SQL string - call stored procedure.
		$iDatabase->set_sql('{call '.$this->config->get_sp_prefix().$this->config->get_sp_clean().'(@life_max = ?)}');				

		// Prepare parameter array.
		$params = array(array(&$life_max, SQLSRV_PARAM_IN));
		
		// Bind parameters and execute query.
		$iDatabase->set_param_array($params);				

		$iDatabase->query_run();		
		
		// Return TRUE.
		return TRUE;
    }
}

