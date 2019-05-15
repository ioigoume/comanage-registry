-- noinspection SqlNoDataSourceInspectionForFile

CREATE TABLE cm_co_ssh_key_provisioner_targets (
  id serial PRIMARY KEY,
  co_provisioning_target_id integer NOT NULL,
  server_url varchar(256),
  port integer,
  remote_user varchar(24),
  remote_path varchar(256),
  private_key varchar(6000),
  public_key varchar(2048),
  created timestamp without time zone,
  modified timestamp without time zone
);

CREATE UNIQUE INDEX cm_co_ssh_key_provisioner_targets_i1 ON public.cm_co_ssh_key_provisioner_targets USING btree (co_provisioning_target_id);
-- Add Foreign Key constraints
ALTER TABLE ONLY public.cm_co_ssh_key_provisioner_targets
ADD CONSTRAINT cm_co_ssh_key_provisioner_targets_co_provisioning_target_id_fkey FOREIGN KEY (co_provisioning_target_id) REFERENCES public.cm_co_provisioning_targets(id);


GRANT SELECT ON TABLE public.cm_co_ssh_key_provisioner_targets TO cmregistryuser_proxy;
