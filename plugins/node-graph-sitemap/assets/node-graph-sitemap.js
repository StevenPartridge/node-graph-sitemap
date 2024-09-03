document.addEventListener('DOMContentLoaded', () => {
    const elements = [];
    const nodeIcons = {};

    // Prepare nodes and edges from localized data
    nodeGraphData.nodes.nodes.forEach(node => {
        // Add nodes with correct data and styles directly
        const icon = node.icon ? node.icon : nodeGraphData.pluginUrl + `assets/icons/default-icon-for-${node.type}.png`;
        if (!nodeIcons[node.type]) {
            nodeIcons[node.type] = icon;
        }

        elements.push({
            data: { id: node.id, label: node.label, type: node.type }
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

    // In your custom-script.js or inline script
    jQuery(document).ready(function($) {
        var $ignorePages = $('.js-ignore-pages');
        
        // Check if the target element exists before initializing Select2
        if ($ignorePages.length > 0) {
            $ignorePages.select2(); // Initialize Select2 on the dropdown
        } else {
            console.warn('Select2 target element not found');
        }
    });
    
});
