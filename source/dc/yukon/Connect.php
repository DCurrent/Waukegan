<?php

namespace dc\yukon;

require_once(dirname(__FILE__).'\config.php');

/* Database connection object. */
interface iConnect 
{	
	function get_member_config();
	function get_member_connection();					// Return database connection resource.
	function set_member_config(ConnectConfig $value);	// Set config object.
	function close_connection();				// Close current connection.
	function open_connection();					// Attempt database connection.
    
    function get_row_object_list($sql_string, $class_name, $constructor_tags = array());
    
    /*
    function transaction_begin();
    function transaction_commit();
    function error_code();
    function error_info();
    function statement_execute();
    function attribute_get();
    function attribute_set();
    function get_drivers();
    function transaction_check_in();
    function get_last_insert_id();
    function statement_prepare();
    function query();
    function quote();
    function transaction_rollback();
    */  
}

/* Database host connection manager. */
class Connect implements iConnect 
{			
	private $connect	= NULL;	// Database connection resource.
    private $config		= NULL;	// Connection parameters object.
    
    private $sql_string = '';
    private $statement = NULL;
    
			
    /*
    * Run a query, then create and return 
    * a double linked list consisting of 
    * line objects from query result.
	*/
    public function get_row_object_list($sql_string, $class_name, $constructor_tags = array())
	{
        $statement = NULL;
        $result = new \SplDoublyLinkedList();	// Linked list object.	
		$line	= NULL;				// Database line objects.
        
        /* 
        * Send and execute query. If
        * sucessful, we'll have a new
        * PDO statement reference.
        */
        try
        {
            $statement = $this->connect->query($sql_string);
        }
        catch(\PDOException $e)
        {
            die('Database error: '.$e->getMessage());
        }
        
        /* 
        * Get every row as an object and 
        * push it into a double linked
        * list.
        */
		while($line = $statement->fetchObject($class_name, $constructor_tags))
		{			
			$result->push($line);
		}
	
		/* Return linked list object. */
		return $result;
	}
    
    /*
    * Run an action query using transactions.
    */
    //public function run_action_query()
    
    
    
	public function __construct(ConnectConfig $connect = NULL)
	{			
		/* 
        * Set connection parameters member. If no argument
		* is provided, then create a blank connection
		* parameter instance.
		*/
        if($connect)
		{
			$this->set_member_config($connect);
		}
		else
		{
			$this->set_member_config(new ConnectConfig);
		}
	
		/* Connect to database server. */
		$this->open_connection();
	}
	
	public function __destruct() 
	{		
		/* Close DB connection. */
		// $this->close_connection();
   	}
	
	/* Accessors. */
	public function get_member_config()
	{
		return $this->config;
	}
	
	public function get_member_connection()
	{	
		return $this->connect;
	}
	
	// Mutators
	public function set_member_config(ConnectConfig $value)
	{
		$this->config = $value;
	}
	
	/* Connect to database host. Returns connection. */
	public function open_connection()
	{			
		$connect = NULL; // Database connection reference.
		$db_cred = NULL; // Credentials array.
		
		$config	= $this->config;
		$error	= $config->get_error();
				
		/* Set up credential array. */
		$db_cred = array('Database'	=> $config->get_db_name(), 
				'UID' 		=> $config->get_db_user(), 
				'PWD' 		=> $config->get_db_password(),
				'CharacterSet' 	=> $config->get_charset());	
		
		try 
		{
			/* Can't connect if there's no host. */
			if(!$config->get_db_host())
			{
				$msg = EXCEPTION_MSG::CONNECT_OPEN_HOST;
				$msg .= ', Host: '.$config->get_db_host();
				$msg .= ', DB: '.$config->get_db_name();
				
				$error->exception_throw(new Exception($msg, EXCEPTION_CODE::CONNECT_OPEN_HOST));				
			}
			
			/* 
            * Establish database connection.
			* $connect = sqlsrv_connect($config->get_db_host(), $db_cred);
			*
			* PDO requires a single concatenated string 
			* containing the type (MYSQL, MSSQL, etc.), 
			* hostname, and database name.
			*/
            $dsn = 'sqlsrv:Server='.$config->get_db_host().';Database='.$config->get_db_name();
						
			$connect = new \PDO($dsn, $config->get_db_user(), $config->get_db_password());
			
			/* 
            * False returned. Database connection failed.
			*/
            if(!$connect)
			{
				$error->exception_throw(new Exception(EXCEPTION_MSG::CONNECT_OPEN_FAIL, EXCEPTION_CODE::CONNECT_OPEN_FAIL));
			}			
		}		
		catch (\PDOException $pdo_exception)
		{
			/*
            * PDO always throws an exception on connect fail.
			* Let's just catch it here since sending it
			* on to our custom exception handler isn't really
			* tennable at the moment. Hopefully fix that soon. :)
			*/
            
			echo('<br />');
			echo('<b>'.LIBRARY::NAME.' error code '.EXCEPTION_CODE::CONNECT_OPEN_FAIL.": </b>");
			echo(EXCEPTION_MSG::CONNECT_OPEN_FAIL);
			echo('<br />');
			
			error_log($pdo_exception->getMessage());
		}
		catch (Exception $exception) 
		{			
			$error->exception_catch();
		}
		
		/* Set and return connect data */
		$this->connect = $connect;
		
		return $connect;
	}
	
    /*
	* Attempts to close connection. Note if there 
    * are other open references to the object, PDO
    * will remain open until parent script completes.
    * 
    * It normally isn't necessary to close a PDO
    * connection as PHP closes connections implicitly
    * when script completes.
    */
    public function close_connection()
	{
		$this->connect = NULL;
	}
}



?>
