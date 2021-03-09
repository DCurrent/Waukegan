<?php 		
	
	require(__DIR__.'/source/main.php');
	
	abstract class SORTING_FIELDS
	{
		const
			NAME 	= 1,
			STATUS	= 2,
			CREATED	= 3,
			UPDATED = 4;
	}
	
	class class_filter
	{
		private
			$create_f	= NULL,
			$create_t	= NULL,
			$update_f	= NULL,
			$update_t 	= NULL,
			$status		= NULL;
		
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
		
		private function validateDate($date, $format = 'Y-m-d')
		{
			$d = DateTime::createFromFormat($format, $date);
			return $d && $d->format($format) == $date;
		}
		
		public function get_create_f()
		{
			return $this->create_f;
		}
		
		public function get_create_t()
		{
			return $this->create_t;
		}
		
		public function get_update_f()
		{
			return $this->update_f;
		}
		
		public function get_update_t()
		{
			return $this->update_t;
		}
		
		public function get_status()
		{
			return $this->status;
		}
		
		public function set_create_f($value)
		{
			if($this->validateDate($value) === TRUE)
			{
				$this->create_f = $value;
			}
		}
		
		public function set_create_t($value)
		{
			if($this->validateDate($value) === TRUE)
			{
				$this->create_t = $value;
			}
		}		
		
		public function set_update_f($value)
		{
			if($this->validateDate($value) === TRUE)
			{
				$this->update_f = $value;
			}
		}
		
		public function set_update_t($value)
		{
			if($this->validateDate($value) === TRUE)
			{
				$this->update_t = $value;
			}
		}
		
		public function set_status($value)
		{		
			$this->status = $value;			
		}
	}
	
	// Prepare redirect url with variables.
	$url_query	= new url_query;
		
	// User access.
	$access_obj = new \dc\stoeckl\status();
	$access_obj->get_config()->set_authenticate_url(APPLICATION_SETTINGS::AUTHENTICATE_URL);
	$access_obj->set_redirect($url_query->return_url());
	
	$access_obj->verify();	
	$access_obj->action();
	
	// Start page cache.
	$page_obj = new class_page_cache();
	ob_start();		
		
	// Set up navigaiton.
	$navigation_obj = new class_navigation();
	$navigation_obj->generate_markup_nav();
	$navigation_obj->generate_markup_footer();	
	
	// Set up database.
	$db_conn_set = new class_db_connect_params();
	$db_conn_set->set_name(DATABASE::NAME);
	
	$db = new class_db_connection($db_conn_set);
	$query = new class_db_query($db);
		
	$paging = new class_paging;
	
	// Establish sorting object, set defaults, and then get settings
	// from user (if any).
	$sorting = new class_sort_control;
	$sorting->set_sort_field(SORTING_FIELDS::CREATED);
	$sorting->set_sort_order(SORTING_ORDER_TYPE::DECENDING);
	$sorting->populate_from_request();
	
	$filter = new class_filter();
	$filter->populate_from_request();
		
	$query->set_sql('{call ticket_list(@page_current 		= ?,														 
										@page_rows 			= ?,
										@page_last 			= ?,
										@row_count_total	= ?,
										@account 			= ?,
										@create_from 		= ?,
										@create_to 			= ?,
										@update_from 		= ?,
										@update_to 			= ?,
										@status				= ?,
										@sort_field 		= ?,
										@sort_order 		= ?)}');
											
	$page_last 	= NULL;
	$row_count 	= NULL;
	$sort_field 		= $sorting->get_sort_field();
	$sort_order 		= $sorting->get_sort_order();	
	
	$params = array(array($paging->get_page_current(), 	SQLSRV_PARAM_IN), 
					array($paging->get_row_max(), 		SQLSRV_PARAM_IN), 
					array($page_last, 					SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($row_count, 					SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($access_obj->get_account(), 	SQLSRV_PARAM_IN),
					array($filter->get_create_f(),		SQLSRV_PARAM_IN),
					array($filter->get_create_t(),		SQLSRV_PARAM_IN),
					array($filter->get_update_f(),		SQLSRV_PARAM_IN),
					array($filter->get_update_t(),		SQLSRV_PARAM_IN),
					array($filter->get_status(),		SQLSRV_PARAM_IN),
					array($sort_field,					SQLSRV_PARAM_IN),
					array($sort_order,					SQLSRV_PARAM_IN));

	$query->set_params($params);
	$query->query();
	
	$query->get_line_params()->set_class_name('class_ticket_data');
	$_obj_data_main_list = $query->get_line_object_list();

	// Send control data from procedure to paging object.
	$paging->set_page_last($page_last);
	$paging->set_row_count_total($row_count);

	// Datalist list generation.
	$_obj_data_list_status = NULL;
	
	$query->set_sql('{call ticket_status_list}');
		
	$query->query();
	$query->get_line_params()->set_class_name('class_status_list_data');
	
	$_obj_data_list_status_list = $query->get_line_object_list();	

?>

<!DOCtype html>
<html lang="en">
    <head>
    	<!-- Disable IE compatability mode. Must be FIRST tag in header. -->
    	<meta http-equiv="X-UA-Compatible" content="IE=EDGE" />
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?></title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="source/bootstrap/style.css">
        <link rel="stylesheet" href="../waukegan/source/css/style.css" />
        <link rel="stylesheet" href="../waukegan/source/css/print.css" media="print" />
        
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
        <script src="source/bootstrap/script.js"></script>
        
        <style>
		
.in.collapse+a.btn.showdetails:before
{
    content:'Hide details «';
}
.collapse+a.btn.showdetails:before
{
    content:'Show details »';
}
</style>
    </head>
    
    <body>    
        <div id="container" class="container">            
            <?php echo $navigation_obj->get_markup_nav(); ?>                                                                                
            <div class="page-header">
                <h1>Tickets List</h1>
                <p>This is a list of tickets. Non administrators will only see their own tickets.</p>
            </div> 
                                
            	<div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 id="h41" class="panel-title">
                        <a class="accordion-toggle" data-toggle="collapse" href="#collapse_module_1"><span class="glyphicon glyphicon-filter"></span><span class="glyphicon glyphicon-menu-down pull-right"></span> Filters</a>
                        </h4>
                    </div>
                
                	<div style="" id="collapse_module_1" class="panel-collapse collapse">
                        <div class="panel-body"> 
                                                        
                            <!--legend></legend-->                           
                            <form class="form-horizontal" role="form" id="filter" method="get" enctype="multipart/form-data">
            	                
                                <input type="hidden" name="field" value="<?php echo $sorting->get_sort_field(); ?>" />
                                <input type="hidden" name="order" value="<?php echo $sorting->get_sort_order(); ?>" />
                            
                                <div class="form-group">
                                    <label class="control-label col-sm-2" for="created">Created (from):</label>
                                    <div class="col-sm-4">
                                        <input 
                                            type	="datetime-local" 
                                            class	="form-control"  
                                            name	="create_f" 
                                            id		="create_f" 
                                            placeholder="yyyy-mm-dd"
                                            value="<?php echo $filter->get_create_f(); ?>">
                                    </div>
                                
                                    <label class="control-label col-sm-2" for="created">Created (to):</label>
                                    <div class="col-sm-4">
                                        <input 
                                            type	="datetime-local" 
                                            class	="form-control"  
                                            name	="create_t" 
                                            id		="create_t" 
                                            placeholder="yyyy-mm-dd"
                                            value="<?php echo $filter->get_create_t(); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="control-label col-sm-2" for="created">Updated (from):</label>
                                    <div class="col-sm-4">
                                        <input 
                                            type	="datetime-local" 
                                            class	="form-control"  
                                            name	="update_f" 
                                            id		="update_f" 
                                            placeholder="yyyy-mm-dd"
                                            value="<?php echo $filter->get_update_f(); ?>">
                                    </div>
                                
                                    <label class="control-label col-sm-2" for="created">Updated (to):</label>
                                    <div class="col-sm-4">
                                        <input 
                                            type	="datetime-local" 
                                            class	="form-control"  
                                            name	="update_t" 
                                            id		="update_t" 
                                            placeholder="yyyy-mm-dd"
                                            value="<?php echo $filter->get_update_t(); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="control-label col-sm-2" for="status">Status:</label>
                                    <div class="col-sm-10">
                                        <?php
                                        if(is_object($_obj_data_list_status_list) === TRUE)
                                        {
                                            for($_obj_data_list_status_list->rewind(); $_obj_data_list_status_list->valid(); $_obj_data_list_status_list->next())
                                            {						
                                                $_obj_data_list_status = $_obj_data_list_status_list->current();							
                                        ?>                           
                                            <div class="radio">
                                            
                                                <label><input 
                                                        type="radio" 
                                                        name="status" 
                                                        value="<?php echo $_obj_data_list_status->get_id(); ?>"                                             
                                                        <?php if($filter->get_status() == $_obj_data_list_status->get_id()) echo ' checked ';?>><?php echo $_obj_data_list_status->get_label(); ?></label>
                                            </div>
                                       
                                        <?php
                                            }
                                        }
                                        ?>     
                                                
                                            <div class="radio">
                                        
                                                <label><input 
                                                        type="radio" 
                                                        name="status" 
                                                        value=""                                             
                                                        <?php if($filter->get_status() == NULL) echo ' checked ';?>>All</label>
                                            </div>                   
                                    </div>
                                </div>
                                
                                <button 
                                                type	="submit"
                                                class 	="btn btn-primary btn-block" 
                                                name	="set_filter" 
                                                id		="set_filter"
                                                title	="Apply selected filters to list."
                                                >
                                                <span class="glyphicon glyphicon-filter"></span>Apply Filters</button>       
                                    
                            </form>                                       
                        </div>
                    </div>
                </div>
            
            <br />
          
          	<?php
				// Clickable rows. Clicking on table rows
				// should take user to a detail page for the
				// record in that row. To do this we first get
				// the base name of this file, and remove "list".
				// 
				// The detail file will always have same name 
				// without "list". Example: area.php, area_list.php
				//
				// Once we have the base name, we can use script to
				// make table rows clickable by class selector
				// and passing a completed URL (see the <tr> in
				// data table we are making clickable).
				//
				// Just to ease in development, we verify the detail
				// file exists before we actually include the script
				// and build a complete URL string. That way if the
				// detail file is not yet built, clicking on a table
				// row does nothing at all instead of giving the end
				// user an ugly 404 error.
				//
				// Lastly, if the base name exists we also build a 
				// "new item" button that takes user directly
				// to detail page with a blank record.	
			 
				$target_url 	= '#';
				$target_name	= basename(__FILE__, '_list.php').'.php';
				$target_file	= __DIR__.'/'.$target_name;				
				
				// Does the file exisit? If so we can
				// use the URL, script, and new 
				// item button.
				if(file_exists($target_file))
				{
					$target_url = $target_name;
				?>
                	<script>
						// Clickable table row.
						jQuery(document).ready(function($) {
							$(".clickable-row").click(function() {
								window.document.location = '<?php echo $target_url; ?>?id=' + $(this).data("href");
							});
						});
					</script>
                    
                    <a href="<?php echo $target_url; ?>&#63;nav_command=<?php echo RECORD_NAV_COMMANDS::NEW_BLANK;?>&amp;id=<?php echo DB_DEFAULTS::NEW_ID; ?>" class="btn btn-success btn-block" title="Click here to start entering a new item."><span class="glyphicon glyphicon-plus"></span> <?php //echo LOCAL_BASE_TITLE; ?></a>
                <?php
				}
				
			?>
          
            <!--div class="table-responsive"-->
            <table class="table">
                <caption></caption>
                <thead>
                    <tr>
                        <th><a href="<?php echo $sorting->sort_url(1); ?>">Name <?php echo $sorting->sorting_markup(1); ?></a></th>
                        <th>Details</th>
                        <th><a href="<?php echo $sorting->sort_url(2); ?>">Status <?php echo $sorting->sorting_markup(2); ?></a></th>
                        <th><a href="<?php echo $sorting->sort_url(3); ?>">Created <?php echo $sorting->sorting_markup(3); ?></a></th>
                        <th><a href="<?php echo $sorting->sort_url(4); ?>">Updated <?php echo $sorting->sorting_markup(4); ?></a></th>
                    </tr>
                </thead>
                <tfoot>
                </tfoot>
                <tbody>                        
                    <?php						
						$_obj_data_main = NULL;
					
						$row_class = array(1 => 'alert-danger',
											2 => 'alert-warning',
											3 => 'alert-success',
											4 => '',
											5 => 'alert-info');
						
						$status = array(1 => 'New Request',
											2 => 'In progress',
											3 => 'Closed - Complete',
											4 => 'Closed - Not Completed.',
											5 => 'On hold.');
					
                        
						if(is_object($_obj_data_main_list) === TRUE)
						{
							for($_obj_data_main_list->rewind(); $_obj_data_main_list->valid(); $_obj_data_main_list->next())
							{						
								$_obj_data_main = $_obj_data_main_list->current();	
								
								// Let's limit how much is shown in the table to keep row height resonable.
								$details_display = strip_tags($_obj_data_main->get_details());
								$details_display .= '...';
								
                        ?>
                                <tr  class="clickable-row <?php echo $row_class[$_obj_data_main->get_status()]; ?>" role="button" data-href="<?php echo $_obj_data_main->get_id(); ?>">
                                    <td><?php echo $_obj_data_main->get_label(); ?></td>
                                    <td><?php echo $details_display; ?></td>
                                    <td><?php echo $status[$_obj_data_main->get_status()]; ?></td>
                                    <td><?php if(is_object($_obj_data_main->get_log_create()) === TRUE) echo date(APPLICATION_SETTINGS::TIME_FORMAT, $_obj_data_main->get_log_create()->getTimestamp()); ?></td>
                                    <td><?php if(is_object($_obj_data_main->get_log_update()) === TRUE) echo date(APPLICATION_SETTINGS::TIME_FORMAT, $_obj_data_main->get_log_update()->getTimestamp()); ?></td>
                                </tr>                                    
                        <?php								
                        	}
						}
                    ?>
                </tbody>                        
            </table>  

            <?php

				echo $paging->generate_paging_markup();
				echo $navigation_obj->get_markup_footer(); 
				echo '<!--Page Time: '.$page_obj->time_elapsed().' seconds-->';
			?>
        </div><!--container-->        
    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-40196994-1', 'uky.edu');
  ga('send', 'pageview');
  
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
</body>
</html>

<?php
	// Collect and output page markup.
	$page_obj->markup_from_cache();	
	$page_obj->output_markup();
?>