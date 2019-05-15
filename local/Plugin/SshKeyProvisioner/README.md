# SshKeyProvisioner
COmanage 3.1.x ssh key provisioner to RCAuth SSH Master portal

# Installation

In order to deploy do the following:
- Clone the plugin under `../comanage/local/Plugin`
- navigate to `../comanage/app` and execute the following command:
```bash
Console/cake schema create --file schema.php --path /path/to/registry-current/local/Plugin/SshKeyProvisioner/Config/Schema
```
- Enter the database and create the foreign key for the provisioning plugin
```sql
ALTER TABLE ONLY public.cm_co_ssh_key_provisioner_targets ADD CONSTRAINT cm_co_ssh_key_provisioner_targets_co_provisioning_target_id_fkey FOREIGN KEY (co_provisioning_target_id) REFERENCES public.cm_co_provisioning_targets(id);
```

# Configuration
- Remote Server Url, the domain of the remote masterportal server where we will send the ssh keys
- sFTP Port, the configured remote sFTP port. If left blank, 22 will be used
- Remote Path, the remote directory path we will use to upload the file created by the plugin
- Remote User, the remote user who has access to the remote path and accepts sFTP connection
- Private Key, the plugins private key
- Public Key, the plugins public key that we shared with the masterportal
