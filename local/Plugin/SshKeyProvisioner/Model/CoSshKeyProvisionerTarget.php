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
App::uses('HttpSocket', 'Network/Http');

/**
 * Class CoSshKeyProvisionerTarget
 */
class CoSshKeyProvisionerTarget extends CoProvisionerPluginTarget {
  // Define class name for cake
  public $name = "CoSshKeyProvisionerTarget";

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
    'server_url' => array(
      'rule' => 'notBlank',
      'required' => true,
      'allowEmpty' => false
    ),
    'port' => array(
      'rule' => array('range', 1, 65535),
      'message' => 'Please enter value from 1-65535',
      'required' => false,
      'allowEmpty' => true
    ),
    'remote_path' => array(
      'rule' => 'notBlank',
      'required' => false,
      'allowEmpty' => true
    ),
    'remote_user' => array(
      'rule' => 'notBlank',
      'required' => false,
      'allowEmpty' => true
    ),
    'private_key' => array(
      'rule' => 'notBlank',
      'required' => false,
      'allowEmpty' => true
    ),
    'public_key' => array(
      'rule' => 'notBlank',
      'required' => false,
      'allowEmpty' => true
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
      case ProvisioningActionEnum::CoPersonUpdated:
        $this->log(get_class($this)."::{$fn}:: Person Updated", LOG_DEBUG);
        // Update the list of ssh keys the SSH Master Portal
        $status = $this->updateSshRemoteFile($coProvisioningTargetData);
//        if(strpos( strtolower($status), 'error' ) == true ){
//          return false;
//        }
        break;
      case ProvisioningActionEnum::CoPersonDeleted:
        // When deleted remove all the entries in the file by epuid
        $this->log(get_class($this)."::{$fn}:: Person deleted", LOG_DEBUG);
        $status = $this->updateSshRemoteFile($coProvisioningTargetData);
//        if(strpos( strtolower($status), 'error' ) == true ){
//          return false;
//        }
        break;
      default:
        // Log noop and fall through.
        $this->log("{$fn}::Provisioning action $op not allowed/implemented", LOG_DEBUG);
      }

    return true;
  }

  /**
   * @param $sftpServer
   * @param $sftpRemoteDir
   * @param $dataFile
   * @param $filesize
   */
  public function do_curl_sftp($sftpServer, $sftpRemoteDir, $dataFile, $filesize, $remoteFilename, $remote_user, $private_key, $public_key){
    $fn = "do_curl_sftp";
    $this->log(get_class($this)."::{$fn}::@", LOG_DEBUG);

    $tmp_private = tempnam("/tmp", "private_key_tmpfile");
    $handle_private = fopen($tmp_private, "w");
    fwrite($handle_private, $private_key);
    fclose($handle_private);


    $tmp_public = tempnam("/tmp", "public_key_tmpfile");
    $handle_public = fopen($tmp_public, "w");
    fwrite($handle_public, $public_key);
    fclose($handle_public);
    chmod($tmp_public, 0644);



    if ($dataFile) {
      $connect_str = 'sftp://' . $sftpServer . '/' . $sftpRemoteDir . '/' . $remoteFilename;
      $this->log(get_class($this) . "::{$fn}::connect string => " . $connect_str, LOG_DEBUG);
      $ch = curl_init($connect_str);
      curl_setopt($ch, CURLOPT_USERNAME, $remote_user);
      curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
      //curl_setopt($ch, CURLOPT_SSH_PUBLIC_KEYFILE, '/var/www/.ssh/id_rsa.pub');
      //curl_setopt($ch, CURLOPT_SSH_PRIVATE_KEYFILE, '/var/www/.ssh/id_rsa');
      curl_setopt($ch, CURLOPT_SSH_PUBLIC_KEYFILE, $tmp_public);
      curl_setopt($ch, CURLOPT_SSH_PRIVATE_KEYFILE, $tmp_private);
      curl_setopt($ch, CURLOPT_SSH_AUTH_TYPES, CURLSSH_AUTH_PUBLICKEY);
      curl_setopt($ch, CURLOPT_UPLOAD, true);
      curl_setopt($ch, CURLOPT_INFILE, $dataFile);
      curl_setopt($ch, CURLOPT_INFILESIZE, $filesize);
      curl_setopt($ch, CURLOPT_VERBOSE, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $verbose = fopen('php://temp', 'w+');
      curl_setopt($ch, CURLOPT_STDERR, $verbose);

      $response = curl_exec($ch);
      $error = curl_error($ch);
      curl_close($ch);

      rewind($verbose);
      $verboseLog = stream_get_contents($verbose);
      $this->log(get_class($this) . "::{$fn}::Verbose information:=> " . $verboseLog, LOG_DEBUG);
      $this->log(get_class($this) . "::{$fn}::Error information:=> " . $error, LOG_DEBUG);

      // Unlink the temporary files
      unlink($tmp_private);
      unlink($tmp_public);
    }

  }

  /**
   *
   */
  public function updateSshRemoteFile($coProvisioningTargetData){
    $fn = "createSshStr";
    $this->log(get_class($this)."::{$fn}::@", LOG_DEBUG);
    $str = "";
    $tmp_key_list = tmpfile();


    $strQuery = "SELECT ident.identifier, ssh.type, ssh.skey, ssh.comment"
      . " FROM cm_ssh_keys AS ssh"
      . " INNER JOIN cm_identifiers AS ident"
      . " ON ssh.co_person_id=ident.co_person_id"
      . " WHERE ssh.ssh_key_id IS NULL"
      . " AND NOT ssh.deleted"
      . " AND ident.identifier_id IS NULL"
      . " AND NOT ident.deleted"
      . " ORDER BY ident.identifier, ssh.skey";

    $responce = $this->query($strQuery);
    if(isset($responce) && count($responce) > 0){
      $str = "";
      foreach($responce as $key){
        $key_type = constant("SshKeyProvisionerEnum::{$key[0]['type']}");
        $str .= $key[0]['identifier'] . " " . $key_type . " " . $key[0]['skey'];
        $str .= empty($key[0]['comment']) ? "\n" : " " . $key[0]['comment'] . "\n";
      }
      $this->log(get_class($this)."::{$fn}::string for file => ".$str, LOG_DEBUG);
    }

    $server = $coProvisioningTargetData['CoSshKeyProvisionerTarget']['server_url'];
    $remote_path = $coProvisioningTargetData['CoSshKeyProvisionerTarget']['remote_path'];
    $remote_user = $coProvisioningTargetData['CoSshKeyProvisionerTarget']['remote_user'];
    $private_key = ($coProvisioningTargetData['CoSshKeyProvisionerTarget']['private_key'] !=null) ? base64_decode($coProvisioningTargetData['CoSshKeyProvisionerTarget']['private_key']) : null;
    $public_key = ($coProvisioningTargetData['CoSshKeyProvisionerTarget']['public_key'] != null) ? base64_decode($coProvisioningTargetData['CoSshKeyProvisionerTarget']['public_key']) : null;
    $port = ($coProvisioningTargetData['CoSshKeyProvisionerTarget']['port']!=null) ? $coProvisioningTargetData['CoSshKeyProvisionerTarget']['port'] : 22;

    if($server == null
       || $remote_user == null
       || $remote_path == null
       || $private_key == null
       || $public_key == null
       ||$port == null ){
      throw new RuntimeException(_txt('er.ssh_key_provisioner.server_access'));
      return _txt('er.ssh_key_provisioner.server_access');
    }

    if($str != ""){
      // Add str to tmp file and rewind to the beggining
      fwrite($tmp_key_list, $str);
      fseek($tmp_key_list, 0);
      $filesize = strlen($str);
      // Pass the file descriptor to the function
      $this->do_curl_sftp($server, $remote_path, $tmp_key_list, $filesize, "ssh_key_list.txt", $remote_user, $private_key, $public_key);
      // Close the temporary file
      fclose($tmp_key_list);
    }

  }
}
