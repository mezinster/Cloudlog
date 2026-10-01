<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_callhistory_files_table extends CI_Migration {

    public function up()
    {
        if (!$this->db->table_exists('callhistory_files')) {
            $this->dbforge->add_field(array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE,
                ),
                'user_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'null' => FALSE,
                ),
                'file_label' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE,
                ),
                'organization_label' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                    'null' => TRUE,
                ),
                'stored_filename' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => FALSE,
                ),
                'original_filename' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => FALSE,
                ),
                'mime_type' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE,
                ),
                'file_size' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'null' => TRUE,
                ),
                'checksum' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 64,
                    'null' => TRUE,
                ),
                'is_active' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ),
                'priority' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                ),
                'parser_status' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'ready',
                ),
                'parser_message' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => TRUE,
                ),
                'uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            ));

            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('user_id');
            $this->dbforge->add_key('is_active');
            $this->dbforge->add_key('priority');
            $this->dbforge->create_table('callhistory_files');
        }
    }

    public function down()
    {
        if ($this->db->table_exists('callhistory_files')) {
            $this->dbforge->drop_table('callhistory_files');
        }
    }
}
