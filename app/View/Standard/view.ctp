<?php
/**
 * COmanage Registry Standard View View
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
 * @since         COmanage Registry v0.1
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

  // Get a pointer to our model
  $model = $this->name;
  $req = Inflector::singularize($model);
  $modelpl = Inflector::tableize($req);
  $modelu = Inflector::underscore($req);
  
  // Get a pointer to our data
  $d = $$modelpl;

  // Add page title
  $params = array();
  $params['title'] = $title_for_layout;

  // Add top links
  $params['topLinks'] = array();

  // If user has edit permission, offer an edit button in the sidebar
  if(!empty($permissions['edit']) && $permissions['edit']) {

    // special case co_people
    $editAction = 'edit';
    if ($modelpl == 'co_people') {
      $editAction = 'canvas';
    }

    // disable the edit button for the case of vo of the co, not for the case of voms provisioner
    $a = array('controller' => $modelpl, 'action' => $editAction, $d[0][$req]['id']);

    if(empty($d[0]['OrgIdentity']['OrgIdentitySourceRecord']['id'])
       && empty($d[0][$req]['source_'.$modelu.'_id'])
       && empty($d[0]['Cert']['id'])) {
      // Add edit button to the top links, except for attributes attached to
      // an Org Identity that came from an Org Identity Source.
      $params['topLinks'][] = $this->Html->link(
        _txt('op.edit'),
        $a,
        array('class' => 'editbutton')
      );
    }
  }

  // Add locally configured page buttons
  if(!empty($this->plugin)) {
    if(file_exists(APP . "Plugin/" . $this->plugin . "/View/" . $model . "/buttons.inc")) {
      include(APP . "Plugin/" . $this->plugin . "/View/" . $model . "/buttons.inc");
    } elseif(file_exists(LOCAL . "Plugin/" . $this->plugin . "/View/" . $model . "/buttons.inc")) {
      include(LOCAL . "Plugin/" . $this->plugin . "/View/" . $model . "/buttons.inc");
    }
  } else {
    if(file_exists(APP . "View/" . $model . "/buttons.inc")) {
      include(APP . "View/" . $model . "/buttons.inc");
    }
  }
?>
<?php if($model == 'CoPetitions' && $vv_pending_coef): ?>
    <div class="co-info-topbox">
      <i class="material-icons">info</i>
      <?php print _txt('in.pending.petition', array($vv_cou_name)); ?>
    </div>
<?php endif;?>

<?php
  // Add top links
  $params['topLinks'] = array();
  if(!empty($vv_handle_pending_petition)) {
    // Only Enrollee can see this action
    if($permissions['isEnrollee'] && $model == 'CoPetitions' && $co_petitions[0]['CoPetition']['status'] != PetitionStatusEnum::Finalized) {
      $params['topLinks'][] = $this->Html->link(
        _txt('op.abort'),
        array(
          'controller' => 'co_petitions',
          'action' => 'start',
          // 'co' => $co_petitions[0]['CoPetition']['co_id'],
          'abort' => 'yes',
          'coef' => $co_petitions[0]['CoPetition']['co_enrollment_flow_id'],
          'done' => 'core',
        ),
        array('class' => 'addbutton', 'style' => 'float:right')
      );
    }
    if($permissions['isEnrollee'] && $permissions['delete'] && $model == 'CoPetitions' && $co_petitions[0]['CoPetition']['status'] != PetitionStatusEnum::Finalized) {
      $displayNameWithId = (!empty($co_petitions[0]['EnrolleeCoPerson']['PrimaryName']) ? generateCn($co_petitions[0]['EnrolleeCoPerson']['PrimaryName']) : _txt('fd.enrollee.new')) . ' (' . $co_petitions[0]['CoPetition']['status'] . ')';
      
      $params['topLinks'][] = '<a type="button" class="deletebutton" style="float:right;" title="' . _txt('op.delete-a',array($displayNameWithId))
        . '" onclick="javascript:js_confirm_generic(\''
        . _txt('js.remove') . '\',\''    // dialog body text
        . $this->Html->url(              // dialog confirm URL
          array(
            'controller' => 'co_petitions',
            'action' => 'delete',
            $co_petitions[0]['CoPetition']['id'],
            'abort' => 1
          )
        ) . '\',\''
        . _txt('op.remove') . '\',\''    // dialog confirm button
        . _txt('op.cancel') . '\',\''    // dialog cancel button
        . _txt('op.remove') . '\',[\''   // dialog title
        . filter_var(_jtxt($displayNameWithId),FILTER_SANITIZE_STRING)  // dialog body text replacement strings
        . '\']);">'
        . _txt('op.petition.delete')
        . '</a>';
    }
  }
  print $this->element("pageTitleAndButtons", $params);
?>
<?php if(!empty($d[0]['OrgIdentity']['OrgIdentitySourceRecord']['description'])): ?>
<div class="ui-state-highlight ui-corner-all co-info-topbox">
  <p>
    <span class="ui-icon ui-icon-info co-info"></span>
    <strong><?php
      print _txt('op.orgid.edit.ois', array($d[0]['OrgIdentity']['OrgIdentitySourceRecord']['description']));
    ?></strong>
  </p>
</div>
<br />
<?php elseif(!empty($d[0][$req]['source_'.$modelu.'_id'])): ?>
<div class="ui-state-highlight ui-corner-all co-info-topbox">
  <p>
    <span class="ui-icon ui-icon-info co-info"></span>
    <strong><?php print _txt('op.pipeline.edit.ois'); ?></strong>
  </p>
</div>
<br />
<?php endif; // readonly ?>
<?php
  // Output the fields
  print '<div class="innerContent">';
  if(!empty($this->plugin)) {
    if(file_exists(APP . "Plugin/" . $this->plugin . "/View/" . $model . "/fields.inc")) {
      include(APP . "Plugin/" . $this->plugin . "/View/" . $model . "/fields.inc");
    } elseif(file_exists(LOCAL . "Plugin/" . $this->plugin . "/View/" . $model . "/fields.inc")) {
      include(LOCAL . "Plugin/" . $this->plugin . "/View/" . $model . "/fields.inc");
    }
  } else {
    include(APP . "View/" . $model . "/fields.inc");
  }
  print '</div>';

?>
