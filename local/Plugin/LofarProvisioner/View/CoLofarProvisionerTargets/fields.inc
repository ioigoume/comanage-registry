<!--
/**
 * COmanage Registry CO Homedir Provisioning Target Fields
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
-->
<?php
  // Determine if fields are editable
  $e = false;
  
  if(($this->action == "add" && $permissions['add']) || ($this->action == "edit" && $permissions['edit']))
    $e = true;
    
  // We shouldn't get here if we don't have at least read permission, but check just in case
  
  if(!$e && !$permissions['view'])
    return false;

  print $this->Form->hidden('co_id', array('default' => $cur_co['Co']['id'])) . "\n";
  print $this->Form->hidden('co_provisioning_target_id', array('default' => $vv_ptid)) . "\n";
?>

<script type="text/javascript">
  <!-- JS specific to these fields -->
  $(function () {
    // User non capturing group regex for the email validation
    var email_regex = "^[A-Za-z0-9-_+]{3,}@[A-Za-z]{3,}(?:\\.[a-z]{2,}){1,}$";

    $('#CoLofarProvisionerTargetEditForm').bind('submit', function (e) {
      //e.preventDefault();
      // caching
      $li = $('#CoLofarProvisionerTargetEmailCsv').closest('li');
      $div = $li.find('div > .field-desc');
      //console.log($div);

      email_list = $('#CoLofarProvisionerTargetEmailCsv').val().split(',');
      email_count = email_list.length;
      // Add abort action if max email transmit is requested

      //console.log(email_list);
      email_list.forEach(function(item){
        found = item.trim().match(email_regex);
        if(found === null){
          text = $div.html().split('-')[0].trim();
          text = text + "<span style='color:red'>  - Wrong Email List Format</span>";
          $div.html(text);
          spinner = $( "#coSpinner" );
          //console.log(spinner);
          spinner.remove();
          e.preventDefault();
        }
      });
    });

  });
</script>

<div class="co-info-topbox">
  <i class="material-icons">info</i>
  <?php print _txt('pl.lofar_provisioner.info'); ?>
</div>
<ul id="<?php print $this->action; ?>_co_lofar_provisioner_target" class="fields form-list form-list-admin">
  <li>
    <div class="field-name">
      <div class="field-title"><?php print _txt('pl.lofar_provisioner.email_list'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.lofar_provisioner.email_list.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('email_csv', array('size' => 1024)) : filter_var($co_lofar_provisioner_targets[0]['CoLofarProvisionerTarget']['email_csv'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title"><?php print _txt('pl.lofar_provisioner.co_admin'); ?>
      </div>
      <div class="field-desc"><?php print _txt('pl.lofar_provisioner.co_admin.desc'); ?></div>
    </div>
    <div class="field-info checkbox">
      <?php
      print ($this->Form->select(
        'co_admin_notify',
        LofarAttrEnum::notify_status,
        array(
          'empty' => false,
          'value' => ($co_lofar_provisioner_targets[0]['CoLofarProvisionerTarget']['co_admin_notify'] ? 1 : 0)
        )));
      ?>
  </li>
  <?php if($e): ?>
        <li class="fields-submit">
            <div class="field-name">
                <span class="required"><?php print _txt('fd.lofar_provisioner.req'); ?></span>
            </div>
            <div class="field-info">
        <?php print $this->Form->submit($submit_label); ?>
            </div>
        </li>
  <?php endif; ?>
</ul>