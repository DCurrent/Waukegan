<?php
	
	require_once(__DIR__.'/data_common.php');

	// tbl_ticket
	class class_ticket_data extends class_common_data
	{
		protected
			$account	= NULL,
			$details	= NULL,
			$eta		= NULL,
			$label		= NULL,
			$status		= NULL,
			$attachment	= NULL;
		
		// Accessors
		public function get_attachment()
		{
			return $this->attachment;
		}
		
		public function get_details()
		{
			return $this->details;
		}
		
		public function get_label()
		{
			return $this->label;
		}
		
		public function get_status()
		{
			return $this->status;
		}
		
		public function get_account()
		{
			return $this->account;
		}
		
		public function get_eta()
		{
			return $this->eta;
		}
		
		// Mutators
		public function set_account($value)
		{
			$this->account = $value;			
		}
		
		public function set_details($value)
		{
			$this->details = $value;
		}
		
		public function set_eta($value)
		{
			// Empty strings cause undefined behavior in 
			// databases.
			if(!$value) $value = NULL;
			
			$this->eta = $value;
		}
		
		public function set_label($value)
		{
			$this->label = $value;
		}	
		
		public function set_status($value)
		{
			$this->status = $value;
		}
	}
	
	// tbl_status_list
	class class_status_list_data extends class_common_data
	{
		protected
			$details	= NULL,
			$label		= NULL;
		
		// Accessors
		public function get_details()
		{
			return $this->details;
		}
		
		public function get_label()
		{
			return $this->label;
		}
		
		// Mutators
		public function set_details($value)
		{
			$this->details = $value;
		}
		
		public function set_label($value)
		{
			$this->label = $value;
		}	
	}
	
	// tbl_ticket_journal
	class class_ticket_journal_data extends class_common_data
	{
		protected
			$details	= NULL,
			$label		= NULL;
				
		// Get and return an xml string for database use.
		public function xml()
		{
			$result = '<root>';
						
			foreach($this->id as $key => $id)
			{								
				$result .= '<row id="'.$id.'">';
				$result .= '<label>'.$this->label[$key].'</label>';
				$result .= '<details>'.htmlspecialchars($this->details[$key]).'</details>';
				$result .= '</row>';									
			}
			
			$result .= '</root>';
			
			return $result;
		}
		
		// Accessors		
		public function get_details()
		{
			return $this->details;
		}
		
		public function get_label()
		{
			return $this->label;
		}
		
		// Mutators
		public function set_id($value)
		{			
		}
		
		public function set_details($value)
		{			
		}
		
		public function set_label($value)
		{
		}
		
		public function set_sub_id($value)
		{			
			$this->id = $value;
		}
		
		public function set_sub_details($value)
		{
			$this->details = $value;
		}	
		
		public function set_sub_label($value)
		{
			$this->label = $value;
		}
	}
	
	// tbl_ticket_party
	class class_ticket_party_data extends class_common_data
	{
		protected
			$account		= NULL,
			$description 	= NULL;
		
		// Get and return an xml string for database use.
		public function xml()
		{
			$result = '<root>';
						
			foreach($this->id as $key => $id)
			{								
				$result .= '<row id="'.$id.'">';
				$result .= '<account>'.htmlspecialchars($this->account[$key]).'</account>';
				$result .= '</row>';									
			}
			
			$result .= '</root>';
			
			return $result;
		}
		
		public function add_party($account)
		{
			$this->id[] = DB_DEFAULTS::NEW_ID;
			$this->account[] = $account;
			
			var_dump($this->id);
			echo '<br>';
			var_dump($this->account);
		}
		
		// Accessors
		public function get_description()
		{
			return $this->description;
		}
		
		public function get_account()
		{
			return $this->account;
		}
		
		// Mutators
		public function set_id($value)
		{
						
		}
		
		public function set_account($value)
		{			
		}
		
		public function set_description($value)
		{			
		}
		
		public function set_sub_party_description($value)
		{			
			$this->description = $value;
		}
		
		public function set_sub_party_account($value)
		{
			$this->account = $value;
		}
		
		public function set_sub_party_id($value)
		{			
			$this->id = $value;
		}
	}
?>
