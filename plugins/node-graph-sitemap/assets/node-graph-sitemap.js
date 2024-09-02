document.addEventListener('DOMContentLoaded', () => {
    const elements = [];

    // Prepare nodes and edges from localized data
    nodeGraphData.nodes.nodes.forEach(node => {
        console.log(node);


        let icon = node.icon ? `url(${node.icon})` : `url(${nodeGraphData.pluginUrl}assets/icons/default-icon-for-post.png)`; // Use custom icon if available
        // if type = link, use the link icon
        if (node.type === 'link') {
            icon = `url(${nodeGraphData.pluginUrl}assets/icons/default-icon-for-link.png)`;
        }

        // Add nodes with correct data and styles directly
        elements.push({
            data: { id: node.id, label: node.label, type: node.type },
            style: {
                'background-image': icon,
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
            }
        });
    });

    nodeGraphData.nodes.edges.forEach(edge => {
        // Check if both source and target nodes exist before creating an edge
        const sourceExists = elements.some(el => el.data.id === edge.source);
        const targetExists = elements.some(el => el.data.id === edge.target);
    
        if (sourceExists && targetExists) {
            elements.push({
                data: { source: edge.source, target: edge.target }
            });
        } else {
            console.warn(`Skipping edge creation for missing nodes: ${edge.source} -> ${edge.target}`);
        }
    });

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
                    'background-image': `url(${nodeGraphData.pluginUrl}assets/icon-base-scroll.png)`,
                    ...baseNodeStyles
                }
            },
            {
                selector: 'node[type = "page"]',
                style: {
                    'background-image': `url(${nodeGraphData.pluginUrl}assets/icon-base-globe.png)`,
                    ...baseNodeStyles
                }
            },
            {
                selector: 'node[type = "attachment"]',
                style: {
                    'background-image': `url(${nodeGraphData.pluginUrl}assets/icon-attachment.png)`,
                    ...baseNodeStyles
                }
            },
            {
                selector: 'node[type = "link"]',
                style: {
                    'background-image': `url(${nodeGraphData.pluginUrl}assets/icon-link.png)`,
                    ...baseNodeStyles
                }
            },
            {
                selector: 'node[type = "custom"]', // Example for custom post types
                style: {
                    'background-image': `url(${nodeGraphData.pluginUrl}assets/icon-custom.png)`,
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
        maxZoom: 2,
        wheelSensitivity: 0.1
    });

    // Re-run layout on node drag to keep the graph tidy
    cy.on('dragfree', 'node', () => {
        cy.layout({ name: 'cose', animate: true, fit: true }).run();
    });

    // Open node URL in a new tab on click
    cy.on('tap', 'node', evt => {
        window.open(evt.target.id(), '_blank');
    });

    // Full-Screen Toggle Logic
    const cyContainer = document.getElementById('cy');
    const fullscreenBtn = document.getElementById('fullscreen-btn');

    fullscreenBtn.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            cyContainer.requestFullscreen()
                .then(() => {
                    cy.resize(); // Adjust the graph size after entering full screen
                    cy.fit();
                    cy.center();
                })
                .catch(err => console.error(`Error entering full-screen mode: ${err.message}`));
        } else {
            document.exitFullscreen()
                .then(() => {
                    cy.resize(); // Adjust the graph size after exiting full screen
                    cy.fit();
                    cy.center();
                })
                .catch(err => console.error(`Error exiting full-screen mode: ${err.message}`));
        }
    });

    // Handle resizing when entering/exiting full-screen
    document.addEventListener('fullscreenchange', () => {
        cy.resize(); // Resize graph on full-screen toggle
        cy.fit();
        cy.center();
    });

    // Reset View Logic
    const resetViewBtn = document.getElementById('reset-view-btn');
    resetViewBtn.addEventListener('click', () => {
        cy.fit();
        cy.center();
    });

    jQuery(document).ready(function($) {
        if ($('.js-ignore-pages').length > 0) {
            $('.js-ignore-pages').select2(); // Initialize select2 on the dropdown
        } else {
            console.error('Select2 target element not found');
        }
    });
    
});
