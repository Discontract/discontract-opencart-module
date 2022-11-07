<?php
class ControllerExtensionModuleDiscontract extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/discontract');
    $this->load->model('extension/discontract/cart');
    $discontractCategory = (int)$this->config->get('module_discontract_category');
    $productId = (int)$this->request->get['product_id'];
    $this->load->model('setting/setting');
    $products = $this->model_extension_discontract_cart->getDiscontractProducsByProductId($productId, $discontractCategory);
    // var_dump($products);
    if (count($products) > 0) {
      $data = array();
      $data['jobs'] = $products;
      return $this->load->view('extension/module/discontract', $data);
    }
	}
  
  public function getSuggestions() {
    $data = array();
    $data['one'] = 'two';
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($data));
  }

  public function addToCart() {
    $output = json_decode($this->response->getOutput());
    if (property_exists($output, 'error')) {
      return;
    }
    $this->load->model('extension/discontract/cart');
    $options = $this->model_extension_discontract_cart->addOptionValue(50, 'Gedimino pr. 5', 49.99, 1);
    $this->cart->add(50, 1, $options);
  }
}
