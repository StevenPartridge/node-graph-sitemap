/**
 * Generates a clip-path with spikes and depth.
 * 
 * @param {number} numSpikes - The number of spikes in the clip-path.
 * @param {number} depth - The depth of the spikes in percentage.
 * @returns {string} - The clip-path value.
 */
function generateClipPath(numSpikes, depth) {
    const points = [];
    const step = 100 / numSpikes; // Divide the shape evenly by the number of spikes

    // Generate points for the top edge
    for (let i = 0; i <= numSpikes; i++) {
        const x = i * step;
        const y = i % 2 === 0 ? 0 : depth; // Alternating depth for jagged effect
        points.push(`${x}% ${y}%`);
    }

    // Generate points for the right edge
    for (let i = 0; i <= numSpikes; i++) {
        const y = i * step;
        const x = i % 2 === 0 ? 100 : 100 - depth;
        points.push(`${x}% ${y}%`);
    }

    // Generate points for the bottom edge
    for (let i = 0; i <= numSpikes; i++) {
        const x = 100 - i * step;
        const y = i % 2 === 0 ? 100 : 100 - depth;
        points.push(`${x}% ${y}%`);
    }

    // Generate points for the left edge
    for (let i = 0; i <= numSpikes; i++) {
        const y = 100 - i * step;
        const x = i % 2 === 0 ? 0 : depth;
        points.push(`${x}% ${y}%`);
    }

    // Join the points into a string suitable for clip-path
    return `polygon(${points.join(', ')})`;
}

// Example usage:
const clipPath = generateClipPath(100, .25); // Generates a clip-path with 20 spikes and 2% depth
console.log(clipPath); // Outputs the clip-path value
