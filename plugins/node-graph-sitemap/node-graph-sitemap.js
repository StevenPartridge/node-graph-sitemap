document.addEventListener('DOMContentLoaded', () => {
    const elements = [];

    // Prepare nodes and edges from localized data
    nodeGraphData.nodes.nodes.forEach(node => {
        elements.push({
            data: { id: node.id, label: node.label, type: node.type }
        });
    });

    nodeGraphData.nodes.edges.forEach(edge => {
        // Exclude edges that point to wp-admin
        if (!edge.target.includes('wp-admin')) {
            elements.push({
                data: { source: edge.source, target: edge.target }
            });
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
                    ...baseNodeStyles,
                    'background-image': `url(${nodeGraphData.pluginUrl}assets/icon-base-scroll.png)`
                }
            },
            {
                selector: 'node[type = "page"]',
                style: {
                    ...baseNodeStyles,
                    'background-image': `url(${nodeGraphData.pluginUrl}assets/icon-base-globe.png)`
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

    // Apply vintage look class and add film grain overlay
    const cyContainer = document.getElementById('cy');
    cyContainer.classList.add('vintage-look');

    const filmGrainOverlay = document.createElement('div');
    filmGrainOverlay.className = 'film-grain-overlay';
    cyContainer.appendChild(filmGrainOverlay);

    // Full-Screen Toggle Logic
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    fullscreenBtn.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            cyContainer.requestFullscreen().catch(err => console.error(`Error entering full-screen mode: ${err.message}`));
        } else {
            document.exitFullscreen();
        }
    });

    // Handle resizing when entering/exiting full-screen
    document.addEventListener('fullscreenchange', () => {
        cy.resize();
        cy.fit();
        cy.center();
    });

    // Reset View Logic
    const resetViewBtn = document.getElementById('reset-view-btn');
    resetViewBtn.addEventListener('click', () => {
        cy.fit();
        cy.center();
    });
});
