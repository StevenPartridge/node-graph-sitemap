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

/**
 * Enqueue necessary scripts and styles.
 */
function node_graph_sitemap_enqueue_assets() {
    // Enqueue Cytoscape.js library
    wp_enqueue_script(
        'cytoscape',
        'https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.30.2/cytoscape.min.js',
        array(),
        null,
        true
    );

    // Enqueue custom JavaScript
    wp_enqueue_script(
        'node-graph-sitemap-script',
        plugins_url( '/node-graph-sitemap.js', __FILE__ ),
        array( 'cytoscape', 'jquery' ),
        null,
        true
    );

    // Enqueue custom CSS
    wp_enqueue_style(
        'node-graph-sitemap-style',
        plugins_url( '/node-graph-sitemap.css', __FILE__ )
    );

    // Pass data to JavaScript
    wp_localize_script( 'node-graph-sitemap-script', 'nodeGraphData', array(
        'nodes'     => node_graph_sitemap_get_site_map(),
        'pluginUrl' => plugins_url( '/', __FILE__ ),
    ));
}
add_action( 'wp_enqueue_scripts', 'node_graph_sitemap_enqueue_assets' );

/**
 * Shortcode to display the graph container with buttons.
 *
 * @return string HTML output for the graph container.
 */
function node_graph_sitemap_shortcode() {
    ob_start();
    ?>
    <div id="parchment">
        <div id="graph-container">
            <button id="fullscreen-btn" class="graph-btn">Full Screen</button>
            <button id="reset-view-btn" class="graph-btn">Reset</button>
            <div id="cy" style="width: 100%; height: 600px;"></div>
        </div>
        <!-- SVG filter for parchment effect -->
        <svg style="display: none;">
            <filter id="wavy2">
                <feTurbulence x="0" y="0" baseFrequency="0.02" numOctaves="5" seed="1"></feTurbulence>
                <feDisplacementMap in="SourceGraphic" scale="20" />
            </filter>
        </svg>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'node_graph_sitemap', 'node_graph_sitemap_shortcode' );

/**
 * Retrieve the site map data for nodes and edges.
 *
 * @return array Site map data including nodes and edges.
 */
function node_graph_sitemap_get_site_map() {
    $nodes = [];
    $edges = [];
    $posts = get_posts(array('numberposts' => -1, 'post_type' => array('post', 'page', 'attachment', 'custom_post_type')));

    foreach ($posts as $post) {
        $url   = get_permalink($post->ID);
        $title = get_the_title($post->ID);
        $type  = get_post_type($post->ID);

        // Add node for this post/page/attachment
        $nodes[] = array('id' => $url, 'label' => $title, 'type' => $type);

        // Load the post content into DOMDocument
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($post->post_content);
        libxml_clear_errors();

        // Find all links
        $xpath = new DOMXPath($dom);
        $links = $xpath->query('//a[@href]');

        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            // Add nodes for linked media/attachments if they're internal
            if (strpos($href, home_url()) === 0 && !in_array($href, array_column($nodes, 'id'))) {
                // Determine if the link is to an attachment
                $attachment_id = attachment_url_to_postid($href);
                if ($attachment_id) {
                    $attachment_title = get_the_title($attachment_id);
                    $nodes[] = array('id' => $href, 'label' => $attachment_title, 'type' => 'attachment');
                } else {
                    // Add link as a generic node if not specifically handled
                    $nodes[] = array('id' => $href, 'label' => basename($href), 'type' => 'link');
                }
            }

            // Only add edges to existing nodes
            if (in_array($href, array_column($nodes, 'id'))) {
                $edges[] = array('source' => $url, 'target' => $href);
            }
        }
    }

    $data = array('nodes' => $nodes, 'edges' => $edges);
    set_transient('node_graph_sitemap_data', $data, 12 * HOUR_IN_SECONDS);

    return $data;
}

?>
