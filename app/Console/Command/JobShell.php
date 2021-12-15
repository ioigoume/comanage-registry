<?php
/**
 * COmanage Job Shell
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
 * @since         COmanage Registry v0.9.2
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

App::uses("PaginatedSqlIterator", "Lib");

class JobShell extends AppShell {
  var $uses = array('Co',
                    'CoExpirationPolicy',
                    'CoGroupMember',
                    'CoSetting',
                    'CoLocalization',
                    'OrgIdentitySource');

  public function getOptionParser() {
    $parser = parent::getOptionParser();

    $parser->addOption(
      'coid',
      array(
        'short' => 'c',
        'help' => _txt('sh.job.arg.coid'),
        'boolean' => false,
        'default' => false
      )
    )->epilog(_txt('sh.job.arg.epilog'));

    return $parser;
  }

  /**
   * Execute expirations for the specified CO
   *
   * @since  COmanage Registry v0.9.2
   * @param  Integer  $coId       CO ID
   */

  protected function expirations($coId) {
    // First see if expirations are enabled

    if($this->CoSetting->expirationEnabled($coId)) {
      $this->CoExpirationPolicy->executePolicies($coId, $this);
    } else {
      $this->out("- " . _txt('sh.job.xp.disabled'));
    }
  }

  /**
   * Execute group validity based reprovisioning for the specified CO
   *
   * @since  COmanage Registry v3.2.0
   * @param  Integer $coId CO ID
   */

  protected function groupValidity($coId) {
    // Pull the current window for reprovisioning

    $w = $this->CoSetting->getGroupValiditySyncWindow($coId);

    if($w > 0) {
      $this->CoGroupMember->reprovisionByValidity($coId, $w);
    } else {
      $this->out("- " . _txt('sh.job.gv.disabled'));
    }
  }

  /**
   * Provision for the specified CO
   *
   * @since  COmanage Registry v2.0.0
   * @param  Integer  $coId       CO ID
   */

  protected function provision($coId) {
    // First see if syncing is enabled

    if($this->CoSetting->provisionEnabled($coId)) {
      try {
        $this->OrgIdentitySource->syncAll($coId);
      }
      catch(Exception $e) {
        $this->out("- " . $e->getMessage());
      }
    } else {
      $this->out("- " . _txt('sh.job.provision.disabled'));
    }
  }

  /**
   * Sync Organizational Identity Sources for the specified CO
   *
   * @since  COmanage Registry v2.0.0
   * @param  Integer  $coId       CO ID
   */

  protected function syncOrgSources($coId) {
    // First see if syncing is enabled

    if($this->CoSetting->oisSyncEnabled($coId)) {
      try {
        $this->OrgIdentitySource->syncAll($coId);
      }
      catch(Exception $e) {
        $this->out("- " . $e->getMessage());
      }
    } else {
      $this->out("- " . _txt('sh.job.sync.ois.disabled'));
    }
  }

  function main() {
    // Run background / scheduled tasks. For now, we only run expirations so we don't
    // bother with any command line flags. This might need to change in the future,
    // especially if we want to run things on an other than nightly/daily schedule.

    _bootstrap_plugin_txt();

    // Load localizations
    $this->CoLocalization->load($this->params['coid']);

    // First, pull a set of COs

    $args = array();
    $args['conditions']['Co.status'] = SuspendableStatusEnum::Active;
    $args['contain'] = false;

    $cos = $this->Co->find('all', $args);

    // Now hand off to the various tasks
    $runAll = empty($this->args);
    $runCoId = $this->params['coid'];

    foreach($cos as $co) {
      if(!$runCoId || $runCoId == $co['Co']['id']) {
        if($runAll || in_array('groupvalidity', $this->args)) {
          $this->out(_txt('sh.job.gv', array($co['Co']['name'], $co['Co']['id'])));
          $this->groupValidity($co['Co']['id']);
        }

        if($runAll || in_array('expirations', $this->args)) {
          $this->out(_txt('sh.job.xp', array($co['Co']['name'], $co['Co']['id'])));
          $this->expirations($co['Co']['id']);
        }

        if($runAll || in_array('syncorgsources', $this->args)) {
          $this->out(_txt('sh.job.sync.ois', array($co['Co']['name'], $co['Co']['id'])));
          $this->syncOrgSources($co['Co']['id']);
        }

        // args[1] must be the provisioner target id
        if($runAll
           || in_array('provisioner', $this->args)
           || ( !empty($this->args[1]) && is_int($this->args[1])) ) {
          $this->out(_txt('sh.job.provision', array($co['Co']['name'], $co['Co']['id'])));
          // XXX List of all allowed Models
          $modelsAllowed = array('CoEmailList', 'CoGroup', 'CoPerson');
          $modelsTodo = array();
          if(!empty($this->args[2])) {
            $modelsTodo = explode(',', $this->args[2]);
            $modelsTodoObject = new ArrayObject($modelsTodo);
            // create a copy of the array
            $modelsTodo_validate = $modelsTodoObject->getArrayCopy();
            // Validate the arguments and drop the ones that do not qualify for provisioning
            foreach ($modelsTodo_validate as $key => $to_validate) {
              if(!in_array($to_validate, $modelsAllowed)) {
                unset($modelsTodo[$key]);
              }
            }
          }
          unset($modelsTodo_validate);
          // Execute provisions
          $this->provision_execute($co['Co']['id'], $this->args[1], $modelsTodo);
        }
      }
    }

    $this->out(_txt('sh.job.done'));
  }

  /**
   * Bulk provision for all Models in the provided list
   *
   * @param integer $coId        CO Id
   * @param integer $ptid        Provisioner Target ID
   * @param array $modelsTodo    List of models to provision
   * @return void
   */
  public function provision_execute($coId, $ptid, $modelsTodo) {
    // Track number of results
    $success = 0;
    $failed = 0;
    $modelCount = 0; // How many models we've worked with so far

    foreach($modelsTodo as $sModel) {
      // We need to manually assemble the model dependencies that ProvisionerBehavior
      // expects, since in Shell they aren't loaded automatically for some reason.
      $Model = ClassRegistry::init($sModel);
      $Model->Co = ClassRegistry::init('Co');
      $Model->Co->CoProvisioningTarget = ClassRegistry::init('CoProvisioningTarget');

      // Attach ProvisionerBehavior
      $Model->Behaviors->load('Provisioner');

      // What provisioning action are we requesting?
      $sAction = null;

      switch($sModel) {
        case 'CoEmailList':
          $sAction = ProvisioningActionEnum::CoEmailListReprovisionRequested;
          break;
        case 'CoGroup':
          $sAction = ProvisioningActionEnum::CoGroupReprovisionRequested;
          break;
        case 'CoPerson':
          $sAction = ProvisioningActionEnum::CoPersonReprovisionRequested;
          break;
        case 'CoService':
          $sAction = ProvisioningActionEnum::CoServiceReprovisionRequested;
          break;
        default:
          throw new LogicException('NOT IMPLEMENTED');
      }

       // Pull IDs of all objects of the requested type
       $iterator = new PaginatedSqlIterator(
         $Model,
         array($sModel.'.co_id' => $coId),
         array($sModel.'.id', $sModel.'.status'),
         false
       );
       $total = $iterator->count();

       // For calculating totals, what percent of models have we processed?
       $modelsDone = $modelCount/count($modelsTodo);
       $modelFraction = 1/count($modelsTodo);
       foreach($iterator as $v) {
         try {
           $Model->manualProvision($ptid,  // coProvisioningTargetId
       /* $coPersonId */           ($Model->name == 'CoPerson' ? $v[$sModel]['id'] : null),
       /* $coGroupId */            ($Model->name == 'CoGroup' ? $v[$sModel]['id'] : null),
       /* provisioningAction */    $sAction,
       /* coEmailListId */         ($Model->name == 'CoEmailList' ? $v[$sModel]['id'] : null),
       /* CoGroupMemberId */       null
                                   );
           $success++;
         } catch (Exception $e) {
           $this->out(_txt('sh.job.provision.failed', array($sModel)));
           $this->out(var_export($e, true),  1, Shell::CODE_ERROR);
           $failed++;
         }

         // If we're working with multiple models, this calculation is a bit off
         // since we don't pull all records at once, just the models that we're
         // currently working with.
         $pctDone = ($modelsDone * 100)
           +
           ($modelFraction * ((($success + $failed) * 100)/$total));
         $this->out(_txt('sh.job.provision.percent', array($pctDone)));
       }

      $modelCount++;
    }
  }
}
