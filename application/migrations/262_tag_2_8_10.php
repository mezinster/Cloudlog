<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
*   Tag Cloudlog as 2.8.10 Migration
*/

class Migration_tag_2_8_10 extends CI_Migration {

    public function up()
    {

        // Tag Cloudlog 2.8.10
        $this->db->where('option_name', 'version');
        $this->db->update('options', array('option_value' => '2.8.10'));

        // Trigger Version Info Dialog
        $this->db->where('option_type', 'version_dialog');
        $this->db->where('option_name', 'confirmed');
        $this->db->update('user_options', array('option_value' => 'false'));

    }

    public function down()
    {
        $this->db->where('option_name', 'version');
        $this->db->update('options', array('option_value' => '2.8.9'));
    }
}