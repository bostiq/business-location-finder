<?php
/**
 * Import Page Template - Business Data Management
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

/* Get current data source */
$data_source = get_option('blf_data_source', 'google_sheets');

/* Handle Google Sheets form submission */
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

/* Handle business form submission */
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
    
    if (!empty($business_data['name']) && !empty($business_data['category']) && !empty($business_data['suburb']) && !empty($business_data['address'])) {
        global $blf_plugin;
        $result = $blf_plugin->insert_business($business_data);
        
        if (is_wp_error($result)) {
            echo '<div class="notice notice-error"><p><strong>Error:</strong> ' . esc_html($result->get_error_message()) . '</p></div>';
        } else {
            echo '<div class="notice notice-success"><p><strong>Success!</strong> Business "' . esc_html($business_data['name']) . '" has been added.</p></div>';
            /* Refresh the businesses list */
            $database_businesses = $blf_plugin->get_businesses();
            $business_count = count($database_businesses);
        }
    } else {
        echo '<div class="notice notice-error"><p><strong>Error:</strong> Please fill in all required fields (Name, Category, Suburb, Address).</p></div>';
    }
}

/* Handle business deletion */
if (isset($_POST['delete_business']) && check_admin_referer('blf_delete_business_nonce')) {
    $business_id = intval($_POST['business_id']);
    global $blf_plugin;
    $result = $blf_plugin->delete_business($business_id);
    
    if ($result) {
        echo '<div class="notice notice-success"><p><strong>Success!</strong> Business has been deleted.</p></div>';
        /* Refresh the businesses list */
        $database_businesses = $blf_plugin->get_businesses();
        $business_count = count($database_businesses);
    } else {
        echo '<div class="notice notice-error"><p><strong>Error:</strong> Could not delete business. Please try again.</p></div>';
    }
}

/* Get current Google Sheets URL */
$current_url = get_option('blf_google_sheets_url', 'https://docs.google.com/spreadsheets/d/1A8W-_GwPfCWbkqzyvSRKNC2x6bTzDCwBNS24tNuKCt8/export?format=csv&gid=1952886414');
?>

<div class="wrap biz-location-finder-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <h4 class="blf-version">Import & Manage Business Data - Version <?php echo esc_html(BLF_VERSION); ?></h4>
    
    <!-- Data Source Status Indicator -->
    <div class="card blf-data-source-status">
        <h2><?php echo blf_heroicon('chart-bar-square', 'header-icon'); ?> Current Data Source: 
            <?php if ($data_source === 'google_sheets'): ?>
                <span class="blf-source-google-sheets"><?php echo blf_heroicon('chart-bar-square', 'status-icon'); ?> Google Sheets</span>
            <?php else: ?>
                <span class="blf-source-database"><?php echo blf_heroicon('archive-box', 'status-icon'); ?> Database Records</span>
            <?php endif; ?>
        </h2>
        <p>
            <?php if ($data_source === 'google_sheets'): ?>
                <strong>Google Sheets Mode:</strong> Configure your external spreadsheet below to manage business data.
            <?php else: ?>
                <strong>Database Mode:</strong> Add and manage businesses directly through the forms below.
            <?php endif; ?>
        </p>
        <p><a href="<?php echo admin_url('admin.php?page=biz-location-finder'); ?>" class="button button-secondary"><?php echo blf_heroicon('cog-6-tooth', 'button-icon'); ?> Change Data Source</a></p>
    </div>
    
    <!-- Google Sheets Configuration (show when Google Sheets is selected) -->
    <?php if ($data_source === 'google_sheets'): ?>
    <div class="card">
        <h2><?php echo blf_heroicon('chart-bar-square', 'header-icon'); ?> Google Sheets Configuration</h2>
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
                            <strong style="color: #d63638;"><?php echo blf_heroicon('exclamation-triangle', '', '16'); ?> IMPORTANT:</strong> Make sure your URL looks like:<br>
                            <code>https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/export?format=csv&gid=SHEET_GID</code><br>
                            <strong>NOT</strong> like: <code>https://docs.google.com/spreadsheets/d/YOUR_SHEET_ID/edit...</code>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Google Sheets URL'); ?>
        </form>
        
        <div class="blf-sheets-config-info">
            <h4><?php echo blf_heroicon('clipboard-document-list', 'section-icon'); ?> Required CSV Format:</h4>
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
        
        <p><strong>Current Sheet:</strong> <a href="<?php echo esc_url($current_url); ?>" target="_blank"><?php echo blf_heroicon('arrow-top-right-on-square', 'link-icon'); ?> Test Current URL</a></p>
    </div>
    <?php endif; ?>
    
    <!-- Database Management (show when Database is selected) -->
    <?php if ($data_source === 'database'): ?>
    <div class="blf-database-section">
        <!-- Add Business Form -->
        <div class="card">
            <h2><?php echo blf_heroicon('plus', 'header-icon'); ?> Add New Business</h2>
            <form method="post" action="" class="blf-form" id="blf-add-business-form">
                <?php wp_nonce_field('blf_add_business_nonce'); ?>
                
                <div class="blf-form-section">
                    <h3>Business Information</h3>
                    
                    <p>
                        <label for="business_name">Business Name <span class="blf-required">*</span></label>
                        <input type="text" id="business_name" name="business_name" required />
                        <span class="blf-field-description">The official business name</span>
                    </p>
                    
                    <p>
                        <label for="business_category">Category <span class="blf-required">*</span></label>
                        <input type="text" id="business_category" name="business_category" required />
                        <span class="blf-field-description">Business category (e.g., Restaurant, Cafe, Shop)</span>
                    </p>
                    
                    <p>
                        <label for="business_description">Description</label>
                        <textarea id="business_description" name="business_description" rows="3"></textarea>
                        <span class="blf-field-description">Brief description of the business</span>
                    </p>
                </div>
                
                <div class="blf-form-section">
                    <h3>Location Details</h3>
                    
                    <p>
                        <label for="business_suburb">Suburb <span class="blf-required">*</span></label>
                        <input type="text" id="business_suburb" name="business_suburb" required />
                        <span class="blf-field-description">Suburb or area name</span>
                    </p>
                    
                    <p>
                        <label for="business_address">Full Address <span class="blf-required">*</span></label>
                        <textarea id="business_address" name="business_address" rows="3" required></textarea>
                        <span class="blf-field-description">Complete street address including postcode</span>
                    </p>
                </div>
                
                <div class="blf-form-section">
                    <h3>Contact Information</h3>
                    
                    <p>
                        <label for="business_phone">Phone Number</label>
                        <input type="tel" id="business_phone" name="business_phone" />
                        <span class="blf-field-description">Business phone number</span>
                    </p>
                    
                    <p>
                        <label for="business_email">Email Address</label>
                        <input type="email" id="business_email" name="business_email" />
                        <span class="blf-field-description">Business email address</span>
                    </p>
                    
                    <p>
                        <label for="business_website">Website URL</label>
                        <input type="url" id="business_website" name="business_website" placeholder="https://" />
                        <span class="blf-field-description">Business website URL</span>
                    </p>
                    
                    <p>
                        <label for="business_instagram">Instagram Handle</label>
                        <input type="text" id="business_instagram" name="business_instagram" placeholder="businesshandle" />
                        <span class="blf-field-description">Instagram username without the @ symbol</span>
                    </p>
                </div>
                
                <?php submit_button('Add Business', 'primary', 'add_business'); ?>
            </form>
        </div>
        
        <!-- Existing Businesses Table -->
        <div class="card">
            <h2><?php echo blf_heroicon('archive-box', 'header-icon'); ?> Manage Existing Businesses (<?php echo $business_count; ?> total)</h2>
            
            <?php if ($business_count > 0): ?>
                <table class="wp-list-table widefat striped blf-data-table">
                    <thead>
                        <tr>
                            <th>Business Name</th>
                            <th>Category</th>
                            <th>Suburb</th>
                            <th>Contact</th>
                            <th>Social</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($database_businesses as $business): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($business['name']); ?></strong>
                                <?php if (!empty($business['description'])): ?>
                                    <br><small><?php echo esc_html(wp_trim_words($business['description'], 10)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($business['category']); ?></td>
                            <td>
                                <?php echo esc_html($business['suburb']); ?>
                                <?php if (!empty($business['address'])): ?>
                                    <br><small><?php echo esc_html($business['address']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($business['phone'])): ?>
                                    <?php echo blf_heroicon('phone', '', '16'); ?> <a href="tel:<?php echo esc_attr($business['phone']); ?>"><?php echo esc_html($business['phone']); ?></a><br>
                                <?php endif; ?>
                                <?php if (!empty($business['email'])): ?>
                                    <?php echo blf_heroicon('envelope', '', '16'); ?> <a href="mailto:<?php echo esc_attr($business['email']); ?>"><?php echo esc_html($business['email']); ?></a><br>
                                <?php endif; ?>
                                <?php if (!empty($business['website'])): ?>
                                    <?php echo blf_heroicon('globe-alt', '', '16'); ?> <a href="<?php echo esc_url($business['website']); ?>" target="_blank">Website</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($business['instagram'])): ?>
                                    <a href="https://instagram.com/<?php echo esc_attr($business['instagram']); ?>" target="_blank">
                                        @<?php echo esc_html($business['instagram']); ?>
                                    </a>
                                <?php else: ?>
                                    <em>No Instagram</em>
                                <?php endif; ?>
                            </td>
                            <td class="blf-actions">
                                <button type="button" class="button button-small blf-edit-business" 
                                        data-business-id="<?php echo esc_attr($business['id']); ?>">
                                    Edit
                                </button>
                                <form method="post" action="">
                                    <?php wp_nonce_field('blf_delete_business_nonce'); ?>
                                    <input type="hidden" name="business_id" value="<?php echo esc_attr($business['id']); ?>" />
                                    <button type="submit" name="delete_business" class="button button-small delete" 
                                            onclick="return confirm('Are you sure you want to delete this business?');">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="blf-notice info">
                    <p><strong>No businesses found.</strong> Add your first business using the form above!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Future Features Section -->
    <div class="card">
        <h2><?php echo blf_heroicon('rocket-launch', 'header-icon'); ?> Future Features</h2>
        <p>Coming in future versions:</p>
        <ul>
            <li><?php echo blf_heroicon('arrow-up-tray', 'list-icon'); ?> <strong>CSV Export:</strong> Download your business data as CSV</li>
            <li><?php echo blf_heroicon('arrow-down-tray', 'list-icon'); ?> <strong>CSV Import:</strong> Bulk upload businesses via CSV file</li>
            <li><?php echo blf_heroicon('arrow-path', 'list-icon'); ?> <strong>Bulk Operations:</strong> Delete or update multiple businesses at once</li>
            <li><?php echo blf_heroicon('cursor-arrow-rays', 'list-icon'); ?> <strong>Categories Management:</strong> Organize and manage business categories</li>
            <li><?php echo blf_heroicon('map', 'list-icon'); ?> <strong>Map Integration:</strong> Display businesses on interactive maps</li>
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
        <p><strong>Security Note:</strong> All form submissions are validated and sanitized to prevent security vulnerabilities.</p>
    </div>
</div>

<!-- Edit Business Modal -->
<div id="blf-edit-modal" class="blf-modal" style="display: none;">
    <div class="blf-modal-content">
        <span class="blf-modal-close">&times;</span>
        <h2>Edit Business</h2>
        <form id="blf-edit-form">
            <?php wp_nonce_field('blf_edit_business_nonce', 'edit_nonce'); ?>
            <input type="hidden" id="edit-business-id" name="business_id" value="" />
            
            <div class="blf-form-section">
                <h3>Business Information</h3>
                
                <p>
                    <label for="edit-business-name">Business Name <span class="blf-required">*</span></label>
                    <input type="text" id="edit-business-name" name="business_name" required />
                </p>
                
                <p>
                    <label for="edit-business-category">Category <span class="blf-required">*</span></label>
                    <input type="text" id="edit-business-category" name="business_category" required />
                </p>
                
                <p>
                    <label for="edit-business-description">Description</label>
                    <textarea id="edit-business-description" name="business_description" rows="3"></textarea>
                </p>
            </div>
            
            <div class="blf-form-section">
                <h3>Location Details</h3>
                
                <p>
                    <label for="edit-business-suburb">Suburb <span class="blf-required">*</span></label>
                    <input type="text" id="edit-business-suburb" name="business_suburb" required />
                </p>
                
                <p>
                    <label for="edit-business-address">Address</label>
                    <input type="text" id="edit-business-address" name="business_address" />
                </p>
            </div>
            
            <div class="blf-form-section">
                <h3>Contact Information</h3>
                
                <p>
                    <label for="edit-business-phone">Phone</label>
                    <input type="tel" id="edit-business-phone" name="business_phone" />
                </p>
                
                <p>
                    <label for="edit-business-email">Email</label>
                    <input type="email" id="edit-business-email" name="business_email" />
                </p>
                
                <p>
                    <label for="edit-business-website">Website</label>
                    <input type="url" id="edit-business-website" name="business_website" />
                </p>
                
                <p>
                    <label for="edit-business-instagram">Instagram Username</label>
                    <input type="text" id="edit-business-instagram" name="business_instagram" />
                </p>
            </div>
            
            <div class="blf-modal-actions">
                <button type="button" class="button" id="blf-cancel-edit">Cancel</button>
                <button type="submit" class="button button-primary">Update Business</button>
            </div>
        </form>
    </div>
</div>

<script>
// Make ajaxurl available to our script
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up edit functionality...');
    
    const modal = document.getElementById('blf-edit-modal');
    const editButtons = document.querySelectorAll('.blf-edit-business');
    const closeModal = document.querySelector('.blf-modal-close');
    const cancelButton = document.getElementById('blf-cancel-edit');
    const editForm = document.getElementById('blf-edit-form');
    
    console.log('Modal found:', modal);
    console.log('Edit buttons found:', editButtons.length);
    console.log('Close button found:', closeModal);
    
    // Open modal when edit button is clicked
    editButtons.forEach(button => {
        console.log('Adding click listener to button:', button);
        button.addEventListener('click', function() {
            console.log('Edit button clicked!');
            const businessId = this.getAttribute('data-business-id');
            console.log('Business ID:', businessId);
            loadBusinessData(businessId);
            console.log('About to show modal...');
            modal.style.display = 'block';
            console.log('Modal display set to:', modal.style.display);
            console.log('Modal element:', modal);
        });
    });
    
    // Close modal functions
    function closeEditModal() {
        modal.style.display = 'none';
        editForm.reset();
    }
    
    closeModal.addEventListener('click', closeEditModal);
    cancelButton.addEventListener('click', closeEditModal);
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeEditModal();
        }
    });
    
    // Load business data into form
    function loadBusinessData(businessId) {
        console.log('Loading business data for ID:', businessId);
        
        // Find the business data from the table row
        const row = document.querySelector(`button[data-business-id="${businessId}"]`).closest('tr');
        const cells = row.querySelectorAll('td');
        
        console.log('Table cells found:', cells.length);
        console.log('Cell contents:', Array.from(cells).map(cell => cell.textContent.trim()));
        
        // Extract data from table cells based on actual table structure
        document.getElementById('edit-business-id').value = businessId;
        
        // Column 0: Business Name (extract main name, ignore description)
        const nameCell = cells[0];
        const nameStrong = nameCell.querySelector('strong');
        const businessName = nameStrong ? nameStrong.textContent.trim() : nameCell.textContent.trim().split('\n')[0];
        document.getElementById('edit-business-name').value = businessName;
        
        // Try to extract description from small tag in name cell
        const descriptionElement = nameCell.querySelector('small');
        document.getElementById('edit-business-description').value = descriptionElement ? descriptionElement.textContent.trim() : '';
        
        // Column 1: Category
        document.getElementById('edit-business-category').value = cells[1].textContent.trim();
        
        // Column 2: Suburb (extract main suburb, ignore address)
        const suburbCell = cells[2];
        const suburbText = suburbCell.textContent.trim().split('\n')[0];
        document.getElementById('edit-business-suburb').value = suburbText;
        
        // Try to extract address from small tag in suburb cell
        const addressElement = suburbCell.querySelector('small');
        document.getElementById('edit-business-address').value = addressElement ? addressElement.textContent.trim() : '';
        
        // Column 3: Contact info (phone, email, website)
        const contactCell = cells[3];
        const contactLinks = contactCell.querySelectorAll('a');
        
        // Reset contact fields
        document.getElementById('edit-business-phone').value = '';
        document.getElementById('edit-business-email').value = '';
        document.getElementById('edit-business-website').value = '';
        
        // Extract contact info from links
        contactLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href.startsWith('tel:')) {
                document.getElementById('edit-business-phone').value = link.textContent.trim();
            } else if (href.startsWith('mailto:')) {
                document.getElementById('edit-business-email').value = link.textContent.trim();
            } else if (href.startsWith('http')) {
                document.getElementById('edit-business-website').value = href;
            }
        });
        
        // Column 4: Social (Instagram)
        const socialCell = cells[4];
        const instagramLink = socialCell.querySelector('a');
        if (instagramLink && instagramLink.textContent.includes('@')) {
            const instagramHandle = instagramLink.textContent.trim().replace('@', '');
            document.getElementById('edit-business-instagram').value = instagramHandle;
        } else {
            document.getElementById('edit-business-instagram').value = '';
        }
        
        console.log('Business data loaded successfully');
    }
    
    // Handle form submission
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(editForm);
        formData.append('action', 'blf_update_business');
        
        // Show loading state
        const submitButton = editForm.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.textContent = 'Updating...';
        submitButton.disabled = true;
        
        // Send AJAX request
        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEditModal();
                location.reload(); // Reload to show updated data
            } else {
                alert('Error updating business: ' + (data.data || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating business. Please try again.');
        })
        .finally(() => {
            submitButton.textContent = originalText;
            submitButton.disabled = false;
        });
    });
});
</script>