<?php
/**
 * COmanage Registry Organizational ProvisionerCertRecordRecord Model
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry
 * @since         COmanage Registry v2.0.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

class ProvisionerCertRecord extends AppModel {
  // Define class name for cake
  public $name = "ProvisionerCertRecord";

  // Current schema version for API
  public $version = "1.0";

  // Add behaviors
  public $actsAs = array('Containable');

  // Association rules from this model to other models
  public $belongsTo = array(
    'CoPersonRole',
    );

  public $hasMany = array();

  // Validation rules for table elements
  public $validate = array(
    'co_person_role_id' => array(
      'content' => array(
        'rule' => 'numeric',
        'required' => true,
        'allowEmpty' => false
      )
    ),
    'cert_id' => array(
      'content' => array(
        'rule' => 'numeric',
        'required' => true,
        'allowEmpty' => false
      )
    ),
  );

  /**
   * @param bool $cascade
   * @return bool|true
   */
  public function beforeDelete($cascade = true) {
    if(!empty($_REQUEST)) {
      $requests = array_keys($_REQUEST);
      $co_person_role_found = array_filter(
        $requests,
        function($value) {
          return strpos($value, 'co_person_roles') !== false;
        }
      );
      if(empty($co_person_role_found)) {
        return parent::beforeDelete($cascade);
      }
      $_SESSION['ProvisionerCertRecord'] = array(
        'cert_id' => (int)$this->field('cert_id'),
        'co_person_role_id' => (int)$this->field('co_person_role_id'),
      );

    }
    return parent::beforeDelete($cascade); // TODO: Change the autogenerated stub
  }
}