<?php

	namespace dc\record_navigation; 

	require_once('config.php');

	interface iPagingConfig
	{
		// Accessors
		function get_url_query_instance();
		
		// Mutators
		function set_url_query_instance($value);
	}

	class PagingConfig implements iPagingConfig
	{
		private $url_query_instance = NULL;
		
		public function get_url_query_instance()
		{
			return $this->url_query_instance;
		}
		
		public function set_url_query_instance($value)
		{
			$this->url_query_instance = $value;
		}
	}

?>