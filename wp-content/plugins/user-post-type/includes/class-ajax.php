<?php

class UPT_Ajax
{

    function __construct() {

        // Authenticated
        if ( current_user_can( 'manage_options' ) ) {
            if ( check_ajax_referer( 'upt_nonce', 'nonce', false ) ) {
                add_action( 'wp_ajax_upt_save', [ $this, 'save_settings' ] );
                add_action( 'wp_ajax_upt_sync', [ $this, 'sync' ] );
                add_action( 'wp_ajax_upt_reset', [ $this, 'reset' ] );
                add_action( 'wp_ajax_upt_heartbeat', [ $this, 'heartbeat' ] );
            }
        }

        add_action( 'wp_ajax_nopriv_upt_resume_sync', [ $this, 'resume_sync' ] );
    }


    /**
     * Save admin settings
     */
    function save_settings() {
        $settings = stripslashes( $_POST['data'] );
        $json_test = json_decode( $settings, true );

        // Check for valid JSON
        if ( isset( $json_test['to_sync'] ) ) {
            update_option( 'upt_settings', $settings );
            $response = [
                'code' => 'success',
                'message' => __( 'Settings saved', 'upt' ),
            ];
        }
        else {
            $response = [
                'code' => 'error',
                'message' => __( 'Error: invalid JSON', 'upt' )
            ];
        }

        wp_send_json( $response );
    }


    /**
     * Sync users
     */
    function sync() {
        UPT()->sync->run_sync();
        exit;
    }


    /**
     * Resume stalled indexer
     */
    function resume_sync() {
        $touch = (int) UPT()->sync->get_transient( 'touch' );
        if ( 0 < $touch && $_POST['touch'] == $touch ) {
            UPT()->sync->run_sync();
        }
        exit;
    }


    /**
     * Keep track of indexing progress
     */
    function heartbeat() {
        echo UPT()->sync->get_progress();
        exit;
    }


    /**
     * Remove all UPT data
     */
    function reset() {
        global $wpdb;

        $meta_key = UPT()->meta_key;
        $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key = '{$meta_key}'" );
        $post_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'upt_user'" );

        foreach ( $post_ids as $post_id ) {
            wp_delete_post( $post_id, true );
        }

        $response = [
            'code' => 'success',
            'message' => __( 'Reset complete', 'upt' ),
        ];

        wp_send_json( $response );
    }
}
