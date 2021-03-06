<?php

class FacetWP_Facet_fSelect extends FacetWP_Facet
{

    function __construct() {
        $this->label = __( 'fSelect', 'fwp' );
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $from_clause = $wpdb->prefix . 'facetwp_index f';
        $where_clause = $params['where_clause'];

        // Preserve options for single-select or when in "OR" mode
        $is_single = FWP()->helper->facet_is( $facet, 'multiple', 'no' );
        $using_or = FWP()->helper->facet_is( $facet, 'operator', 'or' );

        if ( $is_single || $using_or ) {
            $where_clause = $this->get_where_clause( $facet );
        }

        // Orderby
        $orderby = $this->get_orderby( $facet );

        // Limit
        $limit = ctype_digit( $facet['count'] ) ? $facet['count'] : 10;

        $orderby = apply_filters( 'facetwp_facet_orderby', $orderby, $facet );
        $from_clause = apply_filters( 'facetwp_facet_from', $from_clause, $facet );
        $where_clause = apply_filters( 'facetwp_facet_where', $where_clause, $facet );

        $sql = "
        SELECT f.facet_value, f.facet_display_value, f.term_id, f.parent_id, f.depth, COUNT(DISTINCT f.post_id) AS counter
        FROM $from_clause
        WHERE f.facet_name = '{$facet['name']}' $where_clause
        GROUP BY f.facet_value
        ORDER BY $orderby
        LIMIT $limit";

        $output = $wpdb->get_results( $sql, ARRAY_A );

        // Show "ghost" facet choices
        // For performance gains, only run if facets are in use
        $show_ghosts = FWP()->helper->facet_is( $facet, 'ghosts', 'yes' );
        $is_filtered = FWP()->unfiltered_post_ids !== FWP()->facet->query_args['post__in'];

        if ( $show_ghosts && $is_filtered && ! empty( FWP()->unfiltered_post_ids ) ) {
            $raw_post_ids = implode( ',', FWP()->unfiltered_post_ids );

            $sql = "
            SELECT f.facet_value, f.facet_display_value, f.term_id, f.parent_id, f.depth, 0 AS counter
            FROM $from_clause
            WHERE f.facet_name = '{$facet['name']}' AND post_id IN ($raw_post_ids)
            GROUP BY f.facet_value
            ORDER BY $orderby
            LIMIT $limit";

            $ghost_output = $wpdb->get_results( $sql, ARRAY_A );

            // Keep the facet placement intact
            if ( FWP()->helper->facet_is( $facet, 'preserve_ghosts', 'yes' ) ) {
                $tmp = [];
                foreach ( $ghost_output as $row ) {
                    $tmp[ $row['facet_value'] . ' ' ] = $row;
                }

                foreach ( $output as $row ) {
                    $tmp[ $row['facet_value'] . ' ' ] = $row;
                }

                $output = $tmp;
            }
            else {
                // Make the array key equal to the facet_value (for easy lookup)
                $tmp = [];
                foreach ( $output as $row ) {
                    $tmp[ $row['facet_value'] . ' ' ] = $row; // Force a string array key
                }
                $output = $tmp;

                foreach ( $ghost_output as $row ) {
                    $facet_value = $row['facet_value'];
                    if ( ! isset( $output[ "$facet_value " ] ) ) {
                        $output[ "$facet_value " ] = $row;
                    }
                }
            }

            $output = array_splice( $output, 0, $limit );
            $output = array_values( $output );
        }

        return $output;
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $facet = $params['facet'];
        $values = (array) $params['values'];
        $selected_values = (array) $params['selected_values'];

        if ( FWP()->helper->facet_is( $facet, 'hierarchical', 'yes' ) ) {
            $values = FWP()->helper->sort_taxonomy_values( $params['values'], $facet['orderby'] );
        }

        $multiple = FWP()->helper->facet_is( $facet, 'multiple', 'yes' ) ? ' multiple="multiple"' : '';
        $label_any = empty( $facet['label_any'] ) ? __( 'Any', 'fwp-front' ) : $facet['label_any'];
        $label_any = facetwp_i18n( $label_any );

        $output .= '<select class="facetwp-dropdown"' . $multiple . '>';
        $output .= '<option value="">' . esc_html( $label_any ) . '</option>';

        foreach ( $values as $result ) {
            $selected = in_array( $result['facet_value'], $selected_values ) ? ' selected' : '';
            $selected .= ( 0 == $result['counter'] && '' == $selected ) ? ' disabled' : '';

            // Determine whether to show counts
            $display_value = esc_html( $result['facet_display_value'] );
            $show_counts = apply_filters( 'facetwp_facet_dropdown_show_counts', true, [ 'facet' => $facet ] );

            if ( $show_counts ) {
                $display_value .= ' {{(' . $result['counter'] . ')}}';
            }

            $output .= '<option value="' . esc_attr( $result['facet_value'] ) . '" class="d' . $result['depth'] . '"' . $selected . '>' . $display_value . '</option>';
        }

        $output .= '</select>';
        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $output = [];
        $facet = $params['facet'];
        $selected_values = $params['selected_values'];

        $sql = $wpdb->prepare( "SELECT DISTINCT post_id
            FROM {$wpdb->prefix}facetwp_index
            WHERE facet_name = %s",
            $facet['name']
        );

        // Match ALL values
        if ( 'and' == $facet['operator'] ) {
            foreach ( $selected_values as $key => $value ) {
                $results = facetwp_sql( $sql . " AND facet_value IN ('$value')", $facet );
                $output = ( $key > 0 ) ? array_intersect( $output, $results ) : $results;

                if ( empty( $output ) ) {
                    break;
                }
            }
        }
        // Match ANY value
        else {
            $selected_values = implode( "','", $selected_values );
            $output = facetwp_sql( $sql . " AND facet_value IN ('$selected_values')", $facet );
        }

        return $output;
    }


    /**
     * (Front-end) Attach settings to the AJAX response
     */
    function settings_js( $params ) {
        $facet = $params['facet'];

        $label_any = empty( $facet['label_any'] ) ? __( 'Any', 'fwp-front' ) : $facet['label_any'];
        $label_any = facetwp_i18n( $label_any );

        return [
            'placeholder'       => $label_any,
            'overflowText'      => __( '{n} selected', 'fwp-front' ),
            'searchText'        => __( 'Search', 'fwp-front' ),
            'noResultsText'     => __( 'No results found', 'fwp-front' ),
            'operator'          => $facet['operator']
        ];
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
        FWP()->display->assets['fSelect.css'] = FACETWP_URL . '/assets/vendor/fSelect/fSelect.css';
        FWP()->display->assets['fSelect.js'] = FACETWP_URL . '/assets/vendor/fSelect/fSelect.js';
    }


    /**
     * Output admin settings HTML
     */
    function settings_html() {
?>
        <div class="facetwp-row">
            <div><?php _e( 'Default label', 'fwp' ); ?>:</div>
            <div>
                <input type="text" class="facet-label-any" value="<?php _e( 'Any', 'fwp' ); ?>" />
            </div>
        </div>
        <div class="facetwp-row" v-show="facet.source.substr(0, 3) == 'tax'">
            <div>
                <?php _e('Parent term', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content">
                        To show only child terms, enter the parent <a href="https://facetwp.com/how-to-find-a-wordpress-terms-id/" target="_blank">term ID</a>.
                        Otherwise, leave blank.
                    </div>
                </div>
            </div>
            <div>
                <input type="text" class="facet-parent-term" />
            </div>
        </div>
        <div class="facetwp-row">
            <div>
                <?php _e( 'Multi-select', 'fwp' ); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Allow multiple selections?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <label class="facetwp-switch">
                    <input type="checkbox" class="facet-multiple" true-value="yes" false-value="no" />
                    <span class="facetwp-slider"></span>
                </label>
            </div>
        </div>
        <div class="facetwp-row">
            <div>
                <?php _e('Hierarchical', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Is this a hierarchical taxonomy?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <label class="facetwp-switch">
                    <input type="checkbox" class="facet-hierarchical" true-value="yes" false-value="no" />
                    <span class="facetwp-slider"></span>
                </label>
            </div>
        </div>
        <div class="facetwp-row">
            <div>
                <?php _e('Show ghosts', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Show choices that would return zero results?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <label class="facetwp-switch">
                    <input type="checkbox" class="facet-ghosts" true-value="yes" false-value="no" />
                    <span class="facetwp-slider"></span>
                </label>
            </div>
        </div>
        <div class="facetwp-row" v-show="facet.ghosts == 'yes'">
            <div>
                <?php _e('Preserve ghost order', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'Keep ghost choices in the same order?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <label class="facetwp-switch">
                    <input type="checkbox" class="facet-preserve-ghosts" true-value="yes" false-value="no" />
                    <span class="facetwp-slider"></span>
                </label>
            </div>
        </div>
        <div class="facetwp-row" v-show="facet.multiple == 'yes'">
            <div>
                <?php _e('Behavior', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'How should multiple selections affect the results?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <select class="facet-operator">
                    <option value="and"><?php _e( 'Narrow the result set', 'fwp' ); ?></option>
                    <option value="or"><?php _e( 'Widen the result set', 'fwp' ); ?></option>
                </select>
            </div>
        </div>
        <div class="facetwp-row">
            <div><?php _e('Sort by', 'fwp'); ?>:</div>
            <div>
                <select class="facet-orderby">
                    <option value="count"><?php _e( 'Highest Count', 'fwp' ); ?></option>
                    <option value="display_value"><?php _e( 'Display Value', 'fwp' ); ?></option>
                    <option value="raw_value"><?php _e( 'Raw Value', 'fwp' ); ?></option>
                    <option value="term_order"><?php _e( 'Term Order', 'fwp' ); ?></option>
                </select>
            </div>
        </div>
        <div class="facetwp-row">
            <div>
                <?php _e('Count', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'The maximum number of facet choices to show', 'fwp' ); ?></div>
                </div>
            </div>
            <div><input type="text" class="facet-count" value="10" /></div>
        </div>
<?php
    }
}
