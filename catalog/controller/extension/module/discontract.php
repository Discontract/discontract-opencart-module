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
  
  public function locations() {
    $postCode = $this->request->post['postCode'];
    $this->load->model('extension/discontract/api');
    $locations = $this->model_extension_discontract_api->getLocations($postCode);
    // die('[{"postCode":"11329","lat":"54.651886","lng":"25.348360","description":"Airi\u0173 g. 1, Vilnius"},{"postCode":"11329","lat":"54.652248","lng":"25.348738","description":"Airi\u0173 g. 2, Vilnius"},{"postCode":"11329","lat":"54.651775","lng":"25.348730","description":"Airi\u0173 g. 3, Vilnius"},{"postCode":"11329","lat":"54.652195","lng":"25.349113","description":"Airi\u0173 g. 4, Vilnius"},{"postCode":"11329","lat":"54.651669","lng":"25.349117","description":"Airi\u0173 g. 5, Vilnius"},{"postCode":"11329","lat":"54.652145","lng":"25.349464","description":"Airi\u0173 g. 6, Vilnius"},{"postCode":"11329","lat":"54.652550","lng":"25.348812","description":"Angl\u0173 g. 1, Vilnius"},{"postCode":"11329","lat":"54.653004","lng":"25.348902","description":"Angl\u0173 g. 2, Vilnius"},{"postCode":"11329","lat":"54.652431","lng":"25.349342","description":"Angl\u0173 g. 3, Vilnius"},{"postCode":"11329","lat":"54.653214","lng":"25.349165","description":"Angl\u0173 g. 4, Vilnius"}]');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($locations));
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
