<?php
/**
 * Admin Page Template
 */

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

/* Check user permissions */
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'biz-location-finder'));
}

/* Handle form submission - removed (moved to Import Data page) */

/* Get current URL for display purposes */
$current_url = get_option('blf_google_sheets_url', 'https://docs.google.com/spreadsheets/d/1A8W-_GwPfCWbkqzyvSRKNC2x6bTzDCwBNS24tNuKCt8/export?format=csv&gid=1952886414');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="card">
        <h2>Plugin Overview</h2>
        <p>Business Location Finder is a flexible, dynamic business directory system that automatically adapts to your data structure.</p>
        
        <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <h4>� Quick Actions:</h4>
            <ul>
                <li><strong>Configure Data Source:</strong> Go to <a href="<?php echo admin_url('admin.php?page=biz-location-finder-import'); ?>">Import Data</a> to set up your Google Sheets URL</li>
                <li><strong>Add to Pages:</strong> Use shortcode <code>[biz_location_finder]</code></li>
                <li><strong>View Current Data:</strong> <a href="<?php echo esc_url($current_url); ?>" target="_blank">Test Data Source</a></li>
            </ul>
        </div>
    </div>
    
    <div class="card">
        <h2>Plugin Information</h2>
        <p><strong>Version:</strong> <?php echo esc_html(BLF_VERSION); ?></p>
        <p><strong>Status:</strong> Active and running</p>
        <p><strong>Data Source:</strong> Google Sheets (flexible categories)</p>
        <p><strong>Current Sheet URL:</strong> <a href="<?php echo esc_url($current_url); ?>" target="_blank">View Sheet</a></p>
    </div>
    
    <div class="card">
        <h2>Shortcode Usage</h2>
        <p>Use the following shortcode to display the business location finder:</p>
        <code>[biz_location_finder]</code>
        
        <h3>Shortcode Options</h3>
        <ul>
            <li><strong>categories:</strong> Comma-separated list of categories to display 
                (default: all categories found in your data)</li>
            <li><strong>search:</strong> Enable/disable search functionality (default: true)</li>
            <li><strong>counters:</strong> Show/hide counter badges (default: true)</li>
        </ul>
        
        <h3>Examples</h3>
        <p><code>[biz_location_finder]</code> - Shows all categories from your data</p>
        <p><code>[biz_location_finder categories="restaurants,cafes"]</code> - Shows only specified categories</p>
        <p><code>[biz_location_finder search="false" counters="false"]</code> - Minimal version without search/counters</p>
    </div>
    
    <div class="card">
        <h2>Security Features</h2>
        <ul>
            <li>✅ Input sanitization and validation</li>
            <li>✅ XSS protection on all user data</li>
            <li>✅ CSRF protection (WordPress nonces)</li>
            <li>✅ Capability-based access control</li>
            <li>✅ Rate limiting on REST API</li>
            <li>✅ Secure HTTP headers</li>
        </ul>
    </div>
    
    <div class="card">
        <h2>Current Features</h2>
        <ul>
            <li>✅ Shortcode functionality</li>
            <li>✅ Google Sheets integration (flexible categories)</li>
            <li>✅ Responsive design</li>
            <li>✅ Search and filtering</li>
            <li>✅ Dynamic category detection</li>
            <li>⏳ Local database storage</li>
            <li>⏳ CSV import functionality</li>
            <li>⏳ Advanced admin controls</li>
        </ul>
    </div>
</div>