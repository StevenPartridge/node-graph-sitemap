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

    error_log('Node Graph Sitemap Data: ' . print_r(node_graph_sitemap_get_site_map(), true));
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

    // Retrieve settings
    $ignored_pages = explode("\n", get_option('node_graph_sitemap_ignored_pages', ''));
    $ignored_pages = array_map('trim', $ignored_pages);

    $ignore_external = get_option('node_graph_sitemap_ignore_external', false);
    $ignore_media = get_option('node_graph_sitemap_ignore_media', false);
    $ignore_self = get_option('node_graph_sitemap_ignore_self', false);
    $ignore_admin = get_option('node_graph_sitemap_ignore_admin', false);
    $ignore_login = get_option('node_graph_sitemap_ignore_login', false);
    $ignore_categories_tags = get_option('node_graph_sitemap_ignore_categories_tags', false);

    $posts = get_posts(array('numberposts' => -1, 'post_type' => array('post', 'page', 'attachment', 'custom_post_type')));

    foreach ($posts as $post) {
        $url = get_permalink($post->ID);
        
        // Skip ignored pages
        if (in_array($url, $ignored_pages)) {
            continue;
        }

        $title = get_the_title($post->ID);
        $type  = get_post_type($post->ID);
        $plugin_url = plugins_url('/', __FILE__);
        $icon_url = isset($custom_icons[$type]) ? $custom_icons[$type] : "{$plugin_url}assets/default-icon-for-{$type}.png";

        // Add node
        $nodes[] = array('id' => $url, 'label' => $title, 'type' => $type, 'icon' => $icon_url);

        // Parse links in content
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($post->post_content);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $links = $xpath->query('//a[@href]');

        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            // Ignore external links
            if ($ignore_external && strpos($href, home_url()) === false) {
                continue;
            }

            // Ignore media links
            if ($ignore_media && preg_match('/\.(jpg|jpeg|png|gif|pdf|mp4|mp3|avi)$/i', $href)) {
                continue;
            }

            // Ignore self (loopback) links
            if ($ignore_self && $href === $url) {
                continue;
            }

            // Ignore admin links
            if ($ignore_admin && strpos($href, admin_url()) === 0) {
                continue;
            }

            // Ignore login/logout links
            if ($ignore_login && (strpos($href, wp_login_url()) === 0 || strpos($href, wp_logout_url()) === 0)) {
                continue;
            }

            // Ignore categories and tags
            if ($ignore_categories_tags && (strpos($href, get_category_link()) !== false || strpos($href, get_tag_link()) !== false)) {
                continue;
            }

            // Add link as node if internal
            if (strpos($href, home_url()) === 0 && !in_array($href, array_column($nodes, 'id'))) {
                $nodes[] = array('id' => $href, 'label' => basename($href), 'type' => 'link');
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



// Add settings menu item
function node_graph_sitemap_add_admin_menu() {
    add_menu_page(
        'Node Graph Sitemap Settings',
        'Node Graph Sitemap',
        'manage_options',
        'node-graph-sitemap',
        'node_graph_sitemap_settings_page',
        'dashicons-networking', // Icon for the menu item
        80 // Position in the admin menu
    );
}
add_action('admin_menu', 'node_graph_sitemap_add_admin_menu');

function node_graph_sitemap_enqueue_admin_scripts() {
    wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);
    wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
}
add_action('admin_enqueue_scripts', 'node_graph_sitemap_enqueue_admin_scripts');

// Register additional settings for ignore scenarios
function node_graph_sitemap_register_settings() {
    register_setting('node_graph_sitemap_options', 'node_graph_sitemap_ignored_pages');
    register_setting('node_graph_sitemap_options', 'node_graph_sitemap_custom_icons');
    register_setting('node_graph_sitemap_options', 'node_graph_sitemap_ignore_external');
    register_setting('node_graph_sitemap_options', 'node_graph_sitemap_ignore_media');
    register_setting('node_graph_sitemap_options', 'node_graph_sitemap_ignore_self');
    register_setting('node_graph_sitemap_options', 'node_graph_sitemap_ignore_admin');
    register_setting('node_graph_sitemap_options', 'node_graph_sitemap_ignore_login');
    register_setting('node_graph_sitemap_options', 'node_graph_sitemap_ignore_categories_tags');
}
add_action('admin_init', 'node_graph_sitemap_register_settings');

// Settings page callback with multi-select dropdown
// Updated settings page callback
function node_graph_sitemap_settings_page() {
    // Retrieve options
    $ignored_pages = get_option('node_graph_sitemap_ignored_pages', array());
    if (!is_array($ignored_pages)) {
        $ignored_pages = array();
    }

    $custom_icons = get_option('node_graph_sitemap_custom_icons', '{}');
    $custom_icons = json_decode($custom_icons, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $custom_icons = '{}'; // Default to an empty JSON object if invalid
    }

    $ignore_external = get_option('node_graph_sitemap_ignore_external', false);
    $ignore_media = get_option('node_graph_sitemap_ignore_media', false);
    $ignore_self = get_option('node_graph_sitemap_ignore_self', false);
    $ignore_admin = get_option('node_graph_sitemap_ignore_admin', false);
    $ignore_login = get_option('node_graph_sitemap_ignore_login', false);
    $ignore_categories_tags = get_option('node_graph_sitemap_ignore_categories_tags', false);
    ?>

    <div class="wrap">
        <h1>Node Graph Sitemap Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('node_graph_sitemap_options');
            do_settings_sections('node_graph_sitemap_options');
            ?>

            <h2>Ignored Pages</h2>
            <select name="node_graph_sitemap_ignored_pages[]" multiple="multiple" class="js-ignore-pages" style="width: 100%;">
                <?php
                // Get all pages/posts for selection
                $pages = get_posts(array('numberposts' => -1, 'post_type' => array('post', 'page')));
                foreach ($pages as $page) {
                    $url = get_permalink($page->ID);
                    $selected = in_array($url, $ignored_pages) ? 'selected' : '';
                    echo "<option value='" . esc_attr($url) . "' $selected>" . esc_html($page->post_title) . "</option>";
                }
                ?>
            </select>
            <p>Select the pages to ignore from the graph.</p>

            <h2>Ignore Scenarios</h2>
            <label>
                <input type="checkbox" name="node_graph_sitemap_ignore_external" value="1" <?php checked(1, $ignore_external); ?> />
                Ignore all external links
            </label><br>

            <label>
                <input type="checkbox" name="node_graph_sitemap_ignore_media" value="1" <?php checked(1, $ignore_media); ?> />
                Ignore links to media
            </label><br>

            <label>
                <input type="checkbox" name="node_graph_sitemap_ignore_self" value="1" <?php checked(1, $ignore_self); ?> />
                Ignore links to self (loopback)
            </label><br>

            <label>
                <input type="checkbox" name="node_graph_sitemap_ignore_admin" value="1" <?php checked(1, $ignore_admin); ?> />
                Ignore links to admin pages
            </label><br>

            <label>
                <input type="checkbox" name="node_graph_sitemap_ignore_login" value="1" <?php checked(1, $ignore_login); ?> />
                Ignore links to login/logout pages
            </label><br>

            <label>
                <input type="checkbox" name="node_graph_sitemap_ignore_categories_tags" value="1" <?php checked(1, $ignore_categories_tags); ?> />
                Ignore links to categories and tags
            </label><br>

            <h2>Custom Icons</h2>
            <textarea name="node_graph_sitemap_custom_icons" rows="10" style="width: 100%;"><?php echo esc_textarea(json_encode($custom_icons, JSON_PRETTY_PRINT)); ?></textarea>
            <p>Specify custom icons in JSON format. Example: {"post": "icon-url.png", "page": "icon-url.png"}</p>

            <?php submit_button(); ?>
        </form>

        <script>
            jQuery(document).ready(function($) {
                if ($.fn.select2) {
                    $('.js-ignore-pages').select2(); // Initialize select2 on the dropdown
                } else {
                    console.error('Select2 library is not loaded.');
                }
            });
        </script>
    </div>
    <?php
}

?>
