<?php
/**
 * COmanage Registry CO Lofar Provisioner Target Model
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
 * @since         COmanage Registry v0.9
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

App::uses("CoProvisionerPluginTarget", "Model");
App::uses('CakeEmail', 'Network/Email');

/**
 * Class CoLofarProvisionerTarget
 */
class CoLofarProvisionerTarget extends CoProvisionerPluginTarget {
  // Define class name for cake
  public $name = "CoLofarProvisionerTarget";

  // Add behaviors
  public $actsAs = array('Containable');

  // Association rules from this model to other models
  public $belongsTo = array('CoProvisioningTarget');

  // Default display field for cake generated views
  public $displayField = "co_provisioning_target_id";

  // Validation rules for table elements
  public $validate = array(
    'co_provisioning_target_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'message' => 'A CO PROVISIONING TARGET ID must be provided'
    ),
    'open' => array(
      'rule' => array('boolean')
    )
  );

  /**
  * Provision for the specified CO Person.
  *
  * @since  COmanage Registry v0.8
  * @param  Array CO Provisioning Target data
  * @param  ProvisioningActionEnum Registry transaction type triggering provisioning
  * @param  Array Provisioning data, populated with ['CoPerson'] or ['CoGroup']
  * @return Boolean True on success
  * @throws RuntimeException
  */

  public function provision($coProvisioningTargetData, $op, $provisioningData)
  {
    $fn = "provision";
    $this->log(get_class($this)."::{$fn}::@", LOG_DEBUG);

//    $this->log(get_class($this)."::{$fn}::action => ".$op, LOG_DEBUG);
//    $this->log(get_class($this)."::{$fn}::target data => ".print_r($coProvisioningTargetData,true),LOG_DEBUG);
//    $this->log(get_class($this)."::{$fn}::provision data => ".print_r($provisioningData,true),LOG_DEBUG);
    switch ($op) {
      // the whole provisioning happens during provisioning, either we are talking
      // about CO or COU. At the end of petition, provisioning process takes place and
      // the user is part of
      case ProvisioningActionEnum::CoPersonPetitionProvisioned:
        $this->log(get_class($this)."::{$fn}:: person petition provisioned", LOG_DEBUG);
        break;
      case ProvisioningActionEnum::CoGroupUpdated:
        $this->log(get_class($this)."::{$fn}:: co group updated", LOG_DEBUG);
        break;
      case ProvisioningActionEnum::CoGroupAdded:
        $this->log(get_class($this)."::{$fn}:: co group added", LOG_DEBUG);
        break;
      case ProvisioningActionEnum::CoGroupDeleted:
        $this->log(get_class($this)."::{$fn}:: co group deleted", LOG_DEBUG);
        $this-> manageDeleteGroupDeprovision($coProvisioningTargetData, $provisioningData);
        break;
      case ProvisioningActionEnum::CoPersonUpdated:
        $this->log(get_class($this)."::{$fn}:: Person Updated", LOG_DEBUG);
        break;
      case ProvisioningActionEnum::CoPersonDeleted:
        $this->log(get_class($this)."::{$fn}:: Person deleted", LOG_DEBUG);
        break;
      default:
        // Log noop and fall through.
        $this->log("{$fn}::Provisioning action $op not allowed/implemented", LOG_DEBUG);
      }

    return true;
  }

  public function manageDeleteGroupDeprovision($coProvisioningTargetData, $provisioningData){
    $fn = "manageDeleteGroupDeprovision";
    $this->log(get_class($this)."::{$fn}::@", LOG_DEBUG);
    if(isset($coProvisioningTargetData['CoLofarProvisionerTarget'])
      && isset($provisioningData['CoGroup'])) {
      // Get the email list stored by the admin
      $provisioner_email_list = explode(",", $coProvisioningTargetData['CoLofarProvisionerTarget']['email_csv']);
      // My vars
      $co_id = $provisioningData['Co']['id'];
      $group_name = $provisioningData['CoGroup']['name'];
      $group_id = $provisioningData['CoGroup']['id'];
      $group_desc = $provisioningData['CoGroup']['description'];


      $co_admin_email = null;
      if ($coProvisioningTargetData['CoLofarProvisionerTarget']['co_admin_notify']) {
        $co_admin_email = $this->coAdminEmail($co_id);
        //$this->log(get_class($this)."::{$fn}::admin email => ".$co_admin_email, LOG_DEBUG);
      }
      // Fetch group members emails
      $group_members_emails = $this->getGroupMembersEmails($group_id);
      $email_list = array_merge($provisioner_email_list, $group_members_emails);
      $email_list = array_filter($email_list);
      $email_list = array_unique($email_list);
      $this->log(get_class($this)."::{$fn}::email list=> ".print_r($email_list, true), LOG_DEBUG);

      // Now that i found all the data that i need start the process of sending and saving
      // Send the email
      $msgBody = "the Group with name:{$group_name}[desc:{$group_desc}] and id:{$group_id} has been deleted.";
      $this->sendEmail($msgBody, $email_list, "Group Deleted", $co_admin_email);

    }

  }

  /**
   * @param $group_id    id of the group
   * @return array       array list of group members emails(all of the emails) or empty array
   */
  public function getGroupMembersEmails($group_id){
    $fn = "getGroupMembersEmails";
    $this->log(get_class($this)."::{$fn}::@", LOG_DEBUG);

    $this->CoGroupMember = ClassRegistry::init('CoGroupMember');
    $args = array();
    $args['joins'][0]['table'] = 'email_addresses';
    $args['joins'][0]['alias'] = 'EmailAddress';
    $args['joins'][0]['type'] = 'INNER';
    $args['joins'][0]['conditions'][0] = 'EmailAddress.co_person_id=CoGroupMember.co_person_id';
    $args['conditions']['CoGroupMember.co_group_id'] = $group_id;
    $args['conditions']['CoGroupMember.member'] = true;
    $args['fields'] = array('EmailAddress.mail');
    $args['contain'] = false;

    $member_list = $this->CoGroupMember->find('all', $args);
    //$this->log(get_class($this)."::{$fn}::member_list => ". print_r($member_list, true), LOG_DEBUG);
    $email_list = array();
    if(isset($member_list[0])) {
      foreach ($member_list as $mem_email) {
        array_push($email_list, $mem_email['EmailAddress']['mail']);
      }
      $email_list = array_unique($email_list);
      //$this->log(get_class($this)."::{$fn}::email list => ". print_r($email_list, true), LOG_DEBUG);
    }
    return $email_list;

  }


  /**
   * @param $msgBody
   * @param $recipients
   * @param $msgSubject
   * @param $cc
   * @return bool
   */
  public function sendEmail($msgBody, $recipients, $msgSubject, $cc){
    $fn = "sendEmail";
    $this->log(get_class($this)."::{$fn}::@", LOG_DEBUG);

    $email = new CakeEmail('default');

    // Add cc and bcc if specified
    if($cc) {
      $email->cc($cc);
    }

    $email->emailFormat('text')
      ->from('noreply@lofar.org', 'LOFAR CO Notification')
      ->to($recipients)
      ->subject($msgSubject);
    $status = false;
    //$this->log(get_class($this)."::{$fn}::email => ".print_r($email,true), LOG_DEBUG);
    try {
      if ( $email->send("Dear user,\nWe would like to inform you that\n".$msgBody."\n\nThank you.") ) {
        // Success
        $status = true;
      } else {
        // Failure, without any exceptions
        $status = false;
      }
    } catch ( Exception $error ) {
      $status = false;
      $this->log(get_class($this)."::{$fn}::exception error => ".$error, LOG_DEBUG);
    }
    return $status;
  }


  /**
   * @param $co_id    the co id of interest
   * @return array    array list of co admin emails
   */
  public function coAdminEmail($co_id){
    $fn = "coAdminEmail";
    $this->log(get_class($this)."::{$fn}::@", LOG_DEBUG);

    $this->CoGroupMember = ClassRegistry::init('CoGroupMember');
    $args = array();
    $args['joins'][0]['table'] = 'email_addresses';
    $args['joins'][0]['alias'] = 'EmailAddress';
    $args['joins'][0]['type'] = 'INNER';
    $args['joins'][0]['conditions'][0] = 'EmailAddress.co_person_id=CoGroupMember.co_person_id';
    $args['joins'][1]['table'] = 'co_groups';
    $args['joins'][1]['alias'] = 'CoGroup';
    $args['joins'][1]['type'] = 'INNER';
    $args['joins'][1]['conditions'][0] = 'CoGroup.id=CoGroupMember.co_group_id';
    $args['conditions']['CoGroup.co_id'] = $co_id;
    $args['conditions']['CoGroup.name LIKE'] = '%CO:admins%';
    $args['fields'] = array('EmailAddress.mail', 'CoGroup.id', 'CoGroupMember.co_person_id');
    $args['contain'] = false;

    $admin_email_list = $this->CoGroupMember->find('all', $args);

    //$this->log(get_class($this)."::{$fn}::admin_email_list => ".print_r($admin_email_list,true), LOG_DEBUG);
    $email_list = array();
    if(isset($admin_email_list[0])){
      foreach($admin_email_list as $admin){
        array_push($email_list, $admin['EmailAddress']['mail']);
      }
      $email_list = array_unique($email_list);
      $this->log(get_class($this)."::{$fn}::email_list => ".print_r($email_list,true), LOG_DEBUG);
    }

    return $email_list;
  }

}
