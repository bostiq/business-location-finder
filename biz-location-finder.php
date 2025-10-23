<?php
/**
 * Plugin Name: Business Location Finder
 * Plugin URI: https://indexwebmedia.com/
 * 
 * Description: A dynamic, interactive business location finder
 * with tabbed interface, search functionality, and shortcode support.
 * 
 * Version: 2.0.3
 * Author: Lorenzo Colen
 * Author URI: https://indexwebmedia.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: biz-location-finder
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 * Update URI: https://indexwebmedia.com/
 *
 * @category  WordPress_Plugin
 * @package   BizLocationFinder
 * @author    Lorenzo Colen <info@indexwebmedia.com>
 * @copyright 2025 Index Web Media
 * @license   GPL-2.0-or-later <https://www.gnu.org/licenses/gpl-2.0.html>
 * @CVN       2.0.3
 * @link      https://indexwebmedia.com/
 * @tag       WordPress, plugin, business directory, location finder, shortcode
 * @since     1.0.0
 * @requires  PHP 7.4
 */

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

/* Define plugin constants */
define('BLF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BLF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BLF_VERSION', '2.0.3');

/**
 * Main Plugin Class
 */
class BizLocationFinder {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        /* Add settings link to plugin listing page */
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
    }
    
    public function init() {
        /* Load plugin files */
        $this->includes();
        
        /* Initialize components */
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('biz_location_finder', array($this, 'shortcode_handler'));
        
        /* Admin functionality */
        if (is_admin()) {
            add_action('admin_menu', array($this, 'admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
            add_action('admin_init', array($this, 'admin_init'));
        }
        
        /* REST API */
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    /**
     * Add settings link to plugin listing page
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=biz-location-finder-import') . '">' . __('Import Data') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    private function includes() {
        /* Include additional files here as we build them */
        /* require_once BLF_PLUGIN_PATH . 'includes/admin.php'; */
        /* require_once BLF_PLUGIN_PATH . 'includes/database.php'; */
    }
    
    public function enqueue_scripts() {
        /* Only enqueue if shortcode is being used */
        global $post;
        if (is_singular() && is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'biz_location_finder')) {
            wp_enqueue_style(
                'biz-location-finder-css',
                BLF_PLUGIN_URL . 'assets/css/style.min.css',
                array(),
                BLF_VERSION
            );
            
            wp_enqueue_script(
                'biz-location-finder-js',
                BLF_PLUGIN_URL . 'assets/js/stockists.js',
                array(),
                BLF_VERSION,
                true
            );
        }
    }
    
    public function admin_enqueue_scripts($hook) {
        /* Only load on our admin pages */
        if (strpos($hook, 'biz-location-finder') !== false) {
            wp_enqueue_style('wp-admin');
            wp_enqueue_script('jquery');
        }
    }
    
    public function admin_init() {
        /* Register settings */
        register_setting('blf_settings', 'blf_google_sheets_url', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://docs.google.com/spreadsheets/d/1A8W-_GwPfCWbkqzyvSRKNC2x6bTzDCwBNS24tNuKCt8/export?format=csv&gid=1952886414'
        ));
        
        /* Add settings section */
        add_settings_section(
            'blf_main_settings',
            'Google Sheets Configuration',
            array($this, 'settings_section_callback'),
            'blf_settings'
        );
        
        /* Add settings field */
        add_settings_field(
            'blf_google_sheets_url',
            'Google Sheets Export URL',
            array($this, 'google_sheets_url_callback'),
            'blf_settings',
            'blf_main_settings'
        );
    }
    
    public function settings_section_callback() {
        echo '<p>Configure the Google Sheets URL that contains your business data.</p>';
    }
    
    public function google_sheets_url_callback() {
        $url = get_option('blf_google_sheets_url', 'https://docs.google.com/spreadsheets/d/1A8W-_GwPfCWbkqzyvSRKNC2x6bTzDCwBNS24tNuKCt8/export?format=csv&gid=1952886414');
        echo '<input type="url" id="blf_google_sheets_url" name="blf_google_sheets_url" value="' . esc_attr($url) . '" class="regular-text" />';
        echo '<p class="description">Enter your Google Sheets export URL. Go to your Google Sheet → File → Share → Publish to web → CSV format.</p>';
    }
    
    public function shortcode_handler($atts) {
        /* Parse shortcode attributes with sanitization */
        $atts = shortcode_atts(array(
            'categories' => 'all',
            'search' => 'true',
            'counters' => 'true',
            'view' => 'default'
        ), $atts, 'biz_location_finder');
        
        /* Sanitize attributes */
        $atts['categories'] = sanitize_text_field($atts['categories']);
        $atts['search'] = sanitize_text_field($atts['search']) === 'true';
        $atts['counters'] = sanitize_text_field($atts['counters']) === 'true';
        $atts['view'] = sanitize_text_field($atts['view']);
        
        /* Start output buffering */
        ob_start();
        
        /* Include the HTML template */
        include BLF_PLUGIN_PATH . 'templates/finder.php';
        
        return ob_get_clean();
    }
    
    public function admin_menu() {
        add_menu_page(
            'Business Location Finder',
            'Biz Finder',
            'manage_options',
            'biz-location-finder',
            array($this, 'admin_page'),
            'dashicons-location-alt',
            30
        );
        
        add_submenu_page(
            'biz-location-finder',
            'Import Data',
            'Import Data',
            'manage_options',
            'biz-location-finder-import',
            array($this, 'import_page')
        );
    }
    
    public function admin_page() {
        include BLF_PLUGIN_PATH . 'admin/admin-page.php';
    }
    
    public function import_page() {
        include BLF_PLUGIN_PATH . 'admin/import-page.php';
    }
    
    public function register_rest_routes() {
        register_rest_route('jq-stockists/v1', '/get-csv', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_stockists_csv'),
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * REST API callback - currently using Google Sheets
     * TODO: Replace with database queries
     */
    public function get_stockists_csv($request) {
        /* Add security headers */
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        
        /* Validate request (basic rate limiting could be added here) */
        if (!$request instanceof WP_REST_Request) {
            return new WP_Error('invalid_request', 'Invalid request format', array('status' => 400));
        }
        
        /* Get Google Sheets URL from WordPress options (configurable in admin) */
        $default_url = 'https://docs.google.com/spreadsheets/d/1A8W-_GwPfCWbkqzyvSRKNC2x6bTzDCwBNS24tNuKCt8/export?format=csv&gid=1952886414';
        $google_sheets_url = get_option('blf_google_sheets_url', $default_url);
        
        /* If no URL is set, return error */
        if (empty($google_sheets_url)) {
            return new WP_Error('no_url', 'No Google Sheets URL configured. Please check plugin settings.', array('status' => 404));
        }
        
        $response = wp_remote_get($google_sheets_url, array(
            'timeout' => 20,
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
        ));
        
        if (is_wp_error($response)) {
            return new WP_Error('fetch_error', 'Failed to fetch data from Google Sheets', array('status' => 500));
        }
        
        $csv_data = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200 || empty($csv_data)) {
            return new WP_Error('no_data', 'Could not retrieve data from Google Sheets', array('status' => 404));
        }
        
        /* Sanitize CSV data - remove any potential script tags or malicious content */
        $csv_data = wp_kses($csv_data, array());
        
        /* Return raw CSV data */
        return new WP_REST_Response($csv_data, 200, array(
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=300' // Cache for 5 minutes
        ));
    }
    
    public function activate() {
        /* Create database tables on activation */
        /* TODO: Implement database creation */
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
}

/* Initialize the plugin */
new BizLocationFinder();