<?php
include 'config.php';
include 'includes/header.php';
include 'includes/empty-state.php';

$settings = Settings::getInstance();
$items_per_page = $settings->getSetting('items_per_page') ?? 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$priority = isset($_GET['priority']) ? trim($_GET['priority']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Create alerts table if it doesn't exist
    $sql = file_get_contents('sql/alerts.sql');
    $pdo->exec($sql);

    // Build the query
    $query = "SELECT COUNT(*) as total FROM Alerts WHERE 1=1";
    $params = [];

    if ($status) {
        $query .= " AND Status = ?";
        $params[] = $status;
    }

    if ($priority) {
        $query .= " AND Priority = ?";
        $params[] = $priority;
    }

    if ($search) {
        $query .= " AND (Name LIKE ? OR Description LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam]);
    }

    // Get total count
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total / $items_per_page);
    $offset = ($page - 1) * $items_per_page;

    // Get alerts
    $query = str_replace("COUNT(*) as total", "*", $query);
    $query .= " ORDER BY CreatedAt DESC LIMIT ? OFFSET ?";
    $params[] = $items_per_page;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $alerts = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Alerts</h1>
        <div class="d-flex gap-2">
            <form class="d-flex gap-2">
                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search alerts...">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                </select>
                <select class="form-select" name="priority">
                    <option value="">All Priorities</option>
                    <option value="emergency" <?php echo $priority === 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                    <option value="alert" <?php echo $priority === 'alert' ? 'selected' : ''; ?>>Alert</option>
                    <option value="critical" <?php echo $priority === 'critical' ? 'selected' : ''; ?>>Critical</option>
                    <option value="error" <?php echo $priority === 'error' ? 'selected' : ''; ?>>Error</option>
                    <option value="warning" <?php echo $priority === 'warning' ? 'selected' : ''; ?>>Warning</option>
                    <option value="notice" <?php echo $priority === 'notice' ? 'selected' : ''; ?>>Notice</option>
                    <option value="info" <?php echo $priority === 'info' ? 'selected' : ''; ?>>Info</option>
                    <option value="debug" <?php echo $priority === 'debug' ? 'selected' : ''; ?>>Debug</option>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Search
                </button>
            </form>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newAlertModal">
                <i class="bi bi-plus-lg"></i> New Alert
            </button>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
    <?php else: ?>
        <?php if (empty($alerts)): ?>
            <?php
            $message = $search || $status || $priority
                ? 'No alerts found matching your criteria' 
                : 'No alerts have been created yet';
            
            $action_button = $search || $status || $priority
                ? '<a href="alerts.php" class="btn btn-primary">Clear filters</a>'
                : '<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newAlertModal">Create Alert</button>';
            
            show_empty_state($message, 'exclamation-triangle', $action_button);
            ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Created</th>
                                    <th>Name</th>
                                    <th>Condition</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Last Triggered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alerts as $alert): ?>
                                    <tr>
                                        <td><?php echo date($settings->getSetting('date_format'), strtotime($alert['CreatedAt'])); ?></td>
                                        <td><?php echo htmlspecialchars($alert['Name']); ?></td>
                                        <td><?php echo htmlspecialchars($alert['AlertCondition']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo get_priority_class($alert['Priority']); ?>">
                                                <?php echo ucfirst($alert['Priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $alert['Status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($alert['Status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo $alert['LastTriggered'] ? date($settings->getSetting('date_format'), strtotime($alert['LastTriggered'])) : 'Never'; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editAlert(<?php echo $alert['ID']; ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAlert(<?php echo $alert['ID']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?><?php echo $priority ? '&priority=' . urlencode($priority) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- New Alert Modal -->
<div class="modal fade" id="newAlertModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Alert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newAlertForm">
                    <div class="mb-3">
                        <label for="alertName" class="form-label">Alert Name</label>
                        <input type="text" class="form-control" id="alertName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Condition Builder</label>
                        <div class="card mb-2">
                            <div class="card-body py-2">
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <select class="form-select form-select-sm" id="conditionField">
                                            <option value="Priority">Priority</option>
                                            <option value="FromHost">Host</option>
                                            <option value="Facility">Facility</option>
                                            <option value="Message">Message</option>
                                            <option value="SysLogTag">Tag</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm" id="conditionOperator">
                                            <option value="=">=</option>
                                            <option value="!=">!=</option>
                                            <option value=">">></option>
                                            <option value="<"><</option>
                                            <option value="LIKE">Contains</option>
                                            <option value="NOT LIKE">Does not contain</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="conditionValue">
                                            <button class="btn btn-outline-secondary" type="button" onclick="addCondition()">Add</button>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <select class="form-select form-select-sm" id="logicOperator">
                                        <option value="AND">AND - All conditions must match</option>
                                        <option value="OR">OR - Any condition can match</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header py-1">
                                <small>Current Conditions</small>
                            </div>
                            <div class="card-body py-2">
                                <div id="conditionsContainer" class="mb-2">
                                    <div class="text-muted small">No conditions added yet</div>
                                </div>
                                <textarea class="form-control form-control-sm" id="alertCondition" rows="1" required readonly></textarea>
                                <div class="form-text small">SQL WHERE clause to match log events</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="alertImportance" class="form-label">Alert Importance</label>
                            <div class="btn-group w-100" role="group" aria-label="Alert importance">
                                <input type="radio" class="btn-check" name="alertPriority" id="priority-low" value="info" autocomplete="off">
                                <label class="btn btn-sm btn-outline-info" for="priority-low">Low</label>
                                
                                <input type="radio" class="btn-check" name="alertPriority" id="priority-medium" value="warning" autocomplete="off" checked>
                                <label class="btn btn-sm btn-outline-warning" for="priority-medium">Medium</label>
                                
                                <input type="radio" class="btn-check" name="alertPriority" id="priority-high" value="error" autocomplete="off">
                                <label class="btn btn-sm btn-outline-danger" for="priority-high">High</label>
                                
                                <input type="radio" class="btn-check" name="alertPriority" id="priority-critical" value="emergency" autocomplete="off">
                                <label class="btn btn-sm btn-outline-dark" for="priority-critical">Critical</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="notificationMethod" class="form-label">Notification Method</label>
                            <select class="form-select" id="notificationMethod" required>
                                <option value="email">Email</option>
                                <option value="webhook">Webhook</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3 notification-fields" id="emailFields">
                        <label for="emailTarget" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="emailTarget" placeholder="recipient@example.com">
                    </div>
                    
                    <div class="mb-3 notification-fields" id="webhookFields" style="display:none;">
                        <label for="webhookTarget" class="form-label">Webhook URL</label>
                        <input type="url" class="form-control" id="webhookTarget" placeholder="https://example.com/webhook">
                    </div>
                    
                    <div class="mb-3">
                        <label for="alertDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="alertDescription" rows="2"></textarea>
                        <div class="form-text small">Optional explanation of what this alert monitors</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAlert()">Create Alert</button>
            </div>
        </div>
    </div>
</div>

<?php
function get_priority_class($priority) {
    return match ($priority) {
        'emergency', 'alert', 'critical' => 'danger',
        'error' => 'warning',
        'warning' => 'warning',
        'notice' => 'info',
        'info' => 'info',
        'debug' => 'secondary',
        default => 'secondary'
    };
}
?>

<script>
function editAlert(id) {
    // Implement edit functionality
    fetch(`json/get_alert.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate modal with alert data
                document.getElementById('alertName').value = data.alert.Name;
                document.getElementById('alertCondition').value = data.alert.AlertCondition;
                
                // Set the priority based on the returned value
                const priorityMapping = {
                    'info': 'priority-low',
                    'warning': 'priority-medium',
                    'error': 'priority-high',
                    'emergency': 'priority-critical',
                    'alert': 'priority-critical',
                    'critical': 'priority-critical',
                    'notice': 'priority-low',
                    'debug': 'priority-low'
                };
                
                const priorityId = priorityMapping[data.alert.Priority] || 'priority-medium';
                document.getElementById(priorityId).checked = true;
                
                document.getElementById('alertDescription').value = data.alert.Description;
                
                // Set notification method and target
                const notificationMethod = data.alert.NotificationMethod;
                document.getElementById('notificationMethod').value = notificationMethod;
                updateNotificationFields(notificationMethod);
                
                // Determine which notification field to populate
                if (notificationMethod === 'email' || notificationMethod === 'both') {
                    document.getElementById('emailTarget').value = data.alert.NotificationTarget;
                }
                if (notificationMethod === 'webhook' || notificationMethod === 'both') {
                    document.getElementById('webhookTarget').value = data.alert.NotificationTarget;
                }
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('newAlertModal'));
                modal.show();
            } else {
                alert('Failed to load alert: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load alert');
        });
}

function deleteAlert(id) {
    if (confirm('Are you sure you want to delete this alert?')) {
        fetch(`json/delete_alert.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete alert: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete alert');
            });
    }
}

function updateNotificationFields(method) {
    // Hide all notification fields
    document.querySelectorAll('.notification-fields').forEach(field => {
        field.style.display = 'none';
    });
    
    // Show the appropriate fields based on method
    if (method === 'email' || method === 'both') {
        document.getElementById('emailFields').style.display = 'block';
    }
    if (method === 'webhook' || method === 'both') {
        document.getElementById('webhookFields').style.display = 'block';
    }
}

let conditionsList = [];

function addCondition() {
    const field = document.getElementById('conditionField').value;
    const operator = document.getElementById('conditionOperator').value;
    let value = document.getElementById('conditionValue').value.trim();
    
    if (!value) {
        alert('Please enter a value for the condition');
        return;
    }
    
    // Format the value based on the operator
    if (operator === 'LIKE' || operator === 'NOT LIKE') {
        // Automatically add wildcards if not present
        if (!value.includes('%')) {
            value = `%${value}%`;
        }
        value = `'${value}'`;
    } else if (isNaN(value)) {
        // If it's not a number, wrap in quotes
        value = `'${value}'`;
    }
    
    const condition = `${field} ${operator} ${value}`;
    conditionsList.push(condition);
    updateConditionsDisplay();
    
    // Clear the value field
    document.getElementById('conditionValue').value = '';
}

function updateConditionsDisplay() {
    const conditionsContainer = document.getElementById('conditionsContainer');
    const logicOperator = document.getElementById('logicOperator').value;
    
    if (conditionsList.length === 0) {
        conditionsContainer.innerHTML = '<div class="text-muted small">No conditions added yet</div>';
        document.getElementById('alertCondition').value = '';
        return;
    }
    
    // Display the conditions as badges
    let html = '';
    conditionsList.forEach((condition, index) => {
        html += `
            <div class="alert alert-secondary p-2 mb-1 d-flex justify-content-between align-items-center">
                <span>${condition}</span>
                <button type="button" class="btn-close btn-sm" onclick="removeCondition(${index})"></button>
            </div>
        `;
    });
    
    conditionsContainer.innerHTML = html;
    
    // Update the full condition string
    document.getElementById('alertCondition').value = conditionsList.join(` ${logicOperator} `);
}

function removeCondition(index) {
    conditionsList.splice(index, 1);
    updateConditionsDisplay();
}

document.getElementById('logicOperator').addEventListener('change', updateConditionsDisplay);

// Add event listener for notification method
document.addEventListener('DOMContentLoaded', function() {
    const notificationMethodSelect = document.getElementById('notificationMethod');
    if (notificationMethodSelect) {
        notificationMethodSelect.addEventListener('change', function() {
            updateNotificationFields(this.value);
        });
    }
});

function saveAlert() {
    // Validate conditions
    if (conditionsList.length === 0) {
        alert('Please add at least one condition');
        return;
    }
    
    // Get selected priority from radio buttons
    const priorityRadios = document.getElementsByName('alertPriority');
    let selectedPriority = 'warning';
    for (const radio of priorityRadios) {
        if (radio.checked) {
            selectedPriority = radio.value;
            break;
        }
    }
    
    // Get notification target based on method
    const notificationMethod = document.getElementById('notificationMethod').value;
    let notificationTarget = '';
    
    if (notificationMethod === 'email') {
        notificationTarget = document.getElementById('emailTarget').value;
    } else if (notificationMethod === 'webhook') {
        notificationTarget = document.getElementById('webhookTarget').value;
    } else if (notificationMethod === 'both') {
        // For 'both', concatenate email and webhook with a separator
        const email = document.getElementById('emailTarget').value;
        const webhook = document.getElementById('webhookTarget').value;
        notificationTarget = email + '|' + webhook;
    }
    
    // Validate notification target
    if (!notificationTarget) {
        alert('Please enter notification target');
        return;
    }
    
    const formData = {
        name: document.getElementById('alertName').value,
        condition: document.getElementById('alertCondition').value,
        priority: selectedPriority,
        description: document.getElementById('alertDescription').value,
        notificationMethod: notificationMethod,
        notificationTarget: notificationTarget
    };

    fetch('json/save_alert.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to save alert: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save alert');
    });
}
</script>

<?php include 'includes/footer.php'; ?> 