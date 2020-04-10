<?php

class UPT_FacetWP
{

    function __construct() {
        add_filter( 'facetwp_facet_sources', [ $this, 'facet_sources' ] );
        add_filter( 'facetwp_indexer_row_data', [ $this, 'indexer_row_data' ], 10, 2 );
        add_filter( 'facetwp_builder_item_value', [ $this, 'builder_item_value' ], 10, 2 );
    }


    /**
     * Register FacetWP data sources
     */
    function facet_sources( $sources ) {
        $prefixed = [];
        $choices = UPT()->helper->get_user_choices();

        foreach ( $choices as $key => $val ) {
            $prefixed[ "upt/$key" ] = $val;
        }

        $sources['upt'] = [
            'label' => 'User Fields',
            'choices' => $prefixed,
            'weight' => 10
        ];

        return $sources;
    }


    /**
     * Index FacetWP data
     */
    function indexer_row_data( $rows, $params ) {
        $defaults = $params['defaults'];
        $post_id = (int) $defaults['post_id'];

        if ( 0 === strpos( $defaults['facet_source'], 'upt/' ) ) {

            // Exit stage left
            if ( 'upt_user' != get_post_type( $post_id ) ) {
                return $rows;
            }

            $user_id = UPT()->get_user_id( $post_id );
            $choice = str_replace( 'upt/', '', $defaults['facet_source'] );

            // Usermeta
            if ( 0 === strpos( $choice, 'meta-' ) ) {
                $meta_key = str_replace( 'meta-', '', $choice );
                $meta_value = get_user_meta( $user_id, $meta_key, true );

                if ( ! empty( $meta_value ) ) {
                    $defaults['facet_value'] = $meta_value;
                    $defaults['facet_display_value'] = $meta_value;
                    $rows[] = $defaults;
                }
            }
            // User Role
            elseif ( 'roles' == $choice ) {
                $user_data = get_userdata( $user_id );

                if ( ! empty( $user_data->roles ) ) {
                    foreach ( $user_data->roles as $role ) {
                        $defaults['facet_value'] = $role;
                        $defaults['facet_display_value'] = $GLOBALS['wp_roles']->roles[ $role ]['name'];
                        $rows[] = $defaults;
                    }
                }
            }
            // User Table
            else {
                $user_data = get_userdata( $user_id );

                if ( ! empty( $user_data->$choice ) ) {
                    $defaults['facet_value'] = $user_data->$choice;
                    $defaults['facet_display_value'] = $user_data->$choice;
                    $rows[] = $defaults;
                }
            }
        }

        return $rows;
    }


    /**
     * Support FacetWP's built-in Layout Builder
     */
    function builder_item_value( $value, $item ) {
        $source = $item['source'];

        if ( 0 === strpos( $source, 'upt/' ) ) {
            $params = [
                'defaults' => [
                    'post_id' => $GLOBALS['post']->ID,
                    'facet_source' => $source,
                    'facet_display_value' => $source
                ]
            ];
            $rows = $this->indexer_row_data( [], $params );
            $values = wp_list_pluck( $rows, 'facet_display_value' );
            $value = implode( ', ', $values );
        }

        return $value;
    }
}
