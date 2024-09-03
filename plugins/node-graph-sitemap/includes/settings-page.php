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
    register_setting('node_graph_sitemap_options', 'node_graph_sitemap_logging_level');
}
add_action('admin_init', 'node_graph_sitemap_register_settings');

// Settings page callback with multi-select dropdown and media library integration
function node_graph_sitemap_settings_page() {

    $ignored_pages = get_option('node_graph_sitemap_ignored_pages', array());
    
    if (!is_array($ignored_pages)) {
        $ignored_pages = array();
    }
    
    // Set the default icons with paths from the plugin assets
    $plugin_url = plugins_url('../', __FILE__);
    $default_icons = array(
        'attachment' => "{$plugin_url}assets/icons/default-icon-for-attachment.png",
        'custom_post_type' => "{$plugin_url}assets/icons/default-icon-for-custom_post_type.png",
        'link' => "{$plugin_url}assets/icons/default-icon-for-link.png",
        'page' => "{$plugin_url}assets/icons/default-icon-for-page.png",
        'post' => "{$plugin_url}assets/icons/default-icon-for-post.png"
    );

    // Retrieve the custom icons option and ensure it is an array
    $custom_icons = get_option('node_graph_sitemap_custom_icons', $default_icons);

    // Check if the retrieved option is a string and attempt to decode it as JSON
    if (is_string($custom_icons)) {
        $custom_icons = json_decode($custom_icons, true);
    }

    // If JSON decoding failed or the result is not an array, fallback to defaults
    if (!is_array($custom_icons)) {
        $custom_icons = $default_icons;
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
            <?php foreach ($custom_icons as $type => $icon_url) : ?>
                <div>
                    <label><?php echo ucfirst($type); ?> Icon</label><br>
                    <input type="text" name="node_graph_sitemap_custom_icons[<?php echo esc_attr($type); ?>]" value="<?php echo esc_url($icon_url); ?>" style="width: 70%;" />
                    <button type="button" class="button js-select-icon" data-target="input[name='node_graph_sitemap_custom_icons[<?php echo esc_attr($type); ?>]']">Select Icon</button>
                    <?php if ($icon_url): ?>
                        <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($type); ?> icon" style="max-width: 50px; vertical-align: middle;" />
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <h2>Logging Level</h2>
            <select name="node_graph_sitemap_logging_level">
                <option value="error" <?php selected($logging_level, 'error'); ?>>Error (default, errors only)</option>
                <option value="debug" <?php selected($logging_level, 'debug'); ?>>Debug (verbose, includes warnings and logs)</option>
                <option value="info" <?php selected($logging_level, 'info'); ?>>Info (most verbose, includes everything)</option>
            </select>
            <p>Select the level of logging. "Error" will show only errors, "Debug" will show more details including warnings, and "Info" will show all log messages including info logs.</p>

            <?php submit_button(); ?>
        </form>

        <script>
            jQuery(document).ready(function($) {
                // Initialize Select2 for ignored pages
                if ($.fn.select2) {
                    $('.js-ignore-pages').select2();
                } else {
                    console.error('Select2 library is not loaded.');
                }

                // Media library selection
                $('.js-select-icon').on('click', function() {
                    var button = $(this);
                    var targetInput = button.data('target');
                    var frame = wp.media({
                        title: 'Select or Upload Media',
                        button: { text: 'Use this icon' },
                        library: { type: 'image' },
                        multiple: false
                    });

                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        $(targetInput).val(attachment.url);
                    });

                    frame.open();
                });
            });
        </script>
    </div>
    <?php
}
?>
