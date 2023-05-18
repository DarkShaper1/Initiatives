<?php
/**
 * ShMapper
 *
 * @package teplitsa
 */

class ShmLocationNavigatorWidget extends WP_Widget {
	/*  Constructor
	/* ------------------------------------ */
	function __construct()
	{
		parent::__construct( false, __("Shmapper Locations", SHMAPPER), array('description' => 'Locations accordeon', 'classname' => 'widget_location_navigator') );;	
		add_action( 'init',				array($this, 'redirect_login_page'));
	}
	function redirect_login_page() 
	{  
		$login_page  	= home_url( '/' );  
		$page_viewed 	= basename($_SERVER['REQUEST_URI']);  
		$this->name 	= __('Ermak Locations', SHMAPPER);
		$this->widget_options['description'] 	= __('Player Cabinet', SHMAPPER);
	}
	/*  Widget
	/* ------------------------------------ */
	public function widget($args, $instance) 
	{			
		extract( $args );
		$instance['title'] ? NULL : $instance['title'] = '';
		$instance['map_id'] ? NULL : $instance['map_id'] = '';
		$title = apply_filters('widget_title',$instance['title']);
		$output = $before_widget."\n";
		if($title)
			$output .= $before_title.$title.$after_title;
		else
			$output .= $before_title. $instance['title'].$after_title;
		$map = ShmMap::get_instance($instance['map_id']);			
		$output .= $map->draw([ "height" => $instance['height'], "id" => $map->id ]);
		$output .= $after_widget."\n";
		echo $output;
	}
	
	/*  Widget update
	/* ------------------------------------ */
	public function update($new,$old) 
	{
		$instance = $old;
		$instance['title'] = strip_tags($new['title']);
		$instance['map_id'] = strip_tags($new['map_id']);
		$instance['height'] = strip_tags($new['height']);
		return $instance;
	}
	
	/*  Widget form
	/* ------------------------------------ */
	public function form($instance) 
	{
		// Default widget settings
		$defaults = array(
			'title' 			=> '',
			'map_id' 			=> '',
			'height' 			=> 250,
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<div class="shm-row">
			<div class="shm-12">
				<p>
					<label for="<?php echo $instance['title']; ?>"><?php _e("Tite"); ?></label>
					<input class="widefat" id="<?php $instance['title']; ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
				</p>				
				<p>
					<label for="<?php echo $instance['map_id']; ?>"><?php _e("Map", SHMAPPER); ?></label>
					<?php
						echo ShmMap::wp_dropdown([
							"class" 	=> "shm-form",
							"name"		=> $this->get_field_name('map_id'),
							"selected"	=> esc_attr($instance["map_id"]),
							"id"		=> $instance['map_id'],
							"posts"		=> ShmMap::get_all(),
						]);
					?>
				</p>					
				<p>
					<label for="<?php echo $instance['height']; ?>"><?php _e("Height", SHMAPPER); ?></label>
					<input type='number' class="shm-form" id="<?php $instance['height']; ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo esc_attr($instance["height"]); ?>" />
				</p>		
			</div>
		</div>
		<?php
	}

}

/*  Register widget
/* ------------------------------------ */
function register_widget_shmloc_navi() { 
	register_widget( 'ShmLocationNavigatorWidget' );
}
add_action( 'widgets_init', 'register_widget_shmloc_navi' );
