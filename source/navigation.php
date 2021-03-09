<?php

	require_once(__DIR__.'/main.php');

	class class_navigation
	{
		const DIRECTORY_PRIME = '/apps/waukegan';
		
		private
			$access_obj			= NULL,
			$directory_local	= NULL,
			$directory_prime	= NULL,
			$markup_nav			= NULL,
			$markup_footer		= NULL;
		
		public function __construct()
		{
			$this->directory_prime 	= self::DIRECTORY_PRIME;
			$this->access_obj		= new \dc\stoeckl\status();
			
			$this->access_obj->get_config()->set_authenticate_url(APPLICATION_SETTINGS::AUTHENTICATE_URL);
			
		}
		
		public function get_directory_local()
		{
			return $this->directory_local;
		}
		
		public function get_directory_prime()
		{
			return $this->get_directory_prime();
		}
		
		public function set_directory_local($value)
		{
			$this->directory_local = $value;
		}
		
		public function get_markup_footer()
		{
			return $this->markup_footer;
		}
		
		public function get_markup_nav()
		{
			return $this->markup_nav;
		}
			
		public function generate_markup_nav()
		{
			$class_add = NULL;
			
			if(!$this->access_obj->get_account()) $class_add .= "disabled";
			
			// Start output caching.
			ob_start();
		?>
            <nav class="navbar">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#nav_main">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>                        
                        </button>
                        <a class="navbar-brand" href="<?php echo $this->directory_prime; ?>"><?php echo APPLICATION_SETTINGS::NAME; ?></a>
                    </div>
                    <div class="collapse navbar-collapse" id="nav_main">
                        <ul class="nav navbar-nav">
                            <!--<li class="active"><a href="#">Home</a></li>-->
                            <li class="dropdown"><a class="dropdown-toggle <?php echo $class_add; ?>" data-toggle="dropdown" href="#">Tickets</a>                            	<ul class="dropdown-menu">
                            		<li><a class=" <?php echo $class_add; ?>" href="<?php echo $this->directory_prime; ?>/ticket_list.php"><span class="glyphicon glyphicon glyphicon-list"></span> Ticket List</a>
                                    <li><a class=" <?php echo $class_add; ?>" href="<?php echo $this->directory_prime; ?>/ticket.php"><span class="glyphicon glyphicon-eye-open"></span> Ticket Detail View</a></li>
                                    <li><a class=" <?php echo $class_add; ?>" href="<?php echo $this->directory_prime; ?>/ticket.php?id=<?php echo DB_DEFAULTS::NEW_ID ?>"><span class="glyphicon glyphicon-plus"></span> New Ticket</a></li>
                            	</ul>
                            </li>
                            <li><a class="dropdown-toggle <?php echo $class_add; ?>" data-toggle="dropdown" href="#">System<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                  <li><a href="#" class="disabled">Status Types</a></li>
                                  <li><a href="#" class="disabled">1984</a></li>
                                </ul>
                            </li>
                        </ul>
                        <ul class="nav navbar-nav navbar-right">
                        <?php
							if($this->access_obj->get_account())
							{
						?>
                   		  <li><a href="<?php echo $this->access_obj->get_config()->get_authenticate_url(); ?>?access_action=<?php echo \dc\stoeckl\ACTION::LOGOFF; ?>"><span class="glyphicon glyphicon-log-out"></span> <?php echo $this->access_obj->name_full(); ?></a></li>
                        <?php
							}
							else
							{
						?>
                        		<li><a href="<?php echo $this->access_obj->get_config()->get_authenticate_url(); ?>"><span class="glyphicon glyphicon-log-in"></span> Guest</a></li>
                        <?php
							}
						?>                   
                        </ul>
                    </div>
                </div>
            </nav>        	
        <?php
			
			// Collect contents from cache and then clean it.
			$this->markup_nav = ob_get_contents();
			ob_end_clean();	
			
			return $this->markup_nav;
		}			
		
		public function generate_markup_footer()
		{
			// Start output caching.
			ob_start();
		?>
        	
            <div id="nav_footer" class="container well" style="width:95%; margin-top:20px;">
            	<a href="//www.uky.edu"><img src="<?php echo $this->directory_prime; ?>/media/uk_logo_1.png" alt="University of Kentucky" style="float:left; margin-top:10px; margin-bottom:5px;"></a>
                            
                <ul class="list-inline">                       
                    <li>
                    	<ul class="list-unstyled text-muted small" style="margin-bottom:10px;">
                        	<li><?php echo APPLICATION_SETTINGS::NAME; ?> Ver <?php echo APPLICATION_SETTINGS::VERSION; ?></li>   
                        	<li>Developed by: <a href="mailto:dvcask2@uky.edu"><span class="glyphicon glyphicon-envelope"></span> Damon V. Caskey</a></li>
                            <li>Copyright &copy; <?php echo date("Y"); ?>, University of Kentucky</li>
                            <li>Last update: 
                                <?php 
                                echo date(APPLICATION_SETTINGS::TIME_FORMAT, filemtime($_SERVER['SCRIPT_FILENAME']));  
                                
                                if (isset($iReqTime)) 
                                { 
                                    echo ". Generated in " .round(microtime(true) - $iReqTime,3). " seconds."; 
                                } 
                                ?></li>
                     	</ul>
                     </li>
                     <div style="float:right;">
                        <img src="<?php echo $this->directory_prime; ?>/media/php_logo_1.png" class="img-responsive pull-right" alt="Powered by objected oriented PHP." title="Powered by object oriented PHP." />
                     </div>
                </ul>
            </div><!--#nav_footer-->
        <?php
			// Collect contents from cache and then clean it.
			$this->markup_footer = ob_get_contents();
			ob_end_clean();
			
			return $this->markup_footer;
		}
	}

?>