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

/* Get database businesses for display */
global $blf_plugin;
$database_businesses = $blf_plugin->get_businesses();
$business_count = count($database_businesses);

/* Get current Google Sheets URL */
$current_url = get_option('blf_google_sheets_url', 'https://docs.google.com/spreadsheets/d/e/2PACX-1vTp7ka2BJoFXGG81bBev0gUF5HOnv-mA8umBcA219W2tTrXCFrBjIWuMaZTT64cUdny2FuibopMCrXz/pub?output=csv');
?>

<div class="wrap biz-location-finder-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <h4 class="blf-version">Version <?php echo esc_html(BLF_VERSION); ?></h4>
    
    <!-- Google Sheets Mode Indicator -->
    <?php if ($data_source === 'google_sheets'): ?>
    <div class="card blf-sheets-mode-indicator">
        <h2><?php echo blf_heroicon('chart-bar-square', 'header-icon'); ?> Google Sheets Mode</h2>
        <p><strong>Data source is set to Google Sheets.</strong> Your business data comes from the external spreadsheet configured below.</p>
        <p>To add or edit businesses:</p>
        <ul>
            <li><?php echo blf_heroicon('pencil', 'list-icon'); ?> <strong>Edit your Google Sheet</strong> directly in Google Sheets</li>
            <li><?php echo blf_heroicon('arrow-path', 'list-icon'); ?> <strong>Changes appear automatically</strong> on your website</li>
            <li><?php echo blf_heroicon('archive-box', 'list-icon'); ?> <strong>Or switch to "Database Records"</strong> below to manage data locally</li>
        </ul>
        <p><a href="<?php echo esc_url($current_url); ?>" target="_blank" class="button button-secondary"><?php echo blf_heroicon('arrow-top-right-on-square', 'button-icon'); ?> Open Google Sheet</a></p>
    </div>
    <?php endif; ?>
    
    <!-- Top Section: Data Source Config + Quick Start -->
    <div class="blf-top-section">
        <!-- Data Source Selection -->
        <div class="card blf-config-card">
            <h2><?php echo blf_heroicon('cog-6-tooth', 'header-icon'); ?> Data Source Configuration</h2>
            
            <form method="post" action="options.php">
                <?php settings_fields('blf_settings'); ?>
                
                <div class="blf-config-content">
                    <h3>Choose Your Data Source</h3>
                    <?php 
                    $data_source = get_option('blf_data_source', 'google_sheets');
                    ?>
                    
                    <!-- Save button at top for immediate visibility -->
                    <div class="blf-set-save blf-save-top">
                        <?php submit_button('Save Data Source Settings', 'primary', 'submit_top', false); ?>
                    </div>
                    
                    <fieldset class="blf-data-source-options">
                        <label class="blf-source-option <?php echo ($data_source === 'google_sheets') ? 'selected' : ''; ?>">
                            <input type="radio" name="blf_data_source" value="google_sheets" <?php checked($data_source, 'google_sheets'); ?> />
                            <?php echo blf_heroicon('chart-bar-square', 'option-icon'); ?> <strong>Google Sheets</strong> - Use external Google Sheets data
                            <small>Perfect for collaborating with team members or using existing spreadsheets</small>
                        </label>
                        <label class="blf-source-option <?php echo ($data_source === 'database') ? 'selected' : ''; ?>">
                            <input type="radio" name="blf_data_source" value="database" <?php checked($data_source, 'database'); ?> />
                            <?php echo blf_heroicon('archive-box', 'option-icon'); ?> <strong>Database Records</strong> - Use locally managed business data
                            <small>Add and manage businesses directly through this admin interface</small>
                        </label>
                    </fieldset>
                    
                    <?php if ($data_source === 'google_sheets'): ?>
                    <div class="blf-sheets-config">
                        <label for="blf_google_sheets_url"><strong>Google Sheets URL:</strong></label><br>
                        <input type="url" id="blf_google_sheets_url" name="blf_google_sheets_url" 
                               value="<?php echo esc_attr(get_option('blf_google_sheets_url', '')); ?>" 
                               class="regular-text" />
                        <p class="description">Enter your published Google Sheets CSV URL</p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Inline Google Sheets Configuration Card -->
                    <?php if ($data_source === 'google_sheets'): ?>
                    <div class="blf-sheets-detailed-config">
                        <h4><?php echo blf_heroicon('chart-bar-square', 'section-icon'); ?> Google Sheets Configuration</h4>
                        <div class="blf-config-instructions">
                            <p><strong>How to get your Google Sheets export URL:</strong></p>
                            <ol>
                                <li>Open your Google Sheet</li>
                                <li>Go to <strong>File → Share → Publish to web</strong></li>
                                <li>Choose <strong>Comma-separated values (.csv)</strong> format</li>
                                <li>Select the specific sheet tab if needed</li>
                                <li>Click <strong>Publish</strong> and copy the URL</li>
                                <li>Paste the URL above</li>
                            </ol>
                            
                            <div class="blf-url-format-warning">
                                <p><strong style="color: #d63638;"><?php echo blf_heroicon('exclamation-triangle', 'warning-icon'); ?> IMPORTANT:</strong> Make sure your URL looks like:</p>
                                <code>https://docs.google.com/spreadsheets/d/e/YOUR_SHEET_ID/pub?output=csv</code>
                                <p><strong>NOT</strong> like: <code>https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/export?format=csv&gid=SHEET_GID</code></p>
                            </div>
                            
                            <div class="blf-csv-format-info">
                                <h5><?php echo blf_heroicon('clipboard-document-list', 'section-icon'); ?> Required CSV Format:</h5>
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
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Database Records Management Button -->
                    <?php if ($data_source === 'database'): ?>
                    <div class="blf-database-management">
                        <p><strong>Manage your business records:</strong></p>
                        <a href="<?php echo admin_url('admin.php?page=biz-location-finder-import'); ?>" class="button button-primary">
                            <?php echo blf_heroicon('plus', 'link-icon'); ?> Add or Edit Records
                        </a>
                        <p class="description">Add new businesses, edit existing records, or bulk import data</p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Save button at bottom with consistent styling -->
                    <div class="blf-set-save blf-save-bottom">
                        <?php submit_button('Save Data Source Settings', 'primary', 'submit', false); ?>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Quick Start Guide -->
        <div class="card blf-quick-start-card">
            <h2><?php echo blf_heroicon('rocket-launch', 'header-icon'); ?> Quick Start</h2>
            <div class="blf-database-list">
                <h4>Display Your Businesses:</h4>
                <ol>
                    <li><strong>Add businesses</strong> using the form below, OR configure Google Sheets in <a href="<?php echo admin_url('admin.php?page=biz-location-finder-import'); ?>">Import Data</a></li>
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
    
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle data source radio button styling
    const radioButtons = document.querySelectorAll('input[name="blf_data_source"]');
    const labels = document.querySelectorAll('.blf-source-option');
    
    function updateLabels() {
        labels.forEach(label => {
            const radio = label.querySelector('input[type="radio"]');
            if (radio.checked) {
                label.classList.add('selected');
            } else {
                label.classList.remove('selected');
            }
        });
    }
    
    // Update on change
    radioButtons.forEach(radio => {
        radio.addEventListener('change', updateLabels);
    });
    
    // Initial update
    updateLabels();
});
</script>    <!-- Status Overview -->
    <div class="card">
        <h2><?php echo blf_heroicon('chart-bar-square', 'header-icon'); ?> Data Source Status</h2>
        <div class="blf-dashboard-info">
            <div class="blf-info-box blue">
                <h4><?php echo blf_heroicon('archive-box', 'section-icon'); ?> Database Storage</h4>
                <p><strong><?php echo esc_html($business_count); ?> businesses</strong> in local database</p>
                <p><small>Fast, reliable, no external dependencies</small></p>
            </div>
            <div class="blf-info-box orange">
                <h4><?php echo blf_heroicon('chart-bar-square', 'section-icon'); ?> Google Sheets</h4>
                <p><a href="<?php echo esc_url($current_url); ?>" target="_blank">Download Current Sheet</a></p>
                <p><small>Download the .csv file. External data source, requires internet</small></p>
            </div>
        </div>
        
        <div class="blf-info-box green">
            <h4><?php echo blf_heroicon('cursor-arrow-rays', 'section-icon'); ?> Current Behavior</h4>
            <?php if ($business_count > 0): ?>
                <p><strong>Frontend will display database businesses.</strong> The system automatically prioritizes local database when available.</p>
                <p><small>To use Google Sheets instead, manage data via <a href="<?php echo admin_url('admin.php?page=biz-location-finder-import'); ?>">Import Data</a> page.</small></p>
            <?php else: ?>
                <p><strong>Frontend will display Google Sheets data.</strong> No businesses found in local database.</p>
                <p><small>Add businesses below to start using database storage.</small></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Google Sheets Mode Message -->
    <?php 
    $data_source = get_option('blf_data_source', 'google_sheets');
    if ($data_source === 'google_sheets'): 
    ?>
    <div class="card blf-sheets-message-card">
        <h2><?php echo blf_heroicon('chart-bar-square', 'header-icon'); ?> Google Sheets Mode</h2>
        <p><strong>Data source is set to Google Sheets.</strong> Your business data comes from the external spreadsheet configured above.</p>
        <p>To add or edit businesses:</p>
        <ul>
            <li><?php echo blf_heroicon('pencil', 'list-icon'); ?> <strong>Edit your Google Sheet</strong> directly in Google Sheets</li>
            <li><?php echo blf_heroicon('arrow-path', 'list-icon'); ?> <strong>Changes appear automatically</strong> on your website</li>
            <li><?php echo blf_heroicon('archive-box', 'list-icon'); ?> <strong>Or switch to "Database Records"</strong> above to manage data locally</li>
        </ul>
        <p><a href="<?php echo esc_url($current_url); ?>" target="_blank" class="button button-secondary"><?php echo blf_heroicon('document-arrow-down', '', '16'); ?> Download Google Sheet</a></p>
    </div>
    <?php endif; // End of Google Sheets message ?>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle data source radio button styling
    const radioButtons = document.querySelectorAll('input[name="blf_data_source"]');
    const labels = document.querySelectorAll('.blf-source-option');
    
    function updateLabels() {
        labels.forEach(label => {
            const radio = label.querySelector('input[type="radio"]');
            if (radio.checked) {
                label.classList.add('selected');
            } else {
                label.classList.remove('selected');
            }
        });
    }
    
    // Update on change
    radioButtons.forEach(radio => {
        radio.addEventListener('change', updateLabels);
    });
    
    // Initial update
    updateLabels();
});
</script>
