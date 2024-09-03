// Fetch the logging level from WordPress settings (localized or fetched via AJAX as needed)
let loggingLevel = nodeGraphData.loggingLevel; // Default to 'error'

const levels = {
    error: 1,  // Logs only errors
    debug: 2,  // Logs debug, log, and warn
    info: 3    // Logs everything, including info
};

// Determine the current level based on settings
const currentLevel = levels[loggingLevel] || levels['error']; // Default to 'error' if not found

const Logger = {
    log: (...args) => {
        if (currentLevel >= levels.debug) {
            console.log(...args);
        }
    },
    warn: (...args) => {
        if (currentLevel >= levels.debug) {
            console.warn(...args);
        }
    },
    error: (...args) => {
        // Always log errors
        if (currentLevel >= levels.error) {
            console.error(...args);
        }
    },
    info: (...args) => {
        if (currentLevel >= levels.info) {
            console.info(...args);
        }
    },
    debug: (...args) => {
        if (currentLevel >= levels.debug) {
            console.debug(...args);
        }
    }
};

export default Logger;
