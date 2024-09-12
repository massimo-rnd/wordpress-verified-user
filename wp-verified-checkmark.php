<?php
/*
Plugin Name: WP Verified Checkmark
Plugin URI: https://github.com/druffko/wordpress-verified-user
Description: Adds a verified checkmark next to users' names based on their role with a "Verified" tooltip on hover.
Version: 1.0
Requires at least: 4.8
Tested up to: 6.6.1
Requires PHP: 5.6
Author: druffko
Author URI: https://druffko.gg
License: MIT

Copyright (c) 2024 druffko. All rights reserved.
*/

if (!defined('ABSPATH')) {
    exit;
}

class WP_Verified_Checkmark {
    public function __construct() {
        // Hook to display the checkmark in both author display methods
        add_filter('the_author', [$this, 'add_verified_checkmark'], 10, 1);
        add_filter('get_the_author_display_name', [$this, 'add_verified_checkmark'], 10, 1);

        // Also apply for comment authors
        add_filter('get_comment_author', [$this, 'add_verified_checkmark'], 10, 1);

        // Enqueue Font Awesome in both front-end and admin dashboard
        add_action('wp_enqueue_scripts', [$this, 'enqueue_font_awesome']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_font_awesome']);

        // Create admin menu
        add_action('admin_menu', [$this, 'create_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    // Enqueue Font Awesome
    public function enqueue_font_awesome() {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', [], '6.0.0-beta3');
    }

    // Function to add checkmark next to user name with a "Verified" tooltip
    public function add_verified_checkmark($author_name) {
        if (is_admin()) {
            return $author_name; // Don't add checkmark in the admin area
        }

        // Try to get the user by their login name
        $user = get_user_by('login', $author_name);
        if (!$user) {
            // If no user found by login, try by display name (used in some themes)
            $user = get_user_by('slug', sanitize_title($author_name));
        }

        if ($user) {
            $selected_role = get_option('verified_role_option');
            if (in_array($selected_role, (array) $user->roles)) {
                // Adding checkmark with a tooltip on hover
                $author_name .= ' <i class="fa-solid fa-circle-check" title="Verified"></i>';
            }
        }
        return $author_name;
    }

    // Create admin menu for plugin settings
    public function create_admin_menu() {
        add_options_page(
            'Verified Checkmark Settings',
            'Verified Checkmark',
            'manage_options',
            'wp-verified-checkmark',
            [$this, 'settings_page']
        );
    }

    // Register settings
    public function register_settings() {
        register_setting('wp_verified_checkmark_settings', 'verified_role_option');
    }

    // Admin settings page
    public function settings_page() {
        $roles = wp_roles()->roles;
        ?>
        <div class="wrap">
            <h1>Verified Checkmark Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('wp_verified_checkmark_settings'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Select Role for Verified Checkmark:</th>
                        <td>
                            <select name="verified_role_option">
                                <?php foreach ($roles as $role_slug => $role): ?>
                                    <option value="<?php echo esc_attr($role_slug); ?>" <?php selected(get_option('verified_role_option'), $role_slug); ?>>
                                        <?php echo esc_html($role['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

new WP_Verified_Checkmark();
