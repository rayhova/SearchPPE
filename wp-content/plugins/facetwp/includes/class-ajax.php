<?php

class FacetWP_Ajax
{

    function __construct() {

        // Authenticated
        if ( current_user_can( 'manage_options' ) ) {
            if ( check_ajax_referer( 'fwp_admin_nonce', 'nonce', false ) ) {
                add_action( 'wp_ajax_facetwp_save', [ $this, 'save_settings' ] );
                add_action( 'wp_ajax_facetwp_rebuild_index', [ $this, 'rebuild_index' ] );
                add_action( 'wp_ajax_facetwp_get_info', [ $this, 'get_info' ] );
                add_action( 'wp_ajax_facetwp_get_query_args', [ $this, 'get_query_args' ] );
                add_action( 'wp_ajax_facetwp_heartbeat', [ $this, 'heartbeat' ] );
                add_action( 'wp_ajax_facetwp_license', [ $this, 'license' ] );
                add_action( 'wp_ajax_facetwp_backup', [ $this, 'backup' ] );
            }
        }

        // Non-authenticated
        add_action( 'facetwp_refresh', [ $this, 'refresh' ] );
        add_action( 'wp_ajax_nopriv_facetwp_resume_index', [ $this, 'resume_index' ] );

        // Backwards compatibility
        $this->url_vars = FWP()->request->url_vars;
        $this->is_preload = FWP()->request->is_preload;
    }


    /**
     * Save admin settings
     */
    function save_settings() {
        $settings = stripslashes( $_POST['data'] );
        $json_test = json_decode( $settings, true );

        // Check for valid JSON
        if ( isset( $json_test['settings'] ) ) {
            update_option( 'facetwp_settings', $settings, 'no' );

            $response = [
                'code' => 'success',
                'message' => __( 'Settings saved', 'fwp' ),
                'reindex' => FWP()->diff->is_reindex_needed()
            ];
        }
        else {
            $response = [
                'code' => 'error',
                'message' => __( 'Error: invalid JSON', 'fwp' )
            ];
        }

        wp_send_json( $response );
    }


    /**
     * Rebuild the index table
     */
    function rebuild_index() {
        update_option( 'facetwp_indexing_cancelled', 'no', 'no' );
        FWP()->indexer->index();
        exit;
    }


    /**
     * Resume stalled indexer
     */
    function resume_index() {
        $touch = (int) FWP()->indexer->get_transient( 'touch' );
        if ( 0 < $touch && $_POST['touch'] == $touch ) {
            FWP()->indexer->index();
        }
        exit;
    }


    function get_info() {
        $type = $_POST['type'];

        if ( 'post_types' == $type ) {
            $post_types = get_post_types( [ 'exclude_from_search' => false, '_builtin' => false ] );
            $post_types = [ 'post', 'page' ] + $post_types;
            sort( $post_types );

            $response = [
                'code' => 'success',
                'message' => implode( ', ', $post_types )
            ];
        }
        elseif ( 'indexer_stats' == $type ) {
            global $wpdb;

            $row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}facetwp_index" );
            $facet_count = $wpdb->get_var( "SELECT COUNT(DISTINCT facet_name) FROM {$wpdb->prefix}facetwp_index" );
            $last_indexed = get_option( 'facetwp_last_indexed' );
            $last_indexed = $last_indexed ? human_time_diff( $last_indexed ) . ' ago' : 'N/A';

            $response = [
                'code' => 'success',
                'message' => "rows: $row_count, facets: $facet_count, last re-index: $last_indexed"
            ];
        }
        elseif ( 'cancel_reindex' == $type ) {
            update_option( 'facetwp_indexing_cancelled', 'yes' );

            $response = [
                'code' => 'success',
                'message' => 'Indexing cancelled'
            ];
        }
        elseif ( 'purge_index_table' == $type ) {
            global $wpdb;

            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}facetwp_index" );
            delete_option( 'facetwp_version' );

            $response = [
                'code' => 'success',
                'message' => __( 'Done, please re-index', 'fwp' )
            ];
        }

        wp_send_json( $response );
    }


    /**
     * Return query arguments based on a Query Builder object
     */
    function get_query_args() {
        $query_obj = $_POST['query_obj'];

        if ( is_array( $query_obj ) ) {
            $query_args = FWP()->builder->parse_query_obj( $query_obj );
        }

        wp_send_json( $query_args );
    }


    /**
     * The AJAX facet refresh handler
     */
    function refresh() {

        global $wpdb;

        $params = FWP()->request->process_post_data();
        $output = FWP()->facet->render( $params );
        $data = stripslashes_deep( $_POST['data'] );

        // Ignore invalid UTF-8 characters in PHP 7.2+
        if ( version_compare( phpversion(), '7.2', '<' ) ) {
            $output = json_encode( $output );
        }
        else {
            $output = json_encode( $output, JSON_INVALID_UTF8_IGNORE );
        }

        echo apply_filters( 'facetwp_ajax_response', $output, [
            'data' => $data
        ] );

        exit;
    }


    /**
     * Keep track of indexing progress
     */
    function heartbeat() {
        $output = [
            'pct' => FWP()->indexer->get_progress()
        ];

        if ( -1 == $output['pct'] ) {
            $output['rows'] = FWP()->helper->get_row_counts();
        }

        wp_send_json( $output );
    }


    /**
     * Import / export functionality
     */
    function backup() {
        $action_type = $_POST['action_type'];
        $output = [];

        if ( 'export' == $action_type ) {
            $items = $_POST['items'];

            if ( ! empty( $items ) ) {
                foreach ( $items as $item ) {
                    if ( 'facet' == substr( $item, 0, 5 ) ) {
                        $item_name = substr( $item, 6 );
                        $output['facets'][] = FWP()->helper->get_facet_by_name( $item_name );
                    }
                    elseif ( 'template' == substr( $item, 0, 8 ) ) {
                        $item_name = substr( $item, 9 );
                        $output['templates'][] = FWP()->helper->get_template_by_name( $item_name );
                    }
                }
            }
            echo json_encode( $output );
        }
        elseif ( 'import' == $action_type ) {
            $settings = FWP()->helper->settings;
            $import_code = json_decode( stripslashes( $_POST['import_code'] ), true );
            $overwrite = (int) $_POST['overwrite'];

            if ( empty( $import_code ) || ! is_array( $import_code ) ) {
                _e( 'Nothing to import', 'fwp' );
                exit;
            }

            $status = [
                'imported' => [],
                'skipped' => [],
            ];

            foreach ( $import_code as $object_type => $object_items ) {
                foreach ( $object_items as $object_item ) {
                    $is_match = false;
                    foreach ( $settings[$object_type] as $key => $settings_item ) {
                        if ( $object_item['name'] == $settings_item['name'] ) {
                            if ( $overwrite ) {
                                $settings[$object_type][$key] = $object_item;
                                $status['imported'][] = $object_item['label'];
                            }
                            else {
                                $status['skipped'][] = $object_item['label'];
                            }
                            $is_match = true;
                            break;
                        }
                    }

                    if ( ! $is_match ) {
                        $settings[$object_type][] = $object_item;
                        $status['imported'][] = $object_item['label'];
                    }
                }
            }

            update_option( 'facetwp_settings', json_encode( $settings ) );

            if ( ! empty( $status['imported'] ) ) {
                echo ' [<strong>' . __( 'Imported', 'fwp' ) . '</strong>] ' . implode( ', ', $status['imported'] );
            }
            if ( ! empty( $status['skipped'] ) ) {
                echo ' [<strong>' . __( 'Skipped', 'fwp' ) . '</strong>] ' . implode( ', ', $status['skipped'] );
            }
        }

        exit;
    }


    /**
     * License activation
     */
    function license() {
        $license = $_POST['license'];

        $request = wp_remote_post( 'http://api.facetwp.com', [
            'body' => [
                'action'        => 'activate',
                'slug'          => 'facetwp',
                'license'       => $license,
                'host'          => FWP()->helper->get_http_host(),
            ]
        ] );

        if ( ! is_wp_error( $request ) || 200 == wp_remote_retrieve_response_code( $request ) ) {
            update_option( 'facetwp_license', $license );
            update_option( 'facetwp_activation', $request['body'] );
            update_option( 'facetwp_updater_last_checked', 0 );
            echo $request['body'];
        }
        else {
            echo json_encode( [
                'status'    => 'error',
                'message'   => __( 'Error', 'fwp' ) . ': ' . $request->get_error_message(),
            ] );
        }

        exit;
    }
}
