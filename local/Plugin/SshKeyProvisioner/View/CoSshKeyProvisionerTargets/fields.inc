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

<script src="<?= $this->request->webroot . 'ssh_key_provisioner' ?>/jquery/jquery.base64.js"></script>
<script type="text/javascript">
  $(document).ready(function () {
    $('[type=file]').change(function() {
      // Get the id of the input and retrieve the corresponding input textbox
      id = $(this).attr('id').toString().toLowerCase();
      var textbox = id.indexOf("private")>=0 ? $('#CoSshKeyProvisionerTargetPrivateKey') : $('#CoSshKeyProvisionerTargetPublicKey');
      //console.log(textbox);

      if (!("files" in this)) {
        alert("File reading not supported in this browser");
      }
      var file = this.files && this.files[0];
      if (!file) {
        return;
      }
      var fileReader = new FileReader();
      fileReader.onload = function(e) {
        var text = e.target.result;
        // console.log(id.indexOf("private"));
        // console.log(text.indexOf("BEGIN RSA PRIVATE KEY"));
        // console.log(text.indexOf("ssh-rsa"));
        if( (text.indexOf("BEGIN RSA PRIVATE KEY") >= 0 && id.indexOf("private") >= 0) ||
          (text.indexOf("ssh-rsa") >= 0 && id.indexOf("private") < 0) ){
          li = textbox.closest('li');
          //console.log(li);
          description = li.find('.field-desc');
          //console.log(description);
          description_text = description.html().split('-')[0].trim();
          description.html(description_text);
        }else{
          //console.log('Wrong type of file');
          li = textbox.closest('li');
          //console.log(li);
          description = li.find('.field-desc');
          //console.log(description);
          description_text = description.html().split('-')[0].trim();
          description_text = description_text + "<span style='color:red'>  - Wrong File Input</span>";
          description.html(description_text);
          return;
        }
        //Encode to base64
        //console.log(text);
        result = $.base64.encode(text);
        //console.log(result);
        // Add base64 encoded value in the textbox
        textbox.val(result);
      };
      fileReader.readAsText(this.files[0]);
    });

  });
</script>

<div class="co-info-topbox">
  <i class="material-icons">info</i>
  <?php print _txt('ct.co_ssh_key_provisioner_target.1'); ?>
</div>
<ul id="<?php print $this->action; ?>_ssh_key_provisioner_target" class="fields form-list">
  <li>
    <div class="field-name">
      <div class="field-title"><?php print _txt('pl.ssh_key_provisioner.serverurl'); ?>
        <span class="required">*</span>
      </div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('server_url', array('size' => 50, 'maxlength'=>'256')) : filter_var($ssh_key_provisioner[0]['CoSshKeyProvisionerTarget']['server_url'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.ssh_key_provisioner.port'); ?>
      </div>
      <div class="field-desc"><?php print _txt('pl.ssh_key_provisioner.port.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('port', array('type' => 'number')) : filter_var($ssh_key_provisioner[0]['CoSshKeyProvisionerTarget']['port'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.ssh_key_provisioner.remote_path'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.ssh_key_provisioner.remote_path.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('remote_path', array('size' => 50, 'maxlength'=>'256')) : filter_var($ssh_key_provisioner[0]['CoSshKeyProvisionerTarget']['remote_path'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.ssh_key_provisioner.remote_user'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.ssh_key_provisioner.remote_user.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('remote_user', array('size' => 50, 'maxlength'=>'24')) : filter_var($ssh_key_provisioner[0]['CoSshKeyProvisionerTarget']['remote_user'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.ssh_key_provisioner.private_key'); ?>
        <span class="required">*</span>
        <label for="private_key" class="submit-button mdl-button mdl-js-button mdl-button--raised mdl-button--colored mdl-js-ripple-effect">
          <input type="file" id="private_key" style="display: none"/>
          Load
        </label>
      </div>
      <div class="field-desc"><?php print _txt('pl.ssh_key_provisioner.private_key.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('private_key', array('size' => 50, 'maxlength'=>'2048', 'readonly' => 'readonly')) : filter_var($ssh_key_provisioner[0]['CoSshKeyProvisionerTarget']['private_key'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.ssh_key_provisioner.public_key'); ?>
        <span class="required">*</span>
        <label for="public_key" class="submit-button mdl-button mdl-js-button mdl-button--raised mdl-button--colored mdl-js-ripple-effect">
          <input type="file" id="public_key" style="display: none"/>
          Load
        </label>
      </div>
      <div class="field-desc"><?php print _txt('pl.ssh_key_provisioner.public_key.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('public_key', array('size' => 50, 'maxlength'=>'512', 'readonly' => 'readonly')) : filter_var($ssh_key_provisioner[0]['CoSshKeyProvisionerTarget']['public_key'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <?php if($e): ?>
    <li class="fields-submit">
      <div class="field-name">
        <span class="required"><?php print _txt('fd.req'); ?></span>
      </div>
      <div class="field-info">
        <?php print $this->Form->submit($submit_label); ?>
      </div>
    </li>
  <?php endif; ?>
</ul>

