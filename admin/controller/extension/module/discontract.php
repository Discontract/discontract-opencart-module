<?php
class ControllerExtensionModuleDiscontract extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/discontract');

		$this->document->setTitle($this->language->get('heading_title'));

		// $this->load->model('setting/module');

		// if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
		// 	if (!isset($this->request->get['module_id'])) {
		// 		$this->model_setting_module->addModule('bestseller', $this->request->post);
		// 	} else {
		// 		$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
		// 	}

		// 	$this->cache->delete('product');

		// 	$this->session->data['success'] = $this->language->get('text_success');

		// 	$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		// }
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('extension/module/discontract', $data));
	}
}

