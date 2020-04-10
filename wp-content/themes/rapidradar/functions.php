<?php
/**
 * UnderStrap functions and definitions
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$understrap_includes = array(
	'/theme-settings.php',                  // Initialize theme default settings.
	'/setup.php',                           // Theme setup and custom theme supports.
	'/widgets.php',                         // Register widget area.
	'/enqueue.php',                         // Enqueue scripts and styles.
	'/template-tags.php',                   // Custom template tags for this theme.
	'/pagination.php',                      // Custom pagination for this theme.
	'/hooks.php',                           // Custom hooks.
	'/extras.php',                          // Custom functions that act independently of the theme templates.
	'/customizer.php',                      // Customizer additions.
	'/custom-comments.php',                 // Custom Comments file.
	'/jetpack.php',                         // Load Jetpack compatibility file.
	'/class-wp-bootstrap-navwalker.php',    // Load custom WordPress nav walker. Trying to get deeper navigation? Check out: https://github.com/understrap/understrap/issues/567
	'/woocommerce.php',                     // Load WooCommerce functions.
	'/editor.php',                          // Load Editor functions.
	'/deprecated.php',                      // Load deprecated functions.
	'/advance-custom-fields.php', 			// acf functions
	'/custom-post-type.php', 				// CPT functions
	'/shortcodes.php', 						// shortcodes functions
	'/plugin-include.php', 					// Default Plugins functions
	

);

foreach ( $understrap_includes as $file ) {
	require_once get_template_directory() . '/inc' . $file;
}

if( class_exists('acf') ) {
require get_template_directory()  . '/inc/acf-global.php';
}

function my_acf_user_form_func( $atts ) {
 
  $a = shortcode_atts( array(
    'field_group' => ''
  ), $atts );
 
  $uid = get_current_user_id();
  
  if ( ! empty ( $a['field_group'] ) && ! empty ( $uid ) ) {
    $options = array(
      'post_id' => 'user_'.$uid,
      'field_groups' => array( intval( $a['field_group'] ) ),
      'return' => add_query_arg( 'updated', 'true', get_permalink() )
    );
    
    ob_start();
    
    acf_form( $options );
    $form = ob_get_contents();
    
    ob_end_clean();
  }
  
    return $form;
}
 
add_shortcode( 'my_acf_user_form', 'my_acf_user_form_func' );

//adding AFC form head
function add_acf_form_head(){
    global $post;
    
  if ( !empty($post) && has_shortcode( $post->post_content, 'my_acf_user_form' ) ) {
        acf_form_head();
    }
}
add_action( 'wp_head', 'add_acf_form_head', 7 );

function sp_loginForm(){
	 
            global $user_login;

            // In case of a login error.
            if ( isset( $_GET['login'] ) && $_GET['login'] == 'failed' ) : ?>
    	            <div class="tbc_error">
    		            <strong>ERROR:</strong> Invalid username and/or password.
    	            </div>
            <?php 
                endif;

             if ( is_user_logged_in()  ) : ?>

                
               

            <?php 
                // If user is not logged in.
                else: 
                
                    // Login form arguments.
                    $args = array(
                        'echo'           => true,
                        'redirect'       => home_url( '/search-suppliers/' ), 
                        'form_id'        => 'homeloginform',
                        'label_username' => __( 'Username' ),
                        'label_password' => __( 'Password' ),
                        'label_remember' => __( 'Remember Me' ),
                        'label_log_in'   => __( 'Log In' ),
                        'id_username'    => 'user_login',
                        'id_password'    => 'user_pass',
                        'id_remember'    => 'rememberme',
                        'id_submit'      => 'wp-submit',
                        'remember'       => false,
                        'value_username' => NULL,
                        'value_remember' => false
                    ); 
                    
                    // Calling the login form.
                    wp_login_form( $args ); ?>
                    <p id="nav">
						<a href="https://theblackco-op.com/wp-login.php?action=register">Register</a> | 					<a href="https://theblackco-op.com/wp-login.php?action=lostpassword">Lost your password?</a>
					</p>

                   

                <?php endif;
                    
       
}

add_action('after_setup_theme', 'remove_admin_bar');
 
function remove_admin_bar() {
if (!current_user_can('administrator') && !is_admin()) {
  show_admin_bar(false);
}
}

add_filter( 'gform_merge_tag_filter', function ( $value, $merge_tag, $modifier, $field, $raw_value ) {
if ( $merge_tag != 'all_fields' && $modifier == 'gwp_lowercase' ) {
$value = strtolower( $value );
}
 
return $value;
}, 10, 5 );


function my_facetwp_index_row( $params, $class ) {
    $name = $params['facet_name'];
    if ( 'products_2' == $name || 'products_3' == $name ) {
        $params['facet_name'] = 'products';
    }
    return $params;
}

add_filter( 'facetwp_index_row', 'my_facetwp_index_row', 10, 2 );