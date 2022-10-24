<?php
class ControllerExtensionModuleDiscontract extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/discontract');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		// 	$this->cache->delete('product');

		// 	$this->session->data['success'] = $this->language->get('text_success');

		// 	$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		// 
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			// var_dump($this->request->post);
			$this->model_setting_setting->editSetting('module_discontract', $this->request->post);
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));

			// $this->cache->delete('product');

			// $this->session->data['success'] = $this->language->get('text_success');

			// $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['action'] = $this->url->link('extension/module/discontract', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['module_discontract_status'] = $this->config->get('module_discontract_status');
		$data['module_discontract_api_key'] = $this->config->get('module_discontract_api_key');

		$this->response->setOutput($this->load->view('extension/module/discontract', $data));
	}
}

