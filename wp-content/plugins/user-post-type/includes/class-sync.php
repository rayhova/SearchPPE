<?php

class UPT_Sync
{

    /* (boolean) Whether to index a single post */
    public $sync_all = false;

    /* (int) Number of users to index before updating progress */
    public $chunk_size = 10;

    /* (object) User data for the currently synced item */
    public $user_data;


    function __construct() {
        if ( apply_filters( 'upt_sync_is_enabled', true ) ) {
            $this->run_hooks();
            $this->run_cron();
        }
    }


    /**
     * Event listeners
     * @since 2.8.4
     */
    function run_hooks() {
        add_action( 'user_register',    [ $this, 'run_sync' ] );
        add_action( 'profile_update',   [ $this, 'run_sync' ] );
        add_action( 'delete_user',      [ $this, 'delete_user_post' ] );
        add_action( 'upt_sync_cron',    [ $this, 'get_progress' ] );
    }


    /**
     * Cron task
     */
    function run_cron() {
        if ( ! wp_next_scheduled( 'upt_sync_cron' ) ) {
            wp_schedule_single_event( time() + 300, 'upt_sync_cron' );
        }
    }


    function run_sync( $user_id = false ) {
        global $wpdb;

        $where = '1';

        // Sync everything
        if ( false === $user_id ) {

            // Index all flag
            $this->sync_all = true;

            // Bypass the PHP timeout
            ini_set( 'max_execution_time', 0 );

            // Prevent multiple indexing processes
            $touch = (int) $this->get_transient( 'touch' );

        }
        // Sync a user
        elseif ( is_int( $user_id ) ) {
            $where .= " AND u.ID = $user_id";
        }
        else {
            return;
        }

        // Resume indexing?
        $offset = isset( $_POST['offset'] ) ? (int) $_POST['offset'] : 0;
        $attempt = isset( $_POST['retries'] ) ? (int) $_POST['retries'] : 0;

        if ( 0 < $offset ) {
            $result = get_option( 'upt_syncing' );
        }
        else {
            $meta_key = UPT()->meta_key;

            $sql = "
            SELECT u.ID as user_id, um.meta_value AS post_id
            FROM {$wpdb->users} u
            LEFT JOIN {$wpdb->usermeta} um ON um.user_id = u.ID AND um.meta_key = '$meta_key'
            WHERE $where";

            $result = $wpdb->get_results( $sql );

            // Store post IDs
            if ( $this->sync_all ) {
                update_option( 'upt_syncing', $result, false );
            }
        }

        // Count total posts
        $num_total = count( $result );

        // Get the custom "to_sync" fields
        $settings = UPT()->helper->get_settings();

        foreach ( $result as $counter => $row ) {

            // Advance until we reach the offset
            if ( $counter < $offset ) {
                continue;
            }

            // Update the progress bar
            if ( $this->sync_all ) {
                if ( 0 == ( $counter % $this->chunk_size ) ) {
                    $num_retries = (int) $this->get_transient( 'retries' );

                    // Exit if newer retries exist
                    if ( $attempt < $num_retries ) {
                        exit;
                    }

                    $transients = [
                        'num_synced'   => $counter,
                        'num_total'     => $num_total,
                        'retries'       => $attempt,
                        'touch'         => time(),
                    ];
                    update_option( 'upt_transients', json_encode( $transients ) );
                }
            }

            $user_id = (int) $row->user_id;
            $post_id = (int) $row->post_id;

            // Sync the current user?
            if ( apply_filters( 'upt_sync_skip_user', false, $user_id ) ) {
                $this->delete_user_post( $user_id );
                continue;
            }

            $this->user_data = get_userdata( $user_id );

            // Add user
            if ( empty( $post_id ) ) {
                $post_data = [
                    'post_author' => $user_id,
                    'post_date_gmt' => $this->user_data->user_registered,
                    'post_content' => '',
                    'post_title' => $this->user_data->display_name,
                    'post_status' => 'publish',
                    'post_type' => 'upt_user',
                    'post_name' => 'user-' . $user_id,
                ];

                $post_id = wp_insert_post( $post_data );

                add_user_meta( $user_id, UPT()->meta_key, $post_id );
            }
            // Update user
            else {

                // Delete postmeta for this "upt_user" post
                $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id = '$post_id'" );

                $post_data = [
                    'ID' => $post_id,
                    'post_title' => $this->user_data->display_name,
                ];

                wp_update_post( $post_data );
            }

            // Add postmeta
            foreach ( $settings['to_sync'] as $field ) {
                $data = $this->get_row_data( $user_id, $field );
                foreach ( $data as $val ) {
                    add_post_meta( $post_id, $field, $val );
                }
            }

            do_action( 'upt_sync_post', $post_id, $user_id );
        }

        // Indexing complete
        if ( $this->sync_all ) {
            update_option( 'upt_transients', '' );
            update_option( 'upt_syncing', '' );
        }
    }


    /**
     * Extra the data to be synced
     */
    function get_row_data( $user_id, $field ) {
        $output = [];

        // Usermeta
        if ( 0 === strpos( $field, 'meta-' ) ) {
            $meta_key = str_replace( 'meta-', '', $field );
            $meta_value = (array) get_user_meta( $user_id, $meta_key );

            foreach ( $meta_value as $val ) {
                $output[] = $val;
            }
        }
        // User Role
        elseif ( 'roles' == $field ) {
            if ( ! empty( $this->user_data->roles ) ) {
                foreach ( (array) $this->user_data->roles as $role ) {
                    $output[] = $role;
                }
            }
        }
        // User Table
        else {
            if ( ! empty( $this->user_data->$field ) ) {
                foreach ( (array) $this->user_data->$field as $val ) {
                    $output[] = $val;
                }
            }
        }

        return apply_filters( 'upt_sync_row_data', $output, [
            'user_id' => $user_id,
            'field' => $field
        ] );
    }


    /**
     * On user delete, remove the post item
     */
    function delete_user_post( $user_id ) {
        $post_id = (int) get_user_meta( $user_id, UPT()->meta_key, true );
        delete_user_meta( $user_id, UPT()->meta_key );
        wp_delete_post( $post_id, true );
    }


    /**
     * Get the sync completion percentage
     * @return mixed The decimal percentage, or -1
     */
    function get_progress() {
        $return = -1;
        $num_synced = (int) $this->get_transient( 'num_synced' );
        $num_total = (int) $this->get_transient( 'num_total' );
        $retries = (int) $this->get_transient( 'retries' );
        $touch = (int) $this->get_transient( 'touch' );

        if ( 0 < $num_total ) {

            // Resume a stalled indexer
            if ( 60 < ( time() - $touch ) ) {
                $post_data = [
                    'blocking'  => false,
                    'timeout'   => 0.02,
                    'body'      => [
                        'action'    => 'upt_resume_sync',
                        'offset'    => $num_synced,
                        'retries'   => $retries + 1,
                        'touch'     => $touch
                    ]
                ];
                wp_remote_post( admin_url( 'admin-ajax.php' ), $post_data );
            }

            // Calculate the percent completion
            if ( $num_synced != $num_total ) {
                $return = round( 100 * ( $num_synced / $num_total ), 2 );
            }
        }

        return $return;
    }


    /**
     * Get indexer transient variables
     */
    function get_transient( $name = false ) {
        $transients = get_option( 'upt_transients' );

        if ( ! empty( $transients ) ) {
            $transients = json_decode( $transients, true );
            if ( $name ) {
                return isset( $transients[ $name ] ) ? $transients[ $name ] : false;
            }

            return $transients;
        }

        return false;
    }
}
