<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_qso_filter_fields_to_notes extends CI_Migration {

	public function up()
	{
		$fields = array(
			'qso_date_start' => array(
				'type' => 'DATE',
				'null' => TRUE,
			),
			'qso_date_end' => array(
				'type' => 'DATE',
				'null' => TRUE,
			),
			'qso_satellite_only' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
			),
		);

		foreach ($fields as $fieldName => $fieldConfig) {
			if (!$this->db->field_exists($fieldName, 'notes')) {
				$this->dbforge->add_column('notes', array($fieldName => $fieldConfig));
			}
		}
	}

	public function down()
	{
		$this->dbforge->drop_column('notes', 'qso_date_start');
		$this->dbforge->drop_column('notes', 'qso_date_end');
		$this->dbforge->drop_column('notes', 'qso_satellite_only');
	}
}
