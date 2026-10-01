<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h2 class="h5 mb-0">Continents</h2>
        <span class="badge text-bg-secondary rounded-pill">Updated</span>
    </div>
    <div class="card-body">
        <div id="continentParams"
             data-band="<?php echo htmlspecialchars($band, ENT_QUOTES, 'UTF-8'); ?>"
             data-mode="<?php echo htmlspecialchars($mode, ENT_QUOTES, 'UTF-8'); ?>"></div>

        <div style="display: flex;" id="continentContainer">
            <div style="flex: 1;"><canvas id="continentChart" width="500" height="500"></canvas></div>
            <div style="flex: 1;" id="continentTable">
                <table style="width:100%" class="continentstable table table-sm table-bordered table-hover table-striped align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Continent</th>
                            <th scope="col"># of QSOs worked</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
