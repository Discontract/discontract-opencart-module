<?php
class ControllerExtensionModuleDiscontract extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/discontract');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		// 	$this->cache->delete('product');

		// 	$this->session->data['success'] = $this->language->get('text_success');

		// 	$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		// }
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->request->post['name'] = 'discontract';
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('discontract', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			// $this->cache->delete('product');

			// $this->session->data['success'] = $this->language->get('text_success');

			// $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/discontract', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/discontract', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$module_info = array();
		if (isset($this->request->get['module_id'])) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
			$data['api_key'] = $module_info['api_key'];
		}

		$this->response->setOutput($this->load->view('extension/module/discontract', $data));
	}
}

