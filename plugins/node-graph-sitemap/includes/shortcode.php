<?php
/**
 * Shortcode to display the interactive node graph container with controls.
 *
 * Outputs a structured HTML layout for the graph, including fullscreen and reset buttons,
 * and a hidden SVG filter for visual effects on the graph container.
 *
 * @return string HTML output for the graph container.
 */
function node_graph_sitemap_shortcode() {
    // Start output buffering to capture the HTML output
    ob_start();
    ?>
    <div id="parchment" class="node-graph-container">
        <!-- Floating buttons to keep them out of the graph's way -->
        <button id="fullscreen-btn" class="graph-btn" aria-label="Toggle Full Screen">
            Full Screen
        </button>
        <button id="reset-view-btn" class="graph-btn" aria-label="Reset Graph View">
            Reset
        </button>

        <!-- Expanded graph container -->
        <div id="graph-container">
            <div id="cy" aria-live="polite"></div>
        </div>
    </div>
    <?php
    // Capture and return the output buffer content
    return ob_get_clean();
}

// Register the shortcode to make it available in WordPress
add_shortcode('node_graph_sitemap', 'node_graph_sitemap_shortcode');
?>
