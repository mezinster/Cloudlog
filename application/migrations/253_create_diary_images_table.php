<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_diary_images_table extends CI_Migration {

	public function up()
	{
		if (!$this->db->table_exists('diary_images')) {
			$this->dbforge->add_field(array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => TRUE,
					'auto_increment' => TRUE,
				),
				'diary_id' => array(
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => TRUE,
					'null' => FALSE,
				),
				'filename' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => FALSE,
				),
				'caption' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE,
				),
				'sort_order' => array(
					'type' => 'INT',
					'constraint' => 11,
					'default' => 0,
				),
				'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			));

			$this->dbforge->add_key('id', TRUE);
			$this->dbforge->add_key('diary_id');
			$this->dbforge->create_table('diary_images');
		}
	}

	public function down()
	{
		if ($this->db->table_exists('diary_images')) {
			$this->dbforge->drop_table('diary_images');
		}
	}
}
