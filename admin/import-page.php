<?php
/**
 * Import Page Template
 */

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

/* Check user permissions */
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'biz-location-finder'));
}

/* Handle form submission */
if (isset($_POST['submit']) && check_admin_referer('blf_settings_nonce')) {
    $new_url = sanitize_text_field($_POST['blf_google_sheets_url']);
    
    /* Test the URL to see if it returns CSV data */
    $test_response = wp_remote_get($new_url, array('timeout' => 10));
    $test_data = wp_remote_retrieve_body($test_response);
    
    if (is_wp_error($test_response)) {
        echo '<div class="notice notice-error"><p><strong>Error:</strong> Could not connect to the provided URL. Please check the URL and try again.</p></div>';
    } elseif (empty($test_data)) {
        echo '<div class="notice notice-error"><p><strong>Error:</strong> The URL returned empty data. Please verify your Google Sheets export URL.</p></div>';
    } elseif (strpos($test_data, 'var DOCS_timing') !== false || strpos($test_data, '<html') !== false) {
        echo '<div class="notice notice-error"><p><strong>Error:</strong> The URL is returning HTML instead of CSV data. Please make sure you use the "Publish to web" → "CSV" export URL, not the regular Google Sheets edit URL.</p></div>';
    } else {
        /* Check if it looks like CSV */
        $lines = explode("\n", trim($test_data));
        if (count($lines) >= 2 && strpos($lines[0], ',') !== false) {
            update_option('blf_google_sheets_url', esc_url_raw($new_url));
            echo '<div class="notice notice-success"><p><strong>Success!</strong> Settings saved and URL verified to return valid CSV data!</p></div>';
        } else {
            echo '<div class="notice notice-warning"><p><strong>Warning:</strong> URL saved, but the data doesn\'t appear to be in CSV format. Please verify your export settings.</p></div>';
            update_option('blf_google_sheets_url', esc_url_raw($new_url));
        }
    }
}

/* Get current URL */
$current_url = get_option('blf_google_sheets_url', 'https://docs.google.com/spreadsheets/d/1A8W-_GwPfCWbkqzyvSRKNC2x6bTzDCwBNS24tNuKCt8/export?format=csv&gid=1952886414');
?>

<div class="wrap">
    <h1>Import Business Data</h1>
    
    <div class="card">
        <h2>Google Sheets Configuration</h2>
        <form method="post" action="">
            <?php wp_nonce_field('blf_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="blf_google_sheets_url">Google Sheets Export URL</label>
                    </th>
                    <td>
                        <input type="url" 
                               id="blf_google_sheets_url" 
                               name="blf_google_sheets_url" 
                               value="<?php echo esc_attr($current_url); ?>" 
                               class="regular-text" 
                               required />
                        <p class="description">
                            <strong>How to get your Google Sheets export URL:</strong><br>
                            1. Open your Google Sheet<br>
                            2. Go to <strong>File → Share → Publish to web</strong><br>
                            3. Choose <strong>Comma-separated values (.csv)</strong> format<br>
                            4. Select the specific sheet tab if needed<br>
                            5. Click <strong>Publish</strong> and copy the URL<br>
                            6. Paste the URL here<br>
                            <br>
                            <strong style="color: #d63638;">⚠️ IMPORTANT:</strong> Make sure your URL looks like:<br>
                            <code>https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/export?format=csv&gid=SHEET_GID</code><br>
                            <strong>NOT</strong> like: <code>https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/edit...</code>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Google Sheets URL'); ?>
        </form>
        
        <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin-top: 20px;">
            <h4>📋 Required CSV Format:</h4>
            <p>Your Google Sheet must have these columns (in any order):</p>
            <ul>
                <li><strong>name</strong> - Business name</li>
                <li><strong>category</strong> - Business category (any category names work!)</li>
                <li><strong>suburb</strong> - Location suburb</li>
                <li><strong>address</strong> - Full business address</li>
                <li><strong>instagram</strong> - Instagram handle (without @)</li>
            </ul>
            <p><em>The system will automatically create tabs based on whatever categories you have in your data!</em></p>
        </div>
        
        <p><strong>Current Sheet:</strong> <a href="<?php echo esc_url($current_url); ?>" target="_blank">Test Current URL</a></p>
    </div>
    
    <div class="card">
        <h2>Coming Soon: CSV Import</h2>
        <p>In the next version, you'll be able to:</p>
        <ul>
            <li>Upload CSV files directly</li>
            <li>Manage business data from the WordPress admin</li>
            <li>Eliminate dependency on Google Sheets</li>
            <li>Add, edit, and delete businesses individually</li>
            <li>Bulk import/export functionality</li>
        </ul>
        
        <h3>Expected CSV Format</h3>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>name</th>
                    <th>category</th>
                    <th>suburb</th>
                    <th>address</th>
                    <th>instagram</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Business Name</td>
                    <td>Destinational</td>
                    <td>Adelaide</td>
                    <td>123 Main St, Adelaide SA 5000</td>
                    <td>businesshandle</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="notice notice-info">
        <p><strong>Security Note:</strong> All CSV imports will be thoroughly validated 
           and sanitized to prevent security vulnerabilities.</p>
    </div>
</div>