<?php
// Add settings menu item
function node_graph_sitemap_add_admin_menu() {
    add_menu_page(
        'Node Graph Sitemap Settings',
        'Node Graph Sitemap',
        'manage_options',
        'node-graph-sitemap',
        'node_graph_sitemap_settings_page',
        'dashicons-networking',
        80
    );
}
add_action('admin_menu', 'node_graph_sitemap_add_admin_menu');

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
function node_graph_sitemap_settings_page() {
    $ignored_pages = get_option('node_graph_sitemap_ignored_pages', array());
    if (!is_array($ignored_pages)) {
        $ignored_pages = array();
    }

    $custom_icons = get_option('node_graph_sitemap_custom_icons', '{}');
    $custom_icons = json_decode($custom_icons, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $custom_icons = '{}';
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
