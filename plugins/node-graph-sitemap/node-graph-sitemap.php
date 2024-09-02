<?php
/**
 * Plugin Name: Node Graph Sitemap
 * Description: A plugin to display an interactive node graph of the sitemap.
 * Version: 1.0
 * Author: Steven Partridge
 * License: GPL-2.0+
 * Text Domain: node-graph-sitemap
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include necessary files
include_once plugin_dir_path( __FILE__ ) . 'includes/enqueue-scripts.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/shortcode.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/settings-page.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/sitemap-functions.php';
?>
