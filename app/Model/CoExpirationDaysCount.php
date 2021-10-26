<?php
/**
 * COmanage Registry CO Expiration Count Model
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v2.0.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

class CoExpirationDaysCount extends AppModel {
  // Define class name for cake
  public $name = "CoExpirationDaysCount";

  // Add behaviors
  public $actsAs = array('Containable',
                         'Changelog' => array('priority' => 5));


  // Association rules from this model to other models
  public $belongsTo = array(
    "CoExpirationPolicy",
    "CoPersonRole"
  );

  // Default display field for cake generated views
  public $displayField = "co_expiration_policy_id";

  // Validation rules for table elements
  public $validate = array(
    'co_expiration_policy_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'allowEmpty' => false
    ),
    'co_person_role_id' => array(
      'rule' => 'numeric',
      'required' => false,
      'allowEmpty' => true
    ),
    'org_identity_id' => array(
      'rule' => 'numeric',
      'required' => false,
      'allowEmpty' => true
    ),
    'expiration_date_run' => array(
      'rule' => 'datetime',
      'required' => true,
      'allowEmpty' => false
    )
  );

  /**
   * Set expiration_date_run to zero(0)
   *
   * @param $coExpirationPolicyId     CoExpirationPolicy ID
   * @param $coPersonRoleId           CoPerson Role ID
   * @param $orgIdentityId            OrgIdentity ID
   * @throws Exception
   */
  public function date_store($coExpirationPolicyId, $coPersonRoleId = null, $orgIdentityId = null) {
    $dbc = $this->getDataSource();
    $dbc->begin();

    // We don't currently try to validate either foreign key.
    $args = array();
    $args['conditions']['CoExpirationDaysCount.co_expiration_policy_id'] = $coExpirationPolicyId;
    if(!empty($coPersonRoleId)) {
      $args['conditions']['CoExpirationDaysCount.co_person_role_id'] = $coPersonRoleId;
    } elseif (!empty($orgIdentityId)) {
      $args['conditions']['CoExpirationDaysCount.org_identity_id'] = $orgIdentityId;
    } else {
      return false;
    }
    $args['contain'] = false;

    $days_count = $this->find('first', $args);
    $this->clear();

    if(!empty($days_count)) {
      $this->id = $days_count['CoExpirationDaysCount']['id'];
      $this->saveField('expiration_date_run', date('Y-m-d H:i:s'));
    } else {
      $exp_run = array(
        'co_expiration_policy_id' => $coExpirationPolicyId,
        'expiration_date_run'     => date('Y-m-d H:i:s')
      );

      if(!empty($coPersonRoleId)) {
        $exp_run['co_person_role_id'] = $coPersonRoleId;
      } elseif (!empty($orgIdentityId)) {
        $exp_run['org_identity_id'] = $orgIdentityId;
      } else {
        return false;
      }

      $this->save($exp_run);
    }

    $dbc->commit();
  }

  /**
   * Calculate the days passed since last notification sent
   *
   * @param Integer $coExpirationPolicyId CO Expiration Policy ID
   * @param Integer $coPersonRoleId CO Person Role ID
   * @param Integer $orgIdentityId  OrgIdentity ID

   * @return Integer|null Days passed, null in case never ran before
   */

  public function days_diff($coExpirationPolicyId, $coPersonRoleId = null, $orgIdentityId = null) {
    // We don't currently try to validate either foreign key.
    $args = array();
    $args['conditions']['CoExpirationDaysCount.co_expiration_policy_id'] = $coExpirationPolicyId;
    if(!empty($coPersonRoleId)) {
      $args['conditions']['CoExpirationDaysCount.co_person_role_id'] = $coPersonRoleId;
    } elseif (!empty($orgIdentityId)) {
      $args['conditions']['CoExpirationDaysCount.org_identity_id'] = $orgIdentityId;
    } else {
      return false;
    }
    $args['contain'] = false;

    $days_count = $this->find('first', $args);

    if(isset($days_count) && !empty($days_count['CoExpirationDaysCount']['expiration_date_run'])) {
      $run_date = new DateTime($days_count['CoExpirationDaysCount']['expiration_date_run']);
      $now_date = new DateTime();
      $diff_date = (array) date_diff($now_date, $run_date);
      // The difference in days
      return $diff_date['d'];
    }

    return null;
  }
}
