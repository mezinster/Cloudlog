<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Migration_add_ft2_submode
 *
 * Add FT2 submode under MFSK
 * This isn't part of the ADIF Spec yet
 */

class Migration_add_ft2_submode extends CI_Migration
{
	public function up()
	{
		// insert new FT4
		$query = $this->db->get_where('adif_modes', array('submode' => 'FT2'));
		if ($query->num_rows() == 0) {
			$data = array(
				array('mode' => "MFSK", 'submode' => "FT2", 'qrgmode' => "DATA", 'active' => 1),
			);
			$this->db->insert_batch('adif_modes', $data);
		}
	}

	public function down()
	{
		$query = $this->db->get_where('adif_modes', array('submode' => 'FT2'));
		if ($query->num_rows() > 0) {
			$this->db->where('mode', 'MFSK');
			$this->db->where('submode', 'FT2');
			$this->db->delete('adif_modes');
		}
	}
}
?>
