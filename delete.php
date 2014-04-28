<?php
include_once "dns.inc.php";
include_once "db.php";

if (empty($_POST['domain']))
    die("ERROR: Domain not specified\n");

if (empty($_POST['token']) || !preg_match("/^[0-9a-zA-Z]+$/", $_POST['token']))
    die("ERROR: Token not specified\n");
if (!preg_match("/^[a-z0-9][a-z0-9-]*[a-z0-9]$/", $_POST['domain']))
    die("ERROR: Incorrect domain or token\n");

mysql_query("DELETE FROM ddns WHERE token='".addslashes($_POST['token'])."' AND domain='".addslashes($_POST['domain'])."'");
if (mysql_affected_rows() != 1)
    die("ERROR: Incorrect domain or token\n");

$base = "dnamer.net";
$ret = update_subdomain($base, $_POST['domain'], "remove", "remove");
if ($ret != 0)
    die("ERROR: Internal error: nsupdate failed #$ret\n");

echo "OK\n";
?>
