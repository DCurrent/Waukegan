<?php

	namespace dc\stoeckl;

	interface ilookup
	{
		// Accessors.
		function get_config();
		function get_DataAccount();		
		
		// Mutators.
		function set_data_account($value);			
		
		// Operations.
		function lookup(); // Performs the user lookup against LDAP on a login attempt.
	}

	class lookup
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
		
		public function get_config()
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
			
			$result			= FALSE;	// Final result.
			$account		= NULL;		// Prepared account string.
			$prefix_list 	= array();
			$prefix 		= NULL;		// Singular prefix value taken from array.
			$ldap_host_list	= NULL;		// List of LDAP connection strings.
			$ldap_host		= NULL;
			
			// Dereference account name and remove any domain prefixes. We'll add our own below.
			$account = str_ireplace($prefix, '', $account);
			
			// Move connection list to local var.
			$ldap_host_list = array(',', $this->config->get_ldap_host_bind());
			
			// We'll attempt to bind on all known hosts.
			// Here we loop through each host connection
			// string.
			foreach($ldap_host_list as $ldap_host)
			{
				// Check connection string integrity and get a connection
				// resource handle. Don't let the name fool you - this 
				// does NOT connect to the LDAP server.
				$ldap = ldap_connect($this->config->get_ldap_host_bind());
				
				// If we failed to get a connection resource, then 
				// exit this iteration of loop.
				if(!$ldap)
				{
					echo 'no ldap';
					continue;
				}
				echo 'host: '.$ldap_host.PHP_EOL;
				echo 'ldap: '.$ldap.PHP_EOL;
				
				// Need this for win2k3.
				ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
      			ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
			
				$goob = ldap_bind($ldap, "cn=dvcask2,ou=users-fa,dc=ad,dc=uky,dc=edu", "64CeeHello@!");
				if(!$goob) echo 'no bind:'.PHP_EOL;
				else echo 'bind: '.PHP_EOL;
				
				$prefix_list = array('ad/', 'ad\\', 'mc/', 'mc\\');
				
				// Remove the prefix, if any.
				$req_account = str_replace($prefix_list, '', 'ad\rdeldr0');
				
				$filter = "samaccountname=".$req_account;
				
				echo 'filter: '.$filter;
				
				$attributes = array("displayname", "sn", "givenname", "pwdlastset");
				
				// Pull attributes for the AD domain
         		$search_result = ldap_search($ldap, "dc=uky,dc=edu", $filter);
				
				$entry_count = ldap_count_entries($ldap, $search_result);
				
				echo 'entry count: '.$entry_count; 
				
				if(!$entry_count)
				{
					continue;
				}
				
				// Get user info array.
				$entries = ldap_get_entries($ldap, $search_result);
				
				$entry = ldap_first_entry($ldap, $search_result);

				
				$this->write_attr($entry, $ldap);
				for ($i = 0; $i < $entry_count; $i++)
				{
				   $entry = ldap_next_entry($ldap, $entry);
				   $this->write_attr($entry, $ldap);
				}
				

				// Trigger error if entries array is empty.
				if($entries["count"] < 0) trigger_error("Entry found but contained no data.", E_USER_ERROR);

				if(isset($entries[0]['cn'][0])) 			echo $entries[0]['cn'][0];
				if(isset($entries[0]['givenname'][0])) 		echo $entries[0]['givenname'][0];
				if(isset($entries[0]['initials'][0]))		echo $entries[0]['initials'][0];
				if(isset($entries[0]['sn'][0]))				echo $entries[0]['sn'][0];					
				if(isset($entries[0]['department'][0]))		echo $entries[0]['department'][0];
				if(isset($entries[0]['mail'][0]))			echo $entries[0]['mail'][0];
				
				// Populate account object members with user info.
				if(isset($entries[0]['cn'][0])) 			echo $this->data_account->set_account($entries[0]['cn'][0]);
				if(isset($entries[0]['givenname'][0])) 		echo $this->data_account->set_name_f($entries[0]['givenname'][0]);
				if(isset($entries[0]['initials'][0]))		echo $this->data_account->set_name_m($entries[0]['initials'][0]);
				if(isset($entries[0]['sn'][0]))				echo $this->data_account->set_name_l($entries[0]['sn'][0]);					
				if(isset($entries[0]['department'][0]))	echo $this->data_account->set_account_id($entries[0]['department'][0]);
				if(isset($entries[0]['mail'][0]))			echo $this->data_account->set_email($entries[0]['mail'][0]);				

				// Save account data into session.
				$this->data_account->session_save();
				
				break;
			}
					
			// If we never managed to get a connection resource, trigger an error here. 
			if(!$entry_count) trigger_error("Could search entries: ".$this->config->get_ldap_host_bind(), E_USER_ERROR);
			
			// Close ldap connection.
			ldap_close($ldap);
			
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