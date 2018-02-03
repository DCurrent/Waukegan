<?php 
	
	// Just to avoid lots of retpying.
	
	require(__DIR__.'/source/main.php');
	
	// Page caching.
	$page_obj = new class_page_cache();
	ob_start();
			
	// Main navigaiton.
	$obj_navigation_main = new class_navigation();
	$obj_navigation_main->generate_markup_nav();
	$obj_navigation_main->generate_markup_footer();	
				
	// Set up database.
	$db_conn_set = new class_db_connect_params();
	$db_conn_set->set_name(DATABASE::NAME);
	
	$db = new class_db_connection($db_conn_set);
	$query = new class_db_query($db);		
			
	// Record navigation.
	$obj_navigation_rec = new class_record_nav();	
	
	// Prepare redirect url with variables.
	$url_query	= new url_query;
	$url_query->set_data('action', $obj_navigation_rec->get_action());
	$url_query->set_data('id', $obj_navigation_rec->get_id());
	
	// User access.
	$access_obj = new \dc\stoeckl\status();
	$access_obj->get_config()->set_authenticate_url(APPLICATION_SETTINGS::AUTHENTICATE_URL);
	$access_obj->set_redirect($url_query->return_url());
		
	$access_obj->verify();
	$access_obj->action();
	
	
	// Initialize our data objects. This is just in case there is no table
	// data for any of the navigation queries to find, we are making new
	// records, or copies of records. It also has the side effect of enabling 
	// IDE type hinting.
	$_main_data = new class_ticket_data();	
		
	// Ensure the main data ID member is same as navigation object ID.
	$_main_data->set_id($obj_navigation_rec->get_id());
			
	switch($obj_navigation_rec->get_action())
	{		
	
		default:		
		case RECORD_NAV_COMMANDS::NEW_BLANK:
		
			$_main_data->set_account($access_obj->get_account());
			$_main_data->set_status(1);
			break;
			
		case RECORD_NAV_COMMANDS::NEW_COPY:			
			
			// Populate the object from post values.			
			$_main_data->populate_from_request();			
			break;
			
		case RECORD_NAV_COMMANDS::LISTING:
			
			// Direct to listing.				
			header('Location: ticket_list.php');
			break;
			
		case RECORD_NAV_COMMANDS::DELETE:						
			
			// Populate the object from post values.			
			$_main_data->populate_from_request();
				
			// Call and execute delete SP.
			$query->set_sql('{call ticket_delete(@id = ?)}');			
			
			$query->set_params(array(array($_main_data->get_id(), SQLSRV_PARAM_IN)));
			$query->query();
			
			// Refrsh page to the previous record.				
			header('Location: '.$_SERVER['PHP_SELF']);			
				
			break;				
					
		case RECORD_NAV_COMMANDS::SAVE:
			
			// Stop errors in case someone tries a direct command link.
			if($obj_navigation_rec->get_command() != RECORD_NAV_COMMANDS::SAVE) break;
			
			$file_name = NULL;
			
			if(isset($_FILES))
			{				
				$target_dir = 'C:/upload/';
				//$target_dir = __DIR__.'/media/upload/';
				$file_name = basename($_FILES["fileToUpload"]["name"]);
				
				if($file_name != '')
				{
				
					$file_name = uniqid().'_'.$file_name;
					
					$target_file = $target_dir.$file_name;
					$uploadOk = 1;
					$imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);			
					
					var_dump($_FILES);					
					
					// Check if image file is a actual image or fake image
					
						$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);				
						
						if($check !== false) {
							echo "<br />File is an image - " . $check["mime"] . ".";
							$uploadOk = 1;
						} else {
							echo "<br />File is not an image.";
							$uploadOk = 0;
						}
					
					// Check if file already exists
					if (file_exists($target_file)) {
						echo "<br />Sorry, file already exists.";
						$uploadOk = 0;
					}
					
					// Check file size
					$file_size = $_FILES["fileToUpload"]["size"];
					//echo '<br />Size: '.$file_size;
					//if ($file_size > 5000000) {
					//	echo "<br />Sorry, your file is too large.";
					//	$uploadOk = 0;
					//}
					
					// Allow certain file formats
					if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
					&& $imageFileType != "gif" ) {
						echo "<br />Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
						$uploadOk = 0;
					}
					
					// Check if $uploadOk is set to 0 by an error
					if ($uploadOk == 0) {
						echo "<br />Sorry, your file was not uploaded.";
					// if everything is ok, try to upload file
					} else {
						if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
							echo "<br />The file ".basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
						} else {
							echo "<br />Sorry, there was an error uploading your file.";
						}
					}
					
					//$im = imagecreatetruecolor(50, 50);
				}
			}
						
			// Save the record. Saving main record is straight forward. We’ll run the populate method on our 
			// main data object which will gather up post values. Then we can run a query to merge the values into 
			// database table. We’ll then get the id from saved record (since we are using a surrogate key, the ID
			// should remain static unless this is a brand new record). 
			
			// If necessary we will then save any sub records (see each for details).
			
			// Finally, we redirect to the current page using the freshly acquired id. That will ensure we have 
			// always an up to date ID for our forms and navigation system.			
		
			// Populate the object from post values.			
			$_main_data->populate_from_request();
			
			$_main_data_label = $_main_data->get_label(); 
		
			// Call update stored procedure.
			$query->set_sql('{call ticket_update(@id = ?,														 
													@label = ?,
													@details = ?,
													@status = ?,
													@eta = ?,
													@account = ?,
													@attachment = ?,														 
													@log_update = ?, 
													@log_update_by = ?, 
													@log_update_ip = ?)}');
													
			$params = array(array($_main_data->get_id(), SQLSRV_PARAM_IN),
						array($_main_data->get_label(), SQLSRV_PARAM_IN),						
						array($_main_data->get_details(), SQLSRV_PARAM_IN),
						array($_main_data->get_status(), SQLSRV_PARAM_IN),
						array($_main_data->get_eta(), SQLSRV_PARAM_IN),
						array($_main_data->get_account(), SQLSRV_PARAM_IN),
						array(&$file_name, SQLSRV_PARAM_IN),
						array(date(APPLICATION_SETTINGS::TIME_FORMAT), SQLSRV_PARAM_IN),
						array($access_obj->get_account(), SQLSRV_PARAM_IN),
						array($access_obj->get_ip(), SQLSRV_PARAM_IN));
			
			$query->set_params($params);			
			$query->query();
			
			// Repopulate main data object with results from merge query.
			$query->get_line_params()->set_class_name('class_ticket_data');
			$_main_data = $query->get_line_object();
			
						
			// Sub table: Journal.
			$_obj_data_sub_request = new class_ticket_journal_data;
			$_obj_data_sub_request->populate_from_request();			
				
			// If the sub value id is an array, then we know there are 
			// values to update.
			if(is_array($_obj_data_sub_request->get_id()) === TRUE)
			{						
				// Call update stored procedure.
				$query->set_sql('{call ticket_journal_update(@fk_id = ?,														 
														@xml = ?,
														@log_update = ?,
														@log_update_by = ?,
														@log_update_ip = ?)}');
														
				$params = array(array($_main_data->get_id(), 			SQLSRV_PARAM_IN),
								array($_obj_data_sub_request->xml(), 	SQLSRV_PARAM_IN),
								array(date(APPLICATION_SETTINGS::TIME_FORMAT), 					SQLSRV_PARAM_IN),
								array($access_obj->get_account(),		SQLSRV_PARAM_IN),
								array($access_obj->get_ip(), 			SQLSRV_PARAM_IN));
				
				$query->set_params($params);			
							
				// Execute the merging query.
				$query->query();
			}
			
			// Sub table: Parties
			$_obj_data_sub_request = new class_ticket_party_data();
			$_obj_data_sub_request->populate_from_request();			
			
			// If the sub value id is an array, then we know there are 
			// values to update.
			if(is_array($_obj_data_sub_request->get_id()) === TRUE)
			{						
				// Call update stored procedure.
				$query->set_sql('{call ticket_party_update(@fk_id = ?,														 
														@xml = ?,
														@log_update = ?,
														@log_update_by = ?,
														@log_update_ip = ?)}');
														
				$params = array(array($_main_data->get_id(), 			SQLSRV_PARAM_IN),
								array($_obj_data_sub_request->xml(), 	SQLSRV_PARAM_IN),
								array(date(APPLICATION_SETTINGS::TIME_FORMAT), 					SQLSRV_PARAM_IN),
								array($access_obj->get_account(),		SQLSRV_PARAM_IN),
								array($access_obj->get_ip(), 			SQLSRV_PARAM_IN));
				
				$query->set_params($params);			
				$query->query();
			}
			
						
			// Set up and send email alert.
			$address  = NULL;
			
			$sub_data_party_account_arr = $_obj_data_sub_request->get_account();
			
			if(is_array($sub_data_party_account_arr))
			{
				foreach($sub_data_party_account_arr as $sub_data_party_account)
				{
					$address_temp = $sub_data_party_account.'@uky.edu';
					
					if(filter_var($address_temp, FILTER_VALIDATE_EMAIL)	== TRUE)
					{
						$address.= $address_temp.', ';
					}
				}
			}
								
			$subject = MAILING::SUBJECT;
			$body = 'A ticket has been created or updated. <a href="http://ehs.uky.edu/apps/waukegan/ticket.php?id='.$_main_data->get_id().'">Click here</a> to view details.';
					
			$headers   = array();
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=iso-8859-1";
			if(MAILING::FROM)	$headers[] = "From: ".MAILING::FROM;
			if(MAILING::BCC)	$headers[] = "Bcc: ".MAILING::BCC;
			if(MAILING::CC) 	$headers[] = "Cc: ".MAILING::CC;	
			
			// Run mail function.
			mail($address, MAILING::SUBJECT.' - '.$_main_data_label, $body, implode("\r\n", $headers));
			
			// Refrsh page to reload the record.				
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$_main_data->get_id());
			
			break;			
	}
	
	// Last thing to do before moving on to main html is to get data to populate objects that
	// will then be used to generate forms and subforms. This may have already been done, 
	// such as when making copies of a record, but normally only a only blank object 
	// will exist at this point. We run a basic select query from our current ID and 
	// if a row is found overwrite whatever is in the main data object. If needed, we
	// repeat the process for any sub queries and forms.
	//
	// If there is no row at all found, nothing will be done - this is intended behavior because
	// there could be several reasons why no record is found here and we don't want to have 
	// overly complex or repetitive logic, but that does mean we have to make sure there
	// has been an object established at some point above.
	
	//////
	$query->set_sql('{call ticket_detail(@id = ?,														 
								@account 		= ?,
								@sort_field 	= ?,
								@sort_order 	= ?,
								@nav_first		= ?,
								@nav_previous	= ?,
								@nav_next		= ?,
								@nav_last		= ?)}');	
	$nav_first 		= NULL;
	$nav_previous	= NULL;
	$nav_next		= NULL;
	$nav_last 		= NULL;
					
	$params = array(array($obj_navigation_rec->get_id(), 	SQLSRV_PARAM_IN), 
					array($access_obj->get_account(), 		SQLSRV_PARAM_IN),
					array(NULL, 		SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array(NULL, 		SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_first,	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_previous, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_next, 	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_last, 	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT));

	$query->set_params($params);
	$query->query();
	
	
	
	$query->get_line_params()->set_class_name('class_ticket_data');
	
	if($query->get_row_exists() === TRUE) $_main_data = $query->get_line_object();
	
	
	// Sub table (journal) generation
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name('class_ticket_journal_data');
	
	$_obj_data_sub_arr = array();
	if($query->get_row_exists()) $_obj_data_sub_arr = $query->get_line_object_list();
	
	// Sub table (party) generation.
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name('class_ticket_party_data');
	
	$_obj_data_sub_party_arr = array();
	if($query->get_row_exists()) $_obj_data_sub_party_arr = $query->get_line_object_list();
		
	// Now that we have parties, we'll need to add administrators and
	// the current user as default parites.
	
	// Get admin accounts.
	$default_party_arr = explode(', ', APPLICATION_SETTINGS::ADMINS);	
	
	// Add current account if it isn't an admin.
	if(array_search($access_obj->get_account(), $default_party_arr) === FALSE) 
	{
		$default_party_arr[] = $access_obj->get_account();
	}
	
	// Ensure the party list is valid, else create a blank.
	if(is_object($_obj_data_sub_party_arr) === FALSE)
	{
		$_obj_data_sub_party_arr = new SplDoublyLinkedList();
	}
	
	// Loop through elements of default party array.
	foreach($default_party_arr as $default_party)
	{			
		$party_add	= TRUE;
		$temp_obj	= NULL;
		
		// Loop over each exisiting item from party table. If there is a match
		// to the current party array element, we stop and set add flag to FALSE.
		for($_obj_data_sub_party_arr->rewind(); $_obj_data_sub_party_arr->valid(); $_obj_data_sub_party_arr->next())
		{
			$_obj_data_sub_party = $_obj_data_sub_party_arr->current();
			
			if($_obj_data_sub_party->get_account() == $default_party)
			{
				$party_add = FALSE;
				break;				
			} 			
		}
		
		// If add flag was TRUE, no match to the current party element was found.
		// We'll need to create an object, populate it with current party
		// element, and push it into table output for current parties.
		if($party_add == TRUE)
		{
			// Create and populate the object.
			$temp_obj = new class_ticket_party_data();
			$temp_obj->set_sub_party_id(DB_DEFAULTS::NEW_ID);
			$temp_obj->set_sub_party_account($default_party);
			
			// Push object to top of list.
			$_obj_data_sub_party_arr->unshift($temp_obj);
		}
	}
		
		
	// Datalist list generation.
	$_obj_data_list_status = NULL;
	
	$query->set_sql('{call ticket_status_list}');
		
	$query->query();
	$query->get_line_params()->set_class_name('class_status_list_data');
	
	// Populate data object array with results, or a single object if no
	// rows were found.
	if($query->get_row_exists())
	{		
		$_obj_data_list_status_arr = $query->get_line_object_all();
	}
	else
	{
		$_obj_data_list_status_arr = array(new class_status_data());		
	}	
	
	// Create a header for the page and title.
	$ticket_header = NULL;
	if($_main_data->get_label())
	{
		$ticket_header = ' - '.$_main_data->get_label();
	}
	else
	{
		if(!$_main_data->get_id() || $_main_data->get_id() == DB_DEFAULTS::NEW_ID)
		{
			$ticket_header = ' - New Item';
		}
	}
	
	
		$header_class = NULL;		
		switch($_main_data->get_status())
		{
			case 1:
				$header_class = "text-danger";				
				break;
			case 2:
				$header_class = "text-warning";				
				break;
			case 3:
				$header_class = "text-success";				
				break;								
			default:
			case 4:								
				break;
			case 5:
				$header_class = "text-info";				
				break;
		}
		
		// Populate main navigation with ID's from main data stored procedure.
		$obj_navigation_rec->set_id_first($nav_first);
		$obj_navigation_rec->set_id_previous($nav_previous);
		$obj_navigation_rec->set_id_next($nav_next);
		$obj_navigation_rec->set_id_last($nav_last);
	
		$obj_navigation_rec->generate_button_list();
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?>, Ticket Details<?php echo $ticket_header; ?></title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
        
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        
        <!-- Place inside the <head> of your HTML -->
		<script type="text/javascript" src="http://ehs.uky.edu/libraries/vendor/tinymce/tinymce.min.js"></script>
        
    </head>
    
    <body>    
        <div id="container" class="container">            
            <?php echo $obj_navigation_main->get_markup_nav(); ?>                                                                                
            <div class="page-header">           
                <h1>Ticket Entry<span class="<?php echo $header_class; ?>"><?php echo $ticket_header; ?></span></h1>
                <p>This screen allows for adding and editing individual request tickets.</p>
            </div>
            
            <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">           
           		<input type="hidden" name="account" id="account" value="<?php echo $_main_data->get_account(); ?>" />
                
				<?php echo $obj_navigation_rec->get_markup(); ?>         
          
          		<?php
					$lookup = new \dc\stoeckl\lookup;
				
					if($_main_data->get_account())
					{
						$lookup->lookup($_main_data->get_account());
					}
					else
					{
						$lookup->lookup($access_obj->get_account());
					}
				?>         		
          
          		<div class="form-group">
                	<label class="control-label col-sm-2">Created by:</label>
                	<div class="col-sm-10">
                		<p class="form-control-static"><?php echo $lookup->get_account_data()->name_proper(); ?></p>
                	</div>
                </div>
          
                
                <div class="form-group">
                	<label class="control-label col-sm-2">Created:</label>
                	<div class="col-sm-10">
                		<p class="form-control-static"><?php if(is_object($_main_data->get_log_create())) echo date(APPLICATION_SETTINGS::TIME_FORMAT, $_main_data->get_log_create()->getTimestamp()); ?></p>
                	</div>
                </div>
                
             	<div class="form-group">       
                    <label class="control-label col-sm-2">Last Update:</label>
                	<div class="col-sm-10">
                		<p class="form-control-static"><?php if(is_object($_main_data->get_log_update())) echo date(APPLICATION_SETTINGS::TIME_FORMAT, $_main_data->get_log_update()->getTimestamp()); ?></p>
                	</div>
                </div>
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="eta">ETA:</label>
                	<div class="col-sm-10">
                		<input 
                        	type	="datetime-local" 
                            class	="form-control"  
                            name	="eta" 
                            id		="eta" 
                            placeholder="Estimated time of completion." <?php if($access_obj->get_account() != APPLICATION_SETTINGS::ADMIN_MAIN) echo ' readonly '; ?>
                            value="<?php if(is_object($_main_data->get_eta())) echo date(APPLICATION_SETTINGS::TIME_FORMAT, $_main_data->get_eta()->getTimestamp()); ?>">
                	</div>
                </div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="label">Name:</label>
                	<div class="col-sm-10">
                		<input type="text" class="form-control"  name="label" id="label" placeholder="Request Name (Subject)" value="<?php echo $_main_data->get_label(); ?>" required>
                	</div>
                </div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="facility">Status:</label>
                	<div class="col-sm-10">
                    	<?php
                    	foreach($_obj_data_list_status_arr as $_obj_data_list_status)
                        {							
						?>                           
                       		<div class="radio">
                            
                            	<label><input 
                                			type="radio" 
                                            name="status" 
                                            value="<?php echo $_obj_data_list_status->get_id(); ?>"                                            
                                            <?php //if($access_obj->get_account() != APPLICATION_SETTINGS::ADMIN_MAIN) echo ' disabled '; ?> 
											<?php if($_main_data->get_status() == $_obj_data_list_status->get_id()) echo ' checked ';?>><?php echo $_obj_data_list_status->get_label(); ?></label>
                       	 	</div>
                       
                        <?php
                        }
                    	?>                        
                	</div>
				</div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="details">Details:</label>
                	<div class="col-sm-8">
                    	
                        <div id="details_display">
							<?php echo $_main_data->get_details(); ?>
                        </div>
                    
                    	<textarea
                        	style = "display:none;" 
                        	class	="form-control" 
                            rows	="5" 
                            name	="details" 
                            id		="details"><?php echo $_main_data->get_details(); ?></textarea>                        
                	</div>
                    
                    <button 
                            type	="button" 
                            class 	="btn btn-info btn-sm-2" 
                            name	="edit_details" 
                            id		="edit_details" 
                            onclick ="editDetails()"><span class="glyphicon glyphicon-pencil"></span></button>	
                    
                </div> 
                
                <!--<div class="form-group">
                	<label class="control-label col-sm-2" for="upfile">Image (TEST ONLY, NOT FOR USE):</label>
                	<div class="col-sm-5">
                    	<input type="file" name="fileToUpload" accept="image/*" capture>
                	</div>
                    <?php
						if($_main_data->get_attachment())
						{
							$attachment_url ='media/upload/'.$_main_data->get_attachment();
                    ?>
                            <div class="col-sm-5">
                                <a href="<?php echo $attachment_url;?>" target="_blank"><img src="<?php echo $attachment_url; ?>" class="img-thumbnail" style="max-height:100px; max-width:100px"></a>
                            </div>
                    <?php
						}
					?>
                </div>-->
                
                
                
                
                <div class="form-group">                    	
                        <div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend>Parties</legend>                    
                            <p>All accounts listed here will receive status alerts and have access to this ticket in their personal lists.</p>
                                                                                   
                            <table class="table table-striped table-hover" id="party_table"> 
                                <thead>
                                    <tr>
                                        <th>Account</th>                              
                                        <th>Name</th>
                                    </tr>                                    
                                </thead>
                                <tfoot>
                                </tfoot>
                                <tbody class="party_table_body">                        
                                    <?php
                                    if(is_object($_obj_data_sub_party_arr) === TRUE)
									{    
										// Generate table row for each item in list.
										for($_obj_data_sub_party_arr->rewind(); $_obj_data_sub_party_arr->valid(); $_obj_data_sub_party_arr->next())
										{						
											$_obj_data_sub_party = $_obj_data_sub_party_arr->current();
																					   
											// Blank IDs will cause a database error, so make sure there is a
											// usable one here.
											if(!$_obj_data_sub_party->get_id()) $_obj_data_sub_party->set_id(DB_DEFAULTS::NEW_ID);
											
											// If the party is a default administrator, disable editing.
											$class_add = NULL;				
											
											// Make sure there is a non-blank account.
											if($_obj_data_sub_party->get_account())
											{											
												if(array_search($_obj_data_sub_party->get_account(), $default_party_arr) !== FALSE) 
												{													
													$class_add = 'disabled';
												}
												
												// Lookup account info.
												$lookup->lookup($_obj_data_sub_party->get_account());
											}
											
											
									?>
											<tr>
												<td>
													 <input 	type="text"                                                        	 
														name	="sub_party_account[]" 
														id		="sub_party_account_<?php echo $_obj_data_sub_party->get_id(); ?>" 
														class	="form-control <?php echo $class_add; ?>" 
														value 	= "<?php echo $_obj_data_sub_party->get_account(); ?>">
												</td>
															
                                                <td>
                                                	<?php echo $lookup->get_account_data()->name_proper(); ?>													 
												</td>
                                                								  
												<td>													
													<input 
														type	="hidden" 
														name	="sub_party_id[]" 
														id		="sub_party_id_<?php echo $_obj_data_sub_party->get_id(); ?>" 
														value	="<?php echo $_obj_data_sub_party->get_id(); ?>" />                                    
													
													<button 
														type	="button" 
														class 	="btn btn-danger btn-sm <?php echo $class_add; ?>" 
														name	="row_add_party" 
														id		="row_del_party_<?php echo $_obj_data_sub_party->get_id(); ?>" 
														onclick="deleteRowsub_party(this)"><span class="glyphicon glyphicon-minus"></span></button>        
												</td>
											</tr>                                    
									<?php
										}                                        						
									}
									?>                        
                                </tbody>                        
                            </table>                            
                            
                            <button 
                                type	="button" 
                                class 	="btn btn-success" 
                                name	="row_add_party" 
                                id		="row_add_party"
                                title	="Add new item."
                                onclick	="insParty()">
                                <span class="glyphicon glyphicon-plus"></span></button>
                        </fieldset>
                        </div>                        
                    </div>
                
                <div class="form-group">                    	
                        <div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend>Journal</legend>                    
                            <p>This journal allows all parties to make notes on the progress and or resolution of a ticket.</p>
                                                                                   
                            <table class="table table-striped table-hover" id="POITable"> 
                                <thead>
                                    <tr>
                                        <th>Detail</th>
                                        <th>By</th>  
                                        <th colspan="2">Time</th>                            
                                    </tr>
                                </thead>
                                <tfoot>
                                </tfoot>
                                <tbody class="ec">                        
                                    <?php                              
                                    if(is_object($_obj_data_sub_arr) === TRUE)
									{        
										// Generate table row for each item in list.
										for($_obj_data_sub_arr->rewind(); $_obj_data_sub_arr->valid(); $_obj_data_sub_arr->next())
										{						
											$_obj_data_sub = $_obj_data_sub_arr->current();
										
											// Blank IDs will cause a database error, so make sure there is a
											// usable one here.
											if(!$_obj_data_sub->get_id()) $_obj_data_sub->set_id(DB_DEFAULTS::NEW_ID);
											
											// Lookup account info.
											$lookup->lookup($_obj_data_sub->get_log_update_by());
										?>
											<tr>
                                            	<td> 
                                                	<div id="sub_log_details_display_<?php echo $_obj_data_sub->get_id(); ?>">
														<?php echo $_obj_data_sub->get_details(); ?>
                                                    </div>
                                                                                                 	
													<textarea 
                                                    	style="display:none;"                                                 	 
														name	="sub_details[]" 
														id		="sub_details_<?php echo $_obj_data_sub->get_id(); ?>"><?php echo $_obj_data_sub->get_details(); ?></textarea>
												</td>  
												
                                                <td>                                             	
                                                    <?php echo $lookup->get_account_data()->name_proper(); ?>
												</td>
                                                
												<td><a id="<?php echo $_obj_data_sub->get_id(); ?>" href="#<?php echo $_obj_data_sub->get_id(); ?>"><?php if($_obj_data_sub->get_log_update()) echo date(APPLICATION_SETTINGS::TIME_FORMAT, $_obj_data_sub->get_log_update()->getTimestamp()); ?></a>			
												</td>
																							  
												<td>													
													<input 
														type	="hidden" 
														name	="sub_id[]" 
														id		="sub_id_<?php echo $_obj_data_sub->get_id(); ?>" 
														value	="<?php echo $_obj_data_sub->get_id(); ?>" />
														
													<button 
														type	="button" 
														class 	="btn btn-info btn-sm" 
														name	="row_edit" 
														id		="row_edit_<?php echo $_obj_data_sub->get_id(); ?>" 
														onclick ="editRowSub(<?php echo $_obj_data_sub->get_id(); ?>)"><span class="glyphicon glyphicon-pencil"></span></button>
                                                        
                                                    <button 
														type	="button" 
														class 	="btn btn-danger btn-sm" 
														name	="row_add" 
														id		="row_del_<?php echo $_obj_data_sub->get_id(); ?>" 
														onclick="deleteRowsub(this)"><span class="glyphicon glyphicon-minus"></span></button>       
												</td>
											</tr>                                    
									<?php
										}
									}
                                    ?>                        
                                </tbody>                        
                            </table>                            
                            
                            <button 
                                type	="button" 
                                class 	="btn btn-success" 
                                name	="row_add" 
                                id		="row_add_perm"
                                title	="Add new item."
                                onclick	="insRow()">
                                <span class="glyphicon glyphicon-plus"></span></button>
                        </fieldset>
                        </div>                        
                    </div>
                    
                                        
                <hr />
                <div class="form-group">
                	<div class="col-sm-12">
                		<?php echo $obj_navigation_rec->get_markup_cmd_save_block(); ?>
                	</div>
                </div>               
            </form>
            
            <?php echo $obj_navigation_main->get_markup_footer(); ?>
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
	
	<?php if(!$_main_data->get_id() || $_main_data->get_id() == DB_DEFAULTS::NEW_ID)
	{
	?>
		editDetails();
	<?php
	}
	?>
});
</script>

	<script>
		 var $temp_int = 0;
		 
		function deleteRowsub(row)
		{
			var i=row.parentNode.parentNode.rowIndex;
			document.getElementById('POITable').deleteRow(i);
		}
		
		function deleteRowsub_party(row)
		{
			var i=row.parentNode.parentNode.rowIndex;
			document.getElementById('party_table').deleteRow(i);
		}
		
		// Lookup account info.
		<?php 
			$lookup->lookup($access_obj->get_account());
		?>
		
		function editDetails()
		{	
			document.getElementById('edit_details').disabled='true';										
			document.getElementById('details_display').style.display='none';
			document.getElementById('details').style.display='block';
			
			tinymce.init({
            selector: '#details',
			plugins: [
				"advlist autolink lists link image charmap print preview anchor",
				"searchreplace visualblocks code fullscreen",
				"insertdatetime media table contextmenu paste"
			],
			toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"});	
		}
		
		function editRowSub($id)
		{
			document.getElementById('row_edit_'+ $id).disabled='true';
			document.getElementById('sub_details_'+ $id).style.display='block';
			document.getElementById('sub_log_details_display_'+ $id).style.display='none';
						
			tinymce.init({
            selector: '#sub_details_'+ $id,
			plugins: [
				"advlist autolink lists link image charmap print preview anchor",
				"searchreplace visualblocks code fullscreen",
				"insertdatetime media table contextmenu paste"
			],
			toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"});	
		}
		
		// Inserts a new table row on user request.		
		function insRow()
		{						
			$(".ec").append(
				'<tr>'
					+'<td>'
						+'<textarea name="sub_details[]" id="sub_details_'+ $temp_int + '" class="form-control"></textarea>'												
					+'</td>'																				
					+'<td colspan="2">'
						+'<input type="hidden" name="sub_id[]" id="sub_id_js_'+$temp_int+'" value="<?php echo DB_DEFAULTS::NEW_ID; ?>" />'
						+'<button type="button" class ="btn btn-danger btn-sm" name="row_add" id="row_del_js_'+$temp_int+'" onclick="deleteRowsub(this)"><span class="glyphicon glyphicon-minus"></span></button>'						
					+'</td>'
				+'<tr>');
			
			tinymce.init({
            selector: '#sub_details_'+ $temp_int,
			plugins: [
				"advlist autolink lists link image charmap print preview anchor",
				"searchreplace visualblocks code fullscreen",
				"insertdatetime media table contextmenu paste"
			],
			toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"});
			
			$temp_int++;
		}
		
		// Inserts a new table row on user request.
		function insParty()
		{						
			$(".party_table_body").append(
				'<tr>'
					+'<td>'
						+'<input type="text" name="sub_party_account[]" id="sub_party_account_js_'+$temp_int+'" class="form-control">'
					+'</td>'
																  
					+'<td>'													
						+'<input type="hidden" name="sub_party_id[]" id="sub_party_id_js_'+$temp_int+'" value="<?php echo DB_DEFAULTS::NEW_ID; ?>" />'
						+'<button type="button" class="btn btn-danger btn-sm" name="row_add_party" id="row_del_party_js_'+$temp_int+'"'
						+'onclick="deleteRowsub_party(this)"><span class="glyphicon glyphicon-minus"></span></button>'        
					+'</td>'
				+'</tr>');
			
			$temp_int++;
		}
		 
	</script>
</body>
</html>

<?php
	// Collect and output page markup.
	$page_obj->markup_from_cache();	
	$page_obj->output_markup();
?>