<?php
  /**
   * Created by PhpStorm.
   * User: root
   * Date: 15/3/2019
   * Time: 11:57 πμ
   */

  class SshKeyProvisionerEnum
  {
    // Protocol v2
    const DSA         = 'ssh-dss';
    const ECDSA       = 'ECDSA';
    const ED25519     = 'ed25519';
    const RSA         = 'ssh-rsa';
    // Protocol v1
    const RSA1        = 'ssh-rsa1';
  }