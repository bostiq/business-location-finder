<?php
/**
 * Template for Business Location Finder Shortcode
 * 
 * Available variables:
 * $atts - Shortcode attributes (already sanitized)
 * 
 * @package BizLocationFinder
 * @author  Lorenzo Colen <info@indexwebmedia.com>, AI Assistant
 * @since   1.0.0
 * 
 */

/* Prevent direct access */
if (!defined('ABSPATH')) {
    exit;
}

/* Parse categories from sanitized shortcode attributes
 * Behavior:
 * - If 'categories' is not provided or is 'all', we do NOT output a data-categories attribute
 *   so the frontend JS can request the CSV and build the category list dynamically.
 * - If a comma-separated list is provided, we sanitize and output it as data-categories.
 */
$categories_string = isset($atts['categories']) ? trim($atts['categories']) : '';

$categories = array();
if ($categories_string !== '' && strtolower($categories_string) !== 'all') {
    $categories = array_map('trim', explode(',', $categories_string));
    // Rebuild a normalized, safe string for use in HTML attribute
    $categories_string = implode(',', array_map('sanitize_text_field', $categories));
} else {
    // Treat empty or 'all' as no explicit categories requested
    $categories_string = '';
}

/* Normalize boolean-like attributes for search and counters */
$show_search = isset($atts['search']) ? filter_var($atts['search'], FILTER_VALIDATE_BOOLEAN) : true;
$show_counters = isset($atts['counters']) ? filter_var($atts['counters'], FILTER_VALIDATE_BOOLEAN) : true;

/* Handle view parameter */
$view_mode = isset($atts['view']) ? sanitize_text_field($atts['view']) : 'default';
$is_data_view = ($view_mode === 'data');

/* Generate unique ID for this shortcode instance */
$unique_id = 'blf-' . uniqid();
?>

<div class="x-stockists" id="<?php echo esc_attr($unique_id); ?>"
     <?php if (!$show_search): ?>data-search-disabled="true"<?php endif; ?>
     <?php if (!$show_counters): ?>data-counters-disabled="true"<?php endif; ?>
     <?php if ($is_data_view): ?>data-view="data"<?php endif; ?>
    <?php if ($categories_string !== ''): ?>data-categories="<?php echo esc_attr($categories_string); ?>"<?php endif; ?>>
    <!-- TABS NAVIGATION - Will be populated dynamically by JavaScript -->
    <div class="tabs"<?php if ($is_data_view): ?> style="display: none;"<?php endif; ?>>
        <ul class="tab-nav">
            <!-- Dynamic tabs will be inserted here -->
        </ul>
    </div>

    <!-- TABS CONTENT - Will be populated dynamically by JavaScript -->
    <div class="tab-panels">
        <!-- Dynamic tab panels will be inserted here -->
    </div>
</div>