<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_logbook_id_to_notes extends CI_Migration {

	public function up()
	{
		$fields = array(
			'logbook_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'unsigned' => TRUE,
				'null' => TRUE,
				'default' => NULL,
			),
		);

		if (!$this->db->field_exists('logbook_id', 'notes')) {
			$this->dbforge->add_column('notes', $fields);
			
			// Add index for performance
			$this->db->query('ALTER TABLE `notes` ADD INDEX `idx_notes_logbook` (`logbook_id`)');
		}
	}

	public function down()
	{
		if ($this->db->field_exists('logbook_id', 'notes')) {
			$this->db->query('ALTER TABLE `notes` DROP INDEX `idx_notes_logbook`');
			$this->dbforge->drop_column('notes', 'logbook_id');
		}
	}
}
