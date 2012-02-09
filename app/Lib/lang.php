<?php
/**
 * COmanage Registry Default Layout
 *
 * Copyright (C) 2011-12 University Corporation for Advanced Internet Development, Inc.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright     Copyright (C) 2011-12 University Corporation for Advanced Internet Development, Inc.
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry
 * @since         COmanage Registry v0.1
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @version       $Id$
 */
  
global $cm_lang, $cm_texts;

// XXX move this to a master config
$cm_lang = "en_US";

// When localizing, the number in format specifications (eg: %1$s) indicates the argument
// position as passed to _txt.  This can be used to process the arguments in
// a different order than they were passed.

$cm_texts['en_US'] = array(
  // Application name
  'coordinate' =>     'COmanage Registry',
  
  // What a CO is called (abbreviated)
  'co' =>             'CO',
  'cos' =>            'COs',
  
  // What an Org is called
  'org' =>            'Organization',
  
  // Authnz
  'au.not' =>         'Not Logged In',
  
  // COs Controllers
  'co.cm.gradmin' =>  'COmanage Platform Administrators',
  'co.cm.desc' =>     'COmanage Gears Internal CO',
  'co.init' =>        'No COs found, initial CO created',
  'co.nomember' =>    'You are not a member of any COs',
  'co.select' =>      'Select the CO you wish to work with.',
  
  // Titles, per-controller
  'ct.addresses.1' =>           'Address',
  'ct.addresses.pl' =>          'Addresses',
  'ct.cmp_enrollment_configurations.1'  => 'CMP Enrollment Configuration',
  'ct.cmp_enrollment_configurations.pl' => 'CMP Enrollment Configurations',
  'ct.co_enrollment_attributes.1'  => 'CO Enrollment Attribute',
  'ct.co_enrollment_attributes.pl' => 'CO Enrollment Attributes',
  'ct.co_enrollment_flows.1'  => 'CO Enrollment Flow',
  'ct.co_enrollment_flows.pl' => 'CO Enrollment Flows',
  'ct.co_extended_attributes.1'  => 'Extended Attribute',
  'ct.co_extended_attributes.pl' => 'Extended Attributes',
  'ct.co_group_members.1' =>    'Group Member',
  'ct.co_group_members.pl' =>   'Group Members',
  'ct.co_groups.1' =>           'Group',
  'ct.co_groups.pl' =>          'Groups',
  'ct.co_invites.1' =>          'Invite',
  'ct.co_invites.pl' =>         'Invites',
  'ct.co_nsf_demographics.1'  => 'NSF Demographic',
  'ct.co_nsf_demographics.pl' => 'NSF Demographics',
  'ct.co_people.1' =>           'CO Person',
  'ct.co_people.pl' =>          'CO People',
  'ct.co_person_roles.1' =>     'CO Person Role',
  'ct.co_person_roles.pl' =>    'CO Person Roles',
  'ct.cos.1' =>                 'CO',
  'ct.cos.pl' =>                'COs',
  'ct.cous.1' =>                'COU',
  'ct.cous.pl' =>               'COUs',
  'ct.email_addresses.1' =>     'Email Address',
  'ct.email_addresses.pl' =>    'Email Addresses',
  'ct.identifiers.1' =>         'Identifier',
  'ct.identifiers.pl' =>        'Identifiers',
  'ct.org_identities.1' =>      'Organizational Identity',
  'ct.org_identities.pl' =>     'Organizational Identities',
  'ct.organizations.1' =>       'Organization',
  'ct.organizations.pl' =>      'Organizations',
  'ct.telephone_numbers.1' =>   'Telephone Number',
  'ct.telephone_numbers.pl' =>  'Telephone Numbers',

  // Enumerations, corresponding to enum.php
  'en.admin' =>       array(AdministratorEnum::NoAdmin => 'None',
                            AdministratorEnum::CoAdmin => 'CO Admin',
                            AdministratorEnum::CoOrCouAdmin => 'CO or COU Admin'),
  
  'en.affil' =>       array(AffiliationEnum::Faculty       => 'Faculty',
                            AffiliationEnum::Student       => 'Student',
                            AffiliationEnum::Staff         => 'Staff',
                            AffiliationEnum::Alum          => 'Alum',
                            AffiliationEnum::Member        => 'Member',
                            AffiliationEnum::Affiliate     => 'Affiliate',
                            AffiliationEnum::Employee      => 'Employee',
                            AffiliationEnum::LibraryWalkIn => 'Library Walk-In'),

  'en.contact' =>     array(ContactEnum::Fax => 'Fax',
                            ContactEnum::Home => 'Home',
                            ContactEnum::Mobile => 'Mobile',
                            ContactEnum::Office => 'Office',
                            ContactEnum::Postal => 'Postal',
                            ContactEnum::Forwarding => 'Forwarding'),
  
  // Sub-type contacts since some aren't globally applicable
  'en.contact.address' =>  array(ContactEnum::Home => 'Home',
                                 ContactEnum::Office => 'Office',
                                 ContactEnum::Postal => 'Postal',
                                 ContactEnum::Forwarding => 'Forwarding'),
  
  'en.contact.mail' =>     array(ContactEnum::Home => 'Home',
                                 ContactEnum::Mobile => 'Mobile',
                                 ContactEnum::Office => 'Office'),
  
  'en.contact.phone' => array(ContactEnum::Fax => 'Fax',
                              ContactEnum::Home => 'Home',
                              ContactEnum::Mobile => 'Mobile',
                              ContactEnum::Office => 'Office'),
  
  'en.extattr' =>     array('INTEGER' => 'Integer',
                            'TIMESTAMP' => 'Timestamp',
                            'VARCHAR(32)' => 'String (32)'),

  'en.identifier' =>  array(IdentifierEnum::ePPN => 'ePPN',
                            IdentifierEnum::ePTID => 'ePTID',
                            IdentifierEnum::Mail => 'Mail',
                            IdentifierEnum::OpenID => 'OpenID',
                            IdentifierEnum::UID => 'UID'),

  'en.name' =>        array(NameEnum::Author => 'Author',
                            NameEnum::FKA => 'FKA',
                            NameEnum::Official => 'Official',
                            NameEnum::Preferred => 'Preferred'),

  'en.required' =>    array(RequiredEnum::Required => 'Required',
                            RequiredEnum::Optional => 'Optional',
                            RequiredEnum::NotPermitted => 'Not Permitted'),

  'en.status' =>      array('A' => 'Active',
                            'D' => 'Deleted',
                            'I' => 'Invited',
                            'P' => 'Pending',
                            'S' => 'Suspended',
                            'X' => 'Declined'),

  // Demographics
  'en.nsf.gender' =>       array(NSFGenderEnum::Male   => 'Male',
                                 NSFGenderEnum::Female => 'Female'),

  'en.nsf.citizen' =>      array(NSFCitizenshipEnum::USCitizen           => 'U.S. Citizen',
                                 NSFCitizenshipEnum::USPermanentResident => 'U.S. Permanent Resident',
                                 NSFCitizenshipEnum::Other               => 'Other non-U.S. Citizen'),

  'en.nsf.ethnic' =>       array(NSFEthnicityEnum::Hispanic    => 'Hispanic or Latino',
                                 NSFEthnicityEnum::NotHispanic => 'Not Hispanic or Latino'),

  'en.nsf.ethnic.desc' =>       array(NSFEthnicityEnum::Hispanic => 'A person of Mexican, Puerto Rican, Cuban, South or Central American, or other Spanish culture or origin, regardless of race',),


  'en.nsf.race' =>         array(NSFRaceEnum::Asian          => 'Asian',
                                 NSFRaceEnum::AmericanIndian => 'American Indian or Alaskan Native',
                                 NSFRaceEnum::Black          => 'Black or African American',
                                 NSFRaceEnum::NativeHawaiian => 'Native Hawaiian or Pacific Islander',
                                 NSFRaceEnum::White          => 'White'
                                ),

  'en.nsf.race.desc' =>         array(NSFRaceEnum::Asian          => 'A person having origins in any of the original peoples of the Far East, Southeast Asia, or the Indian subcontinent including, for example, Cambodia, China, India, Japan, Korea, Malaysia, Pakistan, the Philippine Islands, Thailand, and Vietnam',
                                      NSFRaceEnum::AmericanIndian => 'A person having origins in any of the original peoples of North and South America (including Central America), and who maintains tribal affiliation or community attachment',
                                      NSFRaceEnum::Black          => 'A person having origins in any of the black racial groups of Africa',
                                      NSFRaceEnum::NativeHawaiian => 'A person having origins in any of the original peoples of Hawaii, Guan, Samoa, or other Pacific Islands',
                                      NSFRaceEnum::White          => 'A person having origins in any of the original peoples of Europe, the Middle East, or North Africa'),

  'en.nsf.disab' =>        array(NSFDisabilityEnum::Hearing  => 'Hearing Impaired',
                                 NSFDisabilityEnum::Visual   => 'Visual Impaired',
                                 NSFDisabilityEnum::Mobility => 'Mobility/Orthopedic Impairment',
                                 NSFDisabilityEnum::Other    => 'Other Impairment'),

  // Errors
  'er.co.cm.edit' =>  'Cannot edit COmanage CO',
  'er.co.cm.rm' =>    'Cannot remove COmanage CO',
  'er.co.exists' =>   'A CO named "%1$s" already exists',
  'er.co.gr.admin' => 'CO created, but failed to create initial admin group',
  'er.co.none' =>     'No COs found (did you run setup.php?)',
  'er.co.unk' =>      'Unknown CO',
  'er.co.unk-a' =>    'Unknown CO "%1$s"',
  'er.comember' =>    '%1$s is a member of one or more COs (%2$s) and cannot be removed.',
  'er.coumember' =>   '%1$s is a member of one or more COUs that you do not manage (%2$s) and cannot be removed.',
  'er.cop.member' =>  '%1$s is already a member of %2$s and cannot be added again. However, an additional role may be added.',
  'er.cop.unk' =>     'Unknown CO Person',
  'er.cop.unk-a' =>   'Unknown CO Person "%1$s"',
  // XXX These should become er.copr (or tossed if not needed)
  'er.cop.nf' =>      'CO Person Role %1$s Not Found',
  'er.copr.none' =>   'CO Person Role Not Provided',
  'er.cou.copr' =>    'There are still one or more CO person role records in the COU %1$s, and so it cannot be deleted.',
  'er.cou.child' =>   'COUs with children can not be deleted',
  'er.cou.cycle' =>   'Parent is a descendant.  Cycles are not permitted.',
  'er.cou.exists' =>  'A COU named "%1$s" already exists',
  'er.cou.gr.admin' => 'COU created, but failed to create initial admin group',
  'er.cou.sameco' =>  'COUs must be in the same CO',
  'er.delete' =>      'Delete Failed',
  'er.deleted-a' =>   'Deleted "%1$s"',
  'er.db.connect' =>  'Failed to connect to database: %1$s',
  'er.db.schema' =>   'Possibly failed to update database schema',
  'er.ea.alter' =>    'Failed to alter table for attribute',
  'er.ea.exists' =>   'An attribute named "%1$s" already exists within the CO',
  'er.ea.index' =>    'Failed to update index for attribute',
  'er.ea.table' =>    'Failed to create CO Extended Attribute table',
  'er.ea.table.d' =>  'Failed to drop CO Extended Attribute table',
  'er.efcf.init' =>   'Failed to set up initial CMP Enrollment Configuration',
  'er.fields' =>      'Please recheck your submission',
  'er.gr.exists' =>   'A group named "%1$s" already exists within the CO',
  'er.gr.init' =>     'Group created, but failed to set initial owner/member',
  'er.gr.nf' =>       'Graup %1$s Not Found',
  'er.gr.res' =>      'Groups named "admin" or prefixed "admin:" are reserved',
  'er.grm.already' => 'CO Person %1$s is already a member of group %2$s',
  'er.grm.none' =>    'No group memberships to add',
  'er.inv.exp' =>     'Invitation Expired',
  'er.inv.nf' =>      'Invitation Not Found',
  'er.nd.already'  => 'NSF Demographic data already exists for this person',
  'er.notfound' =>    '%1$s "%2$s" Not Found',
  'er.notprov' =>     'Not Provided',
  'er.notprov.id' =>  '%1$s ID Not Provided',
  'er.person.noex' => 'Person does not exist',
  'er.person.none' => 'No CO Person, CO Person Role, or Org Identity specified',
  'er.reply.unk' =>   'Unknown Reply',
  'er.timeout' =>     'Your session has expired. Please login again.',
  'er.orgp.nomail' => '%1$s (Org Identity %2$s) has no known email address.<br />Add an email address and then resend the invitation.',
  'er.orgp.pool' =>   'Failed to pool organizational identities',
  'er.orgp.unk-a' =>  'Unknown Org Identity "%1$s"',
  'er.orgp.unpool' => 'Failed to unpool organizational identities',

  // Fields
  'fd.actions' =>     'Actions',
  'fd.address' =>     'Address',
  'fd.address.1' =>   'Address Line 1',
  'fd.address.2' =>   'Address Line 2',
  'fd.admin' =>       'Administrator',
  'fd.affiliation' => 'Affiliation',
  'fd.an.desc' =>     'Alphanumeric characters only',
  'fd.attribute' =>   'Attribute',
  'fd.attr.ldap' =>   'LDAP Name',
  'fd.attr.saml' =>   'SAML Name',
  'fd.attrs.cop' =>   'Person Attributes',
  'fd.attrs.copr' =>  'Role Attributes',
  'fd.attrs.org' =>   'Organizational Attributes',
  'fd.city' =>        'City',
  'fd.closed' =>      'Closed',
  'fd.cou' =>         'COU',
  'fd.country' =>     'Country',
  // Demographics fields
  'fd.de.persid'  =>  'Person ID',
  'fd.de.gender'  =>  'Gender',
  'fd.de.citizen' =>  'Citizenship',
  'fd.de.ethnic'  =>  'Ethnicity',
  'fd.de.race'    =>  'Race',
  'fd.de.disab'   =>  'Disability',
  'fd.desc' =>        'Description',
  'fd.directory' =>   'Directory',
  'fd.domain' =>      'Domain',
  // Enrollment configuration fields
  'fd.ea.desc' =>     'Description',
  'fd.ea.desc.desc' => 'Descriptive text to be displayed when prompting for this attribute (like this text you\'re reading now)',
  'fd.ea.label' =>    'Label',
  'fd.ea.label.desc' => 'The label to be displayed when prompting for this attribute as part of the enrollment process',
  'fd.ea.order' =>    'Order',
  'fd.ea.order.desc' => 'The order in which this attribute will be presented (leave blank to append at the end of the current attributes)',
  'fd.ef.ae' =>       'Enable Administrator Enrollment',
  'fd.ef.ae.desc' =>  'If enabled, allow the specified type(s) of administrators to enroll organizational identities to the platform',
  'fd.ef.aea' =>      'Require Authentication For Administrator Enrollment',
  'fd.ef.aea.desc' => 'If administrator enrollment is enabled, require enrollees to authenticate to the platform in order to complete their enrollment',
  'fd.ef.aee' =>      'Require Email Confirmation For Administrator Enrollment',
  'fd.ef.aee.desc' => 'If administrator enrollment is enabled, require enrollees to confirm their email address in order to complete their enrollment',
  'fd.ef.appr' =>     'Require Approval For Enrollment',
  'fd.ef.appr.desc' => 'If administrator approval is required, a member of the appropriate <tt>admin.approvers</tt> group must approve the enrollment',
  'fd.ef.cf.cmp' =>   'Platform Enrollment Configuration',
  'fd.ef.epx' =>      'Early Provisioning Executable',
  'fd.ef.epx.desc' => '(Need for this TBD)',
  'fd.ef.ldap' =>     'Enable LDAP Attribute Retrieval',
  'fd.ef.ldap.desc' => 'If the enrollee is affiliated with an organization with a known LDAP server, query the LDAP server for authoritative attributes',
  'fd.ef.noa' =>      'Notify On Active Status',
  'fd.ef.noa.desc' => 'Email address to notify upon status being set to active',
  'fd.ef.noep' =>     'Notify On Early Provisioning',
  'fd.ef.noep.desc' => 'Email address to notify upon execution of early provisioning',
  'fd.ef.nop' =>      'Notify On Provisioning',
  'fd.ef.nop.desc' => 'Email address to notify upon execution of provisioning',
  'fd.ef.pool' =>     'Pool Organizational Identities',
  'fd.ef.pool.desc' => 'If pooling is enabled, organizational identities -- as well as any attributes released by IdPs -- will be made available to all COs, regardless of which CO enrollment flows added them',
  'fd.ef.pool.on.warn' => 'Enabling pooling will delete any existing links between organizational identities and the COs which created them (when you click Save). This operation cannot be undone.',
  'fd.ef.pool.off.warn' => 'Disabling pooling will duplicate any organizational identities used by more than one CO (when you click Save). This operation cannot be undone.',
  'fd.ef.px' =>       'Provisioning Executable',
  'fd.ef.px.desc' =>  'Executable to call to initiate user provisioning',
  'fd.ef.saml' =>     'Enable SAML Attribute Extraction',
  'fd.ef.saml.desc' => 'If the enrollee is authenticated via a SAML IdP with attributes released, examine the SAML assertion for authoritative attributes',
  'fd.ef.se' =>       'Enable Self Enrollment',
  'fd.ef.se.desc' =>  'If enabled, allow enrollees to begin the enrollment process themselves',
  'fd.ef.sea' =>      'Require Authentication For Self Enrollment',
  'fd.ef.sea.desc' => 'If self enrollment is enabled, require enrollees who are self-enrolling to authenticate to the platform',
  // (End enrollment configuration fields)
  'fd.false' =>       'False',
  'fd.group.desc.adm' => '%1$s Administrators',
  'fd.group.mem' =>   'Member',
  'fd.group.memin' => 'membership in "%1$s"',
  'fd.group.own' =>   'Owner',
  'fd.groups' =>      'Groups',
  'fd.id' =>          'Identifier',
  'fd.ids' =>         'Identifiers',
  'fd.index' =>       'Index',
  'fd.login' =>       'Login',
  'fd.login.desc' =>  'Allow this identifier to login to COordinate',
  'fd.mail' =>        'Email',
  'fd.members' =>     'Members',
  'fd.name' =>        'Name',
  'fd.name.d' =>      'Display Name',
  'fd.name.f' =>      'Family Name',
  'fd.name.g' =>      'Given Name',
  'fd.name.h' =>      'Honorific',
  'fd.name.h.desc' => '(Dr, Hon, etc)',
  'fd.name.m' =>      'Middle Name',
  'fd.name.s' =>      'Suffix',
  'fd.name.s.desc' => '(Jr, III, etc)',
  'fd.no' =>          'No',
  'fd.o' =>           'Organization',
  'fd.open' =>        'Open',
  'fd.orgid' =>       'Organization ID',
  'fd.ou' =>          'Department',
  'fd.parent' =>      'Parent COU',
  'fd.perms' =>       'Permissions',
  'fd.phone' =>       'Phone',
  'fd.postal' =>      'ZIP/Postal Code',
  'fd.req' =>         '* denotes required field',
  'fd.required' =>    'Required',
  'fd.roles' =>       'Roles',
  'fd.searchbase' =>  'Search Base',
  'fd.sponsor' =>     'Sponsor',
  'fd.sponsor.desc' =>'(for continued membership)',
  'fd.state' =>       'State',
  'fd.status' =>      'Status',
  'fd.title' =>       'Title',
  'fd.true' =>        'True',
  'fd.type' =>        'Type',
  'fd.type.warn' =>   'After an extended attribute is created, its type may not be changed',
  'fd.untitled' =>    'Untitled',
  'fd.valid.f' =>     'Valid From',
  'fd.valid.f.desc' =>  '(leave blank for immediate validity)',
  'fd.valid.u' =>     'Valid Through',
  'fd.valid.u.desc' =>  '(leave blank for indefinite validity)',
  'fd.yes' =>         'Yes',

  // Operations
  'op.add' =>         'Add',
  'op.add-a' =>       'Add "%1$s"',
  'op.add.new' =>     'Add a New %1$s',
  'op.back' =>        'Back',
  'op.cancel' =>      'Cancel',
  'op.compare' =>     'Compare',
  'op.db.ok' =>       'Database schema update successful',
  'op.delete' =>      'Delete',
  'op.delete.ok' =>   'Are you sure you wish to remove "%1$s"? This action cannot be undone.',
  'op.edit' =>        'Edit',
  'op.edit.ea' =>     'Edit Enrollment Attributes',
  'op.edit-a' =>      'Edit "%1$s"',
  'op.edit-f' =>      'Edit %1$s for %2$s',
  'op.find.inv' =>    'Find a Person to Invite to %1$s',
  'op.gr.memadd' =>   'Add Person %1$s to Group',
  'op.grm.add' =>     'Add Person to %1$s Group %2$s',
  'op.inv' =>         'Invite',
  'op.inv-a' =>       'Invite %1$s',
  'op.inv-t' =>       'Invite %1$s to %2$s',
  'op.inv.reply' =>   'Reply to Invitation',
  'op.inv.resend' =>  'Resend Invite',
  'op.inv.send' =>    'Send Invite',
  'op.menu' =>        'Menu',
  'op.login' =>       'Login',
  'op.logout' =>      'Logout',
  'op.ok' =>          'OK',
  'op.proceed.ok' =>  'Are you sure you wish to proceed?',
  'op.remove' =>      'Remove',
  'op.save' =>        'Save',
  'op.select' =>      'Select',
  'op.select-a' =>    'Select a %1$s',
  'op.view' =>        'View',
  'op.view-a' =>      'View "%1$s"',
  
  // Results
  'rs.added' =>       'Added',
  'rs.added-a' =>     '"%1$s" Added',
  'rs.inv.conf' =>    'Invitation Confirmed',
  'rs.inv.dec' =>     'Invitation Declined',
  'rs.updated' =>     '"%1$s" Updated',
  
  // Setup
  
  'se.cf.admin.given' =>  'Enter administrator\'s given name',
  'se.cf.admin.sn' =>     'Enter administrator\'s family name',
  'se.cf.admin.user' =>   'Enter administrator\'s login username',
  'se.db.co' =>           'Creating COmanage CO',
  'se.db.cop' =>          'Adding Org Identity to CO',
  'se.db.group' =>        'Creating COmanage admin group',
  'se.db.op' =>           'Adding initial Org Identity',
  'se.done' =>            'Setup complete',
  'se.users.view' =>      'Creating users view'
);

/**
 * Render localized text
 *
 * @since  COmanage Registry 0.1
 * @param  string Index of message to render
 * @param  array Substitutions for variables within localized text
 * @param  integer If <key> represents an array, the index of the corresponding message
 * @return void
 */

function _txt($key, $vars=null, $index=null)
{
  global $cm_lang, $cm_texts;

  // XXX need to figure out how to pass arbitrary # of args to sprintf
  
  $s = (isset($index) ? $cm_texts[ $cm_lang ][$key][$index] : $cm_texts[ $cm_lang ][$key]);
  
  switch(count($vars))
  {
  case 1:
    return(sprintf($s, $vars[0]));
    break;
  case 2:
    return(sprintf($s, $vars[0], $vars[1]));
    break;
  case 3:
    return(sprintf($s, $vars[0], $vars[1], $vars[2]));
    break;
  case 4:
    return(sprintf($s, $vars[0], $vars[1], $vars[2], $vars[3]));
    break;
  default:
    return($s);
  }
}