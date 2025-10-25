<?php
/**
 * Plugin Name: Business Location Finder
 * Plugin URI: https://indexwebmedia.com/
 * 
 * Description: A dynamic, interactive business location finder
 * with tabbed interface, search functionality, and shortcode support.
 * 
 * Version: 2.1.5
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
 * @CVN       2.1.5
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
define('BLF_VERSION', '2.1.5');

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

/* Initialize the plugin */
$blf_plugin = new BizLocationFinder();