<?php
function show_empty_state($message = 'No records found', $icon = 'search', $action_button = null) {
    ?>
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="bi bi-<?php echo $icon; ?>" style="font-size: 3rem; color: var(--text-secondary);"></i>
        </div>
        <h4 class="text-secondary mb-3"><?php echo $message; ?></h4>
        <?php if ($action_button): ?>
            <?php echo $action_button; ?>
        <?php endif; ?>
    </div>
    <?php
} 