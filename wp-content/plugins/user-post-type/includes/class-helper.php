<?php

class UPT_Helper
{

    function get_settings() {
        $settings = get_option( 'upt_settings' );
        $settings = empty( $settings ) ? [ 'to_sync' => [] ] : json_decode( $settings, true );
        return $settings;
    }


    function get_user_choices() {
        global $wpdb;

        $choices = [
            'ID'                => __( 'User ID', 'upt' ),
            'user_login'        => __( 'User Login', 'upt' ),
            'user_email'        => __( 'User Email', 'upt' ),
            'user_url'          => __( 'User URL', 'upt' ),
            'user_registered'   => __( 'Registration Date', 'upt' ),
            'user_status'       => __( 'User Status', 'upt' ),
            'display_name'      => __( 'Display Name', 'upt' ),
            'roles'             => __( 'Roles', 'upt' ),
        ];

        // Get usermeta keys
        $keys = $wpdb->get_col( "SELECT DISTINCT meta_key FROM {$wpdb->usermeta} ORDER BY meta_key" );

        foreach ( $keys as $key ) {
            if ( ! $this->is_excluded( $key ) ) {
                $choices["meta-$key"] = $key;
            }
        }

        return apply_filters( 'upt_user_choices', $choices );
    }


    function is_excluded( $key ) {
        $prefixes = [ 'closedpostboxes', 'edit_', 'manage', 'meta-box-order_', 'metaboxhidden_', 'screen_layout_' ];
        foreach ( $prefixes as $prefix ) {
            if ( 0 === strpos( $key, $prefix ) ) {
                return true;
            }
        }
        return false;
    }
}
