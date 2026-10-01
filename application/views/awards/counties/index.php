<div class="container">
    <style>
        .counties-hero {
            margin-top: 1rem;
            margin-bottom: 1rem;
            padding: 1rem 1.25rem;
            border: 1px solid #d6e7ff;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #f8fbff 0%, #edf5ff 100%);
        }

        .counties-stats {
            margin-bottom: 1rem;
        }

        .counties-stat-card {
            border: 1px solid #e5e8ec;
            border-radius: 0.75rem;
            background-color: #fff;
            padding: 0.9rem 1rem;
            height: 100%;
        }

        .counties-stat-label {
            font-size: 0.75rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 0.2rem;
        }

        .counties-stat-value {
            font-size: 1.45rem;
            font-weight: 700;
            line-height: 1.2;
            color: #1f2937;
        }

        .counties-stat-sub {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .counties-table-wrap {
            border: 1px solid #dee2e6;
            border-radius: 0.75rem;
            padding: 0.5rem;
            background-color: #fff;
        }

        .counties-progress {
            min-width: 120px;
            margin-bottom: 0;
            height: 0.8rem;
            border-radius: 999px;
            background-color: #e9ecef;
        }

        .counties-progress .progress-bar {
            font-size: 0.65rem;
            font-weight: 600;
        }

        .counties-label-chip {
            display: inline-block;
            padding: 0.15rem 0.45rem;
            border-radius: 0.4rem;
            font-size: 0.75rem;
            color: #0f172a;
            background-color: #e2e8f0;
        }
    </style>

    <!-- Award Info Box -->
    <div class="counties-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <script>
            var lang_awards_info_button = "<?php echo lang('awards_info_button'); ?>";
            var lang_award_info_ln1 = "<?php echo lang('awards_counties_description_ln1'); ?>";
            var lang_award_info_ln2 = "<?php echo lang('awards_counties_description_ln2'); ?>";
            var lang_award_info_ln3 = "<?php echo lang('awards_counties_description_ln3'); ?>";
            var lang_award_info_ln4 = "<?php echo lang('awards_counties_description_ln4'); ?>";
            </script>
            <h2 class="mb-1"><?php echo $page_title; ?></h2>
            <p class="mb-0 text-muted">Track worked and confirmed US counties by state, and quickly spot where progress is closest.</p>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-primary me-1" id="displayAwardInfo"><?php echo lang('awards_info_button'); ?></button>
        </div>
    </div>
    <!-- End of Award Info Box -->

    <?php
    $state_county_targets = [];
    $overall_target = 0;
    $seen_counties = [];
    $state_name_to_abbr = [];

    $usa_states = (array)$this->config->item('usa_states_list');
    if (empty($usa_states)) {
        $this->config->load('cloudlog_data_lists');
        $usa_states = (array)$this->config->item('usa_states_list');
    }
    foreach ($usa_states as $abbr => $full_name) {
        $state_name_to_abbr[strtoupper($full_name)] = strtoupper($abbr);
    }

    $county_csv_path = FCPATH . 'assets/json/US_counties.csv';
    if (!is_readable($county_csv_path)) {
        $county_csv_path = APPPATH . '../assets/json/US_counties.csv';
    }
    if (is_readable($county_csv_path)) {
        $county_handle = fopen($county_csv_path, 'r');
        if ($county_handle !== false) {
            while (($county_row = fgetcsv($county_handle)) !== false) {
                if (!is_array($county_row) || count($county_row) < 2) {
                    continue;
                }

                $csv_state_raw = trim((string)$county_row[0]);
                $csv_county = trim((string)$county_row[1]);

                $csv_state_lookup = strtoupper($csv_state_raw);
                if (strlen($csv_state_lookup) === 2) {
                    $csv_state = $csv_state_lookup;
                } else if (isset($state_name_to_abbr[$csv_state_lookup])) {
                    $csv_state = $state_name_to_abbr[$csv_state_lookup];
                } else {
                    continue;
                }

                if ($csv_state === '' || $csv_county === '' || $csv_state === 'STATE') {
                    continue;
                }

                $county_key = $csv_state . '|' . strtolower($csv_county);
                if (isset($seen_counties[$county_key])) {
                    continue;
                }

                $seen_counties[$county_key] = true;
                if (!isset($state_county_targets[$csv_state])) {
                    $state_county_targets[$csv_state] = 0;
                }
                $state_county_targets[$csv_state]++;
                $overall_target++;
            }
            fclose($county_handle);
        }
    }

    $worked_total = 0;
    $confirmed_total = 0;
    ?>

    <?php if ($counties_array) { ?>
        <?php foreach ($counties_array as $counties) {
            $worked_total += (int)$counties['countycountworked'];
            $confirmed_total += (int)$counties['countycountconfirmed'];
        }

        $remaining_total = $overall_target > 0 ? max(0, $overall_target - $worked_total) : null;
        $worked_progress = $overall_target > 0 ? round(($worked_total / $overall_target) * 100, 1) : null;
        $confirmed_progress = $overall_target > 0 ? round(($confirmed_total / $overall_target) * 100, 1) : null;
        ?>

        <div class="row counties-stats g-3">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="counties-stat-card">
                    <div class="counties-stat-label">Worked Counties</div>
                    <div class="counties-stat-value"><?php echo $worked_total; ?></div>
                    <div class="counties-stat-sub"><?php echo $overall_target > 0 ? 'of ' . $overall_target . ' known counties' : 'county target file unavailable'; ?></div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="counties-stat-card">
                    <div class="counties-stat-label">Confirmed Counties</div>
                    <div class="counties-stat-value"><?php echo $confirmed_total; ?></div>
                    <div class="counties-stat-sub"><?php echo $worked_total > 0 ? round(($confirmed_total / $worked_total) * 100, 1) . '% of worked' : 'no worked counties yet'; ?></div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="counties-stat-card">
                    <div class="counties-stat-label">Remaining Counties</div>
                    <div class="counties-stat-value"><?php echo $remaining_total !== null ? $remaining_total : '-'; ?></div>
                    <div class="counties-stat-sub">Based on current worked count</div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="counties-stat-card">
                    <div class="counties-stat-label">Progress</div>
                    <div class="counties-stat-value"><?php echo $worked_progress !== null ? $worked_progress . '%' : '-'; ?></div>
                    <div class="counties-stat-sub"><?php echo $confirmed_progress !== null ? $confirmed_progress . '% confirmed overall' : 'no percentage available'; ?></div>
                </div>
            </div>
        </div>

        <div class="counties-table-wrap">
            <table style="width:100%" class="countiestable table table-sm table-bordered table-hover table-striped table-condensed text-center align-middle">
                <thead>
                    <tr>
                        <th scope="col">State</th>
                        <th scope="col">Worked</th>
                        <th scope="col">Confirmed</th>
                        <th scope="col">Target</th>
                        <th scope="col">Remaining</th>
                        <th scope="col">Worked Progress</th>
                        <th scope="col">Confirmed Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($counties_array as $counties) {
                        $state = strtoupper((string)$counties['COL_STATE']);
                        $worked = (int)$counties['countycountworked'];
                        $confirmed = (int)$counties['countycountconfirmed'];
                        $target = isset($state_county_targets[$state]) ? (int)$state_county_targets[$state] : null;
                        $remaining = $target !== null ? max(0, $target - $worked) : null;
                        $worked_pct = ($target !== null && $target > 0) ? round(($worked / $target) * 100, 1) : null;
                        $confirmed_pct = ($target !== null && $target > 0) ? round(($confirmed / $target) * 100, 1) : null;
                    ?>
                        <tr>
                            <td><span class="counties-label-chip"><?php echo $state; ?></span></td>
                            <td><a href='counties_details?State="<?php echo $state; ?>"&Type="worked"'><?php echo $worked; ?></a></td>
                            <td><a href='counties_details?State="<?php echo $state; ?>"&Type="confirmed"'><?php echo $confirmed; ?></a></td>
                            <td><?php echo $target !== null ? $target : '-'; ?></td>
                            <td><?php echo $remaining !== null ? $remaining : '-'; ?></td>
                            <td>
                                <?php if ($worked_pct !== null) { ?>
                                    <div class="progress counties-progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $worked_pct; ?>%" aria-valuenow="<?php echo $worked_pct; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $worked_pct; ?>%</div>
                                    </div>
                                <?php } else { echo '-'; } ?>
                            </td>
                            <td>
                                <?php if ($confirmed_pct !== null) { ?>
                                    <div class="progress counties-progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $confirmed_pct; ?>%" aria-valuenow="<?php echo $confirmed_pct; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $confirmed_pct; ?>%</div>
                                    </div>
                                <?php } else { echo '-'; } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="row">Total</th>
                        <th><a href='counties_details?State="All"&Type="worked"'><?php echo $worked_total; ?></a></th>
                        <th><a href='counties_details?State="All"&Type="confirmed"'><?php echo $confirmed_total; ?></a></th>
                        <th><?php echo $overall_target > 0 ? $overall_target : '-'; ?></th>
                        <th><?php echo $remaining_total !== null ? $remaining_total : '-'; ?></th>
                        <th><?php echo $worked_progress !== null ? $worked_progress . '%' : '-'; ?></th>
                        <th><?php echo $confirmed_progress !== null ? $confirmed_progress . '%' : '-'; ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php } else {
        echo '<div class="alert alert-danger" role="alert">Nothing found!</div>';
    } ?>
</div>