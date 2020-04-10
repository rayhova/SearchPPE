<?php 
/*
* Custom Post Types
*/
function create_posttypes() {
 
    register_post_type( 'jobs',
    // CPT Options
        array(
            'labels' => array(
                'name' => __( 'Job' ),
                'singular_name' => __( 'Job' )
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'job'),
            'supports'            => array( 'title', 'editor', 'revisions', 'thumbnail'),
            //'taxonomies'          => array( 'topic' ,'keyword', 'type','audience' ),
        )
    );
    register_post_type( 'schools',
    // CPT Options
        array(
            'labels' => array(
                'name' => __( 'School' ),
                'singular_name' => __( 'School' )
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'school'),
            'supports'            => array( 'title', 'revisions'),
            //'taxonomies'          => array( 'topic' ,'keyword', 'type','audience' ),
        )
    );

   
}
// Hooking up our function to theme setup
add_action( 'init', 'create_posttypes' );

function create_category_taxonomies() {
    	register_taxonomy(
    		'position',
    		'jobs',
    		array(
    			'label' => 'Position',
    			'hierarchical' => true,
    			'show_admin_column' => true

    			
    			
    		)
    	);

    	
    	register_taxonomy(
    		'location',
    		array('jobs','schools'),
    		array(
    			'label' => 'Location',
    			'hierarchical' => true,
    			'show_admin_column' => true

    		)
    	);
       
    	
       

    }

 
add_action( 'init', 'create_category_taxonomies' );