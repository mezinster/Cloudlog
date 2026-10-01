<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Emeinitials extends CI_Controller {

    function __construct()
    {
        parent::__construct();

        $this->load->model('user_model');
        if(!$this->user_model->authorize(2)) { $this->session->set_flashdata('notice', 'You\'re not allowed to do that!'); redirect('dashboard'); }
    }

    public function index()
    {
        // Render Page
        $data['page_title'] = "EME Initials";

        $this->load->model('modes');
        $this->load->model('bands');

        $data['modes'] = $this->modes->active();
        $data['worked_bands'] = $this->bands->get_worked_bands();

        $this->load->view('interface_assets/header', $data);
        $this->load->view('emeinitials/index');
        $this->load->view('interface_assets/footer');
    }

    public function component_eme_results() {
        $this->load->model('Emeinitials_model');

        $band = $this->input->post('band') ?: 'All';
        $mode = $this->input->post('mode') ?: 'All';

        // Get Date format
        if ($this->session->userdata('user_date_format')) {
            $custom_date_format = $this->session->userdata('user_date_format');
        } else {
            $custom_date_format = $this->config->item('qso_date_format');
        }

        $data['timeline_array'] = $this->Emeinitials_model->get_initials($band, $mode);
        $data['custom_date_format'] = $custom_date_format;

        $this->load->view('emeinitials/component_results', $data);
    }
}
