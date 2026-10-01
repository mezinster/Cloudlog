<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_expand_pota_ref_column extends CI_Migration
{
	public function up()
	{
		if ($this->db->field_exists('COL_POTA_REF', $this->config->item('table_name'))) {
			$this->dbforge->modify_column($this->config->item('table_name'), array(
				'COL_POTA_REF' => array(
					'name' => 'COL_POTA_REF',
					'type' => 'VARCHAR',
					'constraint' => '255',
					'null' => true,
				),
			));
		}
	}

	public function down()
	{
		if ($this->db->field_exists('COL_POTA_REF', $this->config->item('table_name'))) {
			$this->dbforge->modify_column($this->config->item('table_name'), array(
				'COL_POTA_REF' => array(
					'name' => 'COL_POTA_REF',
					'type' => 'VARCHAR',
					'constraint' => '30',
					'null' => true,
				),
			));
		}
	}
}
