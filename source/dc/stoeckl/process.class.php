<?php

	namespace dc\stoeckl;

	interface iprocess
	{		
		// Accessors.
		function get_DataAccount();
		function get_feedback();
		function get_login_result();
		function get_redirect();
		function get_config();
		
		// Mutators
		function set_access_action($value);
		function set_authenticate_url($value);
		function set_data_account($value);	
		function set_redirect($value);
		
		// Operations.
		function action();
		function dialog();
		function login_application();
		function login_ldap();		
		function login_local();
		function logoff();
		function populate_from_request();
		function process_control();		
	}

	// process log on and log off.
	class process implements iprocess
	{
		private
			$action			= NULL,
			$data_account	= NULL,	// Object containing acount data (name, account etc.)
			$data_common	= NULL,	// Object containing basic data and operations.
			$login_result	= NULL, // Result of login attempt.
			$lookup			= NULL,	// Lookup object.
			$config 		= NULL,	// config object.
			$feedback		= NULL, // Feedback.
			$redirect		= NULL;	// URL user came from and should be sent back to after login.
			
		public function __construct(config $config = NULL, DataCommon $data_common = NULL, DataAccount $data_account = NULL)
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
			
			// Use argument or create new object if NULL.
			if(is_object($data_common) === TRUE)
			{		
				$this->DataCommon = $data_common;			
			}
			else
			{
				$this->DataCommon = new DataCommon();		
			}
			
			// Use argument or create new object if NULL.
			if(is_object($data_account) === TRUE)
			{		
				$this->data_account = $data_account;			
			}
			else
			{
				$this->data_account = new DataAccount();			
			}
			
			// Establish lookup object.
			$this->lookup = new lookup();

			session_start();

			if(isset($_SESSION[SES_KEY::REDIRECT])) $this->redirect = $_SESSION[SES_KEY::REDIRECT];		
		}	
		
		// Populate members from $_REQUEST.
		public function populate_from_request()
		{
			
			
			$this->DataCommon->populate_from_request($this);	
		}	
		
		public function dialog()
		{
			if(isset($_SESSION[SES_KEY::DIALOG]))
			{
				return $_SESSION[SES_KEY::DIALOG];
			}
		}
		
		public function get_redirect()
		{
			return $this->redirect;
		}
		
		public function get_login_result()
		{
			return $this->login_result;
		}
		
		public function get_feedback()
		{
			return $this->feedback;
		}
		
		public function get_config()
		{
			return $this->config;
		}
		
		public function get_DataAccount()
		{
			return $this->data_account;
		}
		
		public function set_redirect($value)
		{
			// Temporarly disabled so we don't kill redirect. Will need to
			// change namgin convention for mutators to get around this.
			
			//$this->redirect = $value;
		}
		
		public function set_authenticate_url($value)
		{
			$this->config->set_authenticate_url($value);
		}
		
		public function set_data_account($value)
		{
			$this->data_account = $value;
		}
			
		public function set_access_action($value)
		{
			$this->action = $value;
		}
		
		// Shortcut controller for login process.
		public function process_control()
		{			
			$this->populate_from_request();
			
			switch($this->action)
			{
				case ACTION::LOGIN;				
					
					// Populate account data from request.
					$this->data_account->populate_from_request();
					
					// First try local.
					if($this->get_config()->get_use_local() == TRUE)
					{
						$this->login_local();
					}					
									
					// If local fails, try LDAP.
					if($this->login_result != LOGIN_RESULT::LOCAL)
					{
						$this->login_ldap();
					}
					
					$this->action();
					break;
					
				case ACTION::LOGOFF;

					$this->logoff();
					break;
			}
		}
		
		// Take action based on result of login attempt.
		public function action()
		{		
			//echo '<br />$this->login_result: '.$this->login_result;
			
			switch($this->login_result)
			{				
				case LOGIN_RESULT::LOCAL:      			
				case LOGIN_RESULT::LDAP:			
				
					// Get account information from
					// application database.
					//$this->login_application();
					
					//$this->lookup->lookup();
					
					// Record client information information into session.
					$this->data_account->session_save();				
										
						
					// Set dialog.					
					// Start caching page contents.
					ob_start();
					?>
						<span class="text-success">Hello <?php echo $this->data_account->get_name_f(); ?>, your log in was successful.</span>	
					<?php
					
					// Collect contents from cache and then clean it.
					$_SESSION[SES_KEY::DIALOG] = ob_get_contents();
					ob_end_clean();	
					
					// Redirect URL passed?		
					if($this->redirect)
					{					
						// If headers are not sent, redirect to user requested page.
						if(headers_sent())
						{							
						}
						else
						{						
							header('Location: '.$this->redirect);
						}				
					}
					
					break;
				
				case LOGIN_RESULT::NO_BIND:
				
					// Set dialog.					
					// Start caching page contents.
					ob_start();
					?>
						<span class="text-danger">Bad user name or password.</span>	
					<?php
					
					// Collect contents from cache and then clean it.
					$_SESSION[SES_KEY::DIALOG] = ob_get_contents();
					ob_end_clean();
					
					break;	
				
				case LOGIN_RESULT::NO_ACCOUNT:
				case LOGIN_RESULT::NO_INPUT:
				case LOGIN_RESULT::NO_PRINCIPAL:
				default: 				
				
					// Default log in dialog.
					$_SESSION[SES_KEY::DIALOG] = NULL;
			}
		}
		
		// Log the current user.
		public function logoff()
		{	
			// Remove all session data.
			session_unset();
			
			if(session_status() === PHP_SESSION_ACTIVE)
			{
				session_destroy();
			}
			
			// Clear account object data.
			$this->data_account->clear();
			
									
			// If headers are not sent, redirect to the authenticate url.
			if(headers_sent())
			{						
			}
			else
			{						
				header('Location: '.$this->config->get_authenticate_url());
			}		
		}
		
		// login_ldap
		// Caskey, Damon V.
		// 2012-02-03
			
		// Process login attempt.	
		public function login_ldap()
		{		
			//echo '<br /> login_ldap';
						
			$principal		= NULL;		// Active directory principal (account).
			$result			= NULL;		// Active directory account search result.
			$entries		= NULL;		// Active directory entry array.
			
			$bind			= FALSE;	// Result of bind attempt.
			$ldap			= NULL;		// ldap connection reference.
			
			$req_account	= NULL;
			$req_credential	= NULL;					
								
			// Get values.
			$req_account 			= $this->data_account->get_account();
			$req_credential			= $this->data_account->get_credential();				
				
			// If any credentials are missing, 
			// then exit. No point in doing 
			// anything else.
			if ($req_account == NULL || $req_credential == NULL)
			{
				return $this->login_result = LOGIN_RESULT::NO_ACCOUNT;
			}
			
			// Attempt to bind user through LDAP.
			$bind = $this->ldap_bind_check();

			// If we didn't get a bind, the account doesn't exist or 
			// user entered bad credentials. If we did get a bind, we 
			// will then run search to get their account attributes.  
			if($bind != TRUE)
			{
				return $this->login_result = LOGIN_RESULT::NO_BIND;
			}
			
			// Lookup Attributes.

			$this->login_result = LOGIN_RESULT::LDAP;			

			// Release ldap query result.
			//ldap_free_result($result);		

			// Close ldap connection.
			//ldap_close($ldap);									
				
			
			// Return results.
			return $this->login_result;		
		}
		
		// ldap_bind_check
		// Caskey, Damon V.
		//	2013-11-13
		//	~2015-07-19
			
		//	Attempt to bind ldap adding all possible prefixes.
		private function ldap_bind_check()
		{			
			$result			= FALSE;	// Final result.
			$account		= NULL;		// Prepared account string to attempt bind.
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
					continue;
				}
				
				// Need this for win2k3.
				ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
			
				// Now we will attempt the bind using all
				// possible domain prefixes.
				
				// Break prefix list into an array.
				//$prefix_list = explode(',', $this->config->get_dn_prefix());
				$prefix_list = array(NULL, 'ad/', 'ad\\', 'mc/', 'mc\\');
			
				// Keep trying prefixes until there is a bind or we run out.
				foreach($prefix_list as $prefix)
				{		
					$account = $prefix.$this->data_account->get_account();

					// Attempt to bind with account (prefix included) and password.
					$result = @ldap_bind($ldap, $account, $this->data_account->get_credential());

					// If successfull bind break out of loop.
					if($result == TRUE) 
					{
						break;					
					}
				}
				
				// If successfull bind break out of loop.
				if($result == TRUE) 
				{
					//break;	
					
					
					// Search goes here.
					
					// Prepare account filter.
					$filter = "samaccountname=".$this->data_account->get_account();
					
					//echo 'filter: '.$filter;
					
					// Pull attributes for the AD domain
					$attributes = array("displayname", "sn", "givenname", "pwdlastset", "cn");
										
					$sr = ldap_search($ldap, "dc=uky,dc=edu", $filter, $attributes);

					$count = ldap_count_entries($ldap, $sr);
						
					// If no entries are found, return 0.
					if (!$count) 
					{
						return $rc;
					}
					else 
					{
						//echo "found $count entrie(s)\n";

						$rc = 1;

						// get the entries
						$entries = ldap_get_entries($ldap, $sr);
						//echo "DN is: " . $entries[0]["dn"] . "\n";
						//echo "First Name " . $entries[0]["givenname"][0]. "\n";
						//echo "surname " . $entries[0]["sn"][0]. "\n";
						//echo "displayName: " . $entries[0]["displayname"][0]. "\n";
						//echo "pwdlastset: " . $entries[0]["pwdlastset"][0]. "\n";

						//print_r($entries);
						
						// Populate account object members with user info.
						if(isset($entries[0]['cn'][0])) 			$this->data_account->set_account($entries[0]['cn'][0]);
						if(isset($entries[0]['givenname'][0])) 		$this->data_account->set_name_f($entries[0]['givenname'][0]);
						if(isset($entries[0]['initials'][0]))		$this->data_account->set_name_m($entries[0]['initials'][0]);
						if(isset($entries[0]['sn'][0]))				$this->data_account->set_name_l($entries[0]['sn'][0]);					
						if(isset($entries[0]['department'][0]))		$this->data_account->set_account_id($entries[0]['department'][0]);
						if(isset($entries[0]['mail'][0]))			$this->data_account->set_email($entries[0]['mail'][0]);
					}
					
					break;
				}
			}
					
			// If we never managed to get a connection resource, trigger an error here. 
			if(!$ldap) trigger_error("Could not get a connection resource: ".$this->config->get_ldap_host_bind(), E_USER_ERROR);
			
			// Close ldap connection.
			// 2018-01-24, Commented out so lookup  
			// can work. It requires a current bind.
			//ldap_close($ldap);
			
			// Return results.
			return $result;
		}
		
		// Process login attempt through local database.
		public function login_local()
		{					
			// Get values.
			$req_account 			= $this->data_account->get_account();
			$req_credential			= $this->data_account->get_credential();						
			
			$query = $this->config->get_database();			
		
			// Query the local account table using given account and password.
			$query->set_sql("{call account_login(@account 		= ?,														 
												@credential 	= ?)}");				
			
			$params = array(&$req_account, &$req_credential);
			
			$query->set_param_array($params);		
			$query->query_run();
			
			// If a row is returned, then provided credentials match a local login.
			if($query->get_row_exists())
			{
				// Populate account data object with datbase row.
				$query->get_line_config()->set_class_name(__NAMESPACE__.'\DataAccount');					
				$this->data_account = $query->get_line_object();
				
				// Email is not in the data base as a field, but accounts
				// ARE email, so just transpose it here.
				$this->data_account->set_email($this->data_account->get_account());
				
				// Set result to indicate a local login.				 														
				$this->login_result = LOGIN_RESULT::LOCAL;									
			}
			else
			{				
				$this->login_result = LOGIN_RESULT::NO_BIND;
			}
		}
		
		// Get account information from application specific database.
		public function login_application()
		{					
			// Get values.
			$account = $this->data_account->get_account();
			
			$query = $this->config->get_database();			
		
			// Query the local account table using given account and password.
			$query->set_sql("{call account_lookup(@account = ?)}");				
			
			$params = array($account);
			
			$query->set_param_array($params);		
			$query->query_run();
			
			// If a row is returned, then provided credentials match a local login.
			if($query->get_row_exists())
			{
				// Populate account data object with datbase row.
				$query->get_line_config()->set_class_name(__NAMESPACE__.'\DataAccount');					
				$this->data_account = $query->get_line_object();									
			}
			else
			{				
			}
		}
	}

	

?>