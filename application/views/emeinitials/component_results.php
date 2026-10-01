<?php
if ($timeline_array) {
    $ci =& get_instance();
    $i = 1;
    echo '<div class="card shadow-sm">';
    echo '<div class="card-header d-flex align-items-center justify-content-between">';
    echo '<h2 class="h5 mb-0">'.lang('statistics_emeinitials').'</h2>';
    echo '<span class="badge text-bg-primary rounded-pill">'.count($timeline_array).'</span>';
    echo '</div><div class="card-body">';

    echo '<div class="table-responsive"><table style="width:100%" class="table table-sm timelinetable table-bordered table-hover table-striped align-middle text-center mb-0">';
    echo '<thead class="table-light"><tr>';
    echo '<th scope="col">#</th>';
    echo '<th scope="col">'.$ci->lang->line('gen_hamradio_callsign').'</th>';
    echo '<th scope="col">'.$ci->lang->line('statistics_first_qso').'</th>';
    echo '<th scope="col">'.$ci->lang->line('gen_hamradio_gridsquare').'</th>';
    echo '<th scope="col">'.$ci->lang->line('gen_hamradio_state').'</th>';
    echo '<th scope="col">'.$ci->lang->line('statistics_times_worked').'</th>';
    echo '</tr></thead><tbody>';

    foreach ($timeline_array as $line) {
        $date_as_timestamp = strtotime($line->date);
        echo '<tr>';
        echo '<td>' . $i++ . '</td>';
        echo '<td>' . $line->callsign . '</td>';
        echo '<td>' . date($custom_date_format, $date_as_timestamp) . '</td>';
        echo '<td>' . $line->gridsquare . '</td>';
        echo '<td>' . $line->state . '</td>';
        echo '<td>' . $line->count . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
    echo '</div></div>';
} else {
    echo '<div class="alert alert-info" role="alert">';
    echo '<h5 class="alert-heading">No EME Initials Found</h5>';
    echo '<p>You haven\'t worked any callsigns via EME (Earth-Moon-Earth) yet. EME Initials tracks the first time you work a callsign starting with each letter of the alphabet using moon bounce.</p>';
    echo '<p class="mb-0">Start logging your EME contacts to build your initials collection!</p>';
    echo '</div>';
}
