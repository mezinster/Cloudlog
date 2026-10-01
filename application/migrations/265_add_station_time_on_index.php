<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_station_time_on_index extends CI_Migration {

	public function up()
	{
		$table = $this->config->item('table_name');
		if (empty($table)) {
			return;
		}

		$this->db->db_debug = false;
		$indexExists = $this->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = 'idx_station_time_on'")->num_rows();
		if ($indexExists == 0) {
			$this->db->query("ALTER TABLE `{$table}` ADD INDEX `idx_station_time_on` (`station_id`, `COL_TIME_ON`)");
		}
		$this->db->db_debug = true;
	}

	public function down()
	{
		$table = $this->config->item('table_name');
		if (empty($table)) {
			return;
		}

		$this->db->db_debug = false;
		$indexExists = $this->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = 'idx_station_time_on'")->num_rows();
		if ($indexExists > 0) {
			$this->db->query("ALTER TABLE `{$table}` DROP INDEX `idx_station_time_on`");
		}
		$this->db->db_debug = true;
	}
}
