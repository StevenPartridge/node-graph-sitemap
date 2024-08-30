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

### Folder Structure

