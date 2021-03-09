<?php

	namespace dc\stoeckl;

	interface ilookup
	{
		// Accessors.
		function get_member_config();
		function get_DataAccount();		
		
		// Mutators.
		function set_data_account($value);			
		
		// Operations.
		function lookup(); // Performs the user lookup against LDAP on a login attempt.
	}

	class lookup implements iLookup
	{
		private
			$action	= NULL,
			$data_account	= NULL,	// Object containing acount data (name, account etc.)
			$login_result	= NULL, // Result of login attempt.
			$config 		= NULL,	// config object.
			$feedback		= NULL, // Feedback.
			$redirect		= NULL;	// URL user came from and should be sent back to after login.
			
		public function __construct(config $config = NULL)
		{
			// Use argument or create new object if NULL.
			if(is_object($config) === TRUE)
			{		
				$this->config = $config;			
			}
			else
			{
				$this->config = new config();			
			}
			
			$this->data_account = new DataAccount();		
		}	
		
		public function get_member_config()
		{
			return $this->config;
		}
		
		public function get_DataAccount()
		{
			return $this->data_account;
		}
		
		public function set_data_account($value)
		{
			$this->data_account = $value;
		}
		
		// Look up account entries.
		// This allows us to get information
		// from LDAP, like name, mail, etc.
		public function lookup()
		{		
			
			$result = TRUE;
			
			// Return results.
			return $result;					
		}			
		
		private function write_attr($entry,$ds)
		{
		   $attrs = ldap_get_attributes ($ds, $entry);
		   for ($j = 0; $j < $attrs["count"]; $j++){
			  $attr_name = $attrs[$j];
			  $attrs["$attr_name"]["count"] . "\n";
			  for ($k = 0; $k < $attrs["$attr_name"]["count"]; $k++) {
					 echo "<br />";
					 echo $attr_name.": ".$attrs["$attr_name"][$k]."\n";
			  }
		   }
		}
	}


	

?>