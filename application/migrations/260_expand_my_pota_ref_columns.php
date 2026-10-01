<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_expand_my_pota_ref_columns extends CI_Migration
{
	public function up()
	{
		if ($this->db->field_exists('COL_MY_POTA_REF', $this->config->item('table_name'))) {
			$this->dbforge->modify_column($this->config->item('table_name'), array(
				'COL_MY_POTA_REF' => array(
					'name' => 'COL_MY_POTA_REF',
					'type' => 'VARCHAR',
					'constraint' => '255',
					'null' => true,
				),
			));
		}

		if ($this->db->field_exists('station_pota', 'station_profile')) {
			$this->dbforge->modify_column('station_profile', array(
				'station_pota' => array(
					'name' => 'station_pota',
					'type' => 'VARCHAR',
					'constraint' => '255',
					'null' => true,
				),
			));
		}
	}

	public function down()
	{
		if ($this->db->field_exists('COL_MY_POTA_REF', $this->config->item('table_name'))) {
			$this->dbforge->modify_column($this->config->item('table_name'), array(
				'COL_MY_POTA_REF' => array(
					'name' => 'COL_MY_POTA_REF',
					'type' => 'VARCHAR',
					'constraint' => '50',
					'null' => true,
				),
			));
		}

		if ($this->db->field_exists('station_pota', 'station_profile')) {
			$this->dbforge->modify_column('station_profile', array(
				'station_pota' => array(
					'name' => 'station_pota',
					'type' => 'VARCHAR',
					'constraint' => '50',
					'null' => true,
				),
			));
		}
	}
}
