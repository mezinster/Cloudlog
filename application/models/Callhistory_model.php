<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Callhistory_model extends CI_Model {

    public function get_all_for_user($user_id)
    {
        $this->db->from('callhistory_files');
        $this->db->where('user_id', (int)$user_id);
        $this->db->order_by('priority', 'ASC');
        $this->db->order_by('uploaded_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_active_for_user($user_id)
    {
        $this->db->from('callhistory_files');
        $this->db->where('user_id', (int)$user_id);
        $this->db->where('is_active', 1);
        $this->db->order_by('priority', 'ASC');
        $this->db->order_by('uploaded_at', 'DESC');
        return $this->db->get()->result();
    }

    public function create_file($data)
    {
        $this->db->insert('callhistory_files', $data);
        return $this->db->insert_id();
    }

    public function get_for_user_by_id($user_id, $id)
    {
        $this->db->from('callhistory_files');
        $this->db->where('user_id', (int)$user_id);
        $this->db->where('id', (int)$id);
        return $this->db->get()->row();
    }

    public function update_active($user_id, $id, $is_active)
    {
        $this->db->where('user_id', (int)$user_id);
        $this->db->where('id', (int)$id);
        return $this->db->update('callhistory_files', array('is_active' => (int)$is_active));
    }

    public function update_priority($user_id, $id, $priority)
    {
        $this->db->where('user_id', (int)$user_id);
        $this->db->where('id', (int)$id);
        return $this->db->update('callhistory_files', array('priority' => (int)$priority));
    }

    public function delete_file($user_id, $id)
    {
        $this->db->where('user_id', (int)$user_id);
        $this->db->where('id', (int)$id);
        return $this->db->delete('callhistory_files');
    }

    public function get_station_ids_for_logbook($user_id, $logbook_id = NULL)
    {
        if ($logbook_id === NULL || $logbook_id === '' || $logbook_id === 0 || $logbook_id === '0') {
            $this->db->select('station_id');
            $this->db->from('station_profile');
            $this->db->where('user_id', (int)$user_id);

            $query = $this->db->get();
            $station_ids = array();
            foreach ($query->result() as $row) {
                $station_ids[] = (int)$row->station_id;
            }

            return $station_ids;
        }

        $this->db->select('station_location_id');
        $this->db->from('station_logbooks_relationship');
        $this->db->where('station_logbook_id', (int)$logbook_id);
        $query = $this->db->get();

        $candidate_ids = array();
        foreach ($query->result() as $row) {
            $candidate_ids[] = (int)$row->station_location_id;
        }

        if (empty($candidate_ids)) {
            return array();
        }

        $this->db->select('station_id');
        $this->db->from('station_profile');
        $this->db->where('user_id', (int)$user_id);
        $this->db->where_in('station_id', $candidate_ids);
        $verified_query = $this->db->get();

        $verified_ids = array();
        foreach ($verified_query->result() as $row) {
            $verified_ids[] = (int)$row->station_id;
        }

        return $verified_ids;
    }

    public function get_qsos_for_callsigns($user_id, $callsigns, $logbook_id = NULL, $start_date = NULL)
    {
        $callsigns = array_values(array_unique(array_filter(array_map('strval', (array)$callsigns))));
        if (empty($callsigns)) {
            return array();
        }

        $station_ids = $this->get_station_ids_for_logbook($user_id, $logbook_id);
        if (empty($station_ids)) {
            return array();
        }

        $table = $this->config->item('table_name');
        $results = array();

        foreach (array_chunk($callsigns, 500) as $callsign_chunk) {
            $escaped_callsigns = array();
            foreach ($callsign_chunk as $callsign) {
                $escaped_callsigns[] = $this->db->escape(strtoupper($callsign));
            }

            $this->db->select($table . '.COL_PRIMARY_KEY, ' . $table . '.COL_CALL, ' . $table . '.COL_TIME_ON, ' . $table . '.COL_BAND, ' . $table . '.COL_MODE, ' . $table . '.COL_SUBMODE, ' . $table . '.COL_SIG, ' . $table . '.COL_SIG_INFO, ' . $table . '.station_id, station_profile.station_profile_name, station_profile.station_callsign');
            $this->db->from($table);
            $this->db->join('station_profile', 'station_profile.station_id = ' . $table . '.station_id');
            $this->db->where('station_profile.user_id', (int)$user_id);
            $this->db->where_in($table . '.station_id', $station_ids);
            // Match exact call OR compound callsigns where any segment matches (e.g. MM9SQL/P, F/MM9SQL, F/MM9SQL/P)
            $in_list  = implode(',', $escaped_callsigns);
            $col      = $table . '.COL_CALL';
            $norm     = 'UPPER(REPLACE(%s, "Ø", "0"))';
            $exact    = sprintf($norm, $col);
            $first    = sprintf($norm, 'SUBSTRING_INDEX(' . $col . ', "/", 1)');
            $last     = sprintf($norm, 'SUBSTRING_INDEX(' . $col . ', "/", -1)');
            $middle   = sprintf($norm, 'SUBSTRING_INDEX(SUBSTRING_INDEX(' . $col . ', "/", 2), "/", -1)');
            $this->db->where(
                '(' . $exact . ' IN (' . $in_list . ')'
                . ' OR ' . $first  . ' IN (' . $in_list . ')'
                . ' OR ' . $last   . ' IN (' . $in_list . ')'
                . ' OR ' . $middle . ' IN (' . $in_list . '))',
                NULL, FALSE
            );
            if (!empty($start_date)) {
                $this->db->where($table . '.COL_TIME_ON >=', $start_date . ' 00:00:00');
            }
            $this->db->order_by($table . '.COL_TIME_ON', 'DESC');

            $query = $this->db->get();
            $results = array_merge($results, $query->result());
        }

        return $results;
    }

    public function apply_sig_backfill($changes)
    {
        if (empty($changes)) {
            return 0;
        }

        $table = $this->config->item('table_name');
        $applied = 0;

        $this->db->trans_start();
        foreach ($changes as $change) {
            $this->db->where('COL_PRIMARY_KEY', (int)$change['qso_id']);
            $this->db->where('station_id', (int)$change['station_id']);
            $this->db->update($table, array(
                'COL_SIG' => $change['new_sig'],
                'COL_SIG_INFO' => $change['new_sig_info'],
            ));

            if ($this->db->affected_rows() > 0) {
                $applied++;
            }
        }
        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            return 0;
        }

        return $applied;
    }
}
