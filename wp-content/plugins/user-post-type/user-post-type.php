<?php
/*
Plugin Name: User Post Type
Description: Connect users to a post type
Version: 0.7.2
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/user-post-type
*/

defined( 'ABSPATH' ) or exit;

class User_Post_Type
{

    public $meta_key = '_upt_post_id';
    public $lookup_cache = [];
    private static $instance;


    function __construct() {

        // setup variables
        define( 'UPT_VERSION', '0.7.2' );
        define( 'UPT_DIR', dirname( __FILE__ ) );
        define( 'UPT_URL', plugins_url( '', __FILE__ ) );
        define( 'UPT_BASENAME', plugin_basename( __FILE__ ) );

        // get the gears turning
        include( UPT_DIR . '/includes/class-init.php' );
    }


    /**
     * Get a usermeta value
     */
    function get_user_id( $post_id = false ) {
        global $post, $wpdb;

        $post_id = empty( $post_id ) ? $post->ID : (int) $post_id;

        if ( isset( $this->lookup_cache[ $post_id ] ) ) {
            return $this->lookup_cache[ $post_id ];
        }

        $user_id = (int) $wpdb->get_var( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '{$this->meta_key}' AND meta_value = '$post_id' LIMIT 1" );

        $this->lookup_cache[ $post_id ] = $user_id;

        return $user_id;
    }


    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}


function UPT() {
    return User_Post_Type::instance();
}


UPT();
