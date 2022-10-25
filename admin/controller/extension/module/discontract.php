<?php
class ControllerExtensionModuleDiscontract extends Controller {

	public function install() {
		$this->load->model('extension/discontract/db');
		$this->model_extension_discontract_api->install();
	}

	public function uninstall() {
		$this->load->model('extension/discontract/db');
		$this->model_extension_discontract_api->uninstall();
	}

	public function index() {
		$this->load->language('extension/module/discontract');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('extension/discontract/api');
		$this->load->model('extension/discontract/db');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_setting_setting->editSetting('module_discontract', $this->request->post);
		}

		$data['action'] = $this->url->link('extension/module/discontract', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if (isset($this->request->post['module_discontract_status'])) {
			$data['module_discontract_status'] = $this->request->post['module_discontract_status'];
			$data['module_discontract_api_key'] = $this->request->post['module_discontract_api_key'];
		} else {
			$data['module_discontract_status'] = $this->config->get('module_discontract_status');
			$data['module_discontract_api_key'] = $this->config->get('module_discontract_api_key');
		}

		$this->response->setOutput($this->load->view('extension/module/discontract', $data));
	}
}

