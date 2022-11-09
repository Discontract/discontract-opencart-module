<?php

class ModelExtensionDiscontractCart extends Model {
  public function getProduct($productId) {
    $language = (int)$this->config->get('config_language_id');
    $query = $this->db->query(sprintf("SELECT * FROM %s AS p LEFT JOIN %s AS pd ON (pd.product_id = p.product_id) WHERE p.product_id = %d AND pd.language_id = %d", DB_PREFIX."product", DB_PREFIX."product_description", (int)$productId, $language));
    return $query->row;
  }

  public function setDiscontractItemInfo($cartId, $info, $parentId = false) {
    // $session = $this->db->escape($this->session->getId());
    $this->db->query(sprintf("UPDATE %s SET discontract_item='%s' WHERE cart_id = %d",
      DB_PREFIX."cart", $this->db->escape($info), (int)$cartId));
    if ($parentId) {
      $this->db->query(sprintf("UPDATE %s SET parent_product_id=%d WHERE cart_id = %d",
        DB_PREFIX."cart", (int)$parentId, (int)$cartId));
    }
  }

  public function detachDiscontractCart($cartId) {
    $this->db->query(sprintf("DELETE FROM %s WHERE opencart_cart_id='%s'",
      DB_PREFIX."discontract_cart", $this->db->escape($cartId)));
  }

  public function attachDiscontractCart($opencartId, $discontractCartId, $status) {
    $this->db->query(sprintf("INSERT INTO %s SET opencart_cart_id='%s', discontract_cart_id='%s', status='%s'",
      DB_PREFIX."discontract_cart", $this->db->escape($opencartId), $this->db->escape($discontractCartId), $this->db->escape($status)));
  }

  public function updateOptionPrice($cartRowId, $price) {
    $query = $this->db->query(sprintf("SELECT * FROM %s WHERE cart_id = %d", DB_PREFIX."cart", (int)$cartRowId));
    $cartRow = $query->row;
    $optionValueId = array_values((array)json_decode($cartRow['option']))[0];
    $this->db->query(sprintf("UPDATE %s SET price=%f WHERE product_option_value_id = %d",
      DB_PREFIX."product_option_value", (float)$price, (int)$optionValueId));
  }

  public function getDiscontractProductsForCurrentCart($cartId) {
    $query = $this->db->query(sprintf("SELECT * FROM %s WHERE session_id = '%s' AND discontract_item IS NOT NULL", DB_PREFIX."cart", $cartId));
    return $query->rows;
  }

  public function getDiscontractProducsByProductId($productId, $discontractCategoryId) {
    $language = (int)$this->config->get('config_language_id');
    $query = $this->db->query(sprintf("SELECT * FROM %s WHERE product_id = %d", DB_PREFIX."product", (int)$productId));
    $product = $query->row;
    if ($product['discontract_job_id']) {
      return array($product);
    }
    $products = array();
    $productCategories = ($this->db->query(sprintf("SELECT * FROM %s WHERE product_id = %d", DB_PREFIX."product_to_category", (int)$productId)))->rows;
    $discontractProductsCategories = ($this->db->query(sprintf("SELECT * FROM %s WHERE category_id = %d", DB_PREFIX."product_to_category", (int)$discontractCategoryId)))->rows;
    foreach ($productCategories as $category) {
      if ((int)$category['category_id'] === (int)$discontractCategoryId) {
        continue;
      }
      $productsInCategory = ($this->db->query(sprintf("SELECT * FROM %s WHERE category_id = %d", DB_PREFIX."product_to_category", (int)$category['category_id'])))->rows;
      foreach ($productsInCategory as $productInCategory) {
        foreach ($discontractProductsCategories as $discontractProductCategory) {
          if ($productInCategory['product_id'] === $discontractProductCategory['product_id']) {
            $product = $this->getProduct((int)$discontractProductCategory['product_id']);
            if (array_key_exists('status', $product) && $product['status'] == 1) {
              $products[] = $product;
            }
            // $products[] = $productInCategory['product_id'];
          }
        }
      }
    }
    return $products;
  }

  public function addOptionValue($discontract_product_id, $address, $price, $quantity) {
    $language = (int)$this->config->get('config_language_id');
    $query = $this->db->query(sprintf("SELECT * FROM %s WHERE type = 'discontract_arrival_cost'", DB_PREFIX."option"));
    $option_id = 0;
    // get or create option if it does not exist
    if ($query->row) {
      $option_id = $query->row['option_id'];
    } else {
      $this->db->query(sprintf("INSERT INTO %s SET type='discontract_arrival_cost'", DB_PREFIX."option"));
      $option_id = $this->db->getLastId();
      $this->db->query(sprintf("INSERT INTO %s SET language_id=%d, name='Atvykimo mokestis', option_id=%d",
        DB_PREFIX."option_description",
        $language,
        $option_id));
    }
    // attach option to product if it does not exist
    // TODO: consider adding a column for options to save unique value to avoid duplication (for example lat,lng or postcode)
    $this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "'");
    $option_value_id = $this->db->getLastId();

    $this->db->query("INSERT INTO " . DB_PREFIX .
      "option_value_description SET option_value_id = '" . (int)$option_value_id .
      "', language_id = '" . $language .
      "', option_id = '" . (int)$option_id .
      "', name = '" . $this->db->escape($address) . "'");
    
    $query = $this->db->query(sprintf("SELECT * FROM %s WHERE option_id = %d AND product_id = %d", DB_PREFIX."product_option", $option_id, $discontract_product_id));
    $product_option_id = 0;
    if ($query->row) {
      $product_option_id = $query->row['product_option_id'];
    } else {
      $this->db->query(sprintf("INSERT INTO %s SET product_id=%d, option_id=%d, required=1", DB_PREFIX."product_option", $discontract_product_id, $option_id));
      $product_option_id = $this->db->getLastId();
    }
  
    $this->db->query("INSERT INTO " . DB_PREFIX .
      "product_option_value SET product_option_id = '" . (int)$product_option_id .
      "', product_id = '" . (int)$discontract_product_id .
      "', option_id = '" . (int)$option_id . "',
      option_value_id = '" . (int)$option_value_id .
      "',quantity = '" . (int)$quantity .
      "', subtract = '" . (int)0 .
      "', price = '" . (float)$price .
      "', price_prefix = '+" .
      "', points = '" . (int)0 .
      "', points_prefix = '+" .
      "', weight = '" . (float)0 .
      "', weight_prefix = '+'");
    
    return array($product_option_id."" => $this->db->getLastId()."");
  }
}
