<div class="container qso_panel contesting">
    <div class="float-end">
        <button type="button" class="btn btn-sm btn-primary me-2" onclick="openBandmap()" title="Open DX Cluster Bandmap"><i class="fas fa-chart-line"></i> <?php echo lang('menu_bandmap'); ?></button>
        <button type="button" class="btn btn-sm btn-success me-2" data-bs-toggle="modal" data-bs-target="#cabrilloExportModal" title="Export contest log as Cabrillo file"><i class="fas fa-download"></i> Export Cabrillo</button>
        <button type="button" class="btn btn-sm btn-warning" onclick="reset_contest_session()"><i class="fas fa-sync-alt"></i> <?php echo lang('contesting_button_reset_contest_session'); ?></button>
    </div>
    <h2 style="display:inline"><?php echo lang('contesting_page_title'); ?> </h2> <?php echo ($_GET['manual'] == 0 ? " <span style='display:inline; cursor: pointer;' class='align-text-top badge text-bg-success' onclick=\"window.location.href='" . site_url('contesting') . "?manual=1'\" title='Switch to POST mode'>LIVE</span>" : " <span style='display:inline; cursor: pointer;' class='align-text-top badge text-bg-danger' onclick=\"window.location.href='" . site_url('contesting') . "?manual=0'\" title='Switch to LIVE mode'>POST</span>");  ?>
    <div class="row">

        <div class="col-sm-12 col-md-12">
            <div class="card">
                <div class="card-body">
                    <form id="qso_input" name="qsos">
                        <div class="d-flex justify-content-end mb-1">
                            <button type="button" class="btn btn-link btn-sm text-muted p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#contest-settings-panel" title="Toggle session settings">
                                <i class="fas fa-sliders-h me-1"></i><small>Session Settings <i class="fas fa-chevron-down fa-xs" id="settings-chevron"></i></small>
                            </button>
                        </div>
                        <div id="contest-settings-panel" class="collapse show">
                        <div class="mb-3 row">
							<label class="col-auto control-label" for="radio"><?php echo lang('contesting_exchange_type'); ?></label>

							<div class="col-auto">
								<select class="form-select form-select-sm" id="exchangetype" name="exchangetype">
									<option value='None'><?php echo lang('contesting_exchange_type_none'); ?></option>
									<option value='Exchange'><?php echo lang('contesting_exchange_type_exchange'); ?></option>
									<option value='Gridsquare'><?php echo lang('contesting_exchange_type_gridsquare'); ?></option>
									<option value='Serial'><?php echo lang('contesting_exchange_type_serial'); ?></option>
									<option value='Serialexchange'><?php echo lang('contesting_exchange_type_serial_exchange'); ?></option>
									<option value='Serialgridsquare'><?php echo lang('contesting_exchange_type_serial_gridsquare'); ?></option>
								</select>
							</div>

                            <label class="col-auto control-label" for="contestname"><?php echo lang('contesting_contest_name'); ?></label>

                            <div class="col-auto">
                                <select class="form-select form-select-sm" id="contestname" name="contestname">
									<?php foreach($contestnames as $contest) {
										echo "<option value='" . $contest['adifname'] . "'>" . $contest['name'] . "</option>";
									} ?>
                                </select>
                            </div>

                            <label class="col-auto control-label" for="operatorcall"><?php echo lang('contesting_operator_callsign'); ?></label>
                            <div class="col-auto">
                                <input type="text" class="form-control form-control-sm" id="operator_callsign" name="operator_callsign" value='<?php echo $this->session->userdata('operator_callsign'); ?>' required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-2">
                                <label for="start_date"><?php echo lang('general_word_date'); ?></label>
                                <input type="text" class="form-control form-control-sm input_date" name="start_date" id="start_date" value="<?php if (($this->session->userdata('start_date') != NULL && ((time() - $this->session->userdata('time_stamp')) < 24 * 60 * 60))) { echo $this->session->userdata('start_date'); } else { echo date('d-m-Y');}?>" <?php echo ($_GET['manual'] == 0 ? "disabled" : "");  ?> >
                            </div>

                            <div class="mb-3 col-md-1">
                                <label for="start_time"><?php echo lang('general_word_time'); ?></label>
                                <input type="text" class="form-control form-control-sm input_time" name="start_time" id="start_time" value="<?php if (($this->session->userdata('start_time') != NULL && ((time() - $this->session->userdata('time_stamp')) < 24 * 60 * 60))) { echo substr($this->session->userdata('start_time'),0,5); } else { echo $_GET['manual'] == 0 ? date('H:i:s') : date('H:i'); } ?>" size="7" <?php echo ($_GET['manual'] == 0 ? "disabled" : "");  ?> >
                            </div>

                            <?php if ( $_GET['manual'] == 0 ) { ?>
                              <input class="input_time" type="hidden" id="start_time"  name="start_time"value="<?php echo date('H:i'); ?>" />
                              <input class="input_date" type="hidden" id="start_date" name="start_date" value="<?php echo date('d-m-Y'); ?>" />
                            <?php } ?>

                            <div class="mb-3 col-md-2">
                                <label for="mode"><?php echo lang('gen_hamradio_mode'); ?></label>
                                <select id="mode" class="form-select mode form-select-sm" name="mode">
                                    <?php foreach($modes->result() as $mode) {
                                            if ($mode->submode == null) {
                                                printf("<option value=\"%s\" %s>%s</option>", $mode->mode, $this->session->userdata('mode')==$mode->mode?"selected=\"selected\"":"",$mode->mode);
                                            } else {
                                                printf("<option value=\"%s\" %s>&rArr; %s</option>", $mode->submode, $this->session->userdata('mode')==$mode->submode?"selected=\"selected\"":"",$mode->submode);
                                            }
                                    } ?>
                                </select>
                            </div>

                            <div class="mb-3 col-md-2">
                                <label for="band"><?php echo lang('gen_hamradio_band'); ?></label>

                                <select id="band" class="form-select form-select-sm" name="band">
                                <?php foreach($bands as $key=>$bandgroup) {
                                    echo '<optgroup label="' . strtoupper($key) . '">';
                                    foreach($bandgroup as $band) {
                                        echo '<option value="' . $band . '"';
                                        if ($this->session->userdata('band') == $band) echo ' selected';
                                        echo '>' . $band . '</option>'."\n";
                                    }
                                    echo '</optgroup>';
                                    }
                                ?>
                                </select>
                            </div>

                            <div class="mb-3 col-md-2">
                                <label for="frequency"><?php echo lang('gen_hamradio_frequency'); ?></label>
                                <input type="text" class="form-control form-control-sm" id="frequency" name="freq_display" value="<?php echo $this->session->userdata('freq'); ?>" />
                            </div>

                            <div class="mb-3 col-md-2">
                                <label for="inputRadio"><?php echo lang('gen_hamradio_radio'); ?></label>
                                <select class="form-select form-select-sm radios" id="radio" name="radio">
                                    <option value="0" selected="selected"><?php echo lang('general_word_none'); ?></option>
                                        <?php foreach ($radios->result() as $row) { ?>
                                        <option value="<?php echo $row->id; ?>" <?php if($this->session->userdata('radio') == $row->id) { echo "selected=\"selected\""; } ?>><?php echo $row->radio; ?></option>
                                        <?php } ?>
                                </select>
                            </div>
                        </div>

                        </div><!-- /contest-settings-panel -->

                        <div id="radio_status"></div>

                        <div class="row align-items-end">
                            <div class="mb-3 col-md-4">
                                <label for="callsign"><strong><?php echo lang('gen_hamradio_callsign'); ?></strong></label>
                                <input type="text" class="form-control" id="callsign" name="callsign" required pattern="\S+" title="Whitespace is not allowed" autocomplete="off" autocorrect="off" autocapitalize="characters" spellcheck="false">
                            </div>

                            <div class="mb-3 col-md-1">
                                <label for="rst_sent" class="text-muted small"><?php echo lang('gen_hamradio_rsts'); ?></label>
                                <input type="text" class="form-control text-center text-muted" name="rst_sent" id="rst_sent" value="59">
                            </div>

                            <div style="display:none" class="mb-3 col-md-1 serials">
								<label for="exch_serial_s"><?php echo lang('contesting_exchange_serial_s'); ?></label>
                                <input type="number" class="form-control" name="exch_serial_s" id="exch_serial_s" value="">
							</div>

                            <div style="display:none" class="mb-3 col-md-1 exchanges">
                                <label for="exch_sent"><?php echo lang('gen_hamradio_exchange_sent_short'); ?></label>
                                <input type="text" class="form-control" name="exch_sent" id="exch_sent" value="">
                            </div>

							<div style="display:none" class="mb-3 col-md-2 gridsquares">
								<label for="exch_gridsquare_s"><?php echo lang('contesting_exchange_gridsquare_s'); ?></label>
                                <input disabled type="text" class="form-control" name="exch_gridsquare_s" id="exch_gridsquare_s" value="<?php echo $my_gridsquare;?>">
							</div>

                            <div class="mb-3 col-md-1">
                                <label for="rst_rcvd" class="text-muted small"><?php echo lang('gen_hamradio_rstr'); ?></label>
                                <input type="text" class="form-control text-center text-muted" name="rst_rcvd" id="rst_rcvd" value="59">
                            </div>

                            <div style="display:none" class="mb-3 col-md-1 serialr">
								<label for="exch_serial_r"><?php echo lang('contesting_exchange_serial_r'); ?></label>
                                <input type="number" class="form-control" name="exch_serial_r" id="exch_serial_r" value="">
							</div>

							<div style="display:none" class="mb-3 col-md-1 exchanger">
								<label for="exch_rcvd"><?php echo lang('gen_hamradio_exchange_rcvd_short'); ?></label>
                                <input type="text" class="form-control" name="exch_rcvd" id="exch_rcvd" value="">
							</div>

							<div style="display:none" class="mb-3 col-md-2 gridsquarer">
								<label for="exch_gridsquare_r"><?php echo lang('contesting_exchange_gridsquare_r'); ?></label>
                                <input type="text" class="form-control" name="locator" id="exch_gridsquare_r" value="" maxlength="8">
								<small id="locator_info_contest" class="badge text-bg-info"></small>
							</div>

							<div style="display:none" class="mb-3 col-md-1 gridsquarer">
								<label for="distance_contest"><?php echo lang('gen_hamradio_distance'); ?></label>
							<input type="text" class="form-control" id="distance_contest" value="" disabled>
							</div>
                        </div>
                        <div class="mb-1" id="callsign-badge-row" style="min-height:1.5rem">
                            <small id="callsign_info" class="badge text-bg-danger"></small>
                            <small id="locator_info_contest_dxcc" class="badge text-bg-info ms-1" style="display:none"></small>
                            <small id="distance_contest_dxcc" class="badge text-bg-secondary ms-1" style="display:none"></small>
                        </div>

                        <div class="d-flex align-items-center gap-2 mt-1 mb-2">
                            <button type="button" class="btn btn-lg btn-outline-secondary" id="reset_qso" onclick="reset_log_fields()"><i class="fas fa-sync-alt"></i> <?php echo lang('contesting_btn_reset_qso'); ?></button>
                            <button type="button" class="btn btn-success btn-lg px-4" id="save_qso" onclick="logQso();"><i class="fas fa-save"></i> <?php echo lang('contesting_btn_save_qso'); ?></button>
                            <button type="button" class="btn btn-link btn-sm text-muted p-0 text-decoration-none ms-1" data-bs-toggle="collapse" data-bs-target="#extra-fields" title="Name, comment and copy exchange">
                                <i class="fas fa-ellipsis-h me-1"></i><small>Extra fields <i class="fas fa-chevron-right fa-xs" id="extra-chevron"></i></small>
                            </button>
                        </div>
                        <div id="extra-fields" class="collapse">
                            <div class="row mt-1">
                                <div class="mb-2 col-md-4">
                                    <label for="name" class="form-label form-label-sm"><?php echo lang('general_word_name'); ?></label>
                                    <input type="text" class="form-control form-control-sm" name="name" id="name" value="">
                                </div>
                                <div class="mb-2 col-md-4">
                                    <label for="comment" class="form-label form-label-sm"><?php echo lang('general_word_comment'); ?></label>
                                    <input type="text" class="form-control form-control-sm" name="comment" id="comment" value="">
                                </div>
                                <div class="mb-2 col-md-4">
                                    <label class="form-label form-label-sm">Copy exchange to</label>
                                    <select class="form-select form-select-sm" id="copyexchangeto" name="copyexchangeto">
                                        <option value='None'><?php echo lang('contesting_copy_exch_to_none'); ?></option>
                                        <option value='dok'><?php echo lang('contesting_copy_exch_to_dok'); ?></option>
                                        <option value='name'><?php echo lang('contesting_copy_exch_to_name'); ?></option>
                                        <option value='age'><?php echo lang('contesting_copy_exch_to_age'); ?></option>
                                        <option value='state'><?php echo lang('contesting_copy_exch_to_state'); ?></option>
                                        <option value='power'><?php echo lang('contesting_copy_exch_to_power'); ?></option>
                                        <option value='locator'><?php echo lang('contesting_copy_exch_to_locator'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <br/>

            <!-- Callsign SCP Box -->
            <div class="card callsign-suggest" style="display:none">
                <div class="card-header"><h5 class="card-title"><?php echo lang('contesting_title_callsign_suggestions'); ?></h5></div>

                <div class="card-body callsign-suggestions"></div>
            </div>

            <div class="card mt-3" id="callhistory-info-panel" style="display: none;">
                <div class="card-header"><h5 class="card-title mb-0">Call History Membership</h5></div>
                <div class="card-body" id="callhistory-results">
                    <div class="text-muted">Type a callsign to see membership details from your uploaded call history files.</div>
                </div>
            </div>

            <!-- Contest Stats Card -->
            <div class="card mt-3" id="contest-stats-card" style="display:none">
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div>
                            <span class="text-muted me-1">Session:</span>
                            <span class="badge text-bg-primary" id="stats-total">0 QSOs</span>
                        </div>
                        <div>
                            <span class="text-muted me-1">By Band:</span>
                            <span id="stats-bands"></span>
                        </div>
                        <div>
                            <span class="text-muted me-1">Rate:</span>
                            <span class="badge text-bg-info" id="stats-rate">0/hr</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Past QSO Box -->
            <div class="card log">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0"><?php echo lang('contesting_title_contest_logbook'); ?></h5>
                    <div class="d-flex align-items-center gap-1">
                        <label for="logbook-search" class="text-muted small mb-0 me-1">Search:</label>
                        <input type="search" id="logbook-search" class="form-control form-control-sm" style="width:180px" placeholder="">
                    </div>
                </div>
                <table style="width:100%" class="table-sm table qsotable table-bordered table-hover table-striped table-condensed text-center mb-0">
                    <thead>
                        <tr class="log_title titles">
                            <th><?php echo lang('general_word_date'); ?>/<?php echo lang('general_word_time'); ?></th>
                            <th><?php echo lang('gen_hamradio_call'); ?></th>
                            <th><?php echo lang('gen_hamradio_band'); ?></th>
                            <th><?php echo lang('gen_hamradio_mode'); ?></th>
                            <th><?php echo lang('gen_hamradio_rsts'); ?></th>
                            <th><?php echo lang('gen_hamradio_rstr'); ?></th>
                            <th><?php echo lang('gen_hamradio_exchange_sent_short'); ?></th>
                            <th><?php echo lang('gen_hamradio_exchange_rcvd_short'); ?></th>
							<th><?php echo lang('contesting_exchange_serial_s'); ?></th>
							<th><?php echo lang('contesting_exchange_serial_r'); ?></th>
							<th><?php echo lang('contesting_exchange_type_gridsquare'); ?></th>
							<th><?php echo 'VUCC ' . lang('contesting_exchange_type_gridsquare'); ?></th>
                        </tr>
                    </thead>

                    <tbody class="contest_qso_table_contents">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Chevron icons for collapsibles
    document.getElementById('contest-settings-panel').addEventListener('hide.bs.collapse', function () {
        document.getElementById('settings-chevron').classList.replace('fa-chevron-down', 'fa-chevron-right');
    });
    document.getElementById('contest-settings-panel').addEventListener('show.bs.collapse', function () {
        document.getElementById('settings-chevron').classList.replace('fa-chevron-right', 'fa-chevron-down');
    });
    document.getElementById('extra-fields').addEventListener('show.bs.collapse', function () {
        document.getElementById('extra-chevron').classList.replace('fa-chevron-right', 'fa-chevron-down');
    });
    document.getElementById('extra-fields').addEventListener('hide.bs.collapse', function () {
        document.getElementById('extra-chevron').classList.replace('fa-chevron-down', 'fa-chevron-right');
    });

    function openBandmap() {
        // Open bandmap in a new window without URL bar, toolbars, etc.
        const width = 500; 
        const height = 800;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;
        
        // Note: Modern browsers may still show address bar due to security restrictions
        // For Chrome, you can use: chrome.exe --app=http://localhost/index.php/dxcluster/bandmap
        const features = `width=${width},height=${height},left=${left},top=${top},` +
                       `toolbar=no,location=no,directories=no,status=no,menubar=no,` +
                       `scrollbars=yes,resizable=yes,copyhistory=no`;
        
        const popup = window.open('<?php echo site_url('dxcluster/bandmap'); ?>', 'bandmap', features);
        
        // Try to make it fullscreen (user will need to allow this)
        if (popup) {
            popup.focus();
        }
    }
</script>

<!-- Cabrillo Export Modal -->
<div class="modal fade" id="cabrilloExportModal" tabindex="-1" aria-labelledby="cabrilloExportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cabrilloExportModalLabel"><i class="fas fa-download"></i> Export Cabrillo Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo site_url('cabrillo/export'); ?>" method="post">
            <div class="modal-body">
                <input type="hidden" name="station_id" value="<?php echo htmlspecialchars($active_station_id); ?>">
                <input type="hidden" name="contestid" value="<?php echo htmlspecialchars($contest_session ? $contest_session->contestid : ''); ?>">

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label fw-semibold">Contest:</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($contest_session ? $contest_session->contestid : 'No active contest session'); ?>">
                        <?php if (!$contest_session): ?>
                        <div class="form-text text-danger">No active contest session. Start a contest first on this page.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_from">Date From:</label>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="cab_from" name="contestdatesfrom" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <label class="col-md-2 col-form-label" for="cab_to">Date To:</label>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="cab_to" name="contestdatesto" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_categoryoperator"><?php echo lang('export_cabrillo_cat_operator'); ?>:</label>
                    <div class="col-md-8">
                        <select class="form-select" id="cab_categoryoperator" name="categoryoperator">
                            <option value="SINGLE-OP"><?php echo lang('export_cabrillo_cat_operator_single_op'); ?></option>
                            <option value="MULTI-OP"><?php echo lang('export_cabrillo_cat_operator_multi_op'); ?></option>
                            <option value="CHECKLOG"><?php echo lang('export_cabrillo_cat_operator_checklog'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_categoryassisted"><?php echo lang('export_cabrillo_cat_assisted'); ?>:</label>
                    <div class="col-md-8">
                        <select class="form-select" id="cab_categoryassisted" name="categoryassisted">
                            <option value="NON-ASSISTED"><?php echo lang('export_cabrillo_cat_assisted_not_ass'); ?></option>
                            <option value="ASSISTED"><?php echo lang('export_cabrillo_cat_assisted_ass'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_categoryband"><?php echo lang('export_cabrillo_cat_band'); ?>:</label>
                    <div class="col-md-8">
                        <select class="form-select" id="cab_categoryband" name="categoryband">
                            <option value="ALL"><?php echo lang('general_word_all'); ?></option>
                            <option value="160M">160 M</option>
                            <option value="80M">80 M</option>
                            <option value="40M">40 M</option>
                            <option value="20M">20 M</option>
                            <option value="15M">15 M</option>
                            <option value="10M">10 M</option>
                            <option value="6M">6 M</option>
                            <option value="4M">4 M</option>
                            <option value="2M">2 M</option>
                            <option value="222">222 MHz (1.25 M)</option>
                            <option value="432">432 MHz (70 CM)</option>
                            <option value="902">902 MHz (33 CM)</option>
                            <option value="1.2G">1.2 GHz</option>
                            <option value="2.3G">2.3 GHz</option>
                            <option value="3.4G">3.4 GHz</option>
                            <option value="5.7G">5.7 GHz</option>
                            <option value="10G">10 GHz</option>
                            <option value="24G">24 GHz</option>
                            <option value="47G">47 GHz</option>
                            <option value="75G">75 GHz</option>
                            <option value="122G">122 GHz</option>
                            <option value="134G">134 GHz</option>
                            <option value="241G">241 GHz</option>
                            <option value="Light"><?php echo lang('general_word_light'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_categorymode"><?php echo lang('export_cabrillo_cat_mode'); ?>:</label>
                    <div class="col-md-8">
                        <select class="form-select" id="cab_categorymode" name="categorymode">
                            <option value="MIXED">MIXED</option>
                            <option value="CW">CW</option>
                            <option value="DIGI">DIGI</option>
                            <option value="FM">FM</option>
                            <option value="RTTY">RTTY</option>
                            <option value="SSB">SSB</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_categorypower"><?php echo lang('export_cabrillo_cat_power'); ?>:</label>
                    <div class="col-md-8">
                        <select class="form-select" id="cab_categorypower" name="categorypower">
                            <option value="LOW">LOW</option>
                            <option value="HIGH">HIGH</option>
                            <option value="QRP">QRP</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_categorystation"><?php echo lang('export_cabrillo_cat_station'); ?>:</label>
                    <div class="col-md-8">
                        <select class="form-select" id="cab_categorystation" name="categorystation">
                            <option value="FIXED">FIXED</option>
                            <option value="DISTRIBUTED">DISTRIBUTED</option>
                            <option value="MOBILE">MOBILE</option>
                            <option value="PORTABLE">PORTABLE</option>
                            <option value="ROVER">ROVER</option>
                            <option value="ROVER-LIMITED">ROVER-LIMITED</option>
                            <option value="ROVER-UNLIMITED">ROVER-UNLIMITED</option>
                            <option value="EXPEDITION">EXPEDITION</option>
                            <option value="HQ">HQ</option>
                            <option value="SCHOOL">SCHOOL</option>
                            <option value="EXPLORER">EXPLORER</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_categorytransmitter"><?php echo lang('export_cabrillo_cat_transmitter'); ?>:</label>
                    <div class="col-md-8">
                        <select class="form-select" id="cab_categorytransmitter" name="categorytransmitter">
                            <option value="ONE">ONE</option>
                            <option value="TWO">TWO</option>
                            <option value="LIMITED">LIMITED</option>
                            <option value="UNLIMITED">UNLIMITED</option>
                            <option value="SWL">SWL</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_categoryoverlay"><?php echo lang('export_cabrillo_cat_overlay'); ?>:</label>
                    <div class="col-md-8">
                        <select class="form-select" id="cab_categoryoverlay" name="categoryoverlay">
                            <option value="">None / Not Applicable</option>
                            <option value="CLASSIC">CLASSIC</option>
                            <option value="ROOKIE">ROOKIE</option>
                            <option value="TB-WIRES">TB-WIRES</option>
                            <option value="YOUTH">YOUTH</option>
                            <option value="NOVICE-TECH">NOVICE-TECH</option>
                            <option value="YL">YL</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_categorytime">Category Time:</label>
                    <div class="col-md-8">
                        <select class="form-select" id="cab_categorytime" name="categorytime">
                            <option value="">Not specified</option>
                            <option value="6-HOURS">6-HOURS</option>
                            <option value="8-HOURS">8-HOURS</option>
                            <option value="12-HOURS">12-HOURS</option>
                            <option value="24-HOURS">24-HOURS</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_location">Location:
                        <span tabindex="0" data-bs-toggle="tooltip" title="Required for ARRL, CQ, IARU-HF, RSGB-IOTA and RDXC contests. Use your ARRL/RAC section abbreviation (e.g. CT) for US/Canada, DX for foreign stations, Island Name for RSGB-IOTA, or RDA Number for RDXC."><i class="fas fa-info-circle"></i></span>
                    </label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="cab_location" name="location" placeholder="e.g. CT or DX">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_operators"><?php echo lang('export_cabrillo_operators'); ?>:</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="cab_operators" name="operators" placeholder="Space-separated callsigns">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_club">Club:</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="cab_club" name="club">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label" for="cab_soapbox"><?php echo lang('export_cabrillo_soapbox'); ?>:</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="cab_soapbox" name="soapbox">
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Export Cabrillo</button>
            </div>
            </form>
        </div>
    </div>
</div>
