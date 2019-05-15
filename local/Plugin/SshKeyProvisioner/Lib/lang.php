<?php

global $cm_lang, $cm_texts;

// When localizing, the number in format specifications (eg: %1$s) indicates the argument
// position as passed to _txt.  This can be used to process the arguments in
// a different order than they were passed.

$cm_ssh_key_provisioner_texts['en_US'] = array(
  // Titles, per-controller
  'ct.co_ssh_key_provisioner_target.1'  => 'RCAuth MasterPortal SSH key provisioner',
  'ct.co_ssh_key_provisioner_targets.1'  => 'RCAuth MasterPortal SSH key provisioner',
  'ct.co_ssh_key_provisioner_targets.pl' => 'RCAuth MasterPortal SSH keys provisioner',

  // Error messages
  'er.ssh_key_provisioner.code'       => 'Error: Failed to exchange code for RCAUTH and access token: %1$s',
  'er.ssh_key_provisioner.search'     => 'Search request returned %1$s',
  'er.ssh_key_provisioner.server_access'  => 'Error: Provisioner\'s configuration is probably missing required data.',

  // Plugin texts
  'pl.ssh_key_provisioner.remote_user'       => 'Remote User',
  'pl.ssh_key_provisioner.remote_user.desc'  => 'User allowed to upload via sFTP at the Remote Server',
  'pl.ssh_key_provisioner.private_key'       => 'Private Key',
  'pl.ssh_key_provisioner.private_key.desc'  => 'Provide private RSA Key used to access SSH MasterPortal(id_rsa file)',
  'pl.ssh_key_provisioner.public_key'        => 'Public Key',
  'pl.ssh_key_provisioner.public_key.desc'   => 'Provide public RSA Key used to access SSH MasterPortal(id_rsa.pub file)',
  'pl.ssh_key_provisioner.remote_path'       => 'Remote Path',
  'pl.ssh_key_provisioner.remote_path.desc'  => 'Path to upload to sFTP',
  'pl.ssh_key_provisioner.linked'            => 'Obtained DN "%1$s" via authenticated OAuth flow',
  'pl.ssh_key_provisioner.serverurl'         => 'Remote Server Url',
  'pl.ssh_key_provisioner.port'              => 'sFTP Port',
  'pl.ssh_key_provisioner.port.desc'         => 'The port that the remote server exposes for sFTP',
  'pl.ssh_key_provisioner.sshkey'            => 'SSH Key',

  // Actions
  'op.ssh_key_provisioner.save'           => 'Save',
  'op.ssh_key_provisioner.upload.new'     =>  'Upload Private %1$s',
);
