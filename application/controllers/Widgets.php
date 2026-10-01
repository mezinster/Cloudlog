<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	
	Widgets are designed to be addons to use around the internet.
		
*/

class Widgets extends CI_Controller {

	public function index()
	{
		// Show a help page
	}
	
	
	// Can be used to embed last 11 QSOs in a iframe or javascript include.
	public function qsos($logbook_slug = null) {

		if($logbook_slug == null) {
			show_error('Unknown Public Page, please make sure the public slug is correct.');
		}
		$this->load->model('logbook_model');

		$this->load->model('logbooks_model');
		if($this->logbooks_model->public_slug_exists($logbook_slug)) {
			// Load the public view

			$logbook_id = $this->logbooks_model->public_slug_exists_logbook_id($logbook_slug);
			if($logbook_id != false)
			{
				// Get associated station locations for mysql queries
				$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($logbook_id);

				if (!$logbooks_locations_array) {
					show_404('Empty Logbook');
				}
			} else {
				log_message('error', $logbook_slug.' has no associated station locations');
				show_404('Unknown Public Page.');
			}

			$data['last_five_qsos'] = $this->logbook_model->get_last_qsos(15, $logbooks_locations_array);
			
			$this->load->view('widgets/qsos', $data);
		}
	}

	// Embeddable "on air" status widget - pass a callsign to get current radio/satellite status.
	// Embed via: <iframe src="/widgets/on_air/M0ABC" width="300" height="120" frameborder="0"></iframe>
	// Optionally override the displayed callsign: <iframe src="/widgets/on_air/M0ABC/GB2XXX" ...></iframe>
	public function on_air($callsign = null, $display_callsign = null) {
		if ($callsign == null) {
			show_error('Please provide a callsign. Usage: widgets/on_air/YOURCALL');
		}

		$this->load->model('user_model');
		$user_result = $this->user_model->get_by_callsign($callsign);

		if ($user_result->num_rows() == 0) {
			show_error('No user found with callsign: ' . htmlspecialchars($callsign, ENT_QUOTES, 'UTF-8'));
		}

		$user = $user_result->row();

		$this->load->model('cat');
		$data['radio_status'] = $this->cat->recent_status_by_user_id($user->user_id);
		$data['callsign'] = strtoupper($this->security->xss_clean(
			$display_callsign !== null ? $display_callsign : $callsign
		));

		$this->load->view('widgets/on_air', $data);
	}

	// Embeddable "on air" status badge as an SVG image.
	// Embed via: <img src="/widgets/on_air_image/M0ABC" alt="M0ABC on air status">
	// Optionally override the displayed callsign: <img src="/widgets/on_air_image/M0ABC/GB2XXX" ...>
	public function on_air_image($callsign = null, $display_callsign = null) {
		if ($callsign == null) {
			show_error('Please provide a callsign. Usage: widgets/on_air_image/YOURCALL');
		}

		$this->load->model('user_model');
		$user_result = $this->user_model->get_by_callsign($callsign);

		if ($user_result->num_rows() == 0) {
			show_error('No user found with callsign: ' . htmlspecialchars($callsign, ENT_QUOTES, 'UTF-8'));
		}

		$user    = $user_result->row();
		$callsign = strtoupper($this->security->xss_clean(
			$display_callsign !== null ? $display_callsign : $callsign
		));

		$this->load->model('cat');
		$radio_status = $this->cat->recent_status_by_user_id($user->user_id);

		// Build the right-hand badge text and colour
		if ($radio_status->num_rows() > 0) {
			$row = $radio_status->result()[0];
			if (!empty($row->prop_mode) && $row->prop_mode === 'SAT') {
				$status_text = 'SAT: ' . strtoupper($row->sat_name);
			} else {
				$status_text = '';
				if (!empty($row->frequency) && $row->frequency != '0') {
					$status_text = number_format($row->frequency / 1000000, 3) . ' MHz';
				}
				if (!empty($row->mode) && $row->mode !== 'non') {
					$status_text .= ($status_text ? ' ' : '') . strtoupper($row->mode);
				}
				if (empty($status_text)) {
					$status_text = 'on air';
				}
			}
			if ($radio_status->num_rows() > 1) {
				$status_text .= ' +' . ($radio_status->num_rows() - 1) . ' more';
			}
			$right_bg = '#4c9e29';
		} else {
			$status_text = 'qrt';
			$right_bg    = '#9f9f9f';
		}

		// Approximate Verdana 11px average character width; padding each side
		$char_w   = 6.5;
		$padding  = 8;
		$left_text_w  = strlen($callsign)    * $char_w;
		$right_text_w = strlen($status_text) * $char_w;
		$left_w  = (int) ceil($left_text_w  + $padding * 2);
		$right_w = (int) ceil($right_text_w + $padding * 2);
		$total_w = $left_w + $right_w;

		// Text anchor centres
		$left_cx  = round($left_w  / 2, 1);
		$right_cx = round($left_w + $right_w / 2, 1);

		// XML-safe content
		$safe_call   = htmlspecialchars($callsign,    ENT_XML1, 'UTF-8');
		$safe_status = htmlspecialchars($status_text, ENT_XML1, 'UTF-8');

		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $total_w . '" height="20" role="img" aria-label="' . $safe_call . ': ' . $safe_status . '">' . "\n"
			. '  <title>' . $safe_call . ': ' . $safe_status . '</title>' . "\n"
			. '  <clipPath id="r"><rect width="' . $total_w . '" height="20" rx="3"/></clipPath>' . "\n"
			. '  <g clip-path="url(#r)">' . "\n"
			. '    <rect width="' . $left_w . '" height="20" fill="#555"/>' . "\n"
			. '    <rect x="' . $left_w . '" width="' . $right_w . '" height="20" fill="' . $right_bg . '"/>' . "\n"
			. '  </g>' . "\n"
			. '  <g fill="#fff" text-anchor="middle" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" font-size="11">' . "\n"
			. '    <text x="' . $left_cx  . '" y="14">' . $safe_call   . '</text>' . "\n"
			. '    <text x="' . $right_cx . '" y="14">' . $safe_status . '</text>' . "\n"
			. '  </g>' . "\n"
			. '</svg>';

		$this->output
			->set_content_type('image/svg+xml')
			->set_header('Cache-Control: no-cache, max-age=60')
			->set_output($svg);
	}
}