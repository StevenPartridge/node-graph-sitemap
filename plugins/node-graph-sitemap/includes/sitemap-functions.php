<?php
// Retrieve the site map data for nodes and edges
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
        
        if (in_array($url, $ignored_pages)) {
            continue;
        }

        $title = get_the_title($post->ID);
        $type  = get_post_type($post->ID);
        $plugin_url = plugins_url('../', __FILE__);
        $icon_url = isset($custom_icons[$type]) ? $custom_icons[$type] : "{$plugin_url}assets/icons/default-icon-for-{$type}.png";

        $nodes[] = array('id' => $url, 'label' => $title, 'type' => $type, 'icon' => $icon_url);

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($post->post_content);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $links = $xpath->query('//a[@href]');

        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            if ($ignore_external && strpos($href, home_url()) === false) {
                continue;
            }

            if ($ignore_media && preg_match('/\.(jpg|jpeg|png|gif|pdf|mp4|mp3|avi)$/i', $href)) {
                continue;
            }

            if ($ignore_self && $href === $url) {
                continue;
            }

            if ($ignore_admin && strpos($href, admin_url()) === 0) {
                continue;
            }

            if ($ignore_login && (strpos($href, wp_login_url()) === 0 || strpos($href, wp_logout_url()) === 0)) {
                continue;
            }

            // Check if the link points to a category or tag archive and ignore if applicable
            if ($ignore_categories_tags) {
                $is_category = false;
                $is_tag = false;

                // Check if the link is a category link by iterating through all categories
                foreach (get_categories() as $category) {
                    if ($href === get_category_link($category->term_id)) {
                        $is_category = true;
                        break;
                    }
                }

                // Check if the link is a tag link by iterating through all tags
                foreach (get_tags() as $tag) {
                    if ($href === get_tag_link($tag->term_id)) {
                        $is_tag = true;
                        break;
                    }
                }

                // Skip if it's a category or tag link
                if ($is_category || $is_tag) {
                    continue;
                }
            }

            if (strpos($href, home_url()) === 0 && !in_array($href, array_column($nodes, 'id'))) {
                $nodes[] = array('id' => $href, 'label' => basename($href), 'type' => 'link');
            }

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
