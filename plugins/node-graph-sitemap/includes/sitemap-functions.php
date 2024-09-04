<?php
/**
 * Retrieves the site map data for nodes and edges, representing the internal link structure of the site.
 *
 * @return array An array containing nodes and edges representing the site map.
 */
function node_graph_sitemap_get_site_map() {
    $nodes = [];
    $edges = [];

    // Check if the site map data is cached and return it if available
    $data = get_transient('node_graph_sitemap_data');
    if ($data) {
        return $data;
    }

    // Retrieve and safely handle plugin settings for ignored links and options
    $ignored_pages_option = get_option('node_graph_sitemap_ignored_pages', '');

    // Ensure the retrieved option is a string before using explode
    if (is_string($ignored_pages_option)) {
        $ignored_pages = array_map('trim', explode("\n", $ignored_pages_option));
    } elseif (is_array($ignored_pages_option)) {
        $ignored_pages = $ignored_pages_option; // Use the array as is if it's already an array
    } else {
        $ignored_pages = []; // Fallback to an empty array if unexpected type
    }

    // Retrieve custom icons from settings and ensure it is an array
    $plugin_url = plugins_url('../', __FILE__);
    $default_icons = array(
        'attachment' => "{$plugin_url}assets/icons/default-icon-for-attachment.png",
        'custom_post_type' => "{$plugin_url}assets/icons/default-icon-for-custom_post_type.png",
        'link' => "{$plugin_url}assets/icons/default-icon-for-link.png",
        'page' => "{$plugin_url}assets/icons/default-icon-for-page.png",
        'post' => "{$plugin_url}assets/icons/default-icon-for-post.png"
    );

    $custom_icons = get_option('node_graph_sitemap_custom_icons', '{}');

    // Check if the retrieved option is a string and attempt to decode it as JSON
    if (is_string($custom_icons)) {
        $custom_icons = json_decode($custom_icons, true);
    }

    // If JSON decoding failed or the result is not an array, fallback to defaults
    if (!is_array($custom_icons)) {
        $custom_icons = [];
    }

    // Merge the default icons with custom icons to ensure all types are covered
    $custom_icons = array_merge($default_icons, $custom_icons);

    // Debugging to ensure the correct structure of the custom icons
    error_log(print_r($custom_icons, true)); // Logs to WordPress debug log

    // Retrieve other settings
    $ignore_external = get_option('node_graph_sitemap_ignore_external', false);
    $ignore_media = get_option('node_graph_sitemap_ignore_media', false);
    $ignore_self = get_option('node_graph_sitemap_ignore_self', false);
    $ignore_admin = get_option('node_graph_sitemap_ignore_admin', false);
    $ignore_login = get_option('node_graph_sitemap_ignore_login', false);
    $ignore_categories_tags = get_option('node_graph_sitemap_ignore_categories_tags', false);

    // Fetch all relevant posts, pages, attachments, and custom post types
    $posts = get_posts(array('numberposts' => -1, 'post_type' => array('post', 'page', 'attachment', 'custom_post_type')));

    foreach ($posts as $post) {
        $url = get_permalink($post->ID);

        // Skip ignored pages
        if (in_array($url, $ignored_pages)) {
            continue;
        }

        // Prepare node data
        $title = get_the_title($post->ID);
        $type = get_post_type($post->ID);

        // Determine the icon URL, using custom icons if available, otherwise fallback to default icons
        $icon_url = isset($custom_icons[$type]) ? $custom_icons[$type] : $default_icons[$type];

        // Add the post/page/attachment as a node
        $nodes[] = array('id' => $url, 'label' => $title, 'type' => $type, 'icon' => $icon_url);

        // Load the post content into DOMDocument for link extraction
        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // Suppress HTML parsing errors
        $dom->loadHTML($post->post_content);
        libxml_clear_errors();

        // Use XPath to extract all anchor tags with href attributes
        $xpath = new DOMXPath($dom);
        $links = $xpath->query('//a[@href]');

        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            // Apply various ignore rules based on plugin settings
            if ($ignore_external && strpos($href, home_url()) === false) {
                continue; // Ignore external links
            }
            if ($ignore_media && preg_match('/\.(jpg|jpeg|png|gif|pdf|mp4|mp3|avi)$/i', $href)) {
                continue; // Ignore media links
            }
            if ($ignore_self && $href === $url) {
                continue; // Ignore self-referential links
            }
            if ($ignore_admin && strpos($href, admin_url()) === 0) {
                continue; // Ignore admin links
            }
            if ($ignore_login && (strpos($href, wp_login_url()) === 0 || strpos($href, wp_logout_url()) === 0)) {
                continue; // Ignore login/logout links
            }

            // Check for and optionally ignore category or tag archive links
            if ($ignore_categories_tags && (is_category_link($href) || is_tag_link($href))) {
                continue; // Ignore category and tag links
            }

            // Add new nodes for internal links not already in the list
            if (strpos($href, home_url()) === 0 && !in_array($href, array_column($nodes, 'id'))) {
                $nodes[] = array('id' => $href, 'label' => basename($href), 'type' => 'link', 'icon' => $custom_icons['link']);
            }

            // Add edges only if both nodes (source and target) exist
            if (in_array($href, array_column($nodes, 'id'))) {
                $edges[] = array('source' => $url, 'target' => $href);
            }
        }
    }

    // Cache the site map data to improve performance
    $data = array('nodes' => $nodes, 'edges' => $edges);
    set_transient('node_graph_sitemap_data', $data, 15 * MINUTE_IN_SECONDS);

    return $data;
}

/**
 * Helper function to determine if a URL is a category link.
 *
 * @param string $url The URL to check.
 * @return bool True if the URL is a category link, false otherwise.
 */
function is_category_link($url) {
    foreach (get_categories() as $category) {
        if ($url === get_category_link($category->term_id)) {
            return true;
        }
    }
    return false;
}

/**
 * Helper function to determine if a URL is a tag link.
 *
 * @param string $url The URL to check.
 * @return bool True if the URL is a tag link, false otherwise.
 */
function is_tag_link($url) {
    foreach (get_tags() as $tag) {
        if ($url === get_tag_link($tag->term_id)) {
            return true;
        }
    }
    return false;
}
?>
