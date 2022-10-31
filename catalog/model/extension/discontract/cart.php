<?php

class ModelExtensionDiscontractCart extends Model {
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
    
    $query = $this->db->query(sprintf("SELECT * FROM %s WHERE option_id = %d", DB_PREFIX."product_option", $option_id));
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
    
    return array($product_option_id => $this->db->getLastId());
  }
}
