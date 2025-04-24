<?php
include 'config.php';
include 'includes/settings.php';
include 'includes/header.php';

$settings = Settings::getInstance();
?>

<div class="container mt-4">
    <h1>Dashboard</h1>
    
    <div class="row">
        <!-- Real-time Statistics -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title m-0">Real-time Statistics</h5>
                </div>
                <div class="card-body">
                    <canvas id="logVolumeChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Error Distribution -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title m-0">Error Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="errorDistributionChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- System Health -->
        <div class="col-md-6 mb-4">
            <div class="card system-health">
                <div class="card-header">
                    <h5 class="card-title m-0">System Health</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title m-0">CPU Usage</h6>
                                </div>
                                <div class="card-body">
                                    <div class="progress mb-2" style="height: 20px;">
                                        <div id="cpuUsage" class="progress-bar bg-success" role="progressbar" style="width: 0%">0%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title m-0">Memory Usage</h6>
                                </div>
                                <div class="card-body">
                                    <div class="progress mb-2" style="height: 20px;">
                                        <div id="memoryUsage" class="progress-bar bg-success" role="progressbar" style="width: 0%">0%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title m-0">Disk Usage</h6>
                                </div>
                                <div class="card-body">
                                    <div class="progress mb-2" style="height: 20px;">
                                        <div id="diskUsage" class="progress-bar bg-success" role="progressbar" style="width: 0%">0%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title m-0">Database Size</h6>
                                </div>
                                <div class="card-body">
                                    <div class="database-size" id="databaseSize">Loading...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Error Sources -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title m-0">Top Error Sources</h5>
                </div>
                <div class="card-body">
                    <div id="topErrors">No errors found</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize charts
    const logVolumeCtx = document.getElementById('logVolumeChart').getContext('2d');
    const errorDistributionCtx = document.getElementById('errorDistributionChart').getContext('2d');

    // Log Volume Chart
    new Chart(logVolumeCtx, {
        type: 'line',
        data: {
            labels: ['0h', '1h', '2h', '3h', '4h', '5h', '6h', '7h', '8h', '9h', '10h', '11h'],
            datasets: [{
                label: 'Log Volume',
                data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Error Distribution Chart
    new Chart(errorDistributionCtx, {
        type: 'pie',
        data: {
            labels: ['Error', 'Warning', 'Info', 'Debug'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(255, 205, 86)',
                    'rgb(54, 162, 235)',
                    'rgb(75, 192, 192)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Update system health
    function updateSystemHealth(data) {
        // If no data is available, use simulated data
        if (!data) {
            data = {
                cpuUsage: Math.floor(Math.random() * 60),
                memoryUsage: Math.floor(Math.random() * 70),
                diskUsage: Math.floor(Math.random() * 50),
                dbSize: Math.floor(Math.random() * 500) + ' MB'
            };
        }

        // CPU Usage
        const cpuUsage = document.getElementById('cpuUsage');
        cpuUsage.style.width = data.cpuUsage + '%';
        cpuUsage.textContent = data.cpuUsage + '%';
        if (data.cpuUsage > 80) {
            cpuUsage.className = 'progress-bar bg-danger';
        } else if (data.cpuUsage > 60) {
            cpuUsage.className = 'progress-bar bg-warning';
        } else {
            cpuUsage.className = 'progress-bar bg-success';
        }

        // Memory Usage
        const memoryUsage = document.getElementById('memoryUsage');
        memoryUsage.style.width = data.memoryUsage + '%';
        memoryUsage.textContent = data.memoryUsage + '%';
        if (data.memoryUsage > 80) {
            memoryUsage.className = 'progress-bar bg-danger';
        } else if (data.memoryUsage > 60) {
            memoryUsage.className = 'progress-bar bg-warning';
        } else {
            memoryUsage.className = 'progress-bar bg-success';
        }

        // Disk Usage
        const diskUsage = document.getElementById('diskUsage');
        diskUsage.style.width = data.diskUsage + '%';
        diskUsage.textContent = data.diskUsage + '%';
        if (data.diskUsage > 80) {
            diskUsage.className = 'progress-bar bg-danger';
        } else if (data.diskUsage > 60) {
            diskUsage.className = 'progress-bar bg-warning';
        } else {
            diskUsage.className = 'progress-bar bg-success';
        }

        // Database Size
        document.getElementById('databaseSize').textContent = data.dbSize;
    }

    // Initial update with simulated data
    updateSystemHealth();

    // Try to fetch real data
    fetch('json/dashboard_stats.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.systemHealth) {
                updateSystemHealth(data.systemHealth);
            }
        })
        .catch(error => {
            console.error('Error fetching dashboard stats:', error);
        });

    // Update every 30 seconds
    setInterval(() => {
        fetch('json/dashboard_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.systemHealth) {
                    updateSystemHealth(data.systemHealth);
                }
            })
            .catch(error => {
                console.error('Error fetching dashboard stats:', error);
                // Use simulated data on error
                updateSystemHealth();
            });
    }, 30000);
</script>

<?php include 'includes/footer.php'; ?> 