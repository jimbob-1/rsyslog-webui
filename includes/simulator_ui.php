<?php
/**
 * RSyslog WebUI - Simulator UI Component
 * Provides the UI elements for the log simulator functionality
 */

// Only show in debug mode
if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
    return;
}
?>

<div class="simulator-container">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5><i class="fas fa-robot"></i> Log Simulator</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> This tool is for development and testing purposes only. It allows you to generate test logs and alerts.
            </div>
            
            <button id="startSimulation" class="btn btn-primary">
                <i class="fas fa-play"></i> Start Simulation
            </button>
            
            <div id="simulationStatus" class="mt-3" style="display: none;">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Simulation in Progress
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3">
                            <div id="simulationProgress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        Statistics
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Logs Generated:</strong> <span id="logsGenerated">0</span></p>
                                        <p><strong>Errors:</strong> <span id="errorsGenerated">0</span></p>
                                        <p><strong>Alerts:</strong> <span id="alertsGenerated">0</span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        Latest Log
                                    </div>
                                    <div class="card-body">
                                        <div id="latestLog">
                                            <p class="text-muted">No logs generated yet</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button id="stopSimulation" class="btn btn-danger">
                                <i class="fas fa-stop"></i> Stop Simulation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load simulator script -->
<script src="js/simulator.js"></script> 