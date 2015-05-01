<?php
include_once "db.php";

if (empty($_POST['domain']))
    die("ERROR: Domain not specified\n");
if (strlen($_POST['domain']) > 30)
    die("ERROR: Domain too long, at most 30 chars\n");
if (strlen($_POST['domain']) < 3)
    die("ERROR: Domain too short, at least 3 chars\n");
if ($_POST['domain'] == "www")
    die("ERROR: Domain is reserved\n");
if (!preg_match("/^[a-z0-9][a-z0-9-]*[a-z0-9]$/", $_POST['domain']))
    die("ERROR: Domain should only contain lowercase letters, digits and '-'\n");

$count = mysql_result(mysql_query("SELECT COUNT(*) FROM ddns WHERE domain='".addslashes($_POST['domain'])."' AND status='used' AND last_update >= SUBTIME(NOW(), '7 0:0:0')"), 0);
if ($count > 0)
    die("ERROR: Sorry, the domain has been used\n");

$token = random_string(20);
mysql_query("REPLACE INTO ddns SET domain='".addslashes($_POST['domain'])."', status='unused', token='$token', create_time=NOW()");
echo "$token\n";

function random_string($length) {
    $str = '';
    for ($i=0;$i<$length;$i++) {
        $r = rand() % 62;
        if ($r < 26)
            $char = chr(ord('a')+$r);
        else if ($r < 52)
            $char = chr(ord('A')+$r-26);
        else
            $char = chr(ord('0')+$r-52);
        $str = $str.$char;
    }
    return $str;
}
?>
