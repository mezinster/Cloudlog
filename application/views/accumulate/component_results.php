<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h2 class="h5 mb-0">Results</h2>
        <span class="badge text-bg-secondary rounded-pill">Updated</span>
    </div>
    <div class="card-body">
        <div id="accumulateParams"
             data-band="<?php echo htmlspecialchars($band, ENT_QUOTES, 'UTF-8'); ?>"
             data-award="<?php echo htmlspecialchars($award, ENT_QUOTES, 'UTF-8'); ?>"
             data-mode="<?php echo htmlspecialchars($mode, ENT_QUOTES, 'UTF-8'); ?>"
             data-period="<?php echo htmlspecialchars($period, ENT_QUOTES, 'UTF-8'); ?>"></div>

        <div id="accumulateContainer">
            <canvas id="myChartAccumulate" width="400" height="150"></canvas>
            <div id="accumulateTable"></div>
        </div>
    </div>
</div>
