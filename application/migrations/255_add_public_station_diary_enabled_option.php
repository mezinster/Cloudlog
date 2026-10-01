<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_public_station_diary_enabled_option extends CI_Migration {

    public function up()
    {
        // Check if public_station_diary_enabled option already exists
        $this->db->where('option_name', 'public_station_diary_enabled');
        $query = $this->db->get('options');
        
        if ($query->num_rows() == 0) {
            // Option doesn't exist, insert it with default value of 1 (enabled)
            $data = array(
                'option_name' => 'public_station_diary_enabled',
                'option_value' => '1',
                'autoload' => 'yes'
            );
            $this->db->insert('options', $data);
        }
    }

    public function down()
    {
        // Remove the option on rollback
        $this->db->where('option_name', 'public_station_diary_enabled');
        $this->db->delete('options');
    }

}
