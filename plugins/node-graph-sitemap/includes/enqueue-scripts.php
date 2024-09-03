<?php
/**
 * Enqueue necessary scripts and styles for the front-end.
 */
function node_graph_sitemap_enqueue_assets() {
    // Check if we are on the front-end and the specific conditions where the graph is needed
    if (!is_admin()) { // Remove specific page checks if the graph needs to be rendered globally

        // Enqueue Cytoscape.js library for creating the graph
        wp_enqueue_script(
            'cytoscape', // Handle
            'https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.30.2/cytoscape.min.js', // Source URL
            array(), // Dependencies
            null, // Version (null to skip versioning)
            true // Load in footer
        );

        // Enqueue custom JavaScript file for the plugin's front-end functionality
        wp_enqueue_script(
            'node-graph-sitemap-script', // Handle
            plugins_url('../assets/node-graph-sitemap.js', __FILE__), // Source URL (adjusted path)
            array('cytoscape', 'jquery'), // Dependencies
            null, // Version
            true // Load in footer
        );

        // Enqueue custom CSS file for the plugin's front-end styling
        wp_enqueue_style(
            'node-graph-sitemap-style', // Handle
            plugins_url('../assets/node-graph-sitemap.css', __FILE__) // Source URL
        );

        // Localize script to pass PHP data to JavaScript
        wp_localize_script('node-graph-sitemap-script', 'nodeGraphData', array(
            'nodes'     => node_graph_sitemap_get_site_map(), // Retrieve the site map data
            'pluginUrl' => plugins_url('../', __FILE__), // Base URL for plugin assets
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
