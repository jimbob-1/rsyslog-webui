<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - <?php echo $site_name; ?></title>
    
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-table.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-default">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href="index.php"><?php echo $site_name; ?></a>
            </div>
            <ul class="nav navbar-nav">
                <li><a href="index.php">Logs</a></li>
                <li class="active"><a href="dashboard.php">Dashboard</a></li>
                <li><a href="alerts.php">Alerts</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <!-- Real-time Statistics -->
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Real-time Statistics</h3>
                    </div>
                    <div class="panel-body">
                        <canvas id="logVolumeChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Error Distribution -->
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Error Distribution</h3>
                    </div>
                    <div class="panel-body">
                        <canvas id="errorDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Error Sources -->
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Top Error Sources</h3>
                    </div>
                    <div class="panel-body">
                        <div id="topErrors"></div>
                    </div>
                </div>
            </div>
            
            <!-- System Health -->
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">System Health</h3>
                    </div>
                    <div class="panel-body">
                        <div id="systemHealth"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize charts
        const logVolumeCtx = document.getElementById('logVolumeChart').getContext('2d');
        const errorDistributionCtx = document.getElementById('errorDistributionChart').getContext('2d');

        // Log Volume Chart
        new Chart(logVolumeCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Log Volume',
                    data: [],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
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
                responsive: true
            }
        });

        // Update data every 30 seconds
        function updateDashboard() {
            $.getJSON('json/dashboard_stats.php', function(data) {
                // Update charts
                updateLogVolumeChart(data.logVolume);
                updateErrorDistributionChart(data.errorDistribution);
                updateTopErrors(data.topErrors);
                updateSystemHealth(data.systemHealth);
            });
        }

        // Initial update
        updateDashboard();
        setInterval(updateDashboard, 30000);
    </script>
</body>
</html> 