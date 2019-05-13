<?php
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
$mailmanGroups = array(
    "AMB",
    "WP5",
    "WP5-all",
    "test-sso-mailman",
);
$admins = array();

$baseDN = "ou=groups,dc=aai,dc=egi,dc=eu";
$userDN = "ou=people,dc=aai,dc=egi,dc=eu";

// Performing SQL query and modify ldap attribute
$sqlquery = "SELECT id, name FROM cm_co_groups WHERE co_id=" . $co_id . " AND co_group_id IS NULL AND NOT deleted ORDER BY id ASC;";
$sqlresult = pg_query($dbconn, $sqlquery) or die('Query failed: ' . pg_last_error());
$sqlgroups = pg_fetch_all($sqlresult);
// echo "group_ids: " . var_dump($sqlgroups); // TODO DEBUG

foreach($sqlgroups as $sqlgroup) {
    //if(strpos($sqlgroup['name'], 'CO:COU:') !== false) {
    //    $sqlgroup['name'] = str_replace('CO:COU:', '', $sqlgroup['name']);
    //}
    if((strpos($sqlgroup['name'], 'CO:') !== false) && (strpos($sqlgroup['name'], ':admins') !== false)) {
        $sqlgroup['name'] = str_replace('CO:', '', $sqlgroup['name']);
        $sqlgroup['name'] = str_replace('COU:', '', $sqlgroup['name']);
        $sqlgroup['name'] = str_replace(':admins', '', $sqlgroup['name']);
        // echo "sqlgroup name=" . $sqlgroup['name'] . "\n"; // TODO DEBUG
        if($sqlgroup['name'] == "admins") {
            $sqlgroup['name'] = "members";
            // echo "sqlgroup fixed name=" . $sqlgroup['name'] . "\n"; // TODO DEBUG
        }
    } else { // Ignore non admin groups
        continue;
    }
    // TODO: Limit provisioning of owners to mailman groups only?
    //if(in_array(($sqlgroup['name']), $mailmanGroups)) {
        // echo "sqlgroup name=" . $sqlgroup['name'] . " with id '" . $sqlgroup['id']. "' in mailman groups\n"; // TODO DEBUG
        if (empty($sqlgroup['id']) || empty($sqlgroup['name'])) {
            continue;
        } 
        $sqlquery = "SELECT i.identifier FROM cm_co_groups as g "
                  . "INNER JOIN cm_co_group_members as gm ON g.id=gm.co_group_id "
                  . "INNER JOIN cm_identifiers AS i ON gm.co_person_id=i.co_person_id "
                  . "WHERE g.id=".$sqlgroup['id'] . " "
                  . "AND gm.co_group_member_id IS NULL AND NOT gm.deleted "
                  . "AND i.type='uid' AND i.identifier_id IS NULL AND NOT i.deleted "             
                  . "ORDER BY i.identifier";
        // echo "query " . $sqlquery; // TODO DEBUG
        $sqlresult = pg_query($dbconn, $sqlquery) or die('Query failed: ' . pg_last_error());
        $adminMembers = pg_fetch_all($sqlresult);
        // echo "Owners: " . var_dump($adminMembers); // TODO DEBUG
        if (empty($adminMembers)) {
           continue; 
        }
        foreach($adminMembers as $adminMember){
            array_push($admins, "uid=" . $adminMember['identifier'] . "," . $userDN);
        }
        $groupOwners['owner'] = $admins;
        ldap_mod_replace($ldapconn, "cn=" . $sqlgroup['name'] . "," . $baseDN, $groupOwners);
        // echo $sqlgroup['name'] . " " . var_export($groupOwners, true); // TODO DEBUG
        unset($admins);
        $admins = array();
        // echo "\n\n"; // TODO DEBUG
    //}
}

// Free resultset
pg_free_result($sqlresult);

// Closing connection
pg_close($dbconn);
ldap_unbind($ldapconn);

?>
