<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Stationdiary extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('note');
		$this->load->library('pagination');
		$this->load->driver('cache', array('adapter' => 'file'));
	}

	private function get_public_qso_datetime_format($userDateFormat = NULL)
	{
		$dateFormat = !empty($userDateFormat) ? trim((string)$userDateFormat) : trim((string)$this->config->item('qso_date_format'));
		if ($dateFormat === '') {
			$dateFormat = 'Y-m-d';
		}

		if (preg_match('/[GgHh]/', $dateFormat) || strpos($dateFormat, 'i') !== FALSE) {
			return $dateFormat;
		}

		return $dateFormat . ' H:i';
	}

	private function get_or_create_public_visitor_token()
	{
		$cookieName = 'cloudlog_diary_visitor';
		$token = $this->input->cookie($cookieName, TRUE);

		if (!empty($token) && preg_match('/^[a-f0-9]{32,64}$/i', $token)) {
			return $token;
		}

		try {
			$token = bin2hex(random_bytes(16));
		} catch (Exception $e) {
			$token = md5(uniqid((string)mt_rand(), TRUE));
		}

		setcookie($cookieName, $token, time() + (365 * 24 * 60 * 60), '/', '', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), TRUE);
		return $token;
	}

	private function build_public_visitor_hash()
	{
		$token = $this->get_or_create_public_visitor_token();
		$ipAddress = (string)$this->input->ip_address();
		$userAgent = substr((string)$this->input->user_agent(), 0, 255);
		$secret = (string)$this->config->item('encryption_key');

		return hash('sha256', $token . '|' . $ipAddress . '|' . $userAgent . '|' . $secret);
	}

	public function index($callsign = NULL, $offset = 0)
	{
		if ($this->security->xss_clean($callsign, TRUE) === FALSE) {
			show_404();
			return;
		}

		$resolution = $this->note->resolve_public_user_by_callsign($callsign);
		if (!isset($resolution['status']) || $resolution['status'] !== 'ok') {
			show_404();
			return;
		}

		$user_id = (int)$resolution['user_id'];
		$cleanCallsign = strtoupper($resolution['callsign']);
		$pageOffset = is_numeric($offset) ? (int)$offset : 0;
		$cacheVersion = $this->note->get_public_diary_cache_version($user_id);
		$renderVersion = 'public_diary_render_v5';
		$cacheKey = 'public_station_diary_' . md5($cleanCallsign . '_' . $pageOffset . '_' . $cacheVersion . '_' . $renderVersion);

		$cachedHtml = $this->cache->get($cacheKey);
		if ($cachedHtml !== FALSE && !empty($cachedHtml)) {
			$this->output->set_output($cachedHtml);
			return;
		}

		$perPage = 10;
		$totalRows = $this->note->count_public_station_diary_entries($user_id);

		$config['base_url'] = site_url('station-diary/' . rawurlencode($cleanCallsign));
		$config['total_rows'] = $totalRows;
		$config['per_page'] = $perPage;
		$config['uri_segment'] = 3;
		$config['num_links'] = 5;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm">';
		$config['full_tag_close'] = '</ul>';
		$config['attributes'] = array('class' => 'page-link');
		$config['first_link'] = FALSE;
		$config['last_link'] = FALSE;
		$config['first_tag_open'] = '<li class="page-item">';
		$config['first_tag_close'] = '</li>';
		$config['prev_link'] = '&laquo';
		$config['prev_tag_open'] = '<li class="page-item">';
		$config['prev_tag_close'] = '</li>';
		$config['next_link'] = '&raquo';
		$config['next_tag_open'] = '<li class="page-item">';
		$config['next_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-item">';
		$config['last_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
		$config['cur_tag_close'] = '<span class="visually-hidden">(current)</span></a></li>';
		$config['num_tag_open'] = '<li class="page-item">';
		$config['num_tag_close'] = '</li>';

		$this->pagination->initialize($config);

		$data['callsign'] = $cleanCallsign;
		$data['entries'] = $this->note->get_public_station_diary_entries($user_id, $perPage, $pageOffset, FALSE);
		$data['pagination_links'] = $this->pagination->create_links();
		$data['page_title'] = 'Station Diary - ' . $cleanCallsign;
		$data['rss_url'] = site_url('station-diary/' . rawurlencode($cleanCallsign) . '/rss');
		$data['qso_datetime_format'] = $this->get_public_qso_datetime_format($resolution['user_date_format'] ?? NULL);
		$data['is_single_entry'] = false;
		$data['defer_qso_list'] = true;
		$data['current_entry_permalink'] = '';

		$html = $this->load->view('station_diary/public_index', $data, TRUE);
		$this->cache->save($cacheKey, $html, 86400);
		$this->output->set_output($html);
	}

	public function rss($callsign = NULL)
	{
		if ($this->security->xss_clean($callsign, TRUE) === FALSE) {
			show_404();
			return;
		}

		$resolution = $this->note->resolve_public_user_by_callsign($callsign);
		if (!isset($resolution['status']) || $resolution['status'] !== 'ok') {
			show_404();
			return;
		}

		$user_id = (int)$resolution['user_id'];
		$cleanCallsign = strtoupper($resolution['callsign']);
		$entries = $this->note->get_public_station_diary_entries($user_id, 25, 0);

		$this->output->set_content_type('application/rss+xml; charset=UTF-8');
		$this->load->view('station_diary/rss', array(
			'callsign' => $cleanCallsign,
			'entries' => $entries,		'feed_url' => site_url('station-diary/' . rawurlencode($cleanCallsign) . '/rss'),		));
	}

	public function entry($callsign = NULL, $entry_id = 0)
	{
		if ($this->security->xss_clean($callsign, TRUE) === FALSE) {
			show_404();
			return;
		}

		$resolution = $this->note->resolve_public_user_by_callsign($callsign);
		if (!isset($resolution['status']) || $resolution['status'] !== 'ok') {
			show_404();
			return;
		}

		$user_id = (int)$resolution['user_id'];
		$cleanCallsign = strtoupper($resolution['callsign']);
		$entryId = (int)$entry_id;
		if ($entryId <= 0) {
			show_404();
			return;
		}

		$cacheVersion = $this->note->get_public_diary_cache_version($user_id);
		$renderVersion = 'public_diary_render_v4';
		$cacheKey = 'public_station_diary_entry_' . md5($cleanCallsign . '_' . $entryId . '_' . $cacheVersion . '_' . $renderVersion);

		$cachedHtml = $this->cache->get($cacheKey);
		if ($cachedHtml !== FALSE && !empty($cachedHtml)) {
			$this->output->set_output($cachedHtml);
			return;
		}

		$entry = $this->note->get_public_station_diary_entry($user_id, $entryId);
		if (!$entry) {
			show_404();
			return;
		}

		$entryReactionTotals = $this->note->get_station_diary_reaction_totals($entry->id);

		$data['callsign'] = $cleanCallsign;
		$data['entries'] = array($entry);
		$data['pagination_links'] = '';
		$data['page_title'] = $entry->title . ' - Station Diary - ' . $cleanCallsign;
		$data['rss_url'] = site_url('station-diary/' . rawurlencode($cleanCallsign) . '/rss');
		$data['qso_datetime_format'] = $this->get_public_qso_datetime_format($resolution['user_date_format'] ?? NULL);
		$data['is_single_entry'] = true;
		$data['defer_qso_list'] = false;
		$data['current_entry_permalink'] = site_url('station-diary/' . rawurlencode($cleanCallsign) . '/entry/' . (int)$entry->id);
		$data['entry_reaction_totals'] = $entryReactionTotals;
		$data['visitor_reaction'] = null;

		$html = $this->load->view('station_diary/public_index', $data, TRUE);
		$this->cache->save($cacheKey, $html, 86400);
		$this->output->set_output($html);
	}

	public function react($callsign = NULL, $entry_id = 0)
	{
		header('Content-Type: application/json');

		if (strtolower($this->input->method()) !== 'post') {
			echo json_encode(array('success' => false, 'message' => 'Invalid request method'));
			return;
		}

		if ($this->security->xss_clean($callsign, TRUE) === FALSE) {
			echo json_encode(array('success' => false, 'message' => 'Invalid callsign'));
			return;
		}

		$resolution = $this->note->resolve_public_user_by_callsign($callsign);
		if (!isset($resolution['status']) || $resolution['status'] !== 'ok') {
			echo json_encode(array('success' => false, 'message' => 'Not found'));
			return;
		}

		$user_id = (int)$resolution['user_id'];
		$entryId = (int)$entry_id;
		if ($entryId <= 0) {
			echo json_encode(array('success' => false, 'message' => 'Invalid entry'));
			return;
		}

		$entry = $this->note->get_public_station_diary_entry($user_id, $entryId);
		if (!$entry) {
			echo json_encode(array('success' => false, 'message' => 'Entry not found'));
			return;
		}

		$reaction = strtolower(trim((string)$this->input->post('reaction', TRUE)));
		if (!in_array($reaction, array('like', 'love', 'fire'), TRUE)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid reaction'));
			return;
		}

		$visitorHash = $this->build_public_visitor_hash();
		$saved = $this->note->save_station_diary_reaction($entryId, $reaction, $visitorHash);
		if (!$saved) {
			echo json_encode(array('success' => false, 'message' => 'Unable to save reaction'));
			return;
		}

		$cleanCallsign = strtoupper($resolution['callsign']);
		$cacheVersion = $this->note->get_public_diary_cache_version($user_id);
		$renderVersion = 'public_diary_render_v4';
		$entryCacheKey = 'public_station_diary_entry_' . md5($cleanCallsign . '_' . $entryId . '_' . $cacheVersion . '_' . $renderVersion);
		$this->cache->delete($entryCacheKey);

		$totals = $this->note->get_station_diary_reaction_totals($entryId);
		$visitorReaction = $this->note->get_station_diary_visitor_reaction($entryId, $visitorHash);

		echo json_encode(array(
			'success' => true,
			'totals' => $totals,
			'visitor_reaction' => $visitorReaction,
		));
	}

	public function get_filtered_qsos()
	{
		$this->output->set_content_type('application/json');

		if (strtolower($this->input->method()) !== 'post') {
			echo json_encode(array('success' => false, 'message' => 'Invalid request method'));
			return;
		}

		$callsign = $this->input->post('callsign', TRUE);
		$entryId = (int)$this->input->post('entry_id', TRUE);
		$startDate = trim((string)$this->input->post('start_date', TRUE));
		$endDate = trim((string)$this->input->post('end_date', TRUE));
		$logbookId = (int)$this->input->post('logbook_id', TRUE);
		$satOnly = $this->input->post('sat_only', TRUE) === '1';

		if ($this->security->xss_clean($callsign, TRUE) === FALSE || $entryId <= 0) {
			echo json_encode(array('success' => false, 'message' => 'Invalid request'));
			return;
		}

		$resolution = $this->note->resolve_public_user_by_callsign($callsign);
		if (!isset($resolution['status']) || $resolution['status'] !== 'ok') {
			echo json_encode(array('success' => false, 'message' => 'Not found'));
			return;
		}

		$userId = (int)$resolution['user_id'];
		$entry = $this->note->get_public_station_diary_entry($userId, $entryId);
		if (!$entry) {
			echo json_encode(array('success' => false, 'message' => 'Entry not found'));
			return;
		}

		$entryDate = date('Y-m-d', strtotime($entry->created_at));
		if ($startDate === '') {
			$startDate = !empty($entry->qso_date_start) ? $entry->qso_date_start : $entryDate;
		}
		if ($endDate === '') {
			$endDate = !empty($entry->qso_date_end) ? $entry->qso_date_end : $entryDate;
		}

		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid date format'));
			return;
		}

		if ($logbookId <= 0) {
			$logbookId = !empty($entry->logbook_id) ? (int)$entry->logbook_id : 0;
		}

		$qsoSummary = $this->note->get_qso_summary_for_date_range($userId, $startDate, $endDate, $logbookId, $satOnly);
		$qsoList = $this->note->get_qso_list_for_date_range($userId, $startDate, $endDate, $logbookId, $satOnly);

		echo json_encode(array(
			'success' => true,
			'qso_list' => $qsoList,
			'highlight_dx' => $qsoSummary['highlight_dx'] ?? null,
		));
	}

	public function get_qso_map_data()
	{
		$this->output->set_content_type('application/json');

		$dateFrom = $this->input->post('date_from', TRUE);
		$dateTo = $this->input->post('date_to', TRUE);
		$logbookId = (int)$this->input->post('logbook_id', TRUE);
		$satOnly = $this->input->post('sat_only', TRUE) === '1';
		$entryId = (int)$this->input->post('entry_id', TRUE);

		if (empty($dateFrom) || empty($dateTo)) {
			echo json_encode(array('error' => 'Date range required'));
			return;
		}

		// Get user_id from the entry to ensure we only show their QSOs
		$userId = null;
		if ($entryId > 0) {
			$userId = $this->note->get_public_entry_user_id($entryId);
		}

		if (!$userId) {
			echo json_encode(array('error' => 'Unable to determine user context'));
			return;
		}

		// Load necessary models
		$this->load->model('logbook_model');
		$this->load->model('Stations');

		// Get QSOs for the date range
		$qsos = $this->logbook_model->get_qsos_for_public_map($dateFrom, $dateTo, $logbookId, $satOnly, $userId);

		if (empty($qsos) || $qsos->num_rows() === 0) {
			echo json_encode(array('error' => 'No QSOs found', 'markers' => array()));
			return;
		}

		// Get plot array for map markers (uses gridsquare, VUCC, DXCC fallback)
		$plotArray = $this->logbook_model->get_plot_array_for_map($qsos->result(), TRUE);

		// Try to get station location from logbook
		$stationArray = array();
		if ($logbookId > 0) {
			$this->load->model('logbooks_model');
			$this->load->library('qra');
			
			$logbookLocations = $this->logbooks_model->list_logbook_relationships($logbookId);
			if (!empty($logbookLocations)) {
				// Get the first station's location
				$stationData = $this->Stations->profile($logbookLocations[0])->row();
				if ($stationData && !empty($stationData->station_gridsquare)) {
					list($stationLat, $stationLng) = $this->qra->qra2latlong($stationData->station_gridsquare);
					if ($stationLat != 0 && $stationLng != 0) {
						$stationArray = array(
							'station' => array(
								'lat' => $stationLat,
								'lng' => $stationLng,
								'html' => '<strong>' . htmlspecialchars($stationData->station_callsign ?? '', ENT_QUOTES) . '</strong><br>' . $stationData->station_gridsquare,
								'label' => $stationData->station_profile_name ?? '',
								'icon' => 'stationIcon'
							)
						);
					}
				}
			}
		}

		// Merge and return
		echo json_encode(array_merge($plotArray, $stationArray));
	}

	public function search($callsign = NULL)
	{
		if ($this->security->xss_clean($callsign, TRUE) === FALSE) {
			show_404();
			return;
		}

		$resolution = $this->note->resolve_public_user_by_callsign($callsign);
		if (!isset($resolution['status']) || $resolution['status'] !== 'ok') {
			show_404();
			return;
		}

		$query = trim((string)$this->input->get('q', TRUE));
		$cleanCallsign = strtoupper($resolution['callsign']);

		if ($query === '') {
			redirect('station-diary/' . rawurlencode($cleanCallsign));
			return;
		}

		$user_id = (int)$resolution['user_id'];
		$perPage = 10;
		$totalRows = $this->note->count_public_station_diary_search_results($user_id, $query);

		$config['base_url'] = site_url('station-diary/' . rawurlencode($cleanCallsign) . '/search');
		$config['total_rows'] = $totalRows;
		$config['per_page'] = $perPage;
		$config['num_links'] = 5;
		$config['page_query_string'] = TRUE;
		$config['reuse_query_string'] = TRUE;
		$config['query_string_segment'] = 'page';
		$config['use_page_numbers'] = FALSE;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm">';
		$config['full_tag_close'] = '</ul>';
		$config['attributes'] = array('class' => 'page-link');
		$config['first_link'] = FALSE;
		$config['last_link'] = FALSE;
		$config['first_tag_open'] = '<li class="page-item">';
		$config['first_tag_close'] = '</li>';
		$config['prev_link'] = '&laquo';
		$config['prev_tag_open'] = '<li class="page-item">';
		$config['prev_tag_close'] = '</li>';
		$config['next_link'] = '&raquo';
		$config['next_tag_open'] = '<li class="page-item">';
		$config['next_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-item">';
		$config['last_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
		$config['cur_tag_close'] = '<span class="visually-hidden">(current)</span></a></li>';
		$config['num_tag_open'] = '<li class="page-item">';
		$config['num_tag_close'] = '</li>';

		$this->pagination->initialize($config);

		$pageOffset = (int)$this->input->get('page', TRUE);
		if ($pageOffset < 0) {
			$pageOffset = 0;
		}

		$data['callsign'] = $cleanCallsign;
		$data['entries'] = $this->note->search_public_station_diary_entries($user_id, $query, $perPage, $pageOffset);
		$data['pagination_links'] = $this->pagination->create_links();
		$data['page_title'] = 'Search: ' . $query . ' - Station Diary - ' . $cleanCallsign;
		$data['rss_url'] = site_url('station-diary/' . rawurlencode($cleanCallsign) . '/rss');
		$data['qso_datetime_format'] = $this->get_public_qso_datetime_format($resolution['user_date_format'] ?? NULL);
		$data['is_single_entry'] = false;
		$data['defer_qso_list'] = false;
		$data['current_entry_permalink'] = '';
		$data['is_search_results'] = true;
		$data['search_query'] = $query;
		$data['search_total'] = $totalRows;

		$this->load->view('station_diary/public_index', $data);
	}
}