<?php
/**
 * Admin Page Template - Business Management
 */

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

/* Check user permissions */
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'biz-location-finder'));
}

/* Handle form submissions */
if (isset($_POST['add_business']) && check_admin_referer('blf_add_business_nonce')) {
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
    
    if (!is_wp_error($result)) {
        echo '<div class="notice notice-success"><p><strong>Success!</strong> Business "' . esc_html($business_data['name']) . '" has been added to the database!</p></div>';
    } else {
        echo '<div class="notice notice-error"><p><strong>Error:</strong> ' . esc_html($result->get_error_message()) . '</p></div>';
    }
}

/* Get database businesses for display */
global $blf_plugin;
$database_businesses = $blf_plugin->get_businesses();
$business_count = count($database_businesses);

/* Get current Google Sheets URL */
$current_url = get_option('blf_google_sheets_url', 'https://docs.google.com/spreadsheets/d/1A8W-_GwPfCWbkqzyvSRKNC2x6bTzDCwBNS24tNuKCt8/export?format=csv&gid=1952886414');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Data Source Selection -->
    <div class="card" style="background: #f9f9f9; border-left: 4px solid #0073aa;">
        <h2>⚙️ Data Source Configuration</h2>
        <form method="post" action="options.php">
            <?php settings_fields('blf_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Choose Your Data Source</th>
                    <td>
                        <?php 
                        $data_source = get_option('blf_data_source', 'google_sheets');
                        ?>
                        <fieldset>
                            <label style="display: block; margin-bottom: 10px; padding: 10px; background: <?php echo ($data_source === 'google_sheets') ? '#e7f3ff' : '#fff'; ?>; border-radius: 5px; border: 2px solid <?php echo ($data_source === 'google_sheets') ? '#0073aa' : '#ddd'; ?>;">
                                <input type="radio" name="blf_data_source" value="google_sheets" <?php checked($data_source, 'google_sheets'); ?> style="margin-right: 8px;" />
                                <strong>📊 Google Sheets</strong> - Use external Google Sheets data
                                <br><small style="margin-left: 24px; color: #666;">Perfect for collaborating with team members or using existing spreadsheets</small>
                            </label>
                            <label style="display: block; padding: 10px; background: <?php echo ($data_source === 'database') ? '#e7f3ff' : '#fff'; ?>; border-radius: 5px; border: 2px solid <?php echo ($data_source === 'database') ? '#0073aa' : '#ddd'; ?>;">
                                <input type="radio" name="blf_data_source" value="database" <?php checked($data_source, 'database'); ?> style="margin-right: 8px;" />
                                <strong>🗃️ Database Records</strong> - Use locally managed business data
                                <br><small style="margin-left: 24px; color: #666;">Add and manage businesses directly through this admin interface</small>
                            </label>
                        </fieldset>
                        
                        <?php if ($data_source === 'google_sheets'): ?>
                        <div style="margin-top: 15px; padding: 10px; background: #fff2e7; border-radius: 5px;">
                            <label for="blf_google_sheets_url"><strong>Google Sheets URL:</strong></label><br>
                            <input type="url" id="blf_google_sheets_url" name="blf_google_sheets_url" 
                                   value="<?php echo esc_attr(get_option('blf_google_sheets_url', '')); ?>" 
                                   class="regular-text" style="width: 100%; margin-top: 5px;" />
                            <p class="description">Enter your published Google Sheets CSV URL</p>
                        </div>
                        <?php endif; ?>
                        
                        <?php submit_button('Save Data Source Settings', 'primary', 'submit', false); ?>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    
    <script>
    // Show/hide Google Sheets URL field based on selection
    document.querySelectorAll('input[name="blf_data_source"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const urlField = document.querySelector('div[style*="background: #fff2e7"]');
            if (urlField) {
                urlField.style.display = this.value === 'google_sheets' ? 'block' : 'none';
            }
        });
    });
    </script>
    
    <!-- Status Overview -->
    <div class="card">
        <h2>📊 Data Source Status</h2>
        <div style="display: flex; gap: 20px; margin: 15px 0;">
            <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; flex: 1;">
                <h4>🗃️ Database Storage</h4>
                <p><strong><?php echo esc_html($business_count); ?> businesses</strong> in local database</p>
                <p><small>Fast, reliable, no external dependencies</small></p>
            </div>
            <div style="background: #fff2e7; padding: 15px; border-radius: 5px; flex: 1;">
                <h4>📊 Google Sheets</h4>
                <p><a href="<?php echo esc_url($current_url); ?>" target="_blank">View Current Sheet</a></p>
                <p><small>External data source, requires internet</small></p>
            </div>
        </div>
        
        <div style="background: #f0f8f0; padding: 15px; border-radius: 5px; margin-top: 15px;">
            <h4>🎯 Current Behavior</h4>
            <?php if ($business_count > 0): ?>
                <p><strong>Frontend will display database businesses.</strong> The system automatically prioritizes local database when available.</p>
                <p><small>To use Google Sheets instead, manage data via <a href="<?php echo admin_url('admin.php?page=biz-location-finder-import'); ?>">Import Data</a> page.</small></p>
            <?php else: ?>
                <p><strong>Frontend will display Google Sheets data.</strong> No businesses found in local database.</p>
                <p><small>Add businesses below to start using database storage.</small></p>
            <?php endif; ?>
        </div>
    </div>

    <?php 
    $data_source = get_option('blf_data_source', 'google_sheets');
    if ($data_source === 'database'): 
    ?>
    <!-- Add New Business Form -->
    <div class="card">
        <h2>➕ Add New Business</h2>
        <form method="post" action="">
            <?php wp_nonce_field('blf_add_business_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="business_name">Business Name *</label></th>
                    <td><input type="text" id="business_name" name="business_name" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="business_category">Category *</label></th>
                    <td>
                        <input type="text" id="business_category" name="business_category" class="regular-text" required />
                        <p class="description">e.g., Restaurant, Cafe, Service, Retail</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="business_suburb">Suburb</label></th>
                    <td><input type="text" id="business_suburb" name="business_suburb" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="business_address">Address</label></th>
                    <td><textarea id="business_address" name="business_address" class="large-text" rows="2"></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="business_phone">Phone</label></th>
                    <td><input type="tel" id="business_phone" name="business_phone" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="business_email">Email</label></th>
                    <td><input type="email" id="business_email" name="business_email" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="business_website">Website</label></th>
                    <td><input type="url" id="business_website" name="business_website" class="regular-text" placeholder="https://" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="business_instagram">Instagram</label></th>
                    <td>
                        <input type="text" id="business_instagram" name="business_instagram" class="regular-text" placeholder="username (without @)" />
                        <p class="description">Enter just the username, without @ symbol</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="business_description">Description</label></th>
                    <td><textarea id="business_description" name="business_description" class="large-text" rows="3"></textarea></td>
                </tr>
            </table>
            <?php submit_button('Add Business', 'primary', 'add_business'); ?>
        </form>
    </div>
    <?php else: ?>
    <!-- Google Sheets Mode Message -->
    <div class="card" style="background: #fff2e7; border-left: 4px solid #ff8c00;">
        <h2>📊 Google Sheets Mode</h2>
        <p><strong>Data source is set to Google Sheets.</strong> Your business data comes from the external spreadsheet configured above.</p>
        <p>To add or edit businesses:</p>
        <ul style="margin-left: 20px;">
            <li>📝 <strong>Edit your Google Sheet</strong> directly in Google Sheets</li>
            <li>🔄 <strong>Changes appear automatically</strong> on your website</li>
            <li>🗃️ <strong>Or switch to "Database Records"</strong> above to manage data locally</li>
        </ul>
        <p><a href="<?php echo esc_url($current_url); ?>" target="_blank" class="button button-secondary">🔗 Open Google Sheet</a></p>
    </div>
    <?php endif; // End of database-only form ?>

    <!-- Database Businesses List -->
    <?php if ($data_source === 'database' && $business_count > 0): ?>
    <div class="card">
        <h2>🗃️ Database Businesses (<?php echo esc_html($business_count); ?>)</h2>
        <div class="tablenav top">
            <div class="alignleft actions">
                <p>These businesses are stored in your WordPress database and will be displayed on the frontend.</p>
            </div>
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Suburb</th>
                    <th>Contact</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($database_businesses as $business): ?>
                <tr>
                    <td><strong><?php echo esc_html($business['name']); ?></strong></td>
                    <td><?php echo esc_html($business['category']); ?></td>
                    <td><?php echo esc_html($business['suburb']); ?></td>
                    <td>
                        <?php if (!empty($business['phone'])): ?>
                            📞 <?php echo esc_html($business['phone']); ?><br>
                        <?php endif; ?>
                        <?php if (!empty($business['email'])): ?>
                            ✉️ <?php echo esc_html($business['email']); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html(date('M j, Y', strtotime($business['created_at']))); ?></td>
                    <td>
                        <button class="button button-small">Edit</button>
                        <button class="button button-small button-link-delete">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Quick Start Guide -->
    <div class="card">
        <h2>🚀 Quick Start</h2>
        <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <h4>Display Your Businesses:</h4>
            <ol>
                <li><strong>Add businesses</strong> using the form above, OR configure Google Sheets in <a href="<?php echo admin_url('admin.php?page=biz-location-finder-import'); ?>">Import Data</a></li>
                <li><strong>Add shortcode</strong> to any page or post: <code>[biz_location_finder]</code></li>
                <li><strong>View your directory</strong> - businesses will display with search, categories, and contact links</li>
            </ol>
        </div>
        
        <h4>Shortcode Options:</h4>
        <ul>
            <li><code>[biz_location_finder]</code> - Shows all businesses</li>
            <li><code>[biz_location_finder categories="restaurant,cafe"]</code> - Filter by categories</li>
            <li><code>[biz_location_finder search="false"]</code> - Disable search</li>
        </ul>
    </div>
</div>