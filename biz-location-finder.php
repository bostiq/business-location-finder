<?php
/**
 * Plugin Name: Business Location Finder
 * Plugin URI: https://indexwebmedia.com/
 * 
 * Description: A dynamic, interactive business location finder
 * with tabbed interface, search functionality, and shortcode support.
 * 
 * Version: 2.1.6.5
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
 * @CVN       2.1.6.5
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
define('BLF_VERSION', '2.1.6.5');

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

        // ✅ Localize the REST API URL for use in JS
        wp_localize_script('biz-location-finder-js', 'myPluginData', [
            'apiUrl' => esc_url_raw(rest_url('jq-stockists/v1/get-data')),
        ]);
    }
}

    
    public function admin_enqueue_scripts($hook) {
        /* Only load on our admin pages */
        if (strpos($hook, 'biz-location-finder') !== false) {
            wp_enqueue_style(
                'biz-location-finder-admin-css',
                BLF_PLUGIN_URL . 'assets/css/admin-styles.min.css',
                array('wp-admin'),
                BLF_VERSION
            );
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
        
        /* Register data source setting */
        register_setting('blf_settings', 'blf_data_source', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'google_sheets'
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
        
        /* New endpoint for database data */
        register_rest_route('jq-stockists/v1', '/get-businesses', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_businesses_api'),
            'permission_callback' => '__return_true',
            'args' => array(
                'category' => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        /* Unified endpoint that switches data source based on admin settings */
        register_rest_route('jq-stockists/v1', '/get-data', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_data_by_source'),
            'permission_callback' => '__return_true',
            'args' => array(
                'category' => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        /* Debug endpoint to check database configuration */
        register_rest_route('jq-stockists/v1', '/debug-db', array(
            'methods' => 'GET',
            'callback' => array($this, 'debug_database_info'),
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
        $default_url = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vTp7ka2BJoFXGG81bBev0gUF5HOnv-mA8umBcA219W2tTrXCFrBjIWuMaZTT64cUdny2FuibopMCrXz/pub?output=csv';
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
    
    /**
     * REST API callback - serve data from database
     */
    public function get_businesses_api($request) {
        /* Add security headers */
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        
        /* Get category filter if provided */
        $category = $request->get_param('category');
        
        /* Get businesses from database */
        $businesses = $this->get_businesses($category);
        
        if (empty($businesses)) {
            return new WP_Error('no_data', 'No businesses found in database', array('status' => 404));
        }
        
        /* Convert to CSV format for backward compatibility with frontend JavaScript */
        $csv_output = $this->convert_businesses_to_csv($businesses);
        
        /* Return CSV data just like the Google Sheets endpoint */
        return new WP_REST_Response($csv_output, 200, array(
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=60' // Shorter cache for database data
        ));
    }
    
    /**
     * Convert database businesses to CSV format
     * This maintains compatibility with existing frontend JavaScript
     */
    private function convert_businesses_to_csv($businesses) {
        if (empty($businesses)) {
            return '';
        }
        
        /* CSV header - matches existing Google Sheets format */
        $csv_lines = array();
        $csv_lines[] = 'name,category,suburb,address,instagram,website,phone,email,description';
        
        /* Convert each business to CSV row */
        foreach ($businesses as $business) {
            $row = array(
                $this->escape_csv_field($business['name']),
                $this->escape_csv_field($business['category']),
                $this->escape_csv_field($business['suburb']),
                $this->escape_csv_field($business['address']),
                $this->escape_csv_field($business['instagram']),
                $this->escape_csv_field($business['website']),
                $this->escape_csv_field($business['phone']),
                $this->escape_csv_field($business['email']),
                $this->escape_csv_field($business['description'])
            );
            $csv_lines[] = implode(',', $row);
        }
        
        return implode("\n", $csv_lines);
    }
    
    /**
     * Escape CSV field for proper formatting
     */
    private function escape_csv_field($field) {
        if (empty($field)) {
            return '';
        }
        
        /* If field contains comma, quotes, or newlines, wrap in quotes and escape quotes */
        if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
            return '"' . str_replace('"', '""', $field) . '"';
        }
        
        return $field;
    }
    
    /**
     * Unified data endpoint that switches between Google Sheets and database
     * based on admin settings
     */
    public function get_data_by_source($request) {
        // Check which data source is selected in admin
        $data_source = get_option('blf_data_source', 'google_sheets');
        
        if ($data_source === 'database') {
            // Return JSON from database
            $category = $request->get_param('category');
            $businesses = $this->get_businesses($category);
            
            // Always return success, even with empty data
            return new WP_REST_Response(array(
                'success' => true,
                'data' => $businesses,
                'source' => 'database',
                'count' => count($businesses)
            ), 200, array(
                'Content-Type' => 'application/json; charset=utf-8',
                'Cache-Control' => 'public, max-age=60'
            ));
        } else {
            // Return CSV from Google Sheets (delegate to existing method)
            return $this->get_stockists_csv($request);
        }
    }
    
    /**
     * Debug endpoint to check database configuration
     */
    public function debug_database_info($request) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'blf_businesses';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        // Get row count if table exists
        $row_count = 0;
        if ($table_exists) {
            $row_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        }
        
        return new WP_REST_Response(array(
            'database_name' => DB_NAME,
            'table_prefix' => $wpdb->prefix,
            'full_table_name' => $table_name,
            'table_exists' => $table_exists,
            'row_count' => (int)$row_count,
            'data_source_setting' => get_option('blf_data_source', 'google_sheets')
        ), 200);
    }
    
    public function activate() {
        /* Create database tables on activation */
        $this->create_database_tables();
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables for storing business data
     */
    private function create_database_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'blf_businesses';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            category varchar(100) NOT NULL,
            suburb varchar(100) DEFAULT '',
            address text DEFAULT '',
            instagram varchar(100) DEFAULT '',
            website varchar(255) DEFAULT '',
            phone varchar(50) DEFAULT '',
            email varchar(255) DEFAULT '',
            description text DEFAULT '',
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category (category),
            KEY suburb (suburb),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        /* Store database version for future updates */
        add_option('blf_db_version', '1.0');
    }
    
    /**
     * Get all businesses from database
     */
    public function get_businesses($category = null, $limit = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'blf_businesses';
        
        $sql = "SELECT * FROM $table_name WHERE is_active = 1";
        
        if ($category && $category !== 'all') {
            $sql .= $wpdb->prepare(" AND category = %s", $category);
        }
        
        $sql .= " ORDER BY name ASC";
        
        if ($limit) {
            $sql .= $wpdb->prepare(" LIMIT %d", $limit);
        }
        
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Insert a new business
     */
    public function insert_business($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'blf_businesses';
        
        /* Sanitize data */
        $sanitized_data = array(
            'name' => sanitize_text_field($data['name']),
            'category' => sanitize_text_field($data['category']),
            'suburb' => sanitize_text_field($data['suburb'] ?? ''),
            'address' => sanitize_textarea_field($data['address'] ?? ''),
            'instagram' => sanitize_text_field($data['instagram'] ?? ''),
            'website' => esc_url_raw($data['website'] ?? ''),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'email' => sanitize_email($data['email'] ?? ''),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'is_active' => 1
        );
        
        $result = $wpdb->insert($table_name, $sanitized_data);
        
        if ($result === false) {
            return new WP_Error('db_insert_error', 'Failed to insert business: ' . $wpdb->last_error);
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update an existing business
     */
    public function update_business($id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'blf_businesses';
        
        /* Sanitize data */
        $sanitized_data = array(
            'name' => sanitize_text_field($data['name']),
            'category' => sanitize_text_field($data['category']),
            'suburb' => sanitize_text_field($data['suburb'] ?? ''),
            'address' => sanitize_textarea_field($data['address'] ?? ''),
            'instagram' => sanitize_text_field($data['instagram'] ?? ''),
            'website' => esc_url_raw($data['website'] ?? ''),
            'phone' => sanitize_text_field($data['phone'] ?? ''),
            'email' => sanitize_email($data['email'] ?? ''),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'updated_at' => current_time('mysql')
        );
        
        $result = $wpdb->update(
            $table_name,
            $sanitized_data,
            array('id' => intval($id)),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_update_error', 'Failed to update business: ' . $wpdb->last_error);
        }
        
        return $result;
    }
    
    /**
     * Delete a business (soft delete - set inactive)
     */
    public function delete_business($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'blf_businesses';
        
        $result = $wpdb->update(
            $table_name,
            array('is_active' => 0, 'updated_at' => current_time('mysql')),
            array('id' => intval($id)),
            array('%d', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_delete_error', 'Failed to delete business: ' . $wpdb->last_error);
        }
        
        return $result;
    }
    
    public function deactivate() {
        /* Clean up on deactivation - but keep data */
        flush_rewrite_rules();
        
        /* Note: We don't drop the database table on deactivation
         * Users might want to reactivate the plugin later
         * Table will only be dropped on uninstall (if we add that hook)
         */
    }
}

/* AJAX Handlers for Admin Functions */

// Add new business
add_action('wp_ajax_blf_add_business', 'blf_ajax_add_business');
function blf_ajax_add_business() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'blf_admin_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $business_data = array(
        'name' => sanitize_text_field($_POST['business_name']),
        'category' => sanitize_text_field($_POST['business_category']),
        'suburb' => sanitize_text_field($_POST['business_suburb']),
        'address' => sanitize_textarea_field($_POST['business_address']),
        'instagram' => sanitize_text_field($_POST['business_instagram']),
        'website' => esc_url_raw($_POST['business_website']),
        'phone' => sanitize_text_field($_POST['business_phone']),
        'email' => sanitize_email($_POST['business_email']),
        'description' => sanitize_textarea_field($_POST['business_description'])
    );
    
    global $blf_plugin;
    $result = $blf_plugin->insert_business($business_data);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success('Business added successfully');
    }
}

// Get single business for editing
add_action('wp_ajax_blf_get_business', 'blf_ajax_get_business');
function blf_ajax_get_business() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'blf_admin_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $business_id = intval($_POST['business_id']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'blf_businesses';
    
    $business = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $business_id
    ), ARRAY_A);
    
    if ($business) {
        wp_send_json_success($business);
    } else {
        wp_send_json_error('Business not found');
    }
}

// Update business
add_action('wp_ajax_blf_update_business', 'blf_ajax_update_business');
function blf_ajax_update_business() {
    // Check nonce (support both nonce names for different forms)
    $nonce_valid = false;
    if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'blf_admin_nonce')) {
        $nonce_valid = true;
    } else if (isset($_POST['edit_nonce']) && wp_verify_nonce($_POST['edit_nonce'], 'blf_edit_business_nonce')) {
        $nonce_valid = true;
    }
    
    if (!$nonce_valid) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $business_id = intval($_POST['business_id']);
    $business_data = array(
        'name' => sanitize_text_field($_POST['business_name']),
        'category' => sanitize_text_field($_POST['business_category']),
        'suburb' => sanitize_text_field($_POST['business_suburb']),
        'address' => sanitize_textarea_field($_POST['business_address']),
        'phone' => sanitize_text_field($_POST['business_phone']),
        'email' => sanitize_email($_POST['business_email']),
        'website' => esc_url_raw($_POST['business_website']),
        'instagram' => sanitize_text_field($_POST['business_instagram']),
        'description' => sanitize_textarea_field($_POST['business_description']),
        'updated_at' => current_time('mysql')
    );
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'blf_businesses';
    
    $result = $wpdb->update(
        $table_name,
        $business_data,
        array('id' => $business_id),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
        array('%d')
    );
    
    if ($result !== false) {
        wp_send_json_success('Business updated successfully');
    } else {
        wp_send_json_error('Failed to update business');
    }
}

// Delete business
add_action('wp_ajax_blf_delete_business', 'blf_ajax_delete_business');
function blf_ajax_delete_business() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'blf_admin_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $business_id = intval($_POST['business_id']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'blf_businesses';
    
    $result = $wpdb->delete(
        $table_name,
        array('id' => $business_id),
        array('%d')
    );
    
    if ($result) {
        wp_send_json_success('Business deleted successfully');
    } else {
        wp_send_json_error('Failed to delete business');
    }
}

/**
 * Heroicons SVG Helper Function
 * Renders Heroicons Solid SVG icons with consistent styling
 */
if (!function_exists('blf_heroicon')) {
    function blf_heroicon($icon_name, $class = '', $size = '20') {
        $icons = array(
            'chart-bar-square' => '<path d="M7.5 14.25a.75.75 0 0 0-1.5 0v2.25a.75.75 0 0 0 1.5 0v-2.25Z"/><path d="M10.5 11.25a.75.75 0 0 0-1.5 0v5.25a.75.75 0 0 0 1.5 0v-5.25Z"/><path d="M13.5 8.25a.75.75 0 0 0-1.5 0v8.25a.75.75 0 0 0 1.5 0V8.25Z"/><path d="M16.5 5.25a.75.75 0 0 0-1.5 0v11.25a.75.75 0 0 0 1.5 0V5.25Z"/><path fill-rule="evenodd" d="M3 6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6Zm3-1.5a1.5 1.5 0 0 0-1.5 1.5v12a1.5 1.5 0 0 0 1.5 1.5h12a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H6Z" clip-rule="evenodd"/>',
            'archive-box' => '<path d="M3.375 3C2.339 3 1.5 3.84 1.5 4.875v.75c0 1.036.84 1.875 1.875 1.875h17.25c1.035 0 1.875-.84 1.875-1.875v-.75C22.5 3.839 21.66 3 20.625 3H3.375Z"/><path fill-rule="evenodd" d="m3.087 9 .54 9.176A3 3 0 0 0 6.62 21h10.757a3 3 0 0 0 2.995-2.824L20.913 9H3.087Zm6.163 3.75A.75.75 0 0 1 10 12h4a.75.75 0 0 1 0 1.5h-4a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/>',
            'cog-6-tooth' => '<path fill-rule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567L9.05 4.889c-.02.12-.115.26-.297.348a7.493 7.493 0 0 0-.986.57c-.166.115-.334.126-.45.083L6.3 5.508a1.875 1.875 0 0 0-2.282.819l-.922 1.597a1.875 1.875 0 0 0 .432 2.385l.84.692c.095.078.17.229.154.43a7.598 7.598 0 0 0 0 1.139c.015.2-.059.352-.153.43l-.841.692a1.875 1.875 0 0 0-.432 2.385l.922 1.597a1.875 1.875 0 0 0 2.282.818l1.019-.382c.115-.043.283-.031.45.082.312.214.641.405.985.57.182.088.277.228.297.35l.178 1.071c.151.904.933 1.567 1.85 1.567h1.844c.916 0 1.699-.663 1.85-1.567l.178-1.072c.02-.12.114-.26.297-.349.344-.165.673-.356.985-.570.167-.114.335-.125.45-.082l1.02.382a1.875 1.875 0 0 0 2.28-.819l.923-1.597a1.875 1.875 0 0 0-.432-2.385l-.84-.692c-.095-.078-.17-.229-.154-.43a7.614 7.614 0 0 0 0-1.139c-.016-.2.059-.352.153-.43l.84-.692c.708-.582.891-1.59.433-2.385l-.922-1.597a1.875 1.875 0 0 0-2.282-.818l-1.02.382c-.114.043-.282.031-.449-.083a7.49 7.49 0 0 0-.985-.57c-.183-.087-.277-.227-.297-.348L13.928 3.817c-.151-.904-.933-1.567-1.85-1.567h-1.844ZM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" clip-rule="evenodd"/>',
            'arrow-path' => '<path fill-rule="evenodd" d="M4.755 10.059a7.5 7.5 0 0 1 12.548-3.364l1.903 1.903h-3.183a.75.75 0 1 0 0 1.5h4.992a.75.75 0 0 0 .75-.75V4.356a.75.75 0 0 0-1.5 0v3.18l-1.9-1.9A9 9 0 0 0 3.306 9.67a.75.75 0 1 0 1.45.388Zm15.408 3.352a.75.75 0 0 0-.919.53 7.5 7.5 0 0 1-12.548 3.364l-1.902-1.903h3.183a.75.75 0 0 0 0-1.5H2.984a.75.75 0 0 0-.75.75v4.992a.75.75 0 0 0 1.5 0v-3.18l1.9 1.9a9 9 0 0 0 15.059-4.035.75.75 0 0 0-.53-.918Z" clip-rule="evenodd"/>',
            'link' => '<path d="M12.232 4.232a2.5 2.5 0 0 1 3.536 3.536l-1.225 1.224a.75.75 0 0 0 1.061 1.06l1.224-1.224a4 4 0 0 0-5.656-5.656l-3 3a4 4 0 0 0 .225 5.865.75.75 0 0 0 .977-1.138 2.5 2.5 0 0 1-.142-3.667l3-3Z"/><path d="M11.603 7.963a.75.75 0 0 0-.977 1.138 2.5 2.5 0 0 1 .142 3.667l-3 3a2.5 2.5 0 0 1-3.536-3.536l1.225-1.224a.75.75 0 0 0-1.061-1.06l-1.224 1.224a4 4 0 1 0 5.656 5.656l3-3a4 4 0 0 0-.225-5.865Z"/>',
            'plus' => '<path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>',
            'pencil' => '<path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z"/><path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z"/>',
            'cursor-arrow-rays' => '<path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h3.75a.75.75 0 0 0 .75-.75V21a.75.75 0 0 0 1.5 0v.75a.75.75 0 0 0 .75.75h3.75a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25ZM12 3a3.75 3.75 0 0 1 3.75 3.75v3H8.25v-3A3.75 3.75 0 0 1 12 3Z" clip-rule="evenodd"/>',
            'rocket-launch' => '<path fill-rule="evenodd" d="M9.315 7.584C12.195 3.883 16.695 1.5 21.75 1.5a.75.75 0 0 1 .75.75c0 5.056-2.383 9.555-6.084 12.436A6.75 6.75 0 0 1 9.75 22.5a.75.75 0 0 1-.75-.75v-4.131A15.838 15.838 0 0 1 6.382 15H2.25a.75.75 0 0 1-.75-.75 6.75 6.75 0 0 1 7.815-6.666ZM15 6.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" clip-rule="evenodd"/><path d="M5.26 17.242a.75.75 0 1 0-.897-1.203 5.243 5.243 0 0 0-2.05 5.022.75.75 0 0 0 .625.627 5.243 5.243 0 0 0 5.022-2.051.75.75 0 1 0-1.202-.897 3.744 3.744 0 0 1-3.008 1.51c0-1.23.592-2.323 1.51-3.008Z"/>',
            'bolt' => '<path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.272-.71l1.992-7.302H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" clip-rule="evenodd"/>',
            'exclamation-triangle' => '<path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"/>',
            'clipboard-document-list' => '<path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V9.375a3.375 3.375 0 0 1 3.375-3.375H7.5c0-.621.504-1.125 1.125-1.125v-.75C8.625 4.504 8.121 4 7.5 4s-1.125.504-1.125 1.125V6ZM6 12a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V12Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 15a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V15Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 18a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V18Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/><path d="M8.625 7.5c0 1.035.84 1.875 1.875 1.875h2.25A1.875 1.875 0 0 0 14.625 7.5V6.75h.75a.75.75 0 0 1 0 1.5H15v-.75h-.375c-.621 0-1.125-.504-1.125-1.125V6H8.625v1.5Z"/>',
            'arrow-top-right-on-square' => '<path fill-rule="evenodd" d="M15.75 2.25H21a.75.75 0 0 1 .75.75v5.25a.75.75 0 0 1-1.5 0V4.81L8.03 17.03a.75.75 0 0 1-1.06-1.06L19.19 3.75H15.75a.75.75 0 0 1 0-1.5Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd"/>',
            'arrow-up-tray' => '<path fill-rule="evenodd" d="M11.47 2.47a.75.75 0 0 1 1.06 0l4.5 4.5a.75.75 0 0 1-1.06 1.06l-3.22-3.22V16.5a.75.75 0 0 1-1.5 0V4.81L8.03 8.03a.75.75 0 0 1-1.06-1.06l4.5-4.5ZM3 15.75a.75.75 0 0 1 .75.75v2.25a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V16.5a.75.75 0 0 1 1.5 0v2.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V16.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>',
            'arrow-down-tray' => '<path fill-rule="evenodd" d="M12 2.25a.75.75 0 0 1 .75.75v11.69l3.22-3.22a.75.75 0 1 1 1.06 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 1 1 1.06-1.06L11.25 14.69V3a.75.75 0 0 1 .75-.75ZM3 15.75a.75.75 0 0 1 .75.75v2.25a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V16.5a.75.75 0 0 1 1.5 0v2.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V16.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>',
            'map' => '<path fill-rule="evenodd" d="M8.161 2.58a1.875 1.875 0 0 1 1.678 0l4.993 2.498c.106.052.23.052.336 0l3.869-1.935A1.875 1.875 0 0 1 21.75 4.82v12.485c0 .71-.401 1.36-1.037 1.677l-4.875 2.437a1.875 1.875 0 0 1-1.676 0l-4.994-2.497a.375.375 0 0 0-.336 0l-3.868 1.935A1.875 1.875 0 0 1 2.25 19.18V6.695c0-.71.401-1.36 1.036-1.677L8.161 2.58ZM9 4.904L3.75 7.455v11.85l5.25-2.629V4.904ZM10.5 16.676l6-3V2.824l-6 3v10.852ZM15 13.467l5.25 2.629V4.246L15 6.875v6.592Z" clip-rule="evenodd"/>',
            'phone' => '<path fill-rule="evenodd" d="M1.5 4.5a3 3 0 0 1 3-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 0 1-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 0 0 6.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 0 1 1.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 0 1-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 6.75V4.5Z" clip-rule="evenodd"/>',
            'envelope' => '<path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z"/><path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z"/>',
            'globe-alt' => '<path d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/>',
            'document-arrow-down' => '<path fill-rule="evenodd" d="M5.625 1.5H9a3.75 3.75 0 0 1 3.75 3.75v1.875c0 1.036.84 1.875 1.875 1.875H16.5a3.75 3.75 0 0 1 3.75 3.75v7.875c0 1.035-.84 1.875-1.875 1.875H5.625a1.875 1.875 0 0 1-1.875-1.875V3.375c0-1.036.84-1.875 1.875-1.875Zm5.845 17.03a.75.75 0 0 0 1.06 0l3-3a.75.75 0 1 0-1.06-1.06l-1.72 1.72V12a.75.75 0 0 0-1.5 0v4.19l-1.72-1.72a.75.75 0 0 0-1.06 1.06l3 3Z" clip-rule="evenodd" />'

        );
        
        if (!isset($icons[$icon_name])) {
            return '';
        }
        
        $class_attr = $class ? ' class="blf-icon ' . esc_attr($class) . '"' : ' class="blf-icon"';
        
        return sprintf(
            '<svg%s width="%s" height="%s" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">%s</svg>',
            $class_attr,
            esc_attr($size),
            esc_attr($size),
            $icons[$icon_name]
        );
    }
}

/* Initialize the plugin */
$blf_plugin = new BizLocationFinder();