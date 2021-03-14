<?php

	namespace dc\record_navigation; 

	require_once('config.php');
	
    /* 
    * To collect "page control" recordset from
    * database. Needs rolling into main class 
    * or offloading to file.
    */
    class data_paging
    {
        private $page_count = NULL;
        private $record_count = NULL;
        
        public function get_page_count()
        {
            return $this->page_count;
        }
        
        public function get_record_count()
        {
            return $this->record_count;
        }
    }

	interface iPaging
	{		
		// Accessors
		function get_member_config();
		function get_markup();
		function get_row_max();
		function get_row_count();
		function get_page_last();
		function get_page_current();
		
		// Mutators
		function set_member_config($value);
		function set_markup($value);
		function set_row_count_total($value);		
		function set_row_max($value);
		function set_page_last($value);
		function set_page($value);
		
		// Operations.
		function populate_from_request();				
		function generate_paging_markup();	
	}

	// Paging handler.
	class Paging implements iPaging
	{
		const ROW_MAX					= 25;
		const REQUEST_KEY_PAGE_NUMBER	= 'page'; // If this is changed, make sure to change the member name and mutator.
					
		private $config				= NULL;
		private	$markup				= NULL;		
		private	$page_last			= NULL;			
		private	$page_current		= NULL;
		private	$row_count_total	= NULL;	// Row count without paging.
		private $row_max			= NULL;
				
		public function __construct(PagingConfig $config = NULL)
		{			
			if($config)
			{
				$this->set_member_config($config);
			}
			else
			{
				$this->set_member_config(new PagingConfig);
			}
			
			$this->populate_from_request();
			
			$this->row_max = self::ROW_MAX;
			
			// Make sure the page number request is a positive numeric value.
			if (!is_numeric($this->page_current) || $this->page_current < 1)
			{ 
				$this->page_current = 1; 
			}						
		}				
		
		public function get_member_config()
		{
			return $this->config;
		}
		
		public function set_member_config($value)
		{
			$this->config = $value;
		}
		
		// Accessors
		public function get_markup()
		{
			return $this->markup;
		}
		
		public function get_row_max()
		{
			return $this->row_max;
		}
		
		public function get_row_count()
		{
			return $this->row_count_total;
		}
		
		public function get_page_last()
		{
			return $this->page_last;
		}	
		
		public function get_page_current()
		{
			return $this->page_current;
		}
		
		// Mutators
		public function set_markup($value)
		{
			$this->markup = $value;
		}
		
		public function set_row_count_total($value)
		{
			$this->row_count_total = $value;
		}
		
		public function set_row_max($value)
		{
			$this->row_max = $value;
		}
		
		public function set_page_last($value)
		{
			$this->page_last = $value;
		}
		
		public function set_page($value)
		{
			$this->page_current = $value;
		}
		
		// Populate members from $_REQUEST.
		public function populate_from_request()
		{		
			// Interate through each class method.
			foreach(get_class_methods($this) as $method) 
			{		
				$key = str_replace('set_', '', $method);
							
				// If there is a request var with key matching
				// current method name, then the current method 
				// is a set mutator for this request var. Run 
				// it (the set method) with the request var. 
				if(isset($_GET[$key]))
				{					
					$this->$method($_GET[$key]);					
				}
			}			
		}
				
		public function generate_paging_markup() 
		{		
			
			$url_query	= $this->config->get_url_query_instance();
		
			// Start caching page contents.
			ob_start();
				
			?>
			<div class="btn-group">
			<?php
			
			$pages = $this->page_last; // ceil($count/$PERPAGE_LIMIT);

			// if pages exists after loop's lower limit
			if($pages > 1) 
			{
				if(($this->page_current - 3) > 0) 
				{
				
					// Build URL query.		
					$url_query->set_data(self::REQUEST_KEY_PAGE_NUMBER, 1);			
					?>
					<a  
					   	type		="button" 
                    	name		="paging_1"
                    	id			="paging_1"
						class		="btn btn-primary btn-sm" 
						title		="Go to first page."
						href="<?php echo $url_query->return_url_encoded(); ?>"><span class="glyphicon glyphicon-fast-backward">&LeftArrowBar;</span></a>
				<?php
				}

				// Loop for provides links for 2 pages before and after current page
				for($i = ($this->page_current - 2); $i <= ($this->page_current + 2); $i++)	
				{
					if($i < 1) 
					{
						continue;
					}
					
					if($i > $pages) 
					{
						break;
					}
					
					// Build URL query.		
					$url_query->set_data(self::REQUEST_KEY_PAGE_NUMBER, $i);
					
					// Cursor same as current page? We want
					// to indicate to user and disable link.
					// Otherwise build a clickable button.
					?>
					<a  
							type		="button" 
							name		="paging_<?php echo $i; ?>"
							id			="paging_<?php echo $i; ?>"
							class		="btn btn-primary btn-sm <?php echo ($this->page_current == $i ? 'active' : ''); ?>" 
							title		="Go to page <?php echo $i; ?>."
							href="<?php echo $url_query->return_url_encoded(); ?>"><?php echo $i; ?></a>
					<?php
				}				
				
				if(($pages - ($this->page_current + 2)) > 0) 
				{
					// Build URL query.					
					$url_query->set_data(self::REQUEST_KEY_PAGE_NUMBER, $this->page_last);
					
					?>
					<a  
							type		="button" 
							name		="paging_<?php echo $i; ?>"
							id			="paging_<?php echo $i; ?>"
							class		="btn btn-primary btn-sm <?php echo ($this->page_current == $pages ? 'active' : ''); ?>" 
							title		="Go to page <?php echo $i; ?>."
							href="<?php echo $url_query->return_url_encoded(); ?>"><span class="glyphicon glyphicon-fast-forward">&RightArrowBar;</span></a>
					<?php					
				}
			}
			
			?>
			</div>
			<br /><span class="text-muted"><?php echo $this->page_last; ?>&nbsp;<?php echo ($pages == 1 ? 'page' : 'pages'); ?>,&nbsp;<?php echo $this->row_count_total; ?>&nbsp;<?php echo ($this->row_count_total == 1 ? 'record' : 'records'); ?>.</span>
			
			<?php
			
			// Collect contents from cache and then clean it.
			$this->markup = ob_get_contents();
			ob_end_clean();
			
			return $this->markup;
		}
	}

?>