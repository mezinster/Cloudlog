<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Callhistory extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('user_model');
        if (!$this->user_model->authorize(2)) {
            $this->session->set_flashdata('notice', 'You\'re not allowed to do that!');
            redirect('dashboard');
        }

        $this->load->model('callhistory_model');
    }

    public function index()
    {
        $user_id = (int)$this->session->userdata('user_id');

        $this->load->model('logbooks_model');
        $data['page_title'] = 'Call History';
        $data['files'] = $this->callhistory_model->get_all_for_user($user_id);
        $data['logbooks'] = $this->logbooks_model->show_all()->result();

        $this->load->view('interface_assets/header', $data);
        $this->load->view('callhistory/index', $data);
        $this->load->view('interface_assets/footer');
    }

    public function upload()
    {
        if (strtolower($this->input->method()) !== 'post') {
            redirect('callhistory');
            return;
        }

        $user_id = (int)$this->session->userdata('user_id');
        $upload_dir = FCPATH . 'uploads/callhistory/' . $user_id . '/';

        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0755, TRUE)) {
                $this->session->set_flashdata('notice', 'Unable to create upload directory.');
                redirect('callhistory');
                return;
            }
        }

        if (!isset($_FILES['history_file']) || empty($_FILES['history_file']['name'])) {
            $this->session->set_flashdata('notice', 'Please select a call history file to upload.');
            redirect('callhistory');
            return;
        }

        $config = array(
            'upload_path' => $upload_dir,
            'allowed_types' => 'txt|csv|TXT|CSV',
            'max_size' => 5120,
            'encrypt_name' => TRUE,
            'detect_mime' => TRUE,
            'mod_mime_fix' => TRUE,
        );

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('history_file')) {
            $this->session->set_flashdata('notice', trim(strip_tags($this->upload->display_errors('', ''))));
            redirect('callhistory');
            return;
        }

        $upload_data = $this->upload->data();
        $original_filename = $upload_data['client_name'] !== '' ? $upload_data['client_name'] : $upload_data['orig_name'];
        $organization_label = strtoupper(trim((string)$this->input->post('organization_label', TRUE)));

        if ($organization_label === '') {
            @unlink($upload_data['full_path']);
            $this->session->set_flashdata('notice', 'Organization label is required for call history uploads.');
            redirect('callhistory');
            return;
        }

        $data = array(
            'user_id' => $user_id,
            'file_label' => trim((string)$this->input->post('file_label', TRUE)),
            'organization_label' => $organization_label,
            'stored_filename' => $upload_data['file_name'],
            'original_filename' => $original_filename,
            'mime_type' => isset($upload_data['file_type']) ? $upload_data['file_type'] : null,
            'file_size' => isset($upload_data['file_size']) ? (int)round($upload_data['file_size'] * 1024) : null,
            'checksum' => @hash_file('sha256', $upload_data['full_path']),
            'is_active' => 1,
            'priority' => (int)$this->input->post('priority', TRUE),
            'parser_status' => 'ready',
        );

        $this->callhistory_model->create_file($data);

        $this->session->set_flashdata('notice', 'Call history file uploaded successfully.');
        redirect('callhistory');
    }

    public function set_active()
    {
        if (strtolower($this->input->method()) !== 'post') {
            redirect('callhistory');
            return;
        }

        $user_id = (int)$this->session->userdata('user_id');
        $id = (int)$this->input->post('id', TRUE);
        $is_active = $this->input->post('is_active', TRUE) ? 1 : 0;

        $file = $this->callhistory_model->get_for_user_by_id($user_id, $id);
        if (!$file) {
            $this->session->set_flashdata('notice', 'Call history file not found.');
            redirect('callhistory');
            return;
        }

        $this->callhistory_model->update_active($user_id, $id, $is_active);
        redirect('callhistory');
    }

    public function set_priority()
    {
        if (strtolower($this->input->method()) !== 'post') {
            redirect('callhistory');
            return;
        }

        $user_id = (int)$this->session->userdata('user_id');
        $id = (int)$this->input->post('id', TRUE);
        $priority = (int)$this->input->post('priority', TRUE);

        $file = $this->callhistory_model->get_for_user_by_id($user_id, $id);
        if (!$file) {
            $this->session->set_flashdata('notice', 'Call history file not found.');
            redirect('callhistory');
            return;
        }

        $this->callhistory_model->update_priority($user_id, $id, $priority);
        redirect('callhistory');
    }

    public function delete()
    {
        if (strtolower($this->input->method()) !== 'post') {
            redirect('callhistory');
            return;
        }

        $user_id = (int)$this->session->userdata('user_id');
        $id = (int)$this->input->post('id', TRUE);

        $file = $this->callhistory_model->get_for_user_by_id($user_id, $id);
        if (!$file) {
            $this->session->set_flashdata('notice', 'Call history file not found.');
            redirect('callhistory');
            return;
        }

        $path = FCPATH . 'uploads/callhistory/' . $user_id . '/' . $file->stored_filename;
        if (is_file($path)) {
            @unlink($path);
        }

        $this->callhistory_model->delete_file($user_id, $id);

        $this->session->set_flashdata('notice', 'Call history file deleted.');
        redirect('callhistory');
    }

    public function scan_preview()
    {
        if (strtolower($this->input->method()) !== 'post') {
            redirect('callhistory');
            return;
        }

        $user_id = (int)$this->session->userdata('user_id');
        $file_id = (int)$this->input->post('file_id', TRUE);
        $logbook_id = $this->input->post('logbook_id', TRUE);
        $logbook_id = ($logbook_id !== '' && $logbook_id !== FALSE) ? (int)$logbook_id : NULL;
        $start_date = trim((string)$this->input->post('start_date', TRUE));
        if ($start_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
            $this->session->set_flashdata('notice', 'Start date must be in YYYY-MM-DD format.');
            redirect('callhistory');
            return;
        }

        $file = $this->callhistory_model->get_for_user_by_id($user_id, $file_id);
        if (!$file) {
            $this->session->set_flashdata('notice', 'Call history file not found.');
            redirect('callhistory');
            return;
        }

        if (trim((string)$file->organization_label) === '') {
            $this->session->set_flashdata('notice', 'Bulk SIG updates require an organization label on the selected call history file.');
            redirect('callhistory');
            return;
        }

        $path = FCPATH . 'uploads/callhistory/' . $user_id . '/' . $file->stored_filename;
        if (!is_file($path) || !is_readable($path)) {
            $this->session->set_flashdata('notice', 'Uploaded file is not readable.');
            redirect('callhistory');
            return;
        }

        // Parse every callsign in the file
        $all_callsigns = $this->get_all_callsigns_in_file($path);
        if (empty($all_callsigns)) {
            $this->session->set_flashdata('notice', 'No callsigns found in the selected file.');
            redirect('callhistory');
            return;
        }

        // Look up matching QSOs for all those callsigns
        $qsos = $this->callhistory_model->get_qsos_for_callsigns($user_id, array_keys($all_callsigns), $logbook_id, $start_date !== '' ? $start_date : NULL);

        $preview = array();
        foreach ($qsos as $qso) {
            $normalized_call = $this->normalize_callsign($qso->COL_CALL);
            // Fall back to base call (strips /P prefix, country prefix etc.) for index lookup
            if (isset($all_callsigns[$normalized_call])) {
                $lookup_key = $normalized_call;
            } elseif (isset($all_callsigns[$this->extract_base_callsign($normalized_call)])) {
                $lookup_key = $this->extract_base_callsign($normalized_call);
            } else {
                continue;
            }

            $call_data = $all_callsigns[$lookup_key];
            $proposed_sig = $file->organization_label;
            $proposed_sig_info = trim((string)($call_data['exch1'] ?? ''));

            $current_sig = trim((string)($qso->COL_SIG ?? ''));
            $current_sig_info = trim((string)($qso->COL_SIG_INFO ?? ''));

            // Only propose changes where both SIG fields are currently blank
            if ($current_sig !== '' || $current_sig_info !== '') {
                continue;
            }

            if ($proposed_sig === '') {
                continue;
            }

            // Do not propose SIG updates without a membership number.
            if ($proposed_sig_info === '') {
                continue;
            }

            if (!$this->is_valid_sig_info_for_organization($proposed_sig, $proposed_sig_info)) {
                continue;
            }

            $preview[] = array(
                'qso_id' => (int)$qso->COL_PRIMARY_KEY,
                'station_id' => (int)$qso->station_id,
                'callsign' => $qso->COL_CALL,
                'time_on' => $qso->COL_TIME_ON,
                'band' => $qso->COL_BAND,
                'mode' => $qso->COL_SUBMODE !== '' ? $qso->COL_SUBMODE : $qso->COL_MODE,
                'station_location' => $qso->station_profile_name . ' (' . $qso->station_callsign . ')',
                'current_sig' => $current_sig,
                'current_sig_info' => $current_sig_info,
                'new_sig' => $proposed_sig,
                'new_sig_info' => $proposed_sig_info,
            );
        }

        $this->load->model('logbooks_model');
        $data['page_title'] = 'Call History - Scan Preview';
        $data['files'] = $this->callhistory_model->get_all_for_user($user_id);
        $data['preview'] = $preview;
        $data['scan_file'] = $file;
        $data['logbooks'] = $this->logbooks_model->show_all()->result();
        $data['selected_logbook_id'] = $logbook_id;
        $data['selected_start_date'] = $start_date;

        $this->load->view('interface_assets/header', $data);
        $this->load->view('callhistory/index', $data);
        $this->load->view('interface_assets/footer');
    }

    public function scan_apply()
    {
        if (strtolower($this->input->method()) !== 'post') {
            redirect('callhistory');
            return;
        }

        $user_id = (int)$this->session->userdata('user_id');
        $raw_changes_json = (string)$this->input->post('changes_json', FALSE);
        $raw_changes = array();
        if ($raw_changes_json !== '') {
            $decoded_changes = json_decode($raw_changes_json, TRUE);
            if (is_array($decoded_changes)) {
                $raw_changes = $decoded_changes;
            }
        }
        if (empty($raw_changes)) {
            $raw_changes = $this->input->post('changes', TRUE);
        }
        $file_id = (int)$this->input->post('file_id', TRUE);

        $file = $this->callhistory_model->get_for_user_by_id($user_id, $file_id);
        if (!$file || trim((string)$file->organization_label) === '') {
            $this->session->set_flashdata('notice', 'Bulk SIG updates require an organization label on the selected call history file.');
            redirect('callhistory');
            return;
        }

        if (empty($raw_changes) || !is_array($raw_changes)) {
            $this->session->set_flashdata('notice', 'No changes submitted.');
            redirect('callhistory');
            return;
        }

        // Validate that every station_id belongs to this user before applying
        $station_ids = $this->callhistory_model->get_station_ids_for_logbook($user_id);
        $station_ids_set = array_flip($station_ids);

        $safe_changes = array();
        foreach ($raw_changes as $change) {
            $qso_id = isset($change['qso_id']) ? (int)$change['qso_id'] : 0;
            $station_id = isset($change['station_id']) ? (int)$change['station_id'] : 0;

            if ($qso_id <= 0 || $station_id <= 0) {
                continue;
            }

            if (!isset($station_ids_set[$station_id])) {
                continue;
            }

            $safe_changes[] = array(
                'qso_id' => $qso_id,
                'station_id' => $station_id,
                'new_sig' => $this->security->xss_clean((string)($change['new_sig'] ?? '')),
                'new_sig_info' => trim($this->security->xss_clean((string)($change['new_sig_info'] ?? ''))),
            );
        }

        // Membership number is required for batch SIG updates.
        $safe_changes = array_values(array_filter($safe_changes, function($change) {
            return trim((string)$change['new_sig']) !== '' && trim((string)$change['new_sig_info']) !== '';
        }));

        // Enforce per-organization SIG info format rules (e.g. CWOPS requires numeric membership values).
        $safe_changes = array_values(array_filter($safe_changes, function($change) use ($file) {
            return $this->is_valid_sig_info_for_organization($file->organization_label, $change['new_sig_info']);
        }));

        if (empty($safe_changes)) {
            $this->session->set_flashdata('notice', 'No valid changes to apply.');
            redirect('callhistory');
            return;
        }

        $selected_count = count($safe_changes);
        $applied = $this->callhistory_model->apply_sig_backfill($safe_changes);

        $this->session->set_flashdata('notice', $selected_count . ' QSO(s) selected, ' . $applied . ' QSO(s) updated with SIG data.');
        redirect('callhistory');
    }

    public function scan_remove()
    {
        if (strtolower($this->input->method()) !== 'post') {
            redirect('callhistory');
            return;
        }

        $user_id = (int)$this->session->userdata('user_id');
        $file_id = (int)$this->input->post('file_id', TRUE);
        $logbook_id = $this->input->post('logbook_id', TRUE);
        $logbook_id = ($logbook_id !== '' && $logbook_id !== FALSE) ? (int)$logbook_id : NULL;
        $start_date = trim((string)$this->input->post('start_date', TRUE));
        if ($start_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
            $this->session->set_flashdata('notice', 'Start date must be in YYYY-MM-DD format.');
            redirect('callhistory');
            return;
        }

        $file = $this->callhistory_model->get_for_user_by_id($user_id, $file_id);
        if (!$file) {
            $this->session->set_flashdata('notice', 'Call history file not found.');
            redirect('callhistory');
            return;
        }

        $organization_label = strtoupper(trim((string)$file->organization_label));
        if ($organization_label === '') {
            $this->session->set_flashdata('notice', 'Reverse SIG updates require an organization label on the selected call history file.');
            redirect('callhistory');
            return;
        }

        $path = FCPATH . 'uploads/callhistory/' . $user_id . '/' . $file->stored_filename;
        if (!is_file($path) || !is_readable($path)) {
            $this->session->set_flashdata('notice', 'Uploaded file is not readable.');
            redirect('callhistory');
            return;
        }

        $all_callsigns = $this->get_all_callsigns_in_file($path);
        if (empty($all_callsigns)) {
            $this->session->set_flashdata('notice', 'No callsigns found in the selected file.');
            redirect('callhistory');
            return;
        }

        $qsos = $this->callhistory_model->get_qsos_for_callsigns($user_id, array_keys($all_callsigns), $logbook_id, $start_date !== '' ? $start_date : NULL);
        if (empty($qsos)) {
            $this->session->set_flashdata('notice', 'No matching QSOs found for reverse update scope.');
            redirect('callhistory');
            return;
        }

        $changes = array();
        foreach ($qsos as $qso) {
            $current_sig = strtoupper(trim((string)($qso->COL_SIG ?? '')));
            if ($current_sig !== $organization_label) {
                continue;
            }

            $changes[] = array(
                'qso_id' => (int)$qso->COL_PRIMARY_KEY,
                'station_id' => (int)$qso->station_id,
                'new_sig' => '',
                'new_sig_info' => '',
            );
        }

        if (empty($changes)) {
            $this->session->set_flashdata('notice', 'No QSOs found with SIG ' . $organization_label . ' in the selected scope.');
            redirect('callhistory');
            return;
        }

        $applied = $this->callhistory_model->apply_sig_backfill($changes);
        $this->session->set_flashdata('notice', count($changes) . ' QSO(s) matched SIG ' . $organization_label . ', ' . $applied . ' QSO(s) cleared.');
        redirect('callhistory');
    }

    public function lookup()
    {
        if (strtolower($this->input->method()) !== 'post') {
            show_404();
            return;
        }

        $callsign = strtoupper(trim((string)$this->input->post('callsign', TRUE)));
        $normalized_callsign = $this->normalize_callsign($callsign);

        $response = array(
            'status' => 'ok',
            'callsign' => $normalized_callsign,
            'matches' => array(),
            'count' => 0,
        );

        if (strlen($normalized_callsign) < 3) {
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        $user_id = (int)$this->session->userdata('user_id');
        $files = $this->callhistory_model->get_active_for_user($user_id);

        $all_matches = array();
        foreach ($files as $file) {
            $path = FCPATH . 'uploads/callhistory/' . $user_id . '/' . $file->stored_filename;
            if (!is_file($path) || !is_readable($path)) {
                continue;
            }

            $matches = $this->find_matches_in_file($path, $normalized_callsign, $file);
            if (!empty($matches)) {
                $all_matches = array_merge($all_matches, $matches);
            }
        }

        $response['matches'] = $all_matches;
        $response['count'] = count($all_matches);

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    private function get_all_callsigns_in_file($file_path)
    {
        $callsigns = array();
        $header_map = null;

        if (($handle = fopen($file_path, 'r')) === FALSE) {
            return $callsigns;
        }

        while (($row = fgetcsv($handle, 0, ',')) !== FALSE) {
            if (empty($row)) {
                continue;
            }

            $row = array_map('trim', $row);
            if (count($row) === 1 && $row[0] === '') {
                continue;
            }

            if ($this->is_comment_row($row)) {
                continue;
            }

            if ($this->looks_like_header($row)) {
                $header_map = $this->build_header_map($row);
                continue;
            }

            $row = $this->align_row_to_header($row, $header_map);

            $row_callsign = $this->extract_by_map_or_index($row, $header_map, array('call', 'callsign', 'callsigns'), 0);
            $row_callsign = $this->normalize_callsign($row_callsign);

            if ($row_callsign === '') {
                continue;
            }

            // Strip portable/alternative suffixes to get base call for matching
            $base_callsign = $row_callsign;
            if (strpos($base_callsign, '/') !== FALSE) {
                $parts = explode('/', $base_callsign);
                // Use the longest segment as the base callsign
                usort($parts, function($a, $b) { return strlen($b) - strlen($a); });
                $base_callsign = $parts[0];
            }

            $name = $this->extract_name($row, $header_map);
            $exch1 = $this->extract_exch1($row, $header_map);

            $callsigns[$base_callsign] = array(
                'name' => $name,
                'exch1' => $exch1,
            );

            // Also index by original callsign if it differs (e.g. contains /)
            if ($row_callsign !== $base_callsign) {
                $callsigns[$row_callsign] = array(
                    'name' => $name,
                    'exch1' => $exch1,
                );
            }
        }

        fclose($handle);
        return $callsigns;
    }

    private function find_matches_in_file($file_path, $callsign, $file)
    {
        $matches = array();
        $header_map = null;

        if (($handle = fopen($file_path, 'r')) === FALSE) {
            return $matches;
        }

        while (($row = fgetcsv($handle, 0, ',')) !== FALSE) {
            if (empty($row)) {
                continue;
            }

            $row = array_map('trim', $row);
            if (count($row) === 1 && $row[0] === '') {
                continue;
            }

            if ($this->is_comment_row($row)) {
                continue;
            }

            if ($this->looks_like_header($row)) {
                $header_map = $this->build_header_map($row);
                continue;
            }

            $row = $this->align_row_to_header($row, $header_map);

            $row_callsign = $this->extract_by_map_or_index($row, $header_map, array('call', 'callsign', 'callsigns'), 0);
            $row_callsign = $this->normalize_callsign($row_callsign);

            if ($row_callsign === '') {
                continue;
            }

            // Match against the full input callsign OR its base call (strips prefix/suffix like F/ or /P)
            if ($row_callsign !== $callsign && $row_callsign !== $this->extract_base_callsign($callsign)) {
                continue;
            }

            $name = $this->extract_name($row, $header_map);
            $exch1 = $this->extract_exch1($row, $header_map);

            $matches[] = array(
                'file_id' => (int)$file->id,
                'file_label' => $file->file_label,
                'organization_label' => $file->organization_label,
                'name' => $name,
                'exch1' => $exch1,
                'sig' => $this->is_valid_sig_info_for_organization($file->organization_label, $exch1) ? $file->organization_label : '',
                'sig_info' => $exch1,
            );
        }

        fclose($handle);
        return $matches;
    }

    private function is_comment_row($row)
    {
        foreach ($row as $column) {
            $value = trim((string)$column);
            if ($value === '') {
                continue;
            }

            return strpos(ltrim($value, "\xEF\xBB\xBF"), '#') === 0;
        }

        return FALSE;
    }

    private function looks_like_header($row)
    {
        foreach ($row as $column) {
            $normalized = $this->normalize_header_key($column);
            if (in_array($normalized, array('call', 'callsign', 'name', 'exch1', 'exchange1'), TRUE)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    private function build_header_map($row)
    {
        $map = array();
        foreach ($row as $index => $column) {
            $key = $this->normalize_header_key($column);
            if ($key !== '') {
                $map[$key] = $index;
            }
        }
        return $map;
    }

    private function normalize_header_key($value)
    {
        $value = ltrim((string)$value, "\xEF\xBB\xBF");
        $value = strtolower($value);
        return preg_replace('/[^a-z0-9]/', '', $value);
    }

    private function align_row_to_header($row, $header_map)
    {
        if (!is_array($header_map)) {
            return $row;
        }

        if (!array_key_exists('order', $header_map)) {
            return $row;
        }

        $call_index = null;
        if (array_key_exists('call', $header_map)) {
            $call_index = (int)$header_map['call'];
        } elseif (array_key_exists('callsign', $header_map)) {
            $call_index = (int)$header_map['callsign'];
        }

        if ($call_index !== 1 || (int)$header_map['order'] !== 0) {
            return $row;
        }

        // Some N1MM/CWOPS exports include an Order header column but omit that value in data rows.
        if (count($row) < count($header_map)) {
            $first = isset($row[0]) ? $this->normalize_callsign($row[0]) : '';
            $second = isset($row[1]) ? $this->normalize_callsign($row[1]) : '';

            if ($this->looks_like_callsign($first) && !$this->looks_like_callsign($second)) {
                array_unshift($row, '');
            }
        }

        return $row;
    }

    private function extract_by_map_or_index($row, $header_map, $candidate_keys, $default_index)
    {
        if (is_array($header_map)) {
            foreach ($candidate_keys as $key) {
                if (array_key_exists($key, $header_map)) {
                    $idx = $header_map[$key];
                    return isset($row[$idx]) ? trim((string)$row[$idx]) : '';
                }
            }
        }

        return isset($row[$default_index]) ? trim((string)$row[$default_index]) : '';
    }

    private function extract_exch1($row, $header_map)
    {
        // N1MM-style files: trust the declared header order (!!Order!!,Call,Name,Exch1,UserText,...).
        if ($this->has_n1mm_order_header($header_map)) {
            return $this->extract_by_map_or_index($row, $header_map, array('exch1'), 3);
        }

        $exch1 = $this->extract_by_map_or_index($row, $header_map, array('exch1', 'exchange1', 'exchange', 'member', 'membership'), 8);

        if ($exch1 === '' && is_null($header_map)) {
            $exch1 = $this->guess_exch1_without_header($row);
        }

        return $exch1;
    }

    private function has_n1mm_order_header($header_map)
    {
        if (!is_array($header_map)) {
            return FALSE;
        }

        return array_key_exists('order', $header_map)
            && (array_key_exists('call', $header_map) || array_key_exists('callsign', $header_map))
            && array_key_exists('name', $header_map)
            && array_key_exists('exch1', $header_map);
    }

    private function extract_name($row, $header_map)
    {
        $name = $this->extract_by_map_or_index($row, $header_map, array('name', 'operator', 'opname'), 1);
        if ($name !== '' || is_array($header_map)) {
            return $name;
        }

        if (isset($row[1], $row[2])) {
            $second_column = trim((string)$row[1]);
            $third_column = trim((string)$row[2]);

            if ($this->looks_like_exchange_value($second_column) && $this->looks_like_name_value($third_column)) {
                return $third_column;
            }
        }

        return $name;
    }

    private function guess_exch1_without_header($row)
    {
        if (isset($row[1], $row[2])) {
            $second_column = trim((string)$row[1]);
            $third_column = trim((string)$row[2]);

            if ($this->looks_like_exchange_value($second_column) && $this->looks_like_name_value($third_column)) {
                return $second_column;
            }

            if ($this->looks_like_name_value($second_column) && $this->looks_like_exchange_value($third_column)) {
                return $third_column;
            }
        }

        $indexes = array(8, 7, 6, 5, 4, 3, 2);
        foreach ($indexes as $index) {
            if (isset($row[$index]) && trim((string)$row[$index]) !== '') {
                return trim((string)$row[$index]);
            }
        }

        return '';
    }

    private function looks_like_name_value($value)
    {
        $value = trim((string)$value);
        if ($value === '' || preg_match('/\d/', $value)) {
            return FALSE;
        }

        if (preg_match('/[a-z]/', $value)) {
            return TRUE;
        }

        return strlen($value) > 4;
    }

    private function looks_like_exchange_value($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return FALSE;
        }

        return !$this->looks_like_name_value($value);
    }

    private function looks_like_callsign($callsign)
    {
        $callsign = strtoupper(trim((string)$callsign));
        if ($callsign === '' || !preg_match('/^[A-Z0-9\/]{3,15}$/', $callsign)) {
            return FALSE;
        }

        // Basic sanity: callsigns should contain at least one letter and one digit.
        return preg_match('/[A-Z]/', $callsign) && preg_match('/\d/', $callsign);
    }

    private function is_valid_sig_info_for_organization($organization_label, $sig_info)
    {
        $organization_label = strtoupper(trim((string)$organization_label));
        $sig_info = trim((string)$sig_info);

        if ($sig_info === '') {
            return FALSE;
        }

        // CWOPS membership numbers are numeric; skip textual values such as "CWA".
        if ($organization_label === 'CWOPS') {
            return ctype_digit($sig_info);
        }

        return TRUE;
    }

    private function normalize_callsign($callsign)
    {
        $callsign = strtoupper(trim((string)$callsign));
        $callsign = str_replace('Ø', '0', $callsign);
        return $callsign;
    }

    /**
     * Extract the base callsign from a compound callsign like F/MM9SQL/P or MM9SQL/P.
     * Returns the longest segment when split by '/', which is typically the base call.
     */
    private function extract_base_callsign($callsign)
    {
        if (strpos($callsign, '/') === FALSE) {
            return $callsign;
        }
        $parts = explode('/', $callsign);
        usort($parts, function ($a, $b) { return strlen($b) - strlen($a); });
        return $parts[0];
    }
}
