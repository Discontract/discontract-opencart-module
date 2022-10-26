<?php
class ControllerExtensionModuleDiscontract extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/discontract');
    $data = array();
    return $this->load->view('extension/module/discontract', $data);
	}
  
  public function getSuggestions() {
    $data = array();
    $data['one'] = 'two';
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($data));
  }
}
