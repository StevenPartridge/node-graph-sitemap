# Node Graph Sitemap

Node Graph Sitemap is a WordPress plugin that visualizes the interconnected pages and posts of your WordPress site as an interactive node graph. This plugin uses Cytoscape.js to render a visual representation of your site's structure, enhancing navigation and providing a unique, vintage aesthetic with a parchment and film grain overlay.

## Features

- **Interactive Node Graph**: Visualize the structure of your WordPress site with an interactive graph that displays the connections between pages and posts.
- **Dynamic Content Analysis**: Automatically detects and displays internal links, including clickable elements, buttons, and anchors.
- **Vintage Aesthetic**: Custom styling with textures and overlays to evoke an old-timey, vintage look and feel.
- **Full-Screen Mode**: Easily toggle full-screen mode for a more immersive graph exploration experience.
- **Reset View**: Reset the graph to its original layout and zoom with a single click.

Planned:
- Customizable and more neutral themes

## Installation

1. **Upload the Plugin Files**: Upload the `node-graph-sitemap` folder to the `/wp-content/plugins/` directory.
2. **Activate the Plugin**: Activate the plugin through the 'Plugins' menu in WordPress.
3. **Place the Shortcode**: Use the `[node_graph_sitemap]` shortcode on any page or post where you want to display the node graph.

## Usage

- **Full-Screen Toggle**: Click the "Full Screen" button to expand the graph to full screen. Click again or press `Esc` to exit.
- **Reset View**: Click the "Reset" button to return the graph to its original layout and zoom level.
- **Interactive Nodes**: Click on any node to open the corresponding page or post in a new tab.

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher

## Customization

### Enqueueing Styles and Scripts

The plugin enqueues necessary scripts and styles in `node_graph_sitemap_enqueue_assets()`:

- **Cytoscape.js**: For rendering the interactive graph.
- **Custom JavaScript**: Handles graph initialization, full-screen toggle, and reset view functionality.
- **Custom CSS**: Provides the vintage look and feel.

### Shortcode

The plugin provides a shortcode `[node_graph_sitemap]` to embed the graph. This shortcode outputs the graph container with buttons for full-screen mode and resetting the view.

### Dynamic Asset Loading

The plugin dynamically loads assets such as background textures and film grain overlays using URLs passed from PHP to JavaScript. Ensure that your assets are correctly placed in the `assets` folder within the plugin directory.

## Development

### Setting Up with `wp-env`

To streamline development, you can use `wp-env` to create a local WordPress environment that includes your plugin. Follow these steps to get started:

1. **Ensure Node.js is Installed**:
   - You need Node.js (version 14 or higher) and npm (Node Package Manager) installed on your system. You can download and install Node.js from [nodejs.org](https://nodejs.org/).

2. **Install `wp-env` Globally**:
   ```bash
   npm install -g @wordpress/env

3. **Start the Environment**:
    - Run the following command to start the WordPress environment with your plugin pre-installed:
    ```bash
    wp-env start
    ```
4. **Access Your Local Site**:
    - Once wp-env has started, access your local WordPress site at http://localhost:8888.
    - Your plugin will be available under the 'Plugins' menu for activation.

5. **Access Your Local Site**:
    - Stop the Environment:
    ```bash
    wp-env stop
    ```

### Key Files

- **`node-graph-sitemap.php`**: Main plugin file that handles initialization, enqueuing of scripts and styles, and shortcode rendering.
- **`node-graph-sitemap.js`**: JavaScript file that sets up and manages the Cytoscape graph, handles user interactions, and dynamically applies vintage styling.
- **`node-graph-sitemap.css`**: CSS file for additional styles to support the vintage aesthetic of the graph.

## Troubleshooting

- **Graph Not Displaying**: Ensure that all assets are correctly loaded and accessible. Check console for any errors related to script loading.
- **Buttons Not Working**: Make sure that JavaScript is enabled and there are no conflicts with other plugins or themes.
- **Performance Issues**: For sites with a large number of pages and posts, consider optimizing graph layout settings or increasing server resources.

## Contribution

Contributions are welcome! Feel free to fork the repository, submit pull requests, or open issues to improve the plugin.

## License

This plugin is licensed under the MIT License. See the LICENSE file for more information.

---

Crafted with care by [Steven Partridge](https://github.com/stevenpartridge)
