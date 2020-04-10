<?php
/*
Plugin Name: Peter's Login Redirect
Plugin URI: http://www.theblog.ca/wplogin-redirect
Description: Redirect users to different locations after logging in. Define a set of rules for specific users, user with specific roles, users with specific capabilities, and a blanket rule for all other users. This is all managed in Settings > Login/logout redirects.
Author: Peter Keung
Author URI: http://www.theblog.ca
Version: 2.9.7
Text Domain: peters-login-redirect
Domain Path: /languages/
*/

require_once dirname(__FILE__) . '/mo-admin-notice-featured.php';

add_action('init', 'rul_textdomain');
function rul_textdomain()
{
    load_plugin_textdomain('peters-login-redirect', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

global $wpdb;
global $rul_db_addresses;
global $rul_version;
// Name of the database table that will hold group information and moderator rules
$rul_db_addresses = $wpdb->prefix . 'login_redirects';
$rul_version      = '2.9.7';

// doing this so we can pass current user logging out since it is no longer active after logout
if ( ! function_exists('wp_logout')) :
    /**
     * Log the current user out.
     *
     * @since 2.5.0
     */
    function wp_logout()
    {
        $current_user = wp_get_current_user();
        wp_destroy_current_session();
        wp_clear_auth_cookie();
        wp_set_current_user(0);

        /**
         * Fires after a user is logged-out.
         *
         * @since 1.5.0
         */
        do_action('wp_logout', $current_user);
    }
endif;

// Some helper functions, all "public static" in PHP5 land
class rulRedirectFunctionCollection
{
    /*
        Grabs settings from the database as of version 2.5.0 of this plugin.
        Defaults are defined here, but the settings values should be edited in the WordPress admin panel.
        If no setting is asked for, then it returns an array of all settings; otherwise it returns a specific setting
    */
    static function get_settings($setting = false)
    {
        $rul_settings = array();

        // Allow a POST or GET "redirect_to" variable to take precedence over settings within the plugin
        $rul_settings['rul_allow_post_redirect_override'] = false;

        // Allow a POST or GET logout "redirect_to" variable to take precedence over settings within the plugin
        $rul_settings['rul_allow_post_redirect_override_logout'] = false;

        $rul_settings_from_options_table = rulRedirectFunctionCollection::get_settings_from_options_table();

        // Merge the default settings with the settings form the database
        // Limit the settings in case there are ones from the database that are old
        foreach ($rul_settings as $setting_name => $setting_value) {
            if (isset($rul_settings_from_options_table[$setting_name])) {
                $rul_settings[$setting_name] = $rul_settings_from_options_table[$setting_name];
            }
        }

        if ( ! $setting) {
            return $rul_settings;
        }

        if ($setting && isset($rul_settings[$setting])) {
            return $rul_settings[$setting];
        }

        return false;
    }

    static function get_settings_from_options_table()
    {
        return get_option('rul_settings', array());
    }

    static function set_setting($setting = false, $value = false)
    {
        if ($setting) {
            $current_settings = rulRedirectFunctionCollection::get_settings();
            if ($current_settings) {
                $current_settings[$setting] = $value;
                update_option('rul_settings', $current_settings);
            }
        }
    }

    /*
        This extra function is necessary to support the use case where someone was previously logged in
        Thanks to http://wordpress.org/support/topic/97314 for this function
    */
    static function redirect_current_user_can($capability, $current_user)
    {
        global $wpdb;

        $roles        = get_option($wpdb->prefix . 'user_roles');
        $user_roles   = $current_user->{$wpdb->prefix . 'capabilities'};
        $user_roles   = array_keys($user_roles, true);
        $role         = $user_roles[0];
        $capabilities = $roles[$role]['capabilities'];

        if (in_array($capability, array_keys($capabilities, true))) {
            // check array keys of capabilities for match against requested capability
            return true;
        }

        return false;
    }

    /*
        A generic function to return the value mapped to a particular variable
    */
    static function rul_get_variable($variable, $user)
    {
        $variable_value = apply_filters('rul_replace_variable', false, $variable, $user);
        if ( ! $variable_value) {
            // Return the permalink of the post ID
            if (0 === strpos($variable, 'postid-')) {
                $post_id   = str_replace('postid-', '', $variable);
                $permalink = get_permalink($post_id);
                if ($permalink) {
                    $variable_value = $permalink;
                }
            } else {
                switch ($variable) {
                    // Returns the current user's username (only use this if you know they're logged in)
                    case 'username':
                        $variable_value = rawurlencode($user->user_login);
                        break;
                    // Returns the current user's author slug aka nickname as used in URLs
                    // sanitize_title should not be required here since it was already done on insert
                    case 'userslug':
                        $variable_value = $user->user_nicename;
                        break;
                    // Returns the URL of the WordPress files; see http://codex.wordpress.org/Function_Reference/network_site_url
                    case 'siteurl':
                        $variable_value = network_site_url();
                        break;
                    // Returns the URL of the site, possibly different from where the WordPress files are; see http://codex.wordpress.org/Function_Reference/network_home_url
                    case 'homeurl':
                        $variable_value = network_home_url();
                        break;
                    // Returns the login referrer in order to redirect back to the same page
                    // Note that this will not work if the referrer is the same as the login processor (otherwise in a standard setup you'd redirect to the login form)
                    case 'http_referer':
                        $http_referer_parts = parse_url($_SERVER['HTTP_REFERER']);
                        if ($_SERVER['REQUEST_URI'] != $http_referer_parts['path']) {
                            $variable_value = $_SERVER['HTTP_REFERER'];
                        } else {
                            $variable_value = '';
                        }
                        break;
                    default:
                        $variable_value = '';
                        break;
                }
            }
        }

        return $variable_value;
    }

    /**
     * Replaces the syntax [variable]variable_name[/variable] with whatever has been mapped to the variable_name in the rul_get_variable function
     */
    static function rul_replace_variable($string, $user)
    {
        preg_match_all("/\[variable\](.*?)\[\/variable\]/is", $string, $out);

        if ( ! empty($out[0])) {
            foreach ($out[0] as $instance => $full_match) {
                $replaced_variable = rulRedirectFunctionCollection::rul_get_variable($out[1][$instance], $user);
                $string            = str_replace($full_match, $replaced_variable, $string);
            }
        }

        return $string;
    }

    static function rul_trigger_allowed_host($url)
    {
        $url_parsed = parse_url($url);
        if (isset($url_parsed['host'])) {
            $rul_allowed_hosts[] = $url_parsed['host'];
            add_filter('allowed_redirect_hosts', function ($hosts) use ($rul_allowed_hosts) {
                return array_merge($hosts, $rul_allowed_hosts);
            });
        }
    }
}

// Functions specific to logout redirecting
class rulLogoutFunctionCollection
{
    static function logout_redirect($current_user)
    {
        $rul_allow_post_redirect_override_logout = rulRedirectFunctionCollection::get_settings('rul_allow_post_redirect_override_logout');

        $requested_redirect_to = ! empty($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : false;

        if ( ! $requested_redirect_to || ! $rul_allow_post_redirect_override_logout) {
            $rul_url = rulLogoutFunctionCollection::get_redirect_url($current_user, $requested_redirect_to);

            if ($rul_url) {
                wp_redirect($rul_url);
                die();
            }
        }
    }

    static function logout_redirect_2($redirect_to, $requested_redirect_to, $current_user)
    {
        $rul_allow_post_redirect_override_logout = rulRedirectFunctionCollection::get_settings('rul_allow_post_redirect_override_logout');

        $requested_redirect_to = ! empty($requested_redirect_to) ? $requested_redirect_to : false;

        if ( ! $requested_redirect_to || ! $rul_allow_post_redirect_override_logout) {
            $rul_url = rulLogoutFunctionCollection::get_redirect_url($current_user, $requested_redirect_to);

            if ($rul_url) {
                rulRedirectFunctionCollection::rul_trigger_allowed_host($rul_url);
                $redirect_to = $rul_url;
            }
        }

        return $redirect_to;
    }

    // Get the logout redirect URL according to defined rules
    // Functionality for user-, role-, and capability-specific redirect rules is available
    // Note that only the "all other users" redirect URL is currently implemented in the UI
    static function get_redirect_url($user, $requested_redirect_to)
    {
        global $wpdb, $rul_db_addresses;

        $redirect_to = false;

        // Check for an extended custom redirect rule
        $rul_custom_redirect = apply_filters('rul_before_user_logout', false, $requested_redirect_to, $user);

        if ($rul_custom_redirect) {
            return rulRedirectFunctionCollection::rul_replace_variable($rul_custom_redirect, $user);
        }

        // Check for a redirect rule for this user
        $rul_user = $wpdb->get_var('SELECT rul_url_logout FROM ' . $rul_db_addresses .
                                   ' WHERE rul_type = \'user\' AND rul_value = \'' . $user->user_login . '\' LIMIT 1');

        if ($rul_user) {
            return rulRedirectFunctionCollection::rul_replace_variable($rul_user, $user);
        }

        // Check for an extended custom redirect rule
        $rul_custom_redirect = apply_filters('rul_before_role_logout', false, $requested_redirect_to, $user);

        if ($rul_custom_redirect) {
            return rulRedirectFunctionCollection::rul_replace_variable($rul_custom_redirect, $user);
        }

        // Check for a redirect rule that matches this user's role
        $rul_roles = $wpdb->get_results('SELECT rul_value, rul_url_logout FROM ' . $rul_db_addresses .
                                        ' WHERE rul_type = \'role\'', OBJECT);

        if ($rul_roles) {
            foreach ($rul_roles as $rul_role) {
                if ('' != $rul_role->rul_url_logout && isset($user->{$wpdb->prefix . 'capabilities'}[$rul_role->rul_value])) {
                    return rulRedirectFunctionCollection::rul_replace_variable($rul_role->rul_url_logout, $user);
                }
            }
        }

        // Check for an extended custom redirect rule
        $rul_custom_redirect = apply_filters('rul_before_capability_logout', false, $requested_redirect_to, $user);
        if ($rul_custom_redirect) {
            return rulRedirectFunctionCollection::rul_replace_variable($rul_custom_redirect, $user);
        }

        // Check for a redirect rule that matches this user's capability
        $rul_levels = $wpdb->get_results('SELECT rul_value, rul_url_logout FROM ' . $rul_db_addresses .
                                         ' WHERE rul_type = \'level\' ORDER BY rul_order, rul_value', OBJECT);

        if ($rul_levels) {
            foreach ($rul_levels as $rul_level) {
                if ('' != $rul_level->rul_url_logout && rulRedirectFunctionCollection::redirect_current_user_can($rul_level->rul_value, $user)) {
                    return rulRedirectFunctionCollection::rul_replace_variable($rul_level->rul_url_logout, $user);
                }
            }
        }

        // Check for an extended custom redirect rule
        $rul_custom_redirect = apply_filters('rul_before_fallback_logout', false, $requested_redirect_to, $user);
        if ($rul_custom_redirect) {
            return rulRedirectFunctionCollection::rul_replace_variable($rul_custom_redirect, $user);
        }

        // If none of the above matched, look for a rule to apply to all users
        $rul_all = $wpdb->get_var('SELECT rul_url_logout FROM ' . $rul_db_addresses .
                                  ' WHERE rul_type = \'all\' LIMIT 1');

        if ($rul_all) {
            return rulRedirectFunctionCollection::rul_replace_variable($rul_all, $user);
        }

        // No rules matched or existed, so just send them to the WordPress admin panel as usual
        return $redirect_to;
    }
}

// Functions for redirecting post-registration
class rulRedirectPostRegistration
{
    static function post_registration_wrapper($requested_redirect_to)
    {
        /*
            Some limitations:
                - Not yet implemented but possible: toggle whether to allow a GET or POST override of the redirect_to variable (currently it is "yes")
                - Not yet possible: Username-customized page, since the WordPress hook is implemented pre-registration, not post-registration
        */

        $rul_url = rulRedirectPostRegistration::get_redirect_url($requested_redirect_to);
        if ($rul_url) {
            rulRedirectFunctionCollection::rul_trigger_allowed_host($rul_url);
            return $rul_url;
        }

        return $requested_redirect_to;
    }

    // Looks up the redirect URL, if any
    static function get_redirect_url($requested_redirect_to)
    {
        global $wpdb, $rul_db_addresses;

        $redirect_to = false;

        $rul_all = $wpdb->get_var('SELECT rul_url FROM ' . $rul_db_addresses .
                                  ' WHERE rul_type = \'register\' LIMIT 1');

        if ($rul_all) {
            $redirect_to = rulRedirectFunctionCollection::rul_replace_variable($rul_all, false);

            return $redirect_to;
        }

        // No rule exists
        return $redirect_to;
    }
}

function peters_redirect_wrapper($redirect_to, $requested_redirect_to, $user)
{
    $rul_allow_post_redirect_override = rulRedirectFunctionCollection::get_settings('rul_allow_post_redirect_override');

    if ( ! isset($user->user_login)) return $redirect_to;

    if ((admin_url() == $redirect_to && $rul_allow_post_redirect_override) || ! $rul_allow_post_redirect_override) {
        $rul_url = redirect_to_front_page($redirect_to, $requested_redirect_to, $user);
        if ($rul_url) {
            rulRedirectFunctionCollection::rul_trigger_allowed_host($rul_url);

            return $rul_url;
        }
    }

    return $redirect_to;
}

function redirect_to_front_page($redirect_to, $requested_redirect_to, $user)
{
    global $wpdb, $rul_db_addresses;

    // Check for an extended custom redirect rule
    $rul_custom_redirect = apply_filters('rul_before_user', false, $redirect_to, $requested_redirect_to, $user);
    if ($rul_custom_redirect) {
        $redirect_to = rulRedirectFunctionCollection::rul_replace_variable($rul_custom_redirect, $user);

        return $redirect_to;
    }

    // Check for a redirect rule for this user
    $rul_user = $wpdb->get_var('SELECT rul_url FROM ' . $rul_db_addresses .
                               ' WHERE rul_type = \'user\' AND rul_value = \'' . $user->user_login . '\' LIMIT 1');

    if ($rul_user) {
        $redirect_to = rulRedirectFunctionCollection::rul_replace_variable($rul_user, $user);

        return $redirect_to;
    }

    // Check for an extended custom redirect rule
    $rul_custom_redirect = apply_filters('rul_before_role', false, $redirect_to, $requested_redirect_to, $user);
    if ($rul_custom_redirect) {
        $redirect_to = rulRedirectFunctionCollection::rul_replace_variable($rul_custom_redirect, $user);

        return $redirect_to;
    }

    // Check for a redirect rule that matches this user's role
    $rul_roles = $wpdb->get_results('SELECT rul_value, rul_url FROM ' . $rul_db_addresses .
                                    ' WHERE rul_type = \'role\'', OBJECT);

    if ($rul_roles) {
        foreach ($rul_roles as $rul_role) {
            if ('' != $rul_role->rul_url && isset($user->{$wpdb->prefix . 'capabilities'}[$rul_role->rul_value])) {
                $redirect_to = rulRedirectFunctionCollection::rul_replace_variable($rul_role->rul_url, $user);

                return $redirect_to;
            }
        }
    }

    // Check for an extended custom redirect rule
    $rul_custom_redirect = apply_filters('rul_before_capability', false, $redirect_to, $requested_redirect_to, $user);
    if ($rul_custom_redirect) {
        $redirect_to = rulRedirectFunctionCollection::rul_replace_variable($rul_custom_redirect, $user);

        return $redirect_to;
    }

    // Check for a redirect rule that matches this user's capability
    $rul_levels = $wpdb->get_results('SELECT rul_value, rul_url FROM ' . $rul_db_addresses .
                                     ' WHERE rul_type = \'level\' ORDER BY rul_order, rul_value', OBJECT);

    if ($rul_levels) {
        foreach ($rul_levels as $rul_level) {
            if ('' != $rul_level->rul_url && rulRedirectFunctionCollection::redirect_current_user_can($rul_level->rul_value, $user)) {
                $redirect_to = rulRedirectFunctionCollection::rul_replace_variable($rul_level->rul_url, $user);

                return $redirect_to;
            }
        }
    }

    // Check for an extended custom redirect rule
    $rul_custom_redirect = apply_filters('rul_before_fallback', false, $redirect_to, $requested_redirect_to, $user);
    if ($rul_custom_redirect) {
        $redirect_to = rulRedirectFunctionCollection::rul_replace_variable($rul_custom_redirect, $user);

        return $redirect_to;
    }

    // If none of the above matched, look for a rule to apply to all users
    $rul_all = $wpdb->get_var('SELECT rul_url FROM ' . $rul_db_addresses .
                              ' WHERE rul_type = \'all\' LIMIT 1');

    if ($rul_all) {
        $redirect_to = rulRedirectFunctionCollection::rul_replace_variable($rul_all, $user);

        return $redirect_to;
    }

    // No rules matched or existed, so just send them to the WordPress admin panel as usual
    return $redirect_to;

}

// Typically this function is used in templates, similarly to the wp_register function
// It returns a link to the administration panel or the one that was custom defined
// If no user is logged in, it returns the "Register" link
// You can specify tags to go around the returned link (or wrap it with no tags); by default this is a list item
// You can also specify whether to print out the link or just return it
function rul_register($before = '<li>', $after = '</li>', $give_echo = true)
{
    global $current_user;

    if ( ! is_user_logged_in()) {
        if (get_option('users_can_register'))
            $link = $before . '<a href="' . site_url('wp-login.php?action=register', 'login') . '">' . __('Register', 'peters-login-redirect') . '</a>' . $after;
        else
            $link = '';
    } else {
        $link = $before . '<a href="' . redirect_to_front_page('', '', $current_user) . '">' . __('Site Admin', 'peters-login-redirect') . '</a>' . $after;;
    }

    if ($give_echo) {
        echo $link;
    } else {
        return $link;
    }
}

if (is_admin()) {

    // Returns all option HTML for all usernames in the system except for those supplied to it
    function rul_returnusernames($exclude)
    {
        global $wpdb;

        $rul_returnusernames = '';

        // Build the "not in" part of the MySQL query
        $exclude_users = "'" . implode("','", $exclude) . "'";

        $rul_userresults = $wpdb->get_results('SELECT user_login FROM ' . $wpdb->users . ' WHERE user_login NOT IN (' . $exclude_users . ') ORDER BY user_login', ARRAY_N);

        // Built the option HTML
        if ($rul_userresults) {
            foreach ($rul_userresults as $rul_userresult) {
                $rul_returnusernames .= '<option value="' . $rul_userresult[0] . '">' . $rul_userresult[0] . '</option>';
            }
        }

        return $rul_returnusernames;
    }

    // Returns all roles in the system
    function rul_returnrolenames()
    {
        global $wp_roles;

        $rul_returnrolenames = array();
        foreach (array_keys($wp_roles->role_names) as $rul_rolename) {
            $rul_returnrolenames[$rul_rolename] = $rul_rolename;
        }

        return $rul_returnrolenames;
    }

    // Returns option HTML for all roles in the system, except for those supplied to it
    function rul_returnroleoptions($exclude)
    {

        // Relies on a function that just returns the role names
        $rul_rolenames = rul_returnrolenames($exclude);

        $rul_returnroleoptions = '';

        // Build the option HTML
        if ($rul_rolenames) {
            foreach ($rul_rolenames as $rul_rolename) {
                if ( ! isset($exclude[$rul_rolename])) {
                    $rul_returnroleoptions .= '<option value="' . $rul_rolename . '">' . $rul_rolename . '</option>';
                }
            }
        }

        return $rul_returnroleoptions;

    }

    // Returns all level names in the system
    function rul_returnlevelnames()
    {
        global $wp_roles;

        $rul_returnlevelnames = array();

        // Builds the array of level names by combing through each of the roles and listing their levels
        foreach ($wp_roles->roles as $wp_role) {
            $rul_returnlevelnames = array_unique((array_merge($rul_returnlevelnames, array_keys($wp_role['capabilities']))));
        }

        // Sort the level names in alphabetical order
        sort($rul_returnlevelnames);

        return $rul_returnlevelnames;

    }

    // Returns option HTML for all levels in the system, except for those supplied to it
    function rul_returnleveloptions($exclude)
    {

        // Relies on a function that just returns the level names
        $rul_levelnames = rul_returnlevelnames();

        $rul_returnleveloptions = '';

        // Build the option HTML
        foreach ($rul_levelnames as $rul_levelname) {
            if ( ! isset($exclude[$rul_levelname])) {
                $rul_returnleveloptions .= '<option value="' . $rul_levelname . '">' . $rul_levelname . '</option>';
            }
        }

        return $rul_returnleveloptions;

    }

    // Wraps the return message in an informational div
    function rul_format_return($innerMessage)
    {
        return '<div id="message" class="updated fade">' . $innerMessage . '</div>';
    }

    // Validates adds and edits to make sure that the user / role / level
    function rul_validate_submission($typeValue, $type)
    {
        $success       = true;
        $error_message = '';

        if ($type == 'user') {
            if ( ! username_exists($typeValue)) {
                $success       = false;
                $error_message = '<p><strong>****' . __('ERROR: Non-existent username submitted ', 'peters-login-redirect') . '****</strong></p>';
            }
        } elseif ($type == 'role') {
            // Get a list of roles in the system so that we can verify that a valid role was submitted
            $rul_existing_rolenames = rul_returnrolenames();
            if ( ! isset($rul_existing_rolenames[$typeValue])) {
                $success       = false;
                $error_message = '<p><strong>****' . __('ERROR: Non-existent role submitted ', 'peters-login-redirect') . '****</strong></p>';
            }
        } elseif ($type == 'level') {
            // Get a list of levels in the system so that we can verify that a valid level was submitted
            $rul_existing_levelnames = array_flip(rul_returnlevelnames());

            if ( ! isset($rul_existing_levelnames[$typeValue])) {
                $success       = false;
                $error_message = '<p><strong>****' . __('ERROR: Non-existent level submitted ', 'peters-login-redirect') . '****</strong></p>';
            }
        }

        return array('success' => $success, 'error_message' => $error_message);
    }

    // Validates deletions by simply making sure that the entry isn't empty
    // Additional validation / escaping should be performed if WordPress ever removes its automatic addslashes calls (see http://www.theblog.ca/wordpress-addslashes-magic-quotes); at that point, use https://codex.wordpress.org/Class_Reference/wpdb#DELETE_Rows
    function rul_validate_deletion($typeValue, $type)
    {
        $success       = true;
        $error_message = '';

        if (trim($typeValue) == '') {
            $success       = false;
            $error_message = '<p><strong>****' . sprintf(__('ERROR: Empty %s submitted ', 'peters-login-redirect'), $type) . '****</strong></p>';
        }

        return array('success' => $success, 'error_message' => $error_message);
    }

    // Processes adding a new redirect rule
    // $type can be user, role, or level
    function rul_submit_rule($typeValue, $address, $address_logout, $order = 0, $type)
    {
        global $wpdb, $rul_db_addresses;

        // Ensure that the request came from the back-end
        check_admin_referer('rul_' . $type . '_submit');

        $rul_process_submit = '';

        if ($typeValue && ($address || $address_logout)) {
            // Validation depending on the type
            $validation         = rul_validate_submission($typeValue, $type);
            $rul_submit_success = $validation['success'];
            $rul_process_submit = $validation['error_message'];

            if ($rul_submit_success) {

                // Insert a new rule

                $order = abs(intval($order));
                if ($order > 99) {
                    $order = 0;
                }

                $rul_update_rule = $wpdb->insert($rul_db_addresses,
                    array(
                        'rul_url'        => $address,
                        'rul_url_logout' => $address_logout,
                        'rul_type'       => $type,
                        'rul_value'      => $typeValue,
                        'rul_order'      => $order
                    ),
                    array('%s', '%s', '%s', '%s', '%d')
                );

                if ( ! $rul_update_rule) {
                    $rul_submit_success = false;
                    $rul_process_submit = '<p><strong>****' . sprintf(__('ERROR: Unknown error adding %s-specific redirect for %s %s', 'peters-login-redirect'), $type, $type, $typeValue) . '****</strong></p>';
                }
            }

            if ($rul_submit_success) {
                $rul_process_submit = '<p>' . sprintf(__('Successfully added %s-specific redirect rule for %s', 'peters-login-redirect'), $type, $typeValue) . '</p>';
            }
        }

        return rul_format_return($rul_process_submit);
    }

    // Edits a redirect rule
    // $type can be user, role, or level
    function rul_edit_rule($typeValue, $address, $address_logout, $order = 0, $type)
    {
        global $wpdb, $rul_db_addresses;

        // Ensure that the request came from the back-end
        check_admin_referer('rul_' . $type . '_edit');

        if ($typeValue && ($address || $address_logout)) {
            // Validation depending on the type
            $validation         = rul_validate_submission($typeValue, $type);
            $rul_submit_success = $validation['success'];
            $rul_process_submit = $validation['error_message'];

            if ($rul_submit_success) {
                // Edit the rule

                $order = abs(intval($order));
                if ($order > 99) {
                    $order = 0;
                }

                $rul_update_rule = $wpdb->update($rul_db_addresses,
                    array(
                        'rul_url'        => $address,
                        'rul_url_logout' => $address_logout,
                        'rul_order'      => $order
                    ),
                    array(
                        'rul_value' => $typeValue,
                        'rul_type'  => $type
                    ),
                    array('%s', '%s', '%d'),
                    array('%s', '%s')
                );

                if ( ! $rul_update_rule) {
                    $rul_submit_success = false;
                    $rul_process_submit = '<p><strong>****' . sprintf(__('ERROR: Unknown error editing %s-specific redirect for %s %s', 'peters-login-redirect'), $type, $type, $typeValue) . '****</strong></p>';
                }
            }

            if ($rul_submit_success) {
                $rul_process_submit = '<p>' . sprintf(__('Successfully edited %s-specific redirect rule for %s', 'peters-login-redirect'), $type, $typeValue) . '</p>';
            }
        }

        return rul_format_return($rul_process_submit);
    }

    // Deletes a redirect rule
    // $type can be user, role, or level
    function rul_delete_rule($typeValue, $type)
    {
        global $wpdb, $rul_db_addresses;

        // Ensure that the request came from the back-end
        check_admin_referer('rul_' . $type . '_edit');

        if ($typeValue) {
            // Validation depending on the type
            $validation         = rul_validate_deletion($typeValue, $type);
            $rul_submit_success = $validation['success'];
            $rul_process_submit = $validation['error_message'];

            if ($rul_submit_success) {
                // Delete the rule
                $rul_update_rule = $wpdb->query("DELETE FROM `$rul_db_addresses` WHERE `rul_value` = '$typeValue' AND `rul_type` = '$type' LIMIT 1");

                if ( ! $rul_update_rule) {
                    $rul_submit_success = false;
                    $rul_process_submit = '<p><strong>****' . sprintf(__('ERROR: Unknown error deleting %s-specific redirect for %s %s', 'peters-login-redirect'), $type, $type, $typeValue) . '****</strong></p>';
                }
            }

            if ($rul_submit_success) {
                $rul_process_submit = '<p>' . sprintf(__('Successfully deleted %s-specific redirect rule for %s', 'peters-login-redirect'), $type, $typeValue) . '</p>';
            }
        }

        return rul_format_return($rul_process_submit);
    }

    function rul_submit_all($update_or_delete, $address, $address_logout)
    {
        global $wpdb, $rul_db_addresses;

        // Ensure that the request came from the back-end
        check_admin_referer('rul_allupdatesubmit');

        $address        = trim($address);
        $address_logout = trim($address_logout);

        // Open the informational div
        $rul_process_submit = '<div id="message" class="updated fade">';

        // Code for closing the informational div
        $rul_process_close = '</div>';

        // ----------------------------------
        // Process the rule changes
        // ----------------------------------

        // Since we never actually, remove the "all" entry, here we just make its value empty
        if ($update_or_delete == 'delete') {
            $update = $wpdb->update(
                $rul_db_addresses,
                array('rul_url' => '', 'rul_url_logout' => ''),
                array('rul_type' => 'all')
            );

            if ($update === false) {
                $rul_process_submit .= '<p><strong>****' . __('ERROR: Unknown database problem removing URL for &#34;all other users&#34; ', 'peters-login-redirect') . '****</strong></p>';
            } else {
                $rul_process_submit .= '<p>' . __('Successfully removed URL for &#34;all other users&#34; ', 'peters-login-redirect') . '</p>';
            }
        } elseif ($update_or_delete == 'update') {
            $update = $wpdb->update(
                $rul_db_addresses,
                array('rul_url' => $address, 'rul_url_logout' => $address_logout),
                array('rul_type' => 'all')
            );

            if ($update === false) {
                $rul_process_submit .= '<p><strong>****' . __('ERROR: Unknown database problem updating URL for &#34;all other users&#34; ', 'peters-login-redirect') . '****</strong></p>';
            } else {
                $rul_process_submit .= '<p>' . __('Successfully updated URL for &#34;all other users&#34;', 'peters-login-redirect') . '</p>';
            }
        }

        // Close the informational div
        $rul_process_submit .= $rul_process_close;

        // We've made it this far, so success!
        return $rul_process_submit;
    }

    function rul_submit_register($update_or_delete, $address)
    {
        global $wpdb, $rul_db_addresses;

        // Ensure that the request came from the back-end
        check_admin_referer('rul_registerupdatesubmit');

        $address = trim($address);

        // Open the informational div
        $rul_process_submit = '<div id="message" class="updated fade">';

        // Code for closing the informational div
        $rul_process_close = '</div>';

        // ----------------------------------
        // Process the rule changes
        // ----------------------------------

        // Since we never actually remove the "register" entry, here we just make its value empty
        if ($update_or_delete == 'delete') {
            $update = $wpdb->update(
                $rul_db_addresses,
                array('rul_url' => ''),
                array('rul_type' => 'register')
            );

            if ($update === false) {
                $rul_process_submit .= '<p><strong>****' . __('ERROR: Unknown database problem removing URL for &#34;post-registration&#34; ', 'peters-login-redirect') . '****</strong></p>';
            } else {
                $rul_process_submit .= '<p>' . __('Successfully removed URL for &#34;post-registration&#34; ', 'peters-login-redirect') . '</p>';
            }
        } elseif ($update_or_delete == 'update') {
            $update = $wpdb->update(
                $rul_db_addresses,
                array('rul_url' => $address),
                array('rul_type' => 'register')
            );

            if ($update === false) {
                $rul_process_submit .= '<p><strong>****' . __('ERROR: Unknown database problem updating URL for &#34;post-registration&#34; ', 'peters-login-redirect') . '****</strong></p>';
            } else {
                $rul_process_submit .= '<p>' . __('Successfully updated URL for &#34;post-registration&#34;', 'peters-login-redirect') . '</p>';
            }
        }

        // Close the informational div
        $rul_process_submit .= $rul_process_close;

        // We've made it this far, so success!
        return $rul_process_submit;
    }


    // Process submitted information to update plugin settings
    function rul_submit_settings()
    {
        // Ensure that the request came from the back-end
        check_admin_referer('settings');

        $rul_settings = rulRedirectFunctionCollection::get_settings();
        foreach ($rul_settings as $setting_name => $setting_value) {
            if (isset($_POST[$setting_name])) {
                $rul_settings[$setting_name] = $_POST[$setting_name];
            }
        }
        update_option('rul_settings', $rul_settings);
        $rul_process_submit = '<div id="message" class="updated fade">';
        $rul_process_submit .= '<p>' . __('Successfully updated plugin settings', 'peters-login-redirect') . '</p>';
        $rul_process_submit .= '</div>';

        return $rul_process_submit;
    }

    // This is the Settings > Login/logout redirects menu
    function rul_optionsmenu()
    {
        global $wpdb, $rul_db_addresses;

        // Upgrade check here because it's the only place we know they will visit
        rul_upgrade();

        $rul_process_submit = '';

        // Process submitted information to update redirect rules
        if (isset($_POST['rul_username_submit'])) {
            $rul_process_submit = rul_submit_rule($_POST['rul_username'], $_POST['rul_username_address'], $_POST['rul_username_logout'], 0, 'user');
        } elseif (isset($_POST['rul_username_edit'])) {
            $rul_process_submit = rul_edit_rule($_POST['rul_username'], $_POST['rul_username_address'], $_POST['rul_username_logout'], 0, 'user');
        } elseif (isset($_POST['rul_username_delete'])) {
            $rul_process_submit = rul_delete_rule($_POST['rul_username'], 'user');
        } elseif (isset($_POST['rul_role_submit'])) {
            $rul_process_submit = rul_submit_rule($_POST['rul_role'], $_POST['rul_role_address'], $_POST['rul_role_logout'], 0, 'role');
        } elseif (isset($_POST['rul_role_edit'])) {
            $rul_process_submit = rul_edit_rule($_POST['rul_role'], $_POST['rul_role_address'], $_POST['rul_role_logout'], 0, 'role');
        } elseif (isset($_POST['rul_role_delete'])) {
            $rul_process_submit = rul_delete_rule($_POST['rul_role'], 'role');
        } elseif (isset($_POST['rul_level_submit'])) {
            $rul_process_submit = rul_submit_rule($_POST['rul_level'], $_POST['rul_level_address'], $_POST['rul_level_logout'], $_POST['rul_level_order'], 'level');
        } elseif (isset($_POST['rul_level_edit'])) {
            $rul_process_submit = rul_edit_rule($_POST['rul_level'], $_POST['rul_level_address'], $_POST['rul_level_logout'], $_POST['rul_level_order'], 'level');
        } elseif (isset($_POST['rul_level_delete'])) {
            $rul_process_submit = rul_delete_rule($_POST['rul_level'], 'level');
        } elseif (isset($_POST['rul_allupdatesubmit'])) {
            $rul_process_submit = rul_submit_all('update', $_POST['rul_all'], $_POST['rul_all_logout']);
        } elseif (isset($_POST['rul_alldeletesubmit'])) {
            $rul_process_submit = rul_submit_all('delete', $_POST['rul_all'], $_POST['rul_all_logout']);
        } elseif (isset($_POST['rul_registerupdatesubmit'])) {
            $rul_process_submit = rul_submit_register('update', $_POST['rul_register']);
        } elseif (isset($_POST['rul_registerdeletesubmit'])) {
            $rul_process_submit = rul_submit_register('delete', $_POST['rul_register']);
        } elseif (isset($_POST['rul_settingssubmit'])) {
            $rul_process_submit = rul_submit_settings();
        }

        // Settings that can be updated
        $rul_settings = rulRedirectFunctionCollection::get_settings();

        // -----------------------------------
        // Get the existing rules
        // -----------------------------------

        $rul_rules = $wpdb->get_results('SELECT rul_type, rul_value, rul_url, rul_url_logout, rul_order FROM ' . $rul_db_addresses . ' ORDER BY rul_type, rul_order, rul_value', ARRAY_N);

        $rul_usernamevalues     = '';
        $rul_rolevalues         = '';
        $rul_levelvalues        = '';
        $rul_usernames_existing = array();
        $rul_roles_existing     = array();
        $rul_levels_existing    = array();

        if ($rul_rules) {

            $i       = 0;
            $i_user  = 0;
            $i_role  = 0;
            $i_level = 0;

            while ($i < count($rul_rules)) {

                list($rul_type, $rul_value, $rul_url, $rul_url_logout, $rul_order) = $rul_rules[$i];

                // Specific users
                if ($rul_type == 'user') {
                    $rul_usernamevalues .= '<form name="rul_username_edit_form[' . $i_user . ']" action="?page=' . basename(__FILE__) . '" method="post">';
                    $rul_usernamevalues .= '<tr>';
                    $rul_usernamevalues .= '<td><p><input type="hidden" name="rul_username" value="' . htmlspecialchars($rul_value) . '" /> ' . $rul_value . '</p></td>';
                    $rul_usernamevalues .= '<td>';
                    $rul_usernamevalues .= '<p>' . __('Login URL', 'peters-login-redirect') . '<br /><input type="text" size="90" maxlength="500" name="rul_username_address" value="' . htmlspecialchars($rul_url) . '" /></p>';
                    $rul_usernamevalues .= '<p>' . __('Logout URL', 'peters-login-redirect') . '<br /><input type="text" size="60" maxlength="500" name="rul_username_logout" value="' . htmlspecialchars($rul_url_logout) . '" /></p>';
                    $rul_usernamevalues .= '</td>';
                    $rul_usernamevalues .= '<td><p>';
                    $rul_usernamevalues .= '<input class="button button-primary" name="rul_username_edit" type="submit" value="' . __('Edit', 'peters-login-redirect') . '" /> <input type="submit" class="button" name="rul_username_delete" value="' . __('Delete', 'peters-login-redirect') . '" />';
                    $rul_usernamevalues .= wp_nonce_field('rul_user_edit', '_wpnonce', true, false);
                    $rul_usernamevalues .= '</p></td>';
                    $rul_usernamevalues .= '</tr>';
                    $rul_usernamevalues .= '</form>';

                    $rul_usernames_existing[] = $rul_value;

                    ++$i_user;
                } elseif ($rul_type == 'role') {
                    $rul_rolevalues .= '<form name="rul_role_edit_form[' . $i_role . ']" action="?page=' . basename(__FILE__) . '" method="post">';
                    $rul_rolevalues .= '<tr>';
                    $rul_rolevalues .= '<td><p><input type="hidden" name="rul_role" value="' . htmlspecialchars($rul_value) . '" /> ' . $rul_value . '</p></td>';
                    $rul_rolevalues .= '<td>';
                    $rul_rolevalues .= '<p>' . __('Login URL', 'peters-login-redirect') . '<br /><input type="text" size="90" maxlength="500" name="rul_role_address" value="' . htmlspecialchars($rul_url) . '" /></p>';
                    $rul_rolevalues .= '<p>' . __('Logout URL', 'peters-login-redirect') . '<br /><input type="text" size="60" maxlength="500" name="rul_role_logout" value="' . htmlspecialchars($rul_url_logout) . '" /></p>';
                    $rul_rolevalues .= '</td>';
                    $rul_rolevalues .= '<td><p>';
                    $rul_rolevalues .= '<input class="button button-primary" name="rul_role_edit" type="submit" value="' . __('Edit', 'peters-login-redirect') . '" /> <input type="submit" class="button" name="rul_role_delete" value="' . __('Delete', 'peters-login-redirect') . '" />';
                    $rul_rolevalues .= wp_nonce_field('rul_role_edit', '_wpnonce', true, false);
                    $rul_rolevalues .= '</p></td>';
                    $rul_rolevalues .= '</tr>';
                    $rul_rolevalues .= '</form>';

                    $rul_roles_existing[$rul_value] = '';

                    ++$i_role;
                } elseif ($rul_type == 'level') {
                    $rul_levelvalues .= '<form name="rul_level_edit_form[' . $i_level . ']" action="?page=' . basename(__FILE__) . '" method="post">';
                    $rul_levelvalues .= '<tr>';
                    $rul_levelvalues .= '<td><p><input type="hidden" name="rul_level" value="' . htmlspecialchars($rul_value) . '" /> ' . $rul_value . '</p></td>';
                    $rul_levelvalues .= '<td>';
                    $rul_levelvalues .= '<p>' . __('Login URL', 'peters-login-redirect') . '<br /><input type="text" size="90" maxlength="500" name="rul_level_address" value="' . htmlspecialchars($rul_url) . '" /></p>';
                    $rul_levelvalues .= '<p>' . __('Logout URL', 'peters-login-redirect') . '<br /><input type="text" size="60" maxlength="500" name="rul_level_logout" value="' . htmlspecialchars($rul_url_logout) . '" /></p>';
                    $rul_levelvalues .= '</td>';
                    $rul_levelvalues .= '<td><p><input name="rul_level_order" type="text" size="2" maxlength="2" value="' . $rul_order . '" /></td>';
                    $rul_levelvalues .= '<td><p>';
                    $rul_levelvalues .= '<input class="button button-primary" name="rul_level_edit" type="submit" value="' . __('Edit', 'peters-login-redirect') . '" /> <input type="submit" class="button" name="rul_level_delete" value="' . __('Delete', 'peters-login-redirect') . '" />';
                    $rul_levelvalues .= wp_nonce_field('rul_level_edit', '_wpnonce', true, false);
                    $rul_levelvalues .= '</p></td>';
                    $rul_levelvalues .= '</tr>';
                    $rul_levelvalues .= '</form>';

                    $rul_levels_existing[$rul_value] = '';

                    ++$i_level;
                } elseif ($rul_type == 'all') {
                    $rul_allvalue        = $rul_url;
                    $rul_allvalue_logout = $rul_url_logout;
                } elseif ($rul_type == 'register') {
                    $rul_registervalue = $rul_url;
                }
                ++$i;
            }

        }
        ?>
        <div class="wrap">
            <h2><?php _e('Manage redirect rules', 'peters-login-redirect'); ?></h2>
            <?php print $rul_process_submit; ?>
            <p><?php _e('Define custom URLs to which different users, users with specific roles, users with specific levels, and all other users will be redirected upon login.', 'peters-login-redirect'); ?></p>
            <p><?php _e('Define a custom URL to which all users will be redirected upon logout', 'peters-login-redirect'); ?></p>
            <p><?php _e('Note that you can use the syntax <strong>[variable]username[/variable]</strong> in your URLs so that the system will build a dynamic URL upon each login, replacing that text with the users username.', 'peters-login-redirect'); ?></p>

            <h3><?php _e('Specific users', 'peters-login-redirect'); ?></h3>
            <?php
            if ($rul_usernamevalues) {
                print '<table class="widefat">';
                print $rul_usernamevalues;
                print '</table>';
            }
            ?>

            <form name="rul_username_add_form" action="<?php print '?page=' . basename(__FILE__); ?>" method="post">
                <p><?php _e('Add:', 'peters-login-redirect'); ?>
                    <select name="rul_username">
                        <option value="-1"><?php _e('Select a username', 'peters-login-redirect'); ?></option>
                        <?php print rul_returnusernames($rul_usernames_existing); ?>
                    </select>
                    <br/><?php _e('URL:', 'peters-login-redirect'); ?>
                    <input type="text" size="90" maxlength="500" name="rul_username_address"/>
                    <br/><?php _e('Logout URL:', 'peters-login-redirect'); ?>
                    <input type="text" size="90" maxlength="500" name="rul_username_logout"/>
                </p>
                <p class="submit">
                    <input type="submit" class="button button-primary" name="rul_username_submit" value="<?php _e('Add username rule', 'peters-login-redirect'); ?>"/>
                    <?php wp_nonce_field('rul_user_submit'); ?>
                </p>
            </form>

            <h3><?php _e('Specific roles', 'peters-login-redirect'); ?></h3>
            <?php
            if ($rul_rolevalues) {
                print '<table class="widefat">';
                print $rul_rolevalues;
                print '</table>';
            }
            ?>

            <form name="rul_role_add_form" action="<?php print '?page=' . basename(__FILE__); ?>" method="post">
                <p><?php _e('Add:', 'peters-login-redirect'); ?>
                    <select name="rul_role">
                        <option value="-1"><?php _e('Select a role', 'peters-login-redirect'); ?></option>
                        <?php print rul_returnroleoptions($rul_roles_existing); ?>
                    </select>
                    <br/><?php _e('URL:', 'peters-login-redirect'); ?>
                    <input type="text" size="90" maxlength="500" name="rul_role_address"/>
                    <br/><?php _e('Logout URL:', 'peters-login-redirect'); ?>
                    <input type="text" size="90" maxlength="500" name="rul_role_logout"/>
                </p>
                <p class="submit">
                    <input type="submit" class="button button-primary" name="rul_role_submit" value="<?php _e('Add role rule', 'peters-login-redirect'); ?>"/>
                    <?php wp_nonce_field('rul_role_submit'); ?>
                </p>
            </form>

            <h3><?php _e('Specific levels', 'peters-login-redirect'); ?></h3>
            <?php
            if ($rul_levelvalues) {
                print '<table class="widefat">';
                ?>
                <tr>
                    <th></th>
                    <th></th>
                    <th><?php _e('Order', 'peters-login-redirect'); ?></th>
                    <th></th>
                </tr>
                <?php
                print $rul_levelvalues;
                print '</table>';
            }
            ?>

            <form name="rul_level_add_form" action="<?php print '?page=' . basename(__FILE__); ?>" method="post">
                <p><?php _e('Add:', 'peters-login-redirect'); ?>
                    <select name="rul_level">
                        <option value="-1"><?php _e('Select a level', 'peters-login-redirect'); ?></option>
                        <?php print rul_returnleveloptions($rul_levels_existing); ?>
                    </select>
                    <br/><?php _e('Order:', 'peters-login-redirect'); ?>
                    <input type="text" size="2" maxlength="2" name="rul_level_order"/>
                    <br/><?php _e('URL:', 'peters-login-redirect'); ?>
                    <input type="text" size="90" maxlength="500" name="rul_level_address"/>
                    <br/><?php _e('Logout URL:', 'peters-login-redirect'); ?>
                    <input type="text" size="90" maxlength="500" name="rul_level_logout"/>
                </p>
                <p class="submit">
                    <input type="submit" class="button button-primary" name="rul_level_submit" value="<?php _e('Add level rule', 'peters-login-redirect'); ?>"/>
                    <?php wp_nonce_field('rul_level_submit'); ?>
                </p>
            </form>

            <h3><?php _e('All other users', 'peters-login-redirect'); ?></h3>
            <form name="rul_allform" method="post">
                <p><?php _e('URL:', 'peters-login-redirect') ?>
                    <input type="text" size="90" maxlength="500" name="rul_all" value="<?php print htmlspecialchars($rul_allvalue); ?>"/>
                </p>
                <p><?php _e('Logout URL:', 'peters-login-redirect') ?>
                    <input type="text" size="90" maxlength="500" name="rul_all_logout" value="<?php print htmlspecialchars($rul_allvalue_logout); ?>"/>
                </p>
                <p class="submit">
                    <input type="submit" class="button button-primary" name="rul_allupdatesubmit" value="<?php _e('Update', 'peters-login-redirect'); ?>"/>
                    <input type="submit" class="button" name="rul_alldeletesubmit" value="<?php _e('Delete', 'peters-login-redirect'); ?>"/>
                    <?php wp_nonce_field('rul_allupdatesubmit'); ?>
                </p>
            </form>

            <hr/>

            <h3><?php _e('Post-registration', 'peters-login-redirect'); ?></h3>
            <form name="rul_registerform" method="post">
                <p><?php _e('URL:', 'peters-login-redirect') ?>
                    <input type="text" size="90" maxlength="500" name="rul_register" value="<?php print htmlspecialchars($rul_registervalue); ?>"/>
                </p>
                <p class="submit">
                    <input type="submit" class="button button-primary" name="rul_registerupdatesubmit" value="<?php _e('Update', 'peters-login-redirect'); ?>"/>
                    <input type="submit" class="button" name="rul_registerdeletesubmit" value="<?php _e('Delete', 'peters-login-redirect'); ?>"/>
                    <?php wp_nonce_field('rul_registerupdatesubmit'); ?>
                </p>
            </form>

            <hr/>

            <h3><?php _e('Customize plugin settings', 'peters-login-redirect'); ?></h3>
            <form name="rul_settingsform" method="post">
                <table class="widefat">
                    <tr>
                        <td>
                            <p>
                                <strong><?php _e('Allow a POST or GET &#34;redirect_to&#34; variable to take redirect precedence', 'peters-login-redirect'); ?></strong>
                            </p>
                        </td>
                        <td>
                            <select name="rul_allow_post_redirect_override">
                                <option value="1"<?php if ($rul_settings['rul_allow_post_redirect_override']) print ' selected="selected"'; ?>><?php _e('Yes', 'peters-login-redirect'); ?></option>
                                <option value="0"<?php if ( ! $rul_settings['rul_allow_post_redirect_override']) print ' selected="selected"'; ?>><?php _e('No', 'peters-login-redirect'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>
                                <strong><?php _e('Allow a POST or GET &#34;redirect_to&#34; logout variable to take redirect precedence', 'peters-login-redirect'); ?></strong>
                            </p>
                        </td>
                        <td>
                            <select name="rul_allow_post_redirect_override_logout">
                                <option value="1"<?php if ($rul_settings['rul_allow_post_redirect_override_logout']) print ' selected="selected"'; ?>><?php _e('Yes', 'peters-login-redirect'); ?></option>
                                <option value="0"<?php if ( ! $rul_settings['rul_allow_post_redirect_override_logout']) print ' selected="selected"'; ?>><?php _e('No', 'peters-login-redirect'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input class="button button-primary" name="rul_settingssubmit" type="submit" value="<?php _e('Update', 'peters-login-redirect'); ?>"/>
                    <?php wp_nonce_field('settings'); ?>
                </p>
            </form>
        </div>
        <?php
    } // close rul_optionsmenu()

    /*
        Add and remove database tables when installing and uninstalling
    */

    // Perform upgrade functions
    // Some newer operations are duplicated from rul_install() as there's no guarantee that the user will follow a specific upgrade procedure
    function rul_upgrade()
    {
        global $wpdb, $rul_version, $rul_db_addresses;

        // Turn version into an integer for comparisons
        $current_version = intval(str_replace('.', '', get_option('rul_version')));

        if ($current_version < 220) {
            $wpdb->query("ALTER TABLE `$rul_db_addresses` ADD `rul_url_logout` LONGTEXT NOT NULL default '' AFTER `rul_url`");
        }

        if ($current_version < 250) {
            // Insert the "on-register" redirect entry

            $wpdb->query("ALTER TABLE `$rul_db_addresses` CHANGE `rul_type` `rul_type` ENUM( 'user', 'role', 'level', 'all', 'register' ) NOT NULL");
            $wpdb->insert($rul_db_addresses,
                array('rul_type' => 'register')
            );
        }

        if ($current_version < 253) {
            // Allow NULL values for non-essential fields
            $wpdb->query("ALTER TABLE `$rul_db_addresses` CHANGE `rul_value` `rul_value` varchar(255) NULL default NULL");
            $wpdb->query("ALTER TABLE `$rul_db_addresses` CHANGE `rul_url` `rul_url` LONGTEXT NULL default NULL");
            $wpdb->query("ALTER TABLE `$rul_db_addresses` CHANGE `rul_url_logout` `rul_url_logout` LONGTEXT NULL default NULL");
        }

        if ($current_version < 291) {
            // Reduce size of rul_value field to support utf8mb4 character encoding
            $wpdb->query("ALTER TABLE `$rul_db_addresses` CHANGE `rul_value` `rul_value` varchar(191) NULL default NULL");
        }

        if ($current_version != intval(str_replace('.', '', $rul_version))) {
            // Add the version number to the database
            delete_option('rul_version');
            add_option('rul_version', $rul_version, '', 'no');
        }
    }

    function rul_install()
    {
        global $wpdb, $rul_db_addresses, $rul_version;

        // Add the table to hold group information and moderator rules
        if ($rul_db_addresses != $wpdb->get_var("SHOW TABLES LIKE '$rul_db_addresses'")) {
            $sql = "CREATE TABLE $rul_db_addresses (
            `rul_type` enum('user','role','level','all','register') NOT NULL,
            `rul_value` varchar(191) NULL default NULL,
            `rul_url` LONGTEXT NULL default NULL,
            `rul_url_logout` LONGTEXT NULL default NULL,
            `rul_order` int(2) NOT NULL default '0',
            UNIQUE KEY `rul_type` (`rul_type`,`rul_value`)
            )";

            $wpdb->query($sql);

            // Insert the "all" redirect entry
            $wpdb->insert($rul_db_addresses,
                array('rul_type' => 'all')
            );

            // Insert the "on-register" redirect entry
            $wpdb->insert($rul_db_addresses,
                array('rul_type' => 'register')
            );

            // Set the version number in the database
            add_option('rul_version', $rul_version, '', 'no');
        }

        rul_upgrade();
    }

    function rul_uninstall()
    {
        global $wpdb, $rul_db_addresses;

        // Remove the table we created
        if ($rul_db_addresses == $wpdb->get_var('SHOW TABLES LIKE \'' . $rul_db_addresses . '\'')) {
            $sql = 'DROP TABLE ' . $rul_db_addresses;
            $wpdb->query($sql);
        }

        delete_option('rul_version');
        delete_option('rul_settings');
    }

    function rul_addoptionsmenu()
    {
        add_options_page('Login/logout redirects', 'Login/logout redirects', 'manage_categories', 'wplogin_redirect.php', 'rul_optionsmenu');
    }

    add_action('admin_menu', 'rul_addoptionsmenu', 1);
}

register_activation_hook(__FILE__, 'rul_install');
register_uninstall_hook(__FILE__, 'rul_uninstall');


add_filter('login_redirect', 'peters_redirect_wrapper', 999999999, 3);
add_filter('registration_redirect', array('rulRedirectPostRegistration', 'post_registration_wrapper'), 10, 2);
// whichever one works. directly below might not work if wp_logout function is redefined before ours kicks in.
add_action('wp_logout', array('rulLogoutFunctionCollection', 'logout_redirect'), 1);
add_filter('logout_redirect', array('rulLogoutFunctionCollection', 'logout_redirect_2'), 999999999, 3);