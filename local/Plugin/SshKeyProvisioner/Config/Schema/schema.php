<!--Please note that schema generation in 2.x does not handle foreign key constraints.-->
<!--Console/cake schema create --file schema.php --path /srv/comanage/registry-current/local/Plugin/SshKeyProvisioner/Config/Schema-->
<?php
  class AppSchema extends CakeSchema
  {

    public function before($event = array())
    {
      return true;
    }

    public function after($event = array())
    {
    }

    public $co_ssh_key_provisioner_targets = array(
      'id' => array('type' => 'integer', 'autoIncrement' => true, 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'),
      'co_provisioning_target_id' => array('type' => 'integer', 'null' => false, 'length' => 10),
      'server_url' => array('type' => 'string', 'null' => true, 'length' => 256),
      'port' => array('type' => 'integer', 'null' => true, 'length' => 10),
      'remote_path' => array('type' => 'string', 'null' => true, 'length' => 256),
      'remote_user' => array('type' => 'string', 'null' => true, 'length' => 24),
      'private_key' => array('type' => 'string', 'null' => true, 'length' => 6000),
      'public_key' => array('type' => 'string', 'null' => true, 'length' => 2048),
      'created' => array('type' => 'datetime', 'null' => true),
      'modified' => array('type' => 'datetime', 'null' => true),
      'indexes' => array(
        'PRIMARY' => array('column' => 'id', 'unique' => 1),
        'cm_co_ssh_key_provisioner_targets_i1' => array('column' => 'co_provisioning_target_id', 'unique' => 1),
      )
    );
  }
