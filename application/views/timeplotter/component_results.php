<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h2 class="h5 mb-0">Time Distribution</h2>
        <span class="badge text-bg-secondary rounded-pill">Updated</span>
    </div>
    <div class="card-body">
        <div id="timeplotParams"
             data-band="<?php echo htmlspecialchars($band, ENT_QUOTES, 'UTF-8'); ?>"
             data-dxcc="<?php echo htmlspecialchars($dxcc, ENT_QUOTES, 'UTF-8'); ?>"
             data-cqzone="<?php echo htmlspecialchars($cqzone, ENT_QUOTES, 'UTF-8'); ?>"></div>

        <div id="timeplotter_div"></div>
    </div>
</div>
