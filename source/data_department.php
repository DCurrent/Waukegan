<?php

	require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/database/main.php'); 	// Database class.
	
	class class_department_data
	{
		private 
			$id 	= NULL,
			$label	= NULL;
			
		public function get_id()
		{
			return $this->id;
		}
		
		public function get_label()
		{
			return $this->label;
		}
	}
	

	class class_department_list
	{
		private 
			$db_conn_set = NULL,
			$db			= NULL,
			$query		= NULL,
			$markup		= NULL,
			$current	= NULL;
			
		public function __construct()
		{
			// Set connection params to space database.
			$connect = new class_db_connect_params();			
			$connect->set_name('UKSpace');
			
			$this->db 		= new class_db_connection($connect);
			$this->query	= new class_db_query($this->db);
		}
		
		public function generate_list()
		{
			$line_obj_arr 	= NULL;
			$selected 		= NULL;
			
			$this->query->set_sql("SELECT DISTINCT     Dept_ID as id, DeptName as label
									FROM         Departments
									WHERE     (DeptName <> '')
									ORDER BY DeptName");
			$this->query->query();
			
			$this->query->get_line_params()->set_class_name('class_department_data');	
			
			$line_obj_arr = $this->query->get_line_object_all();
			
			// Clear markup.
			$this->markup = NULL;
			
			echo $this->current;
			
			foreach($line_obj_arr as $line_obj)
			{
				if($line_obj->get_id() == $this->current) 
				{
					$selected = ' selected ';
				}
				else
				{
					$selected = NULL;
				}
				
				
				
				$this->markup.=	'<option value="'.$line_obj->get_id().'"'.$selected.'>'.$line_obj->get_id().' - '.ucwords(strtolower($line_obj->get_label())).'</option>';
			}	
			
			return $this->markup;			
		}
		
		public function set_current($value)
		{
			$this->current = $value;
		}
		
		public function get_markup()
		{
			return $this->markup;
		}
	}