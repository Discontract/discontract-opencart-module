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

  public function deleteJobs()
  {
    $sql = 'DELETE FROM ' . DB_PREFIX . 'discontract_job';
    $this->db->query($sql);
  }

  public function updateDiscontractJob($job)
  {
    if (!property_exists($job, 'title')) {
      return;
    }
    $jobId = $this->db->escape($job->id);
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