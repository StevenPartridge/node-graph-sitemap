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
        <!-- Graph container with buttons for fullscreen and reset functionality -->
        <div id="graph-container">
            <button id="fullscreen-btn" class="graph-btn" aria-label="Toggle Full Screen">
                Full Screen
            </button>
            <button id="reset-view-btn" class="graph-btn" aria-label="Reset Graph View">
                Reset
            </button>
            <!-- Graph display area -->
            <div id="cy" style="width: 100%; height: 600px;" aria-live="polite"></div>
        </div>

        <!-- SVG filter for a vintage paper effect on the graph container -->
        <svg style="display: none;">
            <filter id="wavy2">
                <feTurbulence x="0" y="0" baseFrequency="0.02" numOctaves="5" seed="1"></feTurbulence>
                <feDisplacementMap in="SourceGraphic" scale="20" />
            </filter>
        </svg>
    </div>
    <?php
    // Capture and return the output buffer content
    return ob_get_clean();
}

// Register the shortcode to make it available in WordPress
add_shortcode('node_graph_sitemap', 'node_graph_sitemap_shortcode');
?>
