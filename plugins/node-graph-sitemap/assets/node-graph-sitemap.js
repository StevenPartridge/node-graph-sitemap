import Logger from './logger.js'; // Adjust the path if needed based on your directory structure

document.addEventListener('DOMContentLoaded', async () => {
    const elements = [];
    const nodeIcons = {};

    Logger.info('Initializing Node Graph Sitemap...');

    try {
        // Prepare nodes and edges from localized data
        nodeGraphData.nodes.nodes.forEach(node => {
            // Add nodes with correct data and styles directly
            const icon = node.icon ? node.icon : nodeGraphData.pluginUrl + `assets/icons/default-icon-for-${node.type}.png`;
            if (!nodeIcons[node.type]) {
                nodeIcons[node.type] = icon;
                Logger.debug(`Icon set for node type '${node.type}': ${icon}`);
            }

            elements.push({
                data: { id: node.id, label: node.label, type: node.type }
            });
        });

        Logger.info(`Processed ${elements.length} nodes:`, elements);

        nodeGraphData.nodes.edges.forEach(edge => {
            // Check if both source and target nodes exist before creating an edge
            const sourceExists = elements.some(el => el.data.id === edge.source);
            const targetExists = elements.some(el => el.data.id === edge.target);

            if (sourceExists && targetExists) {
                elements.push({
                    data: { source: edge.source, target: edge.target }
                });
                Logger.debug(`Edge created between nodes: ${edge.source} -> ${edge.target}`);
            } else {
                Logger.warn(`Skipping edge due to missing nodes: ${edge.source} -> ${edge.target}`);
            }
        });

        Logger.info(`Processed edges and finalized elements array with ${elements.length} items.`);

        // Define base styles for nodes
        const baseNodeStyles = {
            'background-fit': 'cover',
            'background-image-opacity': 1,
            'width': 40,
            'height': 40,
            'label': 'data(label)',
            'text-valign': 'bottom',
            'text-halign': 'center',
            'font-size': '10px',
            'color': '#ffffff',
            'text-outline-width': 1,
            'text-outline-color': '#333'
        };

        // Initialize Cytoscape with graph container and styles
        const cy = cytoscape({
            container: document.getElementById('cy'),
            elements: elements,
            style: [
                {
                    selector: 'node[type = "post"]',
                    style: {
                        'background-image': `url(${nodeIcons.post})`,
                        ...baseNodeStyles
                    }
                },
                {
                    selector: 'node[type = "page"]',
                    style: {
                        'background-image': `url(${nodeIcons.page})`,
                        ...baseNodeStyles
                    }
                },
                {
                    selector: 'node[type = "attachment"]',
                    style: {
                        'background-image': `url(${nodeIcons.attachment})`,
                        ...baseNodeStyles
                    }
                },
                {
                    selector: 'node[type = "link"]',
                    style: {
                        'background-image': `url(${nodeIcons.link})`,
                        ...baseNodeStyles
                    }
                },
                {
                    selector: 'node[type = "custom"]',
                    style: {
                        'background-image': `url(${nodeIcons.custom})`,
                        ...baseNodeStyles
                    }
                },
                {
                    selector: 'edge',
                    style: {
                        'width': 3,
                        'line-color': '#666',
                        'target-arrow-color': '#666',
                        'target-arrow-shape': 'triangle',
                        'curve-style': 'bezier'
                    }
                },
                {
                    selector: ':selected',
                    style: {
                        'border-width': 4,
                        'border-color': '#FFD700'
                    }
                }
            ],
            layout: {
                name: 'cose',
                padding: 10,
                animate: true,
                animationDuration: 800,
                fit: true,
                gravity: 1,
                nodeDimensionsIncludeLabels: true
            },
            zoom: 0.7,
            minZoom: 0.5,
            maxZoom: 2
            // wheelSensitivity: 0.1
        });

        // Use the 'ready' event to ensure Cytoscape has finished initializing
        cy.ready(() => {
            cy.resize(); // Resize to ensure proper dimensions
            cy.fit();    // Fit the elements to the viewport
            cy.center(); // Center the graph
        });

        Logger.info('Cytoscape initialized with all elements.');

        // Re-run layout on node drag to keep the graph tidy
        cy.on('dragfree', 'node', () => {
            Logger.debug('Node drag detected; re-running layout.');
            cy.layout({ name: 'cose', animate: true, fit: true }).run();
        });

        // Open node URL in a new tab on click
        cy.on('tap', 'node', evt => {
            const url = evt.target.id();
            Logger.info(`Node clicked: ${url}`);
            window.open(url, '_blank');
        });

        // Full-Screen Toggle Logic
        const cyContainer = document.getElementById('cy');
        const fullscreenBtn = document.getElementById('fullscreen-btn');

        fullscreenBtn.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                cyContainer.requestFullscreen()
                    .then(() => {
                        Logger.info('Entered full-screen mode.');
                        cy.resize(); // Adjust the graph size after entering full screen
                        cy.fit();
                        cy.center();
                    })
                    .catch(err => Logger.error(`Error entering full-screen mode: ${err.message}`));
            } else {
                document.exitFullscreen()
                    .then(() => {
                        Logger.info('Exited full-screen mode.');
                        cy.resize(); // Adjust the graph size after exiting full screen
                        cy.fit();
                        cy.center();
                    })
                    .catch(err => Logger.error(`Error exiting full-screen mode: ${err.message}`));
            }
        });

        // Handle resizing when entering/exiting full-screen
        document.addEventListener('fullscreenchange', () => {
            Logger.debug('Full-screen change detected; resizing graph.');
            cy.resize(); // Resize graph on full-screen toggle
            cy.fit();
            cy.center();
        });

        // Reset View Logic
        const resetViewBtn = document.getElementById('reset-view-btn');
        resetViewBtn.addEventListener('click', () => {
            Logger.info('Reset view button clicked.');
            cy.fit();
            cy.center();
        });

        // In your custom-script.js or inline script
        jQuery(document).ready(function($) {
            var $ignorePages = $('.js-ignore-pages');

            // Check if the target element exists before initializing Select2
            if ($ignorePages.length > 0) {
                $ignorePages.select2(); // Initialize Select2 on the dropdown
                Logger.info('Select2 initialized on .js-ignore-pages.');
            } else {
                Logger.warn('Select2 target element not found.');
            }
        });
    } catch (error) {
        Logger.error('An unexpected error occurred:', error);
    }
});
