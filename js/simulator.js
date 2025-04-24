/**
 * RSyslog WebUI - Simulator
 * Generates test logs and alerts for development and testing purposes
 */

document.addEventListener('DOMContentLoaded', function() {
    const startSimBtn = document.getElementById('startSimulation');
    
    if (startSimBtn) {
        startSimBtn.addEventListener('click', function() {
            startSimulation();
        });
    }
});

/**
 * Start the log simulation
 */
function startSimulation() {
    // Create simulation modal
    if (!document.getElementById('simulationModal')) {
        createSimulationModal();
    }
    
    // Show the modal
    const simModal = new bootstrap.Modal(document.getElementById('simulationModal'));
    simModal.show();
    
    // Set default values
    document.getElementById('simDuration').value = 5;
    document.getElementById('logFrequency').value = 10;
    document.getElementById('errorFrequency').value = 20;
    document.getElementById('triggerAlerts').checked = true;
    
    // Update progress indicators
    updateSimulationStatus('Ready to start simulation');
    updateProgressBar(0);
}

/**
 * Create the simulation modal UI
 */
function createSimulationModal() {
    const modalHtml = `
    <div class="modal fade" id="simulationModal" tabindex="-1" aria-labelledby="simulationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="simulationModalLabel">Log Simulator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Generate test logs and alerts for development and testing</p>
                    
                    <form id="simulationForm" class="mb-3">
                        <div class="mb-3">
                            <label for="simDuration" class="form-label">Duration (minutes)</label>
                            <input type="range" class="form-range" id="simDuration" min="1" max="15" value="5">
                            <div class="d-flex justify-content-between">
                                <small>1</small>
                                <small id="simDurationValue">5</small>
                                <small>15</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="logFrequency" class="form-label">Log Frequency (per second)</label>
                            <input type="range" class="form-range" id="logFrequency" min="1" max="100" value="10">
                            <div class="d-flex justify-content-between">
                                <small>1</small>
                                <small id="logFrequencyValue">10</small>
                                <small>100</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="errorFrequency" class="form-label">Error Percentage (%)</label>
                            <input type="range" class="form-range" id="errorFrequency" min="0" max="100" value="20">
                            <div class="d-flex justify-content-between">
                                <small>0%</small>
                                <small id="errorFrequencyValue">20%</small>
                                <small>100%</small>
                            </div>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="triggerAlerts" checked>
                            <label class="form-check-label" for="triggerAlerts">Generate Alerts</label>
                        </div>
                    </form>
                    
                    <div class="progress mb-3">
                        <div id="simulationProgress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
                    
                    <div id="simulationStatus" class="border rounded p-2 bg-light mb-3">
                        <small class="text-muted">Status: Ready to start</small>
                    </div>
                    
                    <div id="simulationStats" class="d-none">
                        <h6>Simulation Results</h6>
                        <div class="row">
                            <div class="col-6">
                                <div class="border rounded p-2 mb-2 text-center">
                                    <div id="logsGenerated" class="fs-4 fw-bold">0</div>
                                    <small class="text-muted">Logs Generated</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 mb-2 text-center">
                                    <div id="alertsGenerated" class="fs-4 fw-bold">0</div>
                                    <small class="text-muted">Alerts Generated</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="runSimulationBtn" class="btn btn-primary">Run Simulation</button>
                </div>
            </div>
        </div>
    </div>`;
    
    // Append the modal to the body
    const modalDiv = document.createElement('div');
    modalDiv.innerHTML = modalHtml;
    document.body.appendChild(modalDiv.firstElementChild);
    
    // Set up event listeners for the range inputs
    document.getElementById('simDuration').addEventListener('input', function() {
        document.getElementById('simDurationValue').textContent = this.value;
    });
    
    document.getElementById('logFrequency').addEventListener('input', function() {
        document.getElementById('logFrequencyValue').textContent = this.value;
    });
    
    document.getElementById('errorFrequency').addEventListener('input', function() {
        document.getElementById('errorFrequencyValue').textContent = this.value + '%';
    });
    
    // Run simulation button
    document.getElementById('runSimulationBtn').addEventListener('click', function() {
        runSimulation();
    });
}

/**
 * Execute the simulation with the configured parameters
 */
function runSimulation() {
    const duration = parseInt(document.getElementById('simDuration').value);
    const logFrequency = parseInt(document.getElementById('logFrequency').value);
    const errorFrequency = parseInt(document.getElementById('errorFrequency').value);
    const triggerAlerts = document.getElementById('triggerAlerts').checked;
    
    // Disable form controls and run button
    document.querySelectorAll('#simulationForm input, #runSimulationBtn').forEach(el => {
        el.disabled = true;
    });
    
    // Reset stats display
    document.getElementById('simulationStats').classList.remove('d-none');
    document.getElementById('logsGenerated').textContent = '0';
    document.getElementById('alertsGenerated').textContent = '0';
    
    // Show initial status
    updateSimulationStatus('Starting simulation...');
    updateProgressBar(2);
    
    // Start the simulation
    const totalTime = duration * 60 * 1000; // Convert minutes to milliseconds
    const startTime = Date.now();
    const endTime = startTime + totalTime;
    let logsGenerated = 0;
    let alertsGenerated = 0;
    
    // Simulation interval
    const simulationInterval = setInterval(() => {
        const currentTime = Date.now();
        const elapsedTime = currentTime - startTime;
        const percentComplete = Math.min(100, Math.round((elapsedTime / totalTime) * 100));
        
        // Update progress
        updateProgressBar(percentComplete);
        
        if (currentTime >= endTime) {
            // Simulation finished
            clearInterval(simulationInterval);
            
            updateSimulationStatus(`Simulation complete! Generated ${logsGenerated} logs and ${alertsGenerated} alerts.`);
            
            // Re-enable form controls and button
            document.querySelectorAll('#simulationForm input, #runSimulationBtn').forEach(el => {
                el.disabled = false;
            });
            
            return;
        }
        
        // Generate logs
        const logsToGenerate = Math.round(logFrequency / 10); // Adjust based on interval frequency
        for (let i = 0; i < logsToGenerate; i++) {
            generateLogEntry(errorFrequency, triggerAlerts).then(result => {
                if (result.success) {
                    logsGenerated++;
                    document.getElementById('logsGenerated').textContent = logsGenerated;
                    
                    if (result.alert) {
                        alertsGenerated++;
                        document.getElementById('alertsGenerated').textContent = alertsGenerated;
                    }
                }
            });
        }
        
        updateSimulationStatus(`Running simulation... ${percentComplete}% complete`);
    }, 100); // Update every 100ms
}

/**
 * Generate a single log entry
 * @param {number} errorProbability - Probability of generating an error log (0-100)
 * @param {boolean} allowAlerts - Whether to generate alerts for severe errors
 * @returns {Promise} - Promise that resolves with the result of the log generation
 */
function generateLogEntry(errorProbability, allowAlerts) {
    // Determine log severity
    const isError = Math.random() * 100 <= errorProbability;
    const severity = isError ? 
        generateRandomSeverity(['error', 'critical', 'alert', 'emergency']) : 
        generateRandomSeverity(['info', 'notice', 'warning']);
    
    // Determine if should generate an alert
    const shouldCreateAlert = allowAlerts && 
        (severity === 'critical' || severity === 'alert' || severity === 'emergency') &&
        Math.random() > 0.7; // 30% chance for alert-worthy severe logs
    
    // Generate simulation data
    const logData = {
        timestamp: new Date().toISOString(),
        source: generateRandomSource(),
        facility: generateRandomFacility(),
        severity: severity,
        message: generateRandomMessage(severity),
        create_alert: shouldCreateAlert ? 1 : 0
    };
    
    // Send log data to server
    return fetch('json/simulate_log.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(logData)
    })
    .then(response => response.json())
    .then(data => {
        return {
            success: true,
            alert: shouldCreateAlert
        };
    })
    .catch(error => {
        console.error('Error simulating log:', error);
        return {
            success: false,
            alert: false
        };
    });
}

/**
 * Update the simulation status message
 * @param {string} message - Status message to display
 */
function updateSimulationStatus(message) {
    document.getElementById('simulationStatus').innerHTML = `
        <small class="text-muted">Status: ${message}</small>
    `;
}

/**
 * Update the progress bar
 * @param {number} percentage - Progress percentage (0-100)
 */
function updateProgressBar(percentage) {
    const progressBar = document.getElementById('simulationProgress');
    progressBar.style.width = `${percentage}%`;
    progressBar.setAttribute('aria-valuenow', percentage);
    
    // Change color based on progress
    progressBar.classList.remove('bg-success', 'bg-warning', 'bg-danger');
    if (percentage < 33) {
        progressBar.classList.add('bg-success');
    } else if (percentage < 66) {
        progressBar.classList.add('bg-warning');
    } else {
        progressBar.classList.add('bg-danger');
    }
}

/**
 * Generate a random severity level
 * @param {array} options - Array of severity options to choose from
 * @returns {string} - Random severity level
 */
function generateRandomSeverity(options) {
    return options[Math.floor(Math.random() * options.length)];
}

/**
 * Generate a random source hostname
 * @returns {string} - Random hostname
 */
function generateRandomSource() {
    const hosts = [
        'app-server-01', 'app-server-02', 'web-server-01', 'web-server-02',
        'db-server-01', 'db-server-02', 'mail-server-01', 'proxy-01',
        'gateway-01', 'firewall-01', 'load-balancer-01', 'auth-server-01'
    ];
    return hosts[Math.floor(Math.random() * hosts.length)];
}

/**
 * Generate a random syslog facility
 * @returns {string} - Random facility name
 */
function generateRandomFacility() {
    const facilities = [
        'kern', 'user', 'mail', 'daemon', 'auth', 'syslog',
        'lpr', 'news', 'uucp', 'cron', 'authpriv', 'ftp',
        'local0', 'local1', 'local2', 'local3'
    ];
    return facilities[Math.floor(Math.random() * facilities.length)];
}

/**
 * Generate a random log message based on severity
 * @param {string} severity - Log severity level
 * @returns {string} - Generated log message
 */
function generateRandomMessage(severity) {
    const infoMessages = [
        'Service started successfully',
        'User logged in successfully',
        'Database connection established',
        'Cache refreshed',
        'Scheduled task completed',
        'Config file loaded',
        'API request processed successfully',
        'Thread pool initialized',
        'SSL certificate valid',
        'Backup completed successfully'
    ];
    
    const warningMessages = [
        'High memory usage detected',
        'Slow query performance',
        'Deprecation notice: using outdated method',
        'Cache size exceeding recommended limit',
        'Connection pool nearing capacity',
        'Disk space running low',
        'Request rate approaching throttle limit',
        'Temporary network timeout, retrying',
        'Session timeout, user reconnecting',
        'Background process taking longer than expected'
    ];
    
    const errorMessages = [
        'Failed to connect to database',
        'Authentication failed for user',
        'Service crashed unexpectedly',
        'Out of memory error',
        'File not found exception',
        'API request failed with 500 error',
        'SSL certificate validation failed',
        'Deadlock detected in thread pool',
        'Failed to write to log file',
        'Critical security update failed to apply'
    ];
    
    const criticalMessages = [
        'CRITICAL: Database cluster unresponsive',
        'CRITICAL: Multiple service failures detected',
        'CRITICAL: Possible security breach detected',
        'CRITICAL: System overload - services shutting down',
        'CRITICAL: Data corruption detected in volume',
        'CRITICAL: Kernel panic on primary node',
        'CRITICAL: RAID failure detected - data at risk',
        'CRITICAL: Network connectivity lost to all external services',
        'CRITICAL: Cache corruption detected',
        'CRITICAL: Certificate authority compromised'
    ];
    
    switch(severity) {
        case 'emergency':
        case 'alert':
            return criticalMessages[Math.floor(Math.random() * criticalMessages.length)] + 
                   ' [EMERGENCY ALERT]';
        case 'critical':
            return criticalMessages[Math.floor(Math.random() * criticalMessages.length)];
        case 'error':
            return errorMessages[Math.floor(Math.random() * errorMessages.length)];
        case 'warning':
            return warningMessages[Math.floor(Math.random() * warningMessages.length)];
        case 'notice':
        case 'info':
        default:
            return infoMessages[Math.floor(Math.random() * infoMessages.length)];
    }
} 