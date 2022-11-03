<?php
class ControllerExtensionModuleDiscontract extends Controller {

	public function install() {
		$this->load->model('extension/discontract/db');
		$this->model_extension_discontract_db->install();
		$this->load->model('setting/event');
		$this->model_setting_event->addEvent('discontract_add_to_cart', 'catalog/controller/checkout/cart/add/after', 'extension/module/discontract/addToCart');
	}

	public function uninstall() {
		$this->load->model('extension/discontract/db');
		$this->model_extension_discontract_db->uninstall();
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('discontract_add_to_cart');
	}

	public function index() {
		$this->load->language('extension/module/discontract');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('extension/discontract/api');
		$this->load->model('extension/discontract/db');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_setting_setting->editSetting('module_discontract', $this->request->post);
			$response = $this->model_extension_discontract_api->getJobs();
			// die();
			// var_dump($jobs);
			$jobs = $response->jobs;
			$this->model_extension_discontract_db->deleteJobs();
			for ($i = 0; $i < count($jobs); $i++) {
				$job = $jobs[$i];
				$this->model_extension_discontract_db->updateDiscontractJob($job);
			}
		}

		$data['action'] = $this->url->link('extension/module/discontract', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['environments'] = array(
			array('name' => 'local', 'value' => 'http://localhost:8020/api/v1'),
			array('name' => 'staging', 'value' => 'https://b2b-stage.discontract.com/api/v1'),
			array('name' => 'production', 'value' => 'https://b2b.discontract.com/api/v1'),
		);

		$keys = array('module_discontract_status', 'module_discontract_api_key', 'module_discontract_environment');
		foreach ($keys as $key) {
			if (isset($this->request->post[$key])) {
				$data[$key] = $this->request->post[$key];
			} else {
				$data[$key] = $this->config->get($key);
			}
		}
		
		$this->response->setOutput($this->load->view('extension/module/discontract', $data));
	}
}

