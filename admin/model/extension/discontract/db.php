<?php

class ModelExtensionDiscontractDb extends Model 
{
  public function install() {
    // create jobs table
    $this->db->query("
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "discontract_job` (
        `discontract_job_id` VARCHAR(255) NOT NULL,
        `title_lt` VARCHAR(2550) NOT NULL,
        `price` INT(11) NOT NULL,
        PRIMARY KEY (`discontract_job_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci
    ");
    // create carts table
    $this->db->query("
    CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "discontract_cart` (
        `opencart_cart_id` VARCHAR(255) NOT NULL,
        `discontract_cart_id` VARCHAR(255) NOT NULL,
        `status` VARCHAR(255) NOT NULL,
        PRIMARY KEY (`discontract_cart_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci
    ");
    $this->db->query(sprintf("ALTER TABLE %s ADD parent_product_id INT(11)", DB_PREFIX."cart"));
    $this->db->query(sprintf("ALTER TABLE %s ADD discontract_item VARCHAR(2550)", DB_PREFIX."cart"));
    $this->db->query(sprintf("ALTER TABLE %s ADD discontract_job_id VARCHAR(255)", DB_PREFIX."product"));
    // create discontract services category
    // create or update address option
    // create or update discontract products (with option) (add column discontract_job_id)
    
  }

  public function getCategories() {
    $language = (int)$this->config->get('config_language_id');
    $resp = $this->db->query(
      sprintf("SELECT * FROM %s WHERE language_id = %d", DB_PREFIX."category_description", $language)
    );
    return $resp->rows;
  }

  public function getStatuses() {
    $language = (int)$this->config->get('config_language_id');
    $resp = $this->db->query(
      sprintf("SELECT * FROM %s WHERE language_id = %d", DB_PREFIX."order_status", $language)
    );
    return $resp->rows;
  }

  public function uninstall() {
    // TODO: need to remove product description and category connections
    $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "discontract_job`");
    $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "discontract_cart`");
    $this->db->query(sprintf("DELETE FROM %s WHERE discontract_job_id IS NOT NULL", DB_PREFIX."product"));
    $this->db->query(sprintf("ALTER TABLE %s DROP COLUMN discontract_job_id", DB_PREFIX."product"));
    $this->db->query(sprintf("ALTER TABLE %s DROP COLUMN discontract_item", DB_PREFIX."cart"));
    $this->db->query(sprintf("ALTER TABLE %s DROP COLUMN parent_product_id", DB_PREFIX."cart"));
  }

  public function deleteJobs()
  {
    // $this->db->query(sprintf("DELETE FROM %s WHERE discontract_job_id IS NOT NULL", DB_PREFIX."product"));
    $sql = 'DELETE FROM ' . DB_PREFIX . 'discontract_job';
    $this->db->query($sql);
  }

  public function updateDiscontractJob($job, $categoryId)
  {
    // TODO: create product_to_store relation
    if (!property_exists($job, 'title')) {
      return;
    }
    $language = (int)$this->config->get('config_language_id');
    $jobId = $this->db->escape($job->id);
    $sql = sprintf('SELECT * FROM `' . DB_PREFIX . 'product` WHERE discontract_job_id = "%s"', $jobId);
    $query = $this->db->query($sql);
    $productId = '';
    if ($query->row) {
      $productId = $query->row['product_id'];
      $sql = sprintf(
        'UPDATE %s SET price = %f WHERE discontract_job_id = "%s"',
        DB_PREFIX . 'product',
        (float)($job->price->unitPrice / 100),
        $jobId
      );
      $this->db->query($sql);
    } else {
      $sql = sprintf(
        'INSERT INTO %s (discontract_job_id, price, model, quantity, subtract, date_available, date_added, date_modified, shipping) VALUES ("%s", %f, "Discontract", 100, 0, CURDATE(), CURDATE(), CURDATE(), 0)',
        DB_PREFIX . 'product',
        $jobId,
        (float)($job->price->unitPrice / 100)
      );
      $this->db->query($sql);
      $productId = $this->db->getLastId();
      $sql = sprintf(
        'INSERT INTO %s (product_id, name, meta_title, description, language_id) VALUES (%d, "%s", "%s", "%s", %d)',
        DB_PREFIX . 'product_description',
        $productId,
        $this->db->escape($job->title),
        $this->db->escape($job->title),
        "",
        $language
      );
      $this->db->query($sql);
      $storeId = $this->config->get('config_store_id');
      $sql = sprintf(
        'INSERT INTO %s (product_id, store_id) VALUES (%d, %d)',
        DB_PREFIX . 'product_to_store',
        $productId,
        $storeId
      );
      $this->db->query($sql);
    }
    $sql = sprintf('SELECT * FROM `' . DB_PREFIX . 'product_to_category` WHERE product_id = %d AND category_id = %d', (int)$productId, (int)$categoryId);
    $query = $this->db->query($sql);
    if (!$query->row) {
      $sql = sprintf(
        'INSERT INTO %s (product_id, category_id) VALUES (%d, %d)',
        DB_PREFIX . 'product_to_category',
        $productId,
        $categoryId
      );
      $this->db->query($sql);
    }
    
    $jobTitleLt = $this->db->escape($job->title);
    $sql = sprintf('SELECT * FROM `' . DB_PREFIX . 'discontract_job` WHERE discontract_job_id = "%s"', $jobId);
    $query = $this->db->query($sql);
    $row = $query->row;
    if ($row) {
      $sql = sprintf(
        'UPDATE %s SET title_lt = "%s", price = %d WHERE discontract_job_id = "%s"',
        DB_PREFIX . 'discontract_job',
        $jobTitleLt,
        (int)$job->price->unitPrice,
        $jobId
      );
      $this->db->query($sql);
    } else {
      $sql = sprintf(
        'INSERT INTO %s (discontract_job_id, title_lt, price) VALUES ("%s", "%s", %d)',
        DB_PREFIX . 'discontract_job',
        $jobId,
        $jobTitleLt,
        (int)$job->price->unitPrice
      );
      $this->db->query($sql);
    }
  }
}