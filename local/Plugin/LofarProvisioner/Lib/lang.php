<?php

global $cm_lang, $cm_texts;

// When localizing, the number in format specifications (eg: %1$s) indicates the argument
// position as passed to _txt.  This can be used to process the arguments in
// a different order than they were passed.

$cm_lofar_provisioner_texts['en_US'] = array(
  // Titles, per-controller
  'ct.co_lofar_provisioner_targets.1'   => 'Lofar Provisioner Target',
  'ct.co_lofar_provisioner_targets.pl'  => 'Lofar Provisioner Targets',

  // action
  'op.lofar_provisioner.save'              => 'Save',
  'op.lofar_provisioner.send.ok'           => 'Email Sent OK',
  'op.lofar_provisioner.send.failed'       => 'Email Sent Failed',

  // Error messages
  'er.lofar_provisioner.connect'            => 'Failed to connect',
  'er.lofar_provisioner.notfound'           => 'Entry not found',


  // fields
  'fd.lofar_provisioner.req'               => '* denotes required field',

  // plugin
  'pl.lofar_provisioner.enable'             => 'Enabled',
  'pl.lofar_provisioner.email_list'        => 'Email List',
  'pl.lofar_provisioner.email_list.desc'    => 'Comma Seperated(csv) List of recipient emails',
  'pl.lofar_provisioner.co_admin'           => 'CO Admin',
  'pl.lofar_provisioner.co_admin.desc'      => 'CO Administrator Enable notification',
  'pl.lofar_provisioner.info'               => 'List of recipients to inform'

  // Success messages
);
