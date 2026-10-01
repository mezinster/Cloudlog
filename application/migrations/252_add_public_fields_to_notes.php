<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_public_fields_to_notes extends CI_Migration {

	public function up()
	{
		if (!$this->db->field_exists('is_public', 'notes')) {
			$this->dbforge->add_column('notes', array(
				'is_public' => array(
					'type' => 'TINYINT',
					'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
				),
			));
		}

		if (!$this->db->field_exists('include_qso_summary', 'notes')) {
			$this->dbforge->add_column('notes', array(
				'include_qso_summary' => array(
					'type' => 'TINYINT',
					'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
				),
			));
		}

		$this->db->db_debug = false;
		$indexExists = $this->db->query("SHOW INDEX FROM notes WHERE Key_name = 'idx_notes_public_diary'")->num_rows();
		if ($indexExists == 0) {
			$this->db->query("ALTER TABLE notes ADD INDEX idx_notes_public_diary (user_id, cat, is_public, created_at)");
		}
		$this->db->db_debug = true;
	}

	public function down()
	{
		$this->db->db_debug = false;
		$indexExists = $this->db->query("SHOW INDEX FROM notes WHERE Key_name = 'idx_notes_public_diary'")->num_rows();
		if ($indexExists > 0) {
			$this->db->query("ALTER TABLE notes DROP INDEX idx_notes_public_diary");
		}
		$this->db->db_debug = true;

		if ($this->db->field_exists('include_qso_summary', 'notes')) {
			$this->dbforge->drop_column('notes', 'include_qso_summary');
		}

		if ($this->db->field_exists('is_public', 'notes')) {
			$this->dbforge->drop_column('notes', 'is_public');
		}
	}
}
