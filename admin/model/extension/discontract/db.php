<?php

class ModelExtensionDiscontractDb extends Model 
{
  public function install() {
    $this->db->query("
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "discontract_job` (
        `discontract_job_id` VARCHAR(255) NOT NULL,
        `title_lt` VARCHAR(2550) NOT NULL,
        `price` INT(11) NOT NULL,
        PRIMARY KEY (`discontract_job_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci
    ");
  }

  public function uninstall() {
    $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "discontract_job`");
  }
}