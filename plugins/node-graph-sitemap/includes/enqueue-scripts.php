<?php
/**
 * Enqueue necessary scripts and styles for the front-end.
 */
function node_graph_sitemap_enqueue_assets() {
    if (!is_admin()) {

        // Enqueue Cytoscape.js library
        wp_enqueue_script(
            'node-graph-sitemap-cytoscape', 
            'https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.30.2/cytoscape.min.js', 
            array(), 
            null, 
            true 
        );

        // Enqueue the Logger script
        wp_enqueue_script(
            'node-graph-sitemap-logger', 
            plugins_url('../assets/logger.js', __FILE__), 
            array(), 
            null, 
            true 
        );

        // Set the Logger script and main script as modules
        add_filter('script_loader_tag', function($tag, $handle, $src) {
            if (in_array($handle, ['node-graph-sitemap-logger', 'node-graph-sitemap-script'])) {
                return '<script type="module" src="' . esc_url($src) . '"></script>';
            }
            return $tag;
        }, 10, 3);

        // Enqueue custom JavaScript file for the plugin's front-end functionality
        wp_enqueue_script(
            'node-graph-sitemap-script', 
            plugins_url('../assets/node-graph-sitemap.js', __FILE__), 
            array('node-graph-sitemap-cytoscape', 'node-graph-sitemap-logger', 'jquery'), 
            null, 
            true 
        );

        // Enqueue custom CSS file for the plugin's front-end styling
        wp_enqueue_style(
            'node-graph-sitemap-style', 
            plugins_url('../assets/node-graph-sitemap.css', __FILE__) 
        );

        // Localize script data
        $logging_level = get_option('node_graph_sitemap_logging_level', 'error'); 
        wp_localize_script('node-graph-sitemap-script', 'nodeGraphData', array(
            'nodes' => node_graph_sitemap_get_site_map(), 
            'pluginUrl' => plugins_url('../', __FILE__), 
            'loggingLevel' => $logging_level, 
        ));
    }
}
add_action('wp_enqueue_scripts', 'node_graph_sitemap_enqueue_assets');

/**
 * Enqueue scripts and styles for the admin settings page.
 */
function node_graph_sitemap_enqueue_admin_scripts($hook) {
    // Check if we are on the plugin's settings page
    if ($hook !== 'toplevel_page_node-graph-sitemap') {
        return; // Only proceed if on the plugin's settings page
    }

    // Enqueue Select2 JavaScript for enhanced select boxes in admin settings
    wp_enqueue_script(
        'select2', // Handle
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', // Source URL
        array('jquery'), // Dependency on jQuery
        null, // Version
        true // Load in footer
    );

    // Enqueue Select2 CSS for styling the enhanced select boxes
    wp_enqueue_style(
        'select2', // Handle
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' // Source URL
    );

    // Enqueue custom admin script for handling Select2 initialization
    wp_enqueue_script(
        'node-graph-sitemap-admin-js', // Handle
        plugins_url('../assets/admin-script.js', __FILE__), // Source URL
        array('jquery', 'select2'), // Dependencies
        null, // Version
        true // Load in footer
    );

    wp_enqueue_media(); // Enqueue media library scripts if using media uploader
}
add_action('admin_enqueue_scripts', 'node_graph_sitemap_enqueue_admin_scripts');
?>
