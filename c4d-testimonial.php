<?php
/*
Plugin Name: C4D Testimonial
Plugin URI: http://coffee4dev.com/
Description: Manager client comments and create slider/ grid
Author: Coffee4dev.com
Author URI: http://coffee4dev.com/
Text Domain: c4d-testimonial
Version: 2.0.0
*/

define('C4DTESTIMONIAL_PLUGIN_URI', plugins_url('', __FILE__));
define('C4DTESTIMONIAL', 'c4d-testimonial');

add_action('wp_enqueue_scripts', 'c4d_testimonial_safely_add_stylesheet_to_frontsite');
add_action('init', 'c4d_testimonial_create_posttype' );
add_action('add_meta_boxes', 'c4d_testimonial_meta_box', 10 , 3);
add_action('save_post', 'c4d_testimonial_save_data');
add_shortcode('c4d-testimonial-slider', 'c4d_testimonial_slider');
add_filter( 'plugin_row_meta', 'c4d_testimonial_plugin_row_meta', 10, 2 );

function c4d_testimonial_plugin_row_meta( $links, $file ) {
    if ( strpos( $file, basename(__FILE__) ) !== false ) {
        $new_links = array(
            'visit' => '<a href="http://coffee4dev.com">Visit Plugin Site</<a>',
            'forum' => '<a href="http://coffee4dev.com/forums/">Forum</<a>',
            'premium' => '<a href="http://coffee4dev.com">Premium Support</<a>'
        );
        
        $links = array_merge( $links, $new_links );
    }
    
    return $links;
}

$c4d_testimonial_meta_boxes = array (
	'id' => 'c4d-testimonial__options-config', 
	'title' => esc_html__('Optios', 'c4d-testimonial'), 
	'callback' => 'c4d_testimonial_html', 
	'page' => C4DTESTIMONIAL, 
	'context' => 'normal',
	'priority' => 'default',
	'fields' => array(
		array(
            'title' => esc_html__('Role', 'c4d-testimonial'),
            // 'desc' => esc_html__('Set url you want to show', 'c4d-testimonial'),
            'id' => 'c4d_testimonial_role',
            'type' => 'text',
            'default' => 'Designer'
        )
    )
);

// Our custom post type function
function c4d_testimonial_create_posttype() {
	register_post_type( C4DTESTIMONIAL,
		array(
			'labels' => array(
				'name' => __( 'C4D Testimonial' ),
				'singular_name' => __( 'C4D Testimonial' )
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => C4DTESTIMONIAL),
			'register_meta_box_cb' => 'c4d_testimonial_meta_box',
            'supports' => array('title', 'editor','thumbnail')
		)
	);
}

function c4d_testimonial_meta_box() {
	global $c4d_testimonial_meta_boxes;
	add_meta_box(
		$c4d_testimonial_meta_boxes['id'], 
		$c4d_testimonial_meta_boxes['title'], 
		$c4d_testimonial_meta_boxes['callback'], 
		$c4d_testimonial_meta_boxes['page'], 
		$c4d_testimonial_meta_boxes['context'], 
		$c4d_testimonial_meta_boxes['priority']);	
}

function c4d_testimonial_safely_add_stylesheet_to_frontsite( $page ) {
    if(!defined('C4DPLUGINMANAGER')) {
    	wp_enqueue_style( 'c4d-testimonial-frontsite-style', C4DTESTIMONIAL_PLUGIN_URI.'/assets/default.css' );
    	wp_enqueue_script( 'c4d-testimonial-frontsite-plugin-js', C4DTESTIMONIAL_PLUGIN_URI.'/assets/default.js', array( 'jquery' ), false, true ); 
    }
    wp_enqueue_style( 'owl-carousel', C4DTESTIMONIAL_PLUGIN_URI.'/libs/owl-carousel/owl.carousel.css' );
    wp_enqueue_style( 'owl-carousel-theme', C4DTESTIMONIAL_PLUGIN_URI.'/libs/owl-carousel/owl.theme.css' );
    wp_enqueue_script( 'owl-carousel', C4DTESTIMONIAL_PLUGIN_URI.'/libs/owl-carousel/owl.carousel.js', array( 'jquery' ), false, true ); 
	wp_localize_script( 'jquery', 'c4d_woo_testimonial',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

function c4d_testimonial_html() {
    global $c4d_testimonial_meta_boxes, $post;
    
    echo '<input type="hidden" name="c4d_testimonial_meta_box_nonce" value="', wp_create_nonce(plugin_basename(__FILE__)), '" />';
    
    foreach ($c4d_testimonial_meta_boxes['fields'] as $key => $value) {
        $current = get_post_meta($post->ID, $value['id'], true);
        $current = $current ? $current : $value['default'];
        echo '<div class="c4d-testimonial__row">';
        echo '<div><label>'.$value['title'].'</label></div>';
        echo '<div class="desc">'.(isset($value['desc']) ? $value['desc'] : '').'</div>';
        switch ($value['type']) {
            case 'text':
                echo '<input id="'.esc_attr($value['id']).'" type="text" name="'.esc_attr($value['id']).'" value="'.esc_attr($current).'">';
            break;
        }
        echo '</div>';
    }
}

// Save data from meta box
function c4d_testimonial_save_data($post_id) {
    global $c4d_testimonial_meta_boxes;
    if (!isset($_POST['c4d_testimonial_meta_box_nonce'])) return $post_id;
    // verify nonce
    if (!wp_verify_nonce($_POST['c4d_testimonial_meta_box_nonce'], plugin_basename(__FILE__))) {
        return $post_id;
    }
    
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } elseif (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    foreach ($c4d_testimonial_meta_boxes['fields'] as $field) {
        $old = get_post_meta($post_id, $field['id'], true);
        $new = sanitize_text_field($_POST[$field['id']]);

        if ($new && $new != $old) {
            update_post_meta($post_id, $field['id'], $new);
        } elseif ('' == $new && $old) {
            delete_post_meta($post_id, $field['id'], $old);
        }
    }
}
function c4d_testimonial_slider($params = array()) {
    $html = '';
    $args = array(
        'numberposts'       => isset($params['count']) ? esc_sql($params['count']) : 10 ,
        'post_type'         => C4DTESTIMONIAL,
        'orderby'           => 'date',
        'order'             => 'desc',
        'post_status'       => 'publish'
    );
    
    $q = new WP_Query( $args );
    ob_start();
    $template = get_template_part('c4d-testimonial/templates/default');
    if ($template && file_exists($template)) {
        require $template;
    } else {
        require dirname(__FILE__). '/templates/default.php';
    }
    $html = ob_get_contents();
    $html = do_shortcode($html);
    ob_end_clean();
    
    woocommerce_reset_loop();
    wp_reset_postdata();

    return $html;
}