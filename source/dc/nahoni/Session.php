<?php 

//declare(strict_types=1);

namespace dc\nahoni;

require_once('config.php');

interface iSession
{
	function get_member_config();
	function set_member_config(SessionConfig $value);
}

/*
Override PHP's default session handling so we can store 
session data in an RDMS table.
*/
class Session implements \SessionHandlerInterface, iSession
{    
	
	private	$config	= NULL;	// Config options.

	function __construct(SessionConfig $config = NULL)
	{
		// Set connection parameters member. If no argument
		// is provided, then create a blank connection
		// parameter instance.
		if($config)
		{
			$this->set_member_config($config);
		}
		else
		{
			$this->set_member_config(new SessionConfig);
		}		
	}
	
	function __destruct()
	{
		// echo '__destruct';		
	}
	
	// Accessors
	public function get_member_config()
	{
		return $this->config;
	}
	
	// Mutators
	public function set_member_config(SessionConfig $value)
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

	/*
	Caskey, Damon V.
	2020-12-23 (Refactor from orginal ~2015)
		
	Locate and read session data from database.
    */
	public function read($id)
    {		
		// error_log('read: '.$id);        

		$dbh_pdo_connection = $this->config->get_database();
		
		// Populate database class members with 
		// the SQL string of our stored procedure
		// and its parameter array. Then we can 
		// execute.
		
		$sql_string = 'EXEC '.$this->config->get_sp_prefix().$this->config->get_sp_get().' :id';
		$dbh_pdo_statement = $dbh_pdo_connection->prepare($sql_string);
		
		$dbh_pdo_statement->bindParam(':id', $id, \PDO::PARAM_STR);
		
		$dbh_pdo_statement->execute();		
		
		// Fetch row into an object using our data class. 
		// If we fail, we need to start up a blank object 
		// instead.	
		
		$result = $dbh_pdo_statement->fetchObject(__NAMESPACE__.'\Data');
					
		if(!$result)
		{		
			$result = new Data();			
		}		
		
		// 7.1+ throws an error when returning a
		// NULL value on session start up. If our
		// session_data member is NULL, return
		// an empty string instead.
				
		$output = $result->get_session_data();	
		
		if(is_null($output))
		{
			$output = '';			
		}
		
		return $output;    	
	}

	/*
	Caskey, Damon V.
	2020-12-23 (Refactor from orginal ~2015)
	
	Update or insert session data. Note that only ID and Session Data are 
	required. Other data is to aid in debugging.
    */
	public function write($id, $data)
    {
		// error_log('write: '.$id);
						
		$source	= $_SERVER['PHP_SELF'];		// Current file.
		$ip		= $_SERVER['REMOTE_ADDR'];	// Client IP address.					
		
		// Time value so we know when the session has expired. While we have the option
		// to set a time here, for consistency we'll normally just send NULL, and 
		// and let the stored procedure add in the database engine's own current time.
		$time	= NULL; //date(DATE_ATOM);
		
		// Ensure IP string is <= 15. Anything over is a MAC or unexpected (and useless) value.
		$ip = substr($ip, 0, 15);
		
		$dbh_pdo_connection = $this->config->get_database();
		
		// Populate database class members with 
		// the SQL string of our stored procedure
		// and its parameter array. Then we can 
		// execute.
		
		$sql_string = 'EXEC '.$this->config->get_sp_prefix().$this->config->get_sp_set().' :id, :data, :source_file, :client_ip';
			
		$dbh_pdo_statement = $dbh_pdo_connection->prepare($sql_string);
				
		$dbh_pdo_statement->bindParam(':id', $id, \PDO::PARAM_STR);
		$dbh_pdo_statement->bindParam(':data', $data, \PDO::PARAM_STR);
		$dbh_pdo_statement->bindParam(':source_file', $source, \PDO::PARAM_STR);
		$dbh_pdo_statement->bindParam(':client_ip', $ip, \PDO::PARAM_STR);
		
		$rowcount = $dbh_pdo_statement->execute();
						
		// Return TRUE. 
		return TRUE;
    }

	/*
	Caskey, Damon V.
	2020-12-23 (Refactor from orginal ~2015)
	
	Delete current session.
    */
	public function destroy($id)
    {	
		// error_log('destroy: '.$id);

		$dbh_pdo_connection = $this->config->get_database();
		
		// Populate database class members with 
		// the SQL string of our stored procedure
		// and its parameter array. Then we can 
		// execute.
				
		$sql_string = 'EXEC '.$this->config->get_sp_prefix().$this->config->get_sp_destroy().' :id';
		$dbh_pdo_statement = $dbh_pdo_connection->prepare($sql_string);
		
		$dbh_pdo_statement->bindParam(':id', $id, \PDO::PARAM_STR);
		
		$rowcount = $dbh_pdo_statement->execute();		
				
		return TRUE;
    }

	/* 
	Caskey, Damon V.
	2020-12-23 (Refactor from orginal ~2015)
	Delete expired session data.
		
	$life_max: Maximum lifetime of a session, in seconds. This is
	passed from the php.ini session.gc_maxlifetime setting.
    */
	public function gc($life_max)
    {
		// error_log('gc: '.$life_max);
		
		// If local setting isn't NULL, use it to override the default
		// lifetime value.
		
		if($this->config->get_life() != NULL)
		{
			$life_max = $this->config->get_life();
		}
				
		$dbh_pdo_connection = $this->config->get_database();
		
		// Populate database class members with 
		// the SQL string of our stored procedure
		// and its parameter array. Then we can 
		// execute.
				
		$sql_string = 'EXEC '.$this->config->get_sp_prefix().$this->config->get_sp_clean().' :life_max';
		$dbh_pdo_statement = $dbh_pdo_connection->prepare($sql_string);
		
		$dbh_pdo_statement->bindParam(':life_max', $life_max, \PDO::PARAM_INT);
		
		$rowcount = $dbh_pdo_statement->execute();		
		
		// Return TRUE.
		return TRUE;
    }
}

