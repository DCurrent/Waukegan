<?php 

interface INT_BANNER_SETTINGS
{
	const PREFIX	= '<a href="//www.uky.edu" class="no_icon">University of Kentucky</a>';
	const SUFFIX 	= 'UK Safety Begins with You!';
	const TITLE_URL	= '/apps/waukegan';
	
	const CONTAINER_CLASS	= 'banner_container'; 	// Class(s) assigned to outer container div of banner.
	const CONTAINER_EXTRA	= '';					// Extra inline attributes assigned to outer container div of banner. 
	const CONTENT_CLASS		= 'banner_content';		// Class(s) assigned to content div of banner.
	const CONTENT_EXTRA		= '';					// Extra inline attributes assigned to content div of banner. 
	const PREFIX_CLASS	 	= 'banner_prefix';		// Class(s) assigned to text above title.
	const PREFIX_EXTRA		= '';					// Extra inline attributes assigned to text above title. 
	const TITLE_CLASS		= 'banner_title';		// Class(s) assigned to banner title.
	const TITLE_EXTRA		= '';					// Extra inline attributes assigned to banner title. 
	const SUFFIX_CLASS		= 'banner_suffix';		// Class(s) assigned to text below banner title.
	const SUFFIX_EXTRA		= '';					// Inline css assigned to text below banner title. 		
}

class class_banner implements INT_BANNER_SETTINGS
{
	private
		$container_class	= NULL,
		$container_extra	= NULL,
		$content_class		= NULL,
		$content_extra		= NULL,
		$prefix 			= NULL,
		$prefix_class		= NULL,
		$prefix_extra		= NULL,
		$suffix				= NULL,
		$suffix_class		= NULL,
		$suffix_extra		= NULL,
		$title				= NULL,
		$title_class		= NULL,
		$title_extra		= NULL,		
		$markup				= NULL;
	
	public function __construct()
	{
		$this->container_class	= INT_BANNER_SETTINGS::CONTAINER_CLASS;
		$this->container_extra	= INT_BANNER_SETTINGS::CONTAINER_EXTRA;
		$this->content_class	= INT_BANNER_SETTINGS::CONTENT_CLASS;
		$this->content_extra	= INT_BANNER_SETTINGS::CONTENT_EXTRA;
		$this->prefix 			= INT_BANNER_SETTINGS::PREFIX;
		$this->prefix_class		= INT_BANNER_SETTINGS::PREFIX_CLASS;
		$this->prefix_extra		= INT_BANNER_SETTINGS::PREFIX_EXTRA;
		$this->suffix			= INT_BANNER_SETTINGS::SUFFIX;
		$this->suffix_class		= INT_BANNER_SETTINGS::SUFFIX_CLASS;
		$this->suffix_extra		= INT_BANNER_SETTINGS::SUFFIX_EXTRA;
		$this->title			= '<a href="'.INT_BANNER_SETTINGS::TITLE_URL.'"><h1>'.APPLICATION_SETTINGS::NAME;'</h1></a>';
		$this->title_class		= INT_BANNER_SETTINGS::TITLE_CLASS;
		$this->title_extra		= INT_BANNER_SETTINGS::TITLE_EXTRA;
	}
	
	public function generate_banner()
	{
	
		// Start caching page contents.
		ob_start();
		
		?>
        	<!--Markup generation: <?php echo __CLASS__.'->'.__FUNCTION__ . ' ('.__FILE__.'), Last update: ' .date(APPLICATION_SETTINGS::TIME_FORMAT,filemtime(__FILE__)); ?>-->
            
            <div id="banner_container" class="<?php echo $this->container_class; ?>" <?php echo $this->container_extra; ?>>	
                <div id="banner_content" class="<?php echo $this->content_class; ?>" <?php echo $this->content_extra; ?>>
                    <div id="banner_prefix" class="<?php echo $this->prefix_class; ?>" <?php echo $this->prefix_extra; ?>>
                    	<?php echo $this->prefix; ?>
                    </div><!--#banner_prefix-->
                    <div id="banner_title" class="<?php echo $this->title_class; ?>" <?php echo $this->title_extra; ?>>
                    	<?php echo $this->title; ?>
                    </div>
                    <div id="banner_suffix" class="<?php echo $this->suffix_class; ?>" <?php echo $this->suffix_extra; ?>>
                        <?php echo $this->suffix; ?>
                    </div><!--#banner_suffix-->
                </div><!--#banner_content-->
            </div><!--#banner_container-->
            
            <!--/<?php echo __CLASS__.'->'.__FUNCTION__; ?>-->
		<?php
		
		// Collect contents from cache and then clean it.
		$this->markup = ob_get_contents();
		ob_end_clean();	
		
		return $this->markup;
	}
	
	//Accessors
	public function get_container_class()
	{
		return $this->container_class;
	}
	
	public function get_content_class()
	{
		return $this->content_class;
	}
	
	public function get_content_extra()
	{
		return $this->content_extra;
	}
	
	public function get_markup()
	{
		return $this->markup;
	}
	
	public function get_prefix()
	{
		return $this->prefix;
	}
	
	public function get_prefix_class()
	{
		return $this->prefix_class;
	}
	
	public function get_prefix_extra()
	{
		return $this->prefix_extra;
	}
	
	public function get_suffix()
	{
		return $this->suffix;
	}
	
	public function get_suffix_class()
	{
		return $this->suffix_class;
	}
	
	public function get_suffix_extra()
	{
		return $this->suffix_extra;
	}
	
	public function get_title()
	{
		return $this->title;
	}
	
	public function get_title_class()
	{
		return $this->title_class;
	}
	
	public function get_title_extra()
	{
		return $this->title_extra;
	}
		
	// Mutators
	public function set_container_class($value)
	{
		$this->container_class = $value;
	}
	
	public function set_content_class($value)
	{
		$this->content_class = $value;
	}
	
	public function set_content_extra($value)
	{
		$this->content_extra = $value;
	}
	
	public function set_prefix($value)
	{
		$this->prefix = $value;
	}
	
	public function set_prefix_class($value)
	{
		$this->prefix_class = $value;
	}
	
	public function set_prefix_extra($value)
	{
		$this->prefix_extra = $value;
	}
	
	public function set_suffix($value)
	{
		$this->suffix = $value;
	}
	
	public function set_suffix_class($value)
	{
		$this->suffix_class = $value;
	}
	
	public function set_suffix_extra($value)
	{
		$this->suffix_extra = $value;
	}
	
	public function set_title($value)
	{
		$this->title = $value;
	}
	
	public function set_title_class($value)
	{
		$this->title_class = $value;
	}
	
	public function set_title_extra($value)
	{
		$this->title_extra = $value;
	}
}
?>