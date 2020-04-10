<?php 

/* ==========================================================================
	Add ACF Permission to Admin
	========================================================================= */

	function add_theme_caps() {
		$role = get_role('administrator');
		$role->add_cap( 'edit_site_options' ); 
	}
    add_action( 'load-themes.php', 'add_theme_caps' );
    
    /* ==========================================================================
	ACF Options Page
	========================================================================= */

	if (function_exists('acf_add_options_page')) { 
		acf_add_options_page(array(
			'page_title'    => 'Site Options',
			'menu_title'    => 'Site Options',
			'capability'    => 'edit_site_options',
		));
		acf_add_options_page(array(
			'page_title'    => 'Footer Options',
			'menu_title'    => 'Footer Options',
			'capability'    => 'edit_site_options',
		));
		acf_add_options_page(array(
			'page_title'    => '404 Page',
			'menu_title'    => '404 Page',
			'capability'    => 'edit_site_options',
		));
		// acf_add_options_page(array(
		// 	'page_title'    => 'Add Custom Page',
		// 	'menu_title'    => 'Add Custom Page',
		// 	'capability'    => 'edit_site_options',
		// ));
	}

/* ======================================================================
	ACF Map Key
	===================================================================== */

	function add_acf_map_key( $api ){
		$api['key'] = ''; //insert API key
		return $api;
	}
	add_filter('acf/fields/google_map/api', 'add_acf_map_key');


/* ======================================================================
	ACF Json load point
	===================================================================== */

add_filter('acf/settings/load_json', 'my_acf_json_load_point');

function my_acf_json_load_point( $paths ) {
    
    // remove original path (optional)
    unset($paths[0]);
    
    
    // append path
    $paths[] = get_stylesheet_directory() . '/inc/acf-json';
    
    
    // return
    return $paths;
    
}

/* ======================================================================
	ACF Json save point
	===================================================================== */
 
add_filter('acf/settings/save_json', 'my_acf_json_save_point');
 
function my_acf_json_save_point( $path ) {
    
    // update path
    $path = get_stylesheet_directory() . '/inc/acf-json';
    
    
    // return
    return $path;
    
}
 
