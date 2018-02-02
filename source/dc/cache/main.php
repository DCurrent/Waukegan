<?php

	class class_page_cache
	{
		private 
			$markup = NULL,
			$time_start	= NULL;
		
		public function __construct()
		{
			
			$this->time_start = microtime(TRUE);			
			
			ob_start();
		}
		
		public function __destruct()
		{
			//ob_end_clean();
		}
		
		public function get_time_start()
		{
			return $this->time_start;
		}
		
		public function time_elapsed()
		{
			return (number_format(microtime(TRUE) - $this->time_start, 5));
		}
		
		public function markup_from_cache()
		{
			$this->markup = ob_get_contents();			
			return $this->markup;
		}
		
		// Turn off cache and output markup to screen.
		public function output_markup()
		{
			ob_end_clean();
			echo $this->markup;
		}
		
		public function get_markup()
		{
			return $this->markup;
		}
		
		public function set_markup($value)
		{
			$this->markup = $value;
		}
	}
?>
