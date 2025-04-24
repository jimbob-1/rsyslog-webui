<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alerts - <?php echo $site_name; ?></title>
    
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-table.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li class="active"><a href="alerts.php">Alerts</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Alert Rules</h3>
                        <button class="btn btn-primary btn-sm pull-right" data-toggle="modal" data-target="#addAlertModal">
                            Add Alert Rule
                        </button>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped" id="alertsTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Condition</th>
                                    <th>Threshold</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Alert rules will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Recent Alerts</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped" id="recentAlertsTable">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Alert Rule</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Recent alerts will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Alert Modal -->
    <div class="modal fade" id="addAlertModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add Alert Rule</h4>
                </div>
                <div class="modal-body">
                    <form id="addAlertForm">
                        <div class="form-group">
                            <label for="alertName">Alert Name</label>
                            <input type="text" class="form-control" id="alertName" required>
                        </div>
                        <div class="form-group">
                            <label for="alertCondition">Condition</label>
                            <select class="form-control" id="alertCondition" required>
                                <option value="error_rate">Error Rate</option>
                                <option value="log_volume">Log Volume</option>
                                <option value="pattern_match">Pattern Match</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="alertThreshold">Threshold</label>
                            <input type="number" class="form-control" id="alertThreshold" required>
                        </div>
                        <div class="form-group">
                            <label for="alertPattern" style="display: none;">Pattern</label>
                            <input type="text" class="form-control" id="alertPattern" style="display: none;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveAlert">Save Alert</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show/hide pattern input based on condition
        $('#alertCondition').change(function() {
            if ($(this).val() === 'pattern_match') {
                $('#alertPattern').show().prev('label').show();
            } else {
                $('#alertPattern').hide().prev('label').hide();
            }
        });

        // Load alerts
        function loadAlerts() {
            $.getJSON('json/alerts.php', function(data) {
                const alertsTable = $('#alertsTable tbody');
                alertsTable.empty();
                
                data.alerts.forEach(function(alert) {
                    alertsTable.append(`
                        <tr>
                            <td>${alert.name}</td>
                            <td>${alert.condition}</td>
                            <td>${alert.threshold}</td>
                            <td><span class="label label-${alert.status === 'active' ? 'success' : 'default'}">${alert.status}</span></td>
                            <td>
                                <button class="btn btn-xs btn-warning" onclick="toggleAlert(${alert.id})">${alert.status === 'active' ? 'Disable' : 'Enable'}</button>
                                <button class="btn btn-xs btn-danger" onclick="deleteAlert(${alert.id})">Delete</button>
                            </td>
                        </tr>
                    `);
                });
            });
        }

        // Load recent alerts
        function loadRecentAlerts() {
            $.getJSON('json/recent_alerts.php', function(data) {
                const recentAlertsTable = $('#recentAlertsTable tbody');
                recentAlertsTable.empty();
                
                data.alerts.forEach(function(alert) {
                    recentAlertsTable.append(`
                        <tr>
                            <td>${alert.time}</td>
                            <td>${alert.rule_name}</td>
                            <td>${alert.message}</td>
                            <td><span class="label label-${alert.status === 'triggered' ? 'danger' : 'success'}">${alert.status}</span></td>
                        </tr>
                    `);
                });
            });
        }

        // Save new alert
        $('#saveAlert').click(function() {
            const alertData = {
                name: $('#alertName').val(),
                condition: $('#alertCondition').val(),
                threshold: $('#alertThreshold').val(),
                pattern: $('#alertPattern').val()
            };

            $.post('json/save_alert.php', alertData, function(response) {
                if (response.success) {
                    $('#addAlertModal').modal('hide');
                    loadAlerts();
                } else {
                    alert('Error saving alert: ' + response.error);
                }
            });
        });

        // Initial load
        loadAlerts();
        loadRecentAlerts();
        setInterval(loadRecentAlerts, 30000);
    </script>
</body>
</html> 