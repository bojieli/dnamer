<?php
include_once "dns.inc.php";
include_once "db.php";

if (empty($_POST['domain']))
    die("ERROR: Domain not specified\n");

if (empty($_POST['token']) || !preg_match("/^[0-9a-zA-Z]+$/", $_POST['token']))
    die("ERROR: Token not specified\n");
if (!preg_match("/^[a-z0-9][a-z0-9-]*[a-z0-9]$/", $_POST['domain']))
    die("ERROR: Incorrect domain or token\n");
if (strlen($_POST['domain']) >= 17)
    goto skip_token_check;

$count = mysql_result(mysql_query("SELECT COUNT(*) FROM ddns WHERE token='".addslashes($_POST['token'])."' AND domain='".addslashes($_POST['domain'])."'"), 0);
if ($count == 0)
    die("ERROR: Incorrect domain or token\n");

skip_token_check:
$ipv4 = $ipv6 = "keep";
$ip = $_SERVER['HTTP_X_REAL_IP'];
if (check_ipv4($ip))
    $ipv4 = $ip;
else if (check_ipv6($ip))
    $ipv6 = $ip;

if (isset($_POST['ipv4']) && ($_POST['ipv4'] == "keep" || $_POST['ipv4'] == "remove" || check_ipv4($_POST['ipv4'])))
    $ipv4 = $_POST['ipv4'];
if (isset($_POST['ipv6']) && ($_POST['ipv6'] == "keep" || $_POST['ipv6'] == "remove" || check_ipv6($_POST['ipv6'])))
    $ipv6 = $_POST['ipv6'];

$base = "dnamer.net";
$ret = update_subdomain($base, $_POST['domain'], $ipv4, $ipv6);
if ($ret != 0)
    die("ERROR: Internal error: nsupdate failed #$ret\n");

if ($_POST['token'])
    mysql_query("UPDATE ddns SET status='used', last_update=NOW() WHERE token='".addslashes($_POST['token'])."'");
echo "OK\n";
?>
