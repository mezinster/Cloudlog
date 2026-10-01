<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_station_diary_reactions_table extends CI_Migration {

	public function up()
	{
		if ($this->db->table_exists('station_diary_reactions')) {
			return;
		}

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
			),
			'reaction' => array(
				'type' => 'VARCHAR',
				'constraint' => 16,
			),
			'visitor_hash' => array(
				'type' => 'VARCHAR',
				'constraint' => 64,
			),
			'created_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
			),
			'updated_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
			),
		));

		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('station_diary_reactions', TRUE);

		$this->db->query('ALTER TABLE station_diary_reactions ADD INDEX idx_diary_reactions_diary (diary_id)');
		$this->db->query('ALTER TABLE station_diary_reactions ADD INDEX idx_diary_reactions_reaction (reaction)');
		$this->db->query('ALTER TABLE station_diary_reactions ADD UNIQUE KEY uniq_diary_reactions_visitor (diary_id, visitor_hash)');
	}

	public function down()
	{
		$this->dbforge->drop_table('station_diary_reactions', TRUE);
	}
}
