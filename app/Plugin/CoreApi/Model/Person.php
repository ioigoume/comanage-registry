<?php
/**
 * COmanage Registry Core API Model
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
 * @since         COmanage Registry v4.0.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

App::uses("CoreApi", "CoreApi.Model");

class Person extends CoreApi {
  // Define class name for cake
  public $name = "Person";

  /**
   * Perform a CO Person Read API v1 request.
   *
   * @since  COmanage Registry v4.0.0
   * @param  integer $coId           CO ID
   * @param  string  $identifier     Identifier to search on
   * @param  string  $identifierType Identifier type
   * @return array                   Array of CO Person data
   * @throws InvalidArgumentException
   */

  public function readV1($coId, $identifier, $identifierType) {
    // First try to map the requested information to a CO Person record.
    // This is similar to CoPerson::idsForIdentifier, but that has some old
    // legacy code we want to avoid.

    $cop = $this->filterMetadataOutbound($this->pull($coId, $identifier, $identifierType), "CoPerson");

    return $cop;
  }

  /**
   * Pull a CO Person record, including associated models.
   *
   * @since  COmanage Registry v4.0.0
   * @param  integer $coId           CO ID
   * @param  string  $identifier     Identifier to query
   * @param  string  $identifierType Identifier type
   * @return array                   Array of CO Person data
   * @throws InvalidArgumentException
   * @todo This probably belongs in CoPerson.php
   */

  protected function pull($coId, $identifier, $identifierType) {
    $args = array();
    $args['conditions']['Identifier.identifier'] = $identifier;
    $args['conditions']['Identifier.type'] = $identifierType;
    $args['conditions']['Identifier.status'] = SuspendableStatusEnum::Active;
    $args['conditions']['CoPerson.co_id'] = $coId;
// We allow people of any status to be pulled, though maybe we could offer a filter
//    $args['conditions']['CoPerson.status'] = array(StatusEnum::Active, StatusEnum::GracePeriod);
    $args['joins'][0]['table'] = 'identifiers';
    $args['joins'][0]['alias'] = 'Identifier';
    $args['joins'][0]['type'] = 'INNER';
    $args['joins'][0]['conditions'][0] = 'Identifier.co_person_id=CoPerson.id';
    // While we're here pull the data we need
    $args['contain'] = array(
      'CoPersonRole' => array(
        'Address',
        'AdHocAttribute',
        'TelephoneNumber'
      ),
      'CoGroupMember',
      'CoOrgIdentityLink' => array(
        'OrgIdentity' => array(
          'Address',
          'AdHocAttribute',
          'EmailAddress',
          'Identifier',
          'Name',
          'TelephoneNumber',
          'Url'
        ),
      ),
      'EmailAddress',
      'Identifier',
      'Name',
      'Url'
    );

    // find('first') won't result in two records, though if identifier is not
    // unique it's non-deterministic as to which record we'll retrieve.

    $cop = $this->Co->CoPerson->find('first', $args);

    if(empty($cop)) {
      throw new InvalidArgumentException(_txt('er.notfound', array(_txt('ct.identifiers.1'), filter_var($identifier,FILTER_SANITIZE_SPECIAL_CHARS))));
    }

    // Promote OrgIdentity to top level. This interface doesn't permit relinking
    // identities, and in v5 CoOrgIdentityLink goes away anyway.

    if(!empty($cop['CoOrgIdentityLink'])) {
      foreach($cop['CoOrgIdentityLink'] as $link) {
        if(!empty($link['OrgIdentity'])) {
          $cop['OrgIdentity'][] = $link['OrgIdentity'];
        }
      }
    }

    unset($cop['CoOrgIdentityLink']);

    // We need to manually pull Authenticator and Cluster data.
    $cop = array_merge($cop, $this->Co->Authenticator->marshallProvisioningData($coId, $cop['CoPerson']['id']));
    $cop = array_merge($cop, $this->Co->Cluster->marshallProvisioningData($coId, $cop['CoPerson']['id'], false));

    return $cop;
  }
}