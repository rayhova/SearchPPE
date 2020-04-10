<?php

class UPT_BuddyPress
{

    public $cache;


    function __construct() {
        if ( ! function_exists( 'buddypress' ) ) {
            return;
        }

        add_filter( 'facetwp_facet_sources', [ $this, 'facet_sources' ], 12 );
        add_filter( 'facetwp_indexer_row_data', [ $this, 'indexer_row_data' ], 10, 2 );
        add_filter( 'upt_user_choices', [ $this, 'user_choices' ] );
        add_filter( 'upt_sync_row_data', [ $this, 'sync_row_data' ], 10, 2 );

        $this->cache = [
            'bp_fields' => [],
            'bp_user_data' => [],
            'bp_user_id' => 0
        ];
    }


    /**
     * Get a flattened array of all BuddyPress user fields
     */
    function get_bp_fields() {
        global $wpdb;

        if ( ! empty( $this->cache['bp_fields'] ) ) {
            return $this->cache['bp_fields'];
        }

        $groups = [];
        $fields = [];

        // Get groups
        $results = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}bp_xprofile_groups" );

        foreach ( $results as $result ) {
            $groups[ $result->id ] = $result->name;
        }

        // Get fields
        $sql = "
        SELECT id, group_id, type, name
        FROM {$wpdb->prefix}bp_xprofile_fields
        WHERE parent_id = 0
        ORDER BY group_id, field_order";

        $results = $wpdb->get_results( $sql );

        foreach ( $results as $result ) {
            $fields[ $result->id ] = [
                'name' => $result->name,
                'type' => $result->type,
                'group' => $groups[ $result->group_id ]
            ];
        }

        $this->cache['bp_fields'] = $fields;

        return $fields;
    }


    /**
     * Get (and cache) a user's data
     */
    function get_bp_user_data( $user_id ) {
        global $wpdb;

        if ( $user_id === $this->cache['bp_user_id'] ) {
            return $this->cache['bp_user_data'];
        }

        $data = [];
        $sql = "SELECT field_id, value FROM {$wpdb->prefix}bp_xprofile_data WHERE user_id = '$user_id'";
        $results = $wpdb->get_results( $sql );

        foreach ( $results as $result ) {
            $values = maybe_unserialize( $result->value );
            foreach ( (array) $values as $val ) {
                $data[ $result->field_id ][] = $val;
            }
        }

        $this->cache['bp_user_data'] = $data;
        $this->cache['bp_user_id'] = $user_id;

        return $data;
    }


    /**
     * Get a field value
     */
    function get_bp_field_data( $user_id, $field_id = false ) {
        $user_data = $this->get_bp_user_data( $user_id );

        if ( false !== $field_id ) {
            return isset( $user_data[ $field_id ] ) ? $user_data[ $field_id ] : [];
        }

        return $user_data;
    }


    /**
     * Add BuddyPress choices to the FacetWP "Data Source" box
     */
    function facet_sources( $sources ) {
        $fields = $this->get_bp_fields();

        foreach ( $fields as $field_id => $field ) {
            $sources['upt']['choices'][ "upt/bp-$field_id" ] = '[' . $field['group'] . '] ' . $field['name'];
        }

        return $sources;
    }


    /**
     * Add BuddyPress choices to the UPT box
     */
    function user_choices( $choices ) {
        $fields = $this->get_bp_fields();

        foreach ( $fields as $field_id => $field ) {
            $choices[ "bp-$field_id" ] = '[' . $field['group'] . '] ' . $field['name'];
        }

        return $choices;
    }


    /**
     * Index BuddyPress data for facets
     */
    function indexer_row_data( $rows, $params ) {
        $defaults = $params['defaults'];
        $post_id = (int) $defaults['post_id'];
        $choice = $defaults['facet_source'];

        if ( 0 === strpos( $choice, 'upt/bp-' ) ) {

            // Exit stage left
            if ( 'upt_user' != get_post_type( $post_id ) ) {
                return $rows;
            }

            $user_id = UPT()->get_user_id( $post_id );
            $field_id = (int) str_replace( 'upt/bp-', '', $choice );
            $field_data = $this->get_bp_field_data( $user_id, $field_id );

            foreach ( $field_data as $val ) {
                $defaults['facet_value'] = $val;
                $defaults['facet_display_value'] = $val;
                $rows[] = $defaults;
            }
        }

        return $rows;
    }


    function sync_row_data( $output, $params ) {
        $user_id = (int) $params['user_id'];
        $field = $params['field'];

        if ( 0 === strpos( $field, 'bp-' ) ) {
            $field_id = (int) str_replace( 'bp-', '', $field );
            $field_data = $this->get_bp_field_data( $user_id, $field_id );

            foreach ( $field_data as $val ) {
                $output[] = $val;
            }
        }

        return $output;
    }
}
