<?php
include 'config.php';
include 'includes/settings.php';
include 'includes/header.php';

$settings = Settings::getInstance();
?>

<h1>System Events</h1>

<div class="card mb-4">
    <div class="card-body">
        <form id="searchForm" class="search-form row g-2">
            <div class="col-md-6">
                <input type="text" class="form-control" id="txtSearch" placeholder="Search events..." autocomplete="off">
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <div class="progress" style="height: 25px; border-radius: 0;">
            <div id="pgError" class="progress-bar bg-danger" role="progressbar" style="width: 0%" data-bs-toggle="tooltip" title="Errors">
                <span class="severity-label">Error</span>
            </div>
            <div id="pgWarning" class="progress-bar bg-warning" role="progressbar" style="width: 0%" data-bs-toggle="tooltip" title="Warnings">
                <span class="severity-label">Warning</span>
            </div>
            <div id="pgNotice" class="progress-bar bg-info" role="progressbar" style="width: 0%" data-bs-toggle="tooltip" title="Notices">
                <span class="severity-label">Notice</span>
            </div>
            <div id="pgInfo" class="progress-bar bg-primary" role="progressbar" style="width: 0%" data-bs-toggle="tooltip" title="Info">
                <span class="severity-label">Info</span>
            </div>
            <div id="pgDebug" class="progress-bar bg-secondary" role="progressbar" style="width: 0%" data-bs-toggle="tooltip" title="Debug">
                <span class="severity-label">Debug</span>
            </div>
        </div>
        <table id="table-style" 
                data-toggle="table" 
                data-url="json/events.php" 
                data-height="550"
                data-sort-name="ReceivedAt"
                data-sort-order="desc"
                data-pagination="true"
                data-page-size="50"
                data-page-list="[10, 25, 50, 100]"
                data-search="true"
                data-search-align="left"
                data-search-on-enter-key="false"
                data-strict-search="false"
                data-trim-on-search="true"
                data-show-search-button="false"
                data-search-accent-neutralise="true"
                data-show-refresh="true"
                data-show-toggle="true"
                data-show-columns="true"
                data-show-columns-toggle-all="true"
                data-show-fullscreen="true"
                data-show-pagination-switch="false"
                data-show-export="true"
                data-buttons="buttons"
                data-buttons-align="right"
                data-buttons-class="primary"
                data-buttons-prefix="btn-"
                data-click-to-select="true"
                data-export-types='["csv", "txt", "excel"]'
                data-export-data-type="all"
                data-export-options='{
                    "fileName": "system-events",
                    "worksheetName": "System Events",
                    "csvSeparator": ",",
                    "ignoreColumn": [5],
                    "exportDataType": "all"
                }'
                data-row-style="rowStyle"
                class="table table-hover table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th data-field="Priority" 
                        data-sortable="true" 
                        data-width="100"
                        data-visible="true"
                        data-switchable="true"
                        data-formatter="SeverityFormat"
                        data-search-formatter="false">Severity</th>
                    <th data-field="ReceivedAt" 
                        data-sortable="true"
                        data-visible="true"
                        data-switchable="true"
                        data-width="160">Date</th>
                    <th data-field="Facility" 
                        data-sortable="true"
                        data-visible="true"
                        data-switchable="true"
                        data-width="100">Facility</th>
                    <th data-field="FromHost" 
                        data-sortable="true"
                        data-visible="true"
                        data-switchable="true"
                        data-width="180">Host</th>
                    <th data-field="SysLogTag" 
                        data-sortable="true"
                        data-visible="true"
                        data-switchable="true"
                        data-width="200">Tag</th>
                    <th data-field="MessageType" 
                        data-visible="false"
                        data-switchable="true">Type</th>
                    <th data-field="Message" 
                        data-sortable="true"
                        data-visible="true"
                        data-switchable="true">Message</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- JavaScript Dependencies -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.22.3/dist/bootstrap-table.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.22.3/dist/extensions/export/bootstrap-table-export.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.22.3/dist/extensions/toolbar/bootstrap-table-toolbar.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.22.3/dist/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.27.0/tableExport.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.27.0/libs/jsPDF/jspdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.27.0/libs/jsPDF-AutoTable/jspdf.plugin.autotable.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script type="text/javascript">
$(function () {
    var firstRowID, lastRowID;
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Add event handlers for bootstrap table
    $('#table-style').on('all.bs.table', function(e, name, args) {
        console.log('Event:', name, 'triggered with args:', args);
    });

    $('#table-style').on('post-body.bs.table', function() {
        console.log('Table body loaded');
    });
    
    $('#table-style').on('load-success.bs.table', function(data) {
        console.log('Data loaded successfully:', data);
    });
    
    $('#table-style').on('load-error.bs.table', function(status, res) {
        console.error('Load Error:', status, res);
    });
    
    getSummary();
    
    var selectedRow = "";
    var selectedNodeText = "";

    var menu = "", menudate = "";
    
    $('#table-style').css('cursor', 'pointer');

    $("#table-style").delegate("tr td", "mousedown", function(event) {
        if(event.which == 3){
            
            context.destroy();

            var selectedRow = $(this);
            selectedNodeText = selectedRow.html();
            selectedColumn = "";
            
            if(selectedRow.find('span').length > 0) selectedNodeText = selectedRow.find('span').html();

            if(selectedRow.index() == 6) return;
            if(selectedRow.index() == 0 && selectedRow.hasClass('expandedMessage') == false) selectedColumn = "Severity";
            if(selectedRow.index() == 1) 
            {
                selectedNodeText = selectedNodeText.replace(" ", "T");
                selectedColumn = "Date";
            }
            if(selectedRow.index() == 2) selectedColumn = "Facility";
            if(selectedRow.index() == 3) selectedColumn = "Host";
            if(selectedRow.index() == 4) selectedColumn = "SysLogTag";
            if(selectedRow.index() == 5) return;
            if(selectedRow.hasClass('expandedMessage') == true) return;
            
            menudate = [{
            text: 'Add logs newer than \'' + selectedNodeText + '\' to filterset',
            action: function () {
                    $("#txtSearch").val($("#txtSearch").val() + "\"" + selectedColumn + "\">\"" + selectedNodeText + "\" ");
                    updateTable();
                    context.destroy();
                }
            }, {
                text: 'Add logs older than \'' + selectedNodeText + '\' in filterset',
                action: function (t) {
                    $("#txtSearch").val($("#txtSearch").val() + "\"" + selectedColumn + "\"<\"" + selectedNodeText + "\" ");
                    updateTable();
                    context.destroy();
                }
            }];
            
            menu = [{
            text: 'Add \'' + selectedNodeText + '\' to filterset',
            action: function () {
                    $("#txtSearch").val($("#txtSearch").val() + "\"" + selectedColumn + "\"=\"" + selectedNodeText + "\" ");
                    updateTable();
                    context.destroy();
                }
            }, {
                text: 'Exclude \'' + selectedNodeText + '\' in filterset',
                action: function (t) {
                    $("#txtSearch").val($("#txtSearch").val() + "\"" + selectedColumn + "\"<>\"" + selectedNodeText + "\" ");
                    updateTable();
                    context.destroy();
                }
            }];
            
            if(selectedRow.index() == 1) 
                context.attach($("table-style tr"), menudate);
            else
                context.attach($("table-style tr"), menu);
        }
    }); 

    $('#table-style').on('click-row.bs.table', function (e, row, $element) {
        if($element.hasClass('expandedMessage') == false)
        {
            // Add new tr with full message + add class
            $element.after('<tr><td colspan="7" class="expandedMessage"><div class="increase-font-size">' + escapeHtml(row.Message) + '</div></td></tr>');
            $element.addClass('expandedMessage');
        }
        else
        {
            // Remove previous created tr + remove class
            $element.closest('tr').next().remove();
            $element.removeClass('expandedMessage');
        }
    });

    $('[data-toggle="tooltip"]').tooltip({
        'placement': 'top',  
        'trigger': 'hover focus'
    });

    $('[data-toggle="tooltip-bottom"]').tooltip({
        'placement': 'bottom',  
        'trigger': 'hover focus'
    });
    
    // Function to update table with current search
    function updateTable() {
        var search = $('#txtSearch').val();
        getSummary();
        $('#table-style').bootstrapTable('refresh', {
            url: 'json/events.php?&search=' + encodeURIComponent(search)
        });
    }
    
    // Live search on input change
    var searchTimeout;
    $('#txtSearch').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(updateTable, 300); // Debounce for 300ms
    });

    // Remove the old search box since we're using bootstrap-table's built-in search
    $('#searchForm').remove();

    // Update the click handlers for severity filters
    $("#pgDebug").on("click", function() {
        $('#table-style').bootstrapTable('filterBy', { Priority: 7 });
    });

    $("#pgNotice").on("click", function() {
        $('#table-style').bootstrapTable('filterBy', { Priority: 5 });
    });

    $("#pgInfo").on("click", function() {
        $('#table-style').bootstrapTable('filterBy', { Priority: 6 });
    });

    $("#pgWarning").on("click", function() {
        $('#table-style').bootstrapTable('filterBy', { Priority: 4 });
    });

    $("#pgError").on("click", function() {
        $('#table-style').bootstrapTable('filterBy', { Priority: 3 });
    });
    
    // Check if there are any events every 10 seconds
    setInterval(function() {
        getSummary();
    }, 10000);
});

function getSummary() { 
    $.getJSON("json/events_summary.php?" + "search=" + encodeURIComponent($("#txtSearch").val()), function(data) {
        var items = data.length;
        var sum = 0;
        var progressBars = {
            3: { id: "#pgError", flag: false },
            4: { id: "#pgWarning", flag: false },
            5: { id: "#pgNotice", flag: false },
            6: { id: "#pgInfo", flag: false },
            7: { id: "#pgDebug", flag: false }
        };

        for (var x = 0; x < items; x++) {
            var key = parseInt(data[x][0], 10);
            if (progressBars[key]) {
                sum += data[x][1];
            }
        }

        for (var x = 0; x < items; x++) {
            var key = parseInt(data[x][0], 10);
            if (progressBars[key]) {
                $(progressBars[key].id).css('width', ((data[x][1] / sum) * 100) + "%");
                progressBars[key].flag = true;
            }
        }

        for (var key in progressBars) {
            if (!progressBars[key].flag) {
                $(progressBars[key].id).css('width', "0%");
            }
        }
    });
}

function toInt(val) {
    return val & 1;
}

function rowStyle(row, index) {
    return {
        classes: 'ID_' + row.ID
    };
}

function SeverityFormat(value, row, index) {
    var bg = "bg-info", text = "Unknown";
    
    switch(parseInt(value)) {
        case 0:
            bg = "bg-danger";
            text = "EMERGENCY";
            break;
        case 1:
            bg = "bg-danger";
            text = "ALERT";
            break;
        case 2:
            bg = "bg-danger";
            text = "CRITICAL";
            break;
        case 3:
            bg = "bg-danger";
            text = "ERROR";
            break;
        case 4:
            bg = "bg-warning";
            text = "WARNING";
            break;
        case 5:
            bg = "bg-info";
            text = "NOTICE";
            break;
        case 6:
            bg = "bg-info";
            text = "INFO";
            break;
        case 7:
            bg = "bg-secondary";
            text = "DEBUG";
            break;
        default:
            bg = "bg-info";
            text = "Unknown";
    }
    
    // Add a data attribute with the text value for searching
    return "<span class='badge " + bg + "' data-severity='" + text + "'>" + text + "</span>";
}

// Add a custom filter function for severity
$.extend($.fn.bootstrapTable.defaults.formatSearch, {
    Priority: function(value) {
        var severityMap = {
            'EMERGENCY': 0,
            'ALERT': 1,
            'CRITICAL': 2,
            'ERROR': 3,
            'WARNING': 4,
            'NOTICE': 5,
            'INFO': 6,
            'DEBUG': 7
        };
        return severityMap[value.toUpperCase()] || value;
    }
});

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>

<?php include 'includes/footer.php'; ?>

