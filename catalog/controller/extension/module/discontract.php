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
      $data['product'] = $this->model_extension_discontract_cart->getProduct($productId);
      $data['jobs'] = $products;
      $data['jobs'][0]['selected'] = 'checked';
      for ($i = 0; $i < count($data['jobs']); $i++) {
        $data['jobs'][$i]['price'] = number_format($data['jobs'][$i]['price'], 2);
      }
      return $this->load->view('extension/module/discontract', $data);
    }
	}
  
  public function orderHistoryAdd(&$route, &$args) {
    $orderId = $args[0];
    $statusId = $args[1];
    $cartId = $this->db->escape($this->session->getId());
    $this->load->model('extension/discontract/cart');
    $discontractCart = $this->model_extension_discontract_cart->attachOrderIdToDiscontractCart($cartId, $orderId);
    if (!$discontractCart) {
      return;
    }
    $discontractCartId = $discontractCart['discontract_cart_id'];
    $this->load->model('extension/discontract/api');
    $purchasedStates = $this->config->get('module_discontract_statuses_purchased');
    $deliveredStates = $this->config->get('module_discontract_statuses_delivered');
    $data = $this->model_extension_discontract_cart->getOrderInfo($orderId);
    if (in_array((string)$statusId, $purchasedStates)) { // car purchased
      $request = new stdClass();
      $request->billingDetails = new stdClass();
      $request->contactDetails = new stdClass();
      $request->comment = "";
      $request->billingDetails->firstName =  $data['payment_firstname'];
      $request->billingDetails->lastName = $data['payment_lastname'];
      $request->billingDetails->companyName = $data['payment_company'];
      // $request->billingDetails->businessCode = $data['dni'];
      // $request->billingDetails->vatCode = $data['vat_number'];

      $request->contactDetails->firstName = $data['firstname'];
      $request->contactDetails->lastName = $data['lastname'];
      $value = $data['telephone'];
      $value = preg_replace("/[^0-9]/", "", $value);
      $value = preg_replace('/\s+/', '', $value);
      if (substr($value, 0, 1) == '8') {
        $value = '+370'.substr($value, 1);
      } else if (substr($value, 0, 3) == '370') {
        $value = '+'.$value;
      }
      $request->contactDetails->phoneNumber = $value;
      $request->contactDetails->email = $data['email'];
      if ($discontractCart["status"] === 'reserved') {
        $response = $this->model_extension_discontract_api->purchaseCart($discontractCartId, $request);
        $this->model_extension_discontract_cart->updateCartStatus($discontractCartId, $response->status);
      }
    } else if (in_array((string)$statusId, $deliveredStates)) { // issiusta
      if ($discontractCart["status"] === 'purchased') {
        $time = time() * 1000 + 3600 * 48 * 1000;
        $response = $this->model_extension_discontract_api->deliverCart($discontractCartId, $time);
        $this->model_extension_discontract_cart->updateCartStatus($discontractCartId, $response->status);
      }
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

  public function price() {
    $address = new stdClass();
    $address->lat = (float)$this->request->post['lat'];
    $address->lng = (float)$this->request->post['lng'];
    $address->description = $this->request->post['description'];
    $this->load->model('extension/discontract/api');
    $jobId = $this->request->post['jobId'];
    $response = $this->model_extension_discontract_api->getPriceQuote($jobId, array("location" => $address));
    $response->jobId = $jobId;
    $response->location = $address;
    die(json_encode($response));
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($response));
  }

  public function syncDiscontractCart() {
    $this->load->model('extension/discontract/cart');
    $cartId = $this->db->escape($this->session->getId());
    $this->model_extension_discontract_cart->detachDiscontractCart($cartId);
    $products = $this->model_extension_discontract_cart->getDiscontractProductsForCurrentCart($cartId);
    if (count($products) == 0) {
      return;
    }
    // TODO: do not detach/attach in case carts are exactly the same
    $jobItems = array();
    for ($i = 0; $i < count($products); $i++) {
      $c = $products[$i];
      $cartItemId = $c['cart_id'];
      $quantity = $c['quantity'];
      $jobInfo = json_decode($c['discontract_item']);
      $job = new stdClass();
      $job->jobId = $jobInfo->jobId;
      $job->amount = (int)$quantity;
      $job->productName = $jobInfo->productName;
      $job->location = $jobInfo->location;
      $jobItems[] = $job;
      $job->externalItemId = $cartItemId;
    }
    $this->load->model('extension/discontract/api');
    $response = $this->model_extension_discontract_api->createCart($jobItems, $cartId);
    $this->model_extension_discontract_cart->attachDiscontractCart($cartId, $response->cartId, $response->status);
    for ($i = 0; $i < count($response->items); $i++) {
      $order = $response->items[$i];
      // TODO: also update speicific prices in case there is an unexpected price change
      $this->model_extension_discontract_cart->updateOptionPrice($order->externalItemId, ($order->price->arrivalCost / 100 / $order->amount));
      $this->model_extension_discontract_cart->setDiscontractItemInfo($order->externalItemId, json_encode($order));
    }
  }

  // public function removeCartItem() {
  // hooking into add/remove does not work because redirect is done before that
  // }

  public function addToCart() {
    $output = json_decode($this->response->getOutput());
    if (property_exists($output, 'error') || !array_key_exists('discontract_cart', $this->request->post)) {
      return;
    }
    $discontractCartEncoded = htmlspecialchars_decode($this->request->post['discontract_cart']);
    $quantity = (int)$this->request->post['quantity'];
    if (!$discontractCartEncoded) {
      return;
    }
    $discontractCart = json_decode($discontractCartEncoded);

    $this->load->model('extension/discontract/cart');
    $options = $this->model_extension_discontract_cart->addOptionValue(
      $discontractCart->productId,
      $discontractCart->location->description,
      $discontractCart->price->arrivalCost / 100,
      $quantity
    );
    $this->cart->add($discontractCart->productId, $quantity, $options);
    $cartRowId = $this->db->getLastId();
    // var_dump($cartId);
    $this->model_extension_discontract_cart->setDiscontractItemInfo($cartRowId, $discontractCartEncoded, $this->request->post['product_id']);
    $this->syncDiscontractCart();
  }
}
