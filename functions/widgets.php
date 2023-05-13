<?php
class ndok_widget extends WP_Widget {
	
	function __construct() {
        parent::__construct(false, $name = 'Custom Widget');	
    }
 
    function widget($args, $instance) {	
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        $show = $instance['show'];
		show_widget($title, $show);
    }
 
    function update($new_instance, $old_instance) {		
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['show'] = strip_tags($new_instance['show']);
        return $instance;
    }
 
    function form($instance) {	
 
        $title = isset($instance['title']) ? esc_attr($instance['title']) : 'Title';
        $show = isset($instance['show']) ? esc_attr($instance['show']) : 'search';
        ?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('show'); ?>"><?php _e('Show'); ?></label> 
			<select class="widefat" id="<?php echo $this->get_field_id('show'); ?>" name="<?php echo $this->get_field_name('show'); ?>" >
				<option value="services" <?php if($show=='services'){?> selected <?php } ?>>Services</option>
				<option value="testimoni" <?php if($show=='testimoni'){?> selected <?php } ?>>Testimonial</option>
			</select>
		</p>
        <?php 
    }
 
}
//add_action('widgets_init', create_function('', 'return register_widget("ndok_widget");'));
function ndok_widget ()
{
    return register_widget('ndok_widget');
}
add_action ('widgets_init', 'ndok_widget');

function show_widget($title=null, $show){
	$html = null; 
 	if($show=='services'){
 		$html .= '<div class="aside-service">';
 		if($title){
			$html .= '<h5>'.$title.'</h5>';
		}
 		$currentID = get_the_ID();
 		$arg = array(
 			'post_type' => 'service',
 			'posts_per_page' => -1,
 			'orderby' => 'title',
 			'order' => 'ASC'
 		);
		$posts = get_posts($arg);
		if($posts){
			$html .= '<ul>';
			foreach($posts as $p){
				$current = ($p->ID == $currentID) ? 'class="current"' : null;
				$html .= '<li><a '.$current.' href="'.get_permalink($p->ID).'">'.$p->post_title.'</a></li>';
			}
			$html .= '</ul>';
		}
		$html .= '</div>';
 	} elseif($show=='testimoni'){
 		$html .= '<div class="aside-testimonials">';
 		$testi = get_widget_data_for('Testimonial'); $testi = $testi[0];
 		$html .= '<h2>'.$testi->title.'</h2>';
		$html .=  $testi->text;
		$html .= '<img src="'.get_template_directory_uri().'/assets/img/family.png"/>';
		$html .= '</div>';
 	}
 	echo $html;
}
?>