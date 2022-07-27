<?php
/**
 * COmanage Registry COU Controller
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
 * @package       registry
 * @since         COmanage Registry v0.2
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

App::uses("StandardController", "Controller");

class CousController extends StandardController {
  // Class name, used by Cake
  public $name = "Cous";
  
  // Establish pagination parameters for HTML views
  public $paginate = array(
    'limit' => 25,
    'order' => array(
      'Cou.name' => 'asc'
    )
  );
  
  // This controller needs a CO to be set
  public $requires_co = true;
  
  public $delete_contains = array(
    'ChildCou'
  );

  public $edit_contains = array(
    'ParentCou'
  );
  
  public $view_contains = array(
    'ParentCou',
    'CoDepartment'
  );

  /**
   * Perform filtering of COU parent options for dropdown.
   * - postcondition: parent_options set
   *
   * @since  COmanage Registry v0.3
   */
 
  function beforeRender() {
    // This loop is concerned with computing the options for parents 
    // to display for a dropdown menu or similar for the GUI when the 
    // user is editing or adding a COU.
    //
    // REST calls do not need to compute options for parents.
    if(!$this->request->is('restful')) {
      if($this->action == 'edit' || $this->action == 'add') {

      switch ($this->action) {
        case 'edit':
          $couId = $this->request->data['Cou']['id'];
          $coId  = $this->request->data['Cou']['co_id'];
          break;

        case 'add':
          $couId = null;
          $coId = $this->cur_co['Co']['id'];
          break;
      }

      $options = $this->Cou->potentialParents($couId, $coId);
      $this->set('parent_options', $options);
      }
    }
    
    parent::beforeRender();
  }

  /**
   * Perform any dependency checks required prior to a delete operation.
   * This method is intended to be overridden by model-specific controllers.
   * - postcondition: Session flash message updated (HTML) or HTTP status returned (REST)
   *
   * @since  COmanage Registry v0.2
   * @param  Array Current data
   * @return boolean true if dependency checks succeed, false otherwise.
   */
  
  function checkDeleteDependencies($curdata) {
    $couppl = $this->Cou->CoPersonRole->findAllByCouId($curdata['Cou']['id']);
    
    if(!empty($couppl)) {
      // A COU can't be removed if anyone is still a member of it.
      
      if($this->request->is('restful')) {
        $this->Api->restResultHeader(403, "CoPersonRole Exists");
      } else {
        $this->Flash->set(_txt('er.cou.copr', array($curdata['Cou']['name'])), array('key' => 'error'));
      }
      
      return false;
    }
    
    // A COU can't be removed if it has children.
    
    $childCous = $curdata['ChildCou'];
    
    if(!empty($childCous)) {
      if($this->request->is('restful')) {
        $this->Api->restResultHeader(403, "Child COU Exists");
      } else {
        $this->Flash->set(_txt('er.cou.child', array(filter_var($curdata['Cou']['name'],FILTER_SANITIZE_SPECIAL_CHARS))), array('key' => 'error'));
      }
      
      return false;
    }

    return true;
  }

  /**
   * Perform any dependency checks required prior to a write (add/edit) operation.
   * This method is intended to be overridden by model-specific controllers.
   * - postcondition: Session flash message updated (HTML) or HTTP status returned (REST)
   *
   * @since  COmanage Registry v0.3
   * @param  Array Request data
   * @param  Array Current data
   * @return boolean true if dependency checks succeed, false otherwise.
   */
  
  function checkWriteDependencies($reqdata, $curdata = null) {
    if(!isset($curdata)
       || ($curdata['Cou']['name'] != $reqdata['Cou']['name'])) {
      // Make sure name doesn't exist within this CO
      $args['conditions']['Cou.name'] = $reqdata['Cou']['name'];
      $args['conditions']['Cou.co_id'] = $reqdata['Cou']['co_id'];
      
      $x = $this->Cou->find('all', $args);
      
      if(!empty($x)) {
        if($this->request->is('restful')) {
          $this->Api->restResultHeader(403, "Name In Use");
        } else {
          $this->Flash->set(_txt('er.cou.exists', array($reqdata['Cou']['name'])), array('key' => 'error')); 
        }
        
        return false;
      }
    }
    
    // Parent COU must be in same CO as child

    // Name of parent
    $parentCou = (!empty($reqdata['Cou']['parent_id']) 
                  ? $reqdata['Cou']['parent_id']
                  : "");

    if(isset($parentCou) && $parentCou != "") {
      if($this->action != 'add') {
        // Parent not found in CO
        if(!$this->Cou->isInCo($parentCou, $reqdata['Cou']['co_id'])) {
          if($this->request->is('restful')) {
            $this->Api->restResultHeader(403, "Wrong CO");
          } else {
            $this->Flash->set(_txt('er.cou.sameco', array($reqdata['Cou']['name'])), array('key' => 'error'));
          }
          
          return false;
        }
        
        // Check if parent would cause a loop
        if($this->Cou->isChildCou($reqdata['Cou']['id'], $parentCou)) {
          if($this->request->is('restful')) {
            $this->Api->restResultHeader(403, "Parent Would Create Cycle");
          } else {
            $this->Flash->set(_txt('er.cou.cycle', array($reqdata['CoGroupMember']['co_group_id'])), array('key' => 'error'));
          }
          
          return false;
        }
      }
    }
    
    return true;
  }

  /**
   * Perform any followups following a write operation.  Note that if this
   * method fails, it must return a warning or REST response, but that the
   * overall transaction is still considered a success (add/edit is not
   * rolled back).
   * This method is intended to be overridden by model-specific controllers.
   * - postcondition: Session flash message updated (HTML) or HTTP status returned (REST)
   *
   * @since  COmanage Registry v0.2
   * @param  Array Request data
   * @param  Array Current data
   * @param  Array Original request data (unmodified by callbacks)
   * @return boolean true if dependency checks succeed, false otherwise.
   */
  
  function checkWriteFollowups($reqdata, $curdata = null, $origdata = null) {
    if(!$this->request->is('restful') && $this->action == 'edit') {
      if(!empty($reqdata['Cou']['name'])
         && !empty($curdata['Cou']['name'])
         && $reqdata['Cou']['name'] != $curdata['Cou']['name']) {
        // The COU has been renamed, so update the relevant group names
        
        $this->Cou->Co->CoGroup->addDefaults($reqdata['Cou']['co_id'], $this->Cou->id, true);
      }
    }
    
    return true;
  }


  /**
   * Obtain all Standard Objects (of the model's type).
   * - postcondition: $<object>s set on success (REST or HTML), using pagination (HTML only)
   * - postcondition: HTTP status returned (REST)
   * - postcondition: Session flash message updated (HTML) on suitable error
   *
   * @since  COmanage Registry v3.1.0
   */

  public function index() {
    // override the parent flow ony for the case of the department search
    if($this->request->is('restful')) {
      if(empty($this->request->query['dept'])) {
        return parent::index();
      }

      $args = array();
      $args['joins'][0]['table'] = 'co_departments';
      $args['joins'][0]['alias'] = 'CoDepartment';
      $args['joins'][0]['type'] = 'INNER';
      $args['joins'][0]['conditions'][0] = "Cou.id=CoDepartment.cou_id";
      $args['conditions']['Cou.co_id'] = $this->request->query['coid'];
      $args['conditions']['CoDepartment.type'] = $this->request->query['dept'];
      // XXX Do not allow COU admins to trick the system and expose all the COUs of the CO
      if (!empty($this->viewVars["vv_roles"]["apiuser"])
        && $this->viewVars["vv_roles"]["apiuser"]
        && !$this->Session->read('Auth.User.privileged')) {
        $args['conditions']['Cou.id'] = explode(",", $this->Session->read('Auth.User.cou_id_list'));
      }
      $args['contain'] = false;

      $cou_list = $this->Cou->find('all', $args);

      $this->set('cous', $this->Api->convertRestResponse($cou_list));
    }
  }

  /**
   * Authorization for this Controller, called by Auth component
   * - precondition: Session.Auth holds data used for authz decisions
   * - postcondition: $permissions set with calculated permissions
   *
   * @since  COmanage Registry v0.2
   * @return Array Permissions
   */
  
  function isAuthorized() {
    $roles = $this->Role->calculateCMRoles();             // What was authenticated
    $this->set('vv_roles', $roles);
    // Is this a record we can manage?
    $managed = false;

    if($roles["apiuser"]) {
      $cou_list = $this->Session->read('Auth.User.cou_id_list');
      if(!empty($cou_list)) {
        // Fetch all COU names
        $args = array();
        $args['conditions'] = array();
        if($this->Session->read('Auth.User.co_id') > 1) {
          $args['conditions']['Cou.co_id'] = $this->Session->read('Auth.User.co_id');
        }
        $args['conditions']['Cou.id'] = explode(",", $cou_list);
        $args['fields'] = array('Cou.id', 'Cou.name');
        $args['contain'] = false;

        $cou_nl = $this->Cou->find('list', $args);

        $managed = in_array($this->request->query['name'], $cou_nl);
      }
    }
    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();
    
    // Determine what operations this user can perform
    
    // Add a new COU?
    $p['add'] = ($roles['cmadmin'] || $roles['coadmin']);
    
    // Delete an existing COU?
    $p['delete'] = ($roles['cmadmin'] || $roles['coadmin']);
    
    // Edit an existing COU?
    $p['edit'] = ($roles['cmadmin'] || $roles['coadmin']);

    // View all existing COUs?
    $p['index'] = $roles['cmadmin']
                  || $roles['coadmin']
                  || ($managed && $roles['couadmin'])
                  || $roles['apiuser'];

    // View an existing COU?
    $p['view'] = $roles['cmadmin']
                 || $roles['coadmin']
                 || ($managed && $roles['couadmin'])
                 || ($managed && $roles['apiuser']);


    $this->set('permissions', $p);
    return $p[$this->action];
  }
}
