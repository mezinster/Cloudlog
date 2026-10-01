<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
*   Convert notes table columns to utf8mb4 to support emoji and other 4-byte characters
*/

class Migration_notes_utf8mb4_columns extends CI_Migration {

    public function up()
    {
        $this->db->query("ALTER TABLE notes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE notes CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;");
    }
}
