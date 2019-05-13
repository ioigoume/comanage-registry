<?php
$coId = 2;
// PostgreSQL
// Connecting, selecting database
$dbconn = pg_connect("host=192.168.68.5 dbname=registry user=cmregistryadmin password=8&rM*qK2Nm-NR2eS") or die('Could not connect: ' . pg_last_error());

// LDAP
// Connecting and binding
$ldapconn = ldap_connect("ldaps://ldap.aai.egi.eu") or die("Could not connect to LDAP server.");
if ($ldapconn) {
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    $l = ldap_bind($ldapconn, 'cn=comanage,ou=services,dc=aai,dc=egi,dc=eu', 'tQfVJXm2n?MWy3QY') or die ("Error trying to bind: ".ldap_error($ldapconn));
}

// Initialize variables
$baseDN = "ou=groups,dc=aai,dc=egi,dc=eu";

// Performing SQL query and modify descriptions
$sqlquery = "SELECT name, description FROM cm_co_groups "
            . "WHERE co_id=" . $coId . " AND co_group_id IS NULL AND NOT deleted;";
$sqlresult = pg_query($dbconn, $sqlquery) or die('Query failed: ' . pg_last_error());
$sqlgroups = pg_fetch_all($sqlresult);

foreach($sqlgroups as $sqlgroup) {
    if((strpos($sqlgroup['name'], 'CO:') !== false) && (strpos($sqlgroup['name'], ':active') !== false)) {
        $sqlgroup['name'] = str_replace('CO:', '', $sqlgroup['name']);
        $sqlgroup['name'] = str_replace('COU:', '', $sqlgroup['name']);
        $sqlgroup['name'] = str_replace(':members', '', $sqlgroup['name']);
        $sqlgroup['name'] = str_replace(':active', '', $sqlgroup['name']);
        // v1.0.x $sqlquery = "SELECT name, description FROM cm_cous WHERE name='" . $sqlgroup['name'] . "'";
        // $sqlquery = "SELECT name, description FROM cm_cous WHERE name='" . $sqlgroup['name'] . "' AND cou_id IS NULL AND NOT deleted;";
        // $result = pg_query($dbconn, $sqlquery) or die('Query failed: ' . pg_last_error());
        // $sqldescription = pg_fetch_all($result) . "\n";
        // echo var_export($sqldescription, true); // TODO DEBUG
        if (empty($sqlgroup['description'])) {
            continue;
        }
        $ldapresult = ldap_search($ldapconn, $baseDN, "(cn=" . $sqlgroup['name'] . ")", array("cn"));
        $entries = ldap_get_entries($ldapconn, $ldapresult);
        //echo "ldap results: " . var_export($entries, true) . "\n"; // TODO DEBUG
        if ($entries['count']==0) {
            continue;
        }
        //echo var_export($sqlgroup, true); // TODO DEBUG
        //echo "description: " . var_export($sqlgroup['name'], true) . " desc=" . $sqlgroup['description'] . "\n"; // TODO DEBUG
        $description['description'] = $sqlgroup['description'];
        ldap_mod_replace($ldapconn, "cn=" . $sqlgroup['name'] . "," . $baseDN, $description);
    }
}

// Free resultset
pg_free_result($sqlresult);

// Closing connection
pg_close($dbconn);
ldap_unbind($ldapconn);

?>
