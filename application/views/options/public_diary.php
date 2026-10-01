<div class="container settings">

	<div class="row">
		<!-- Nav Start -->
		<?php $this->load->view('options/sidebar') ?>
		<!-- Nav End -->

		<!-- Content -->
		<div class="col-md-9">
            <div class="card">
                <div class="card-header"><h2><i class="fas fa-book"></i> <?php echo $page_title; ?> - <?php echo $sub_heading; ?></h2></div>

                <div class="card-body">
                    <?php if($this->session->flashdata('success')) { ?>
                        <div class="alert alert-success">
                            <?php echo $this->session->flashdata('success'); ?>
                        </div>
                    <?php } ?>

                    <?php echo form_open('options/public_diary_save'); ?>

                    <div class="mb-3">
                        <label for="publicStationDiaryEnabled"><?php echo lang('options_public_station_diary_enabled'); ?></label>
                        <select class="form-select" id="publicStationDiaryEnabled" name="public_station_diary_enabled" aria-describedby="publicStationDiaryEnabledHelp" required>
                            <option value='1' <?php if($this->optionslib->get_option('public_station_diary_enabled') == "1" || $this->optionslib->get_option('public_station_diary_enabled') == "true") { echo "selected=\"selected\""; } ?>><?php echo lang('options_enabled'); ?></option>
                            <option value='0' <?php if($this->optionslib->get_option('public_station_diary_enabled') != "1" && $this->optionslib->get_option('public_station_diary_enabled') != "true") { echo "selected=\"selected\""; } ?>><?php echo lang('options_disabled'); ?></option>
                        </select>
                        <small id="publicStationDiaryEnabledHelp" class="form-text text-muted"><?php echo lang('options_public_station_diary_enabled_hint'); ?></small>
                    </div>

                    <input class="btn btn-primary" type="submit" value="<?php echo lang('options_save'); ?>" />
                    </form>
                </div>
            </div>
		</div>
	</div>

</div>
