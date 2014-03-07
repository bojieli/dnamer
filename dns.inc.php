<?php
function __nsupdate($commands) {
    $tmpfile = tempnam('/tmp', 'ns_');
    $fp = fopen($tmpfile, "w");
    fwrite($fp, "server dns.lug.ustc.edu.cn\n");
    foreach ($commands as $cmd) {
        fwrite($fp, "update $cmd\n");
    }
    fwrite($fp, "send\n");
    fclose($fp);
    chmod($tmpfile, 0644);
    exec("nsupdate -k /var/www/default-update-key.key $tmpfile", $output, $status);
    unlink($tmpfile);
    return $status;
}
function nsupdate_add($fqdn, $record, $content) {
    return __nsupdate(array("add $fqdn $record $content"));
}
function nsupdate_replace($fqdn, $record, $content) {
    $ttl = 10;
    return __nsupdate(array("delete $fqdn $record", "add $fqdn $ttl $record $content"));
}
function nsupdate_delete($fqdn, $record) {
    return __nsupdate(array("delete $fqdn $record"));
}

function do_update_ip($fqdn, $type, $ip) {
    if ($ip == "keep")
        return 0;
    if ($ip == "remove")
        return nsupdate_delete($fqdn, $type, $ip);
    return nsupdate_replace($fqdn, $type, $ip);
}

function update_subdomain($base, $subdomain, $ipv4, $ipv6) {
    $fqdn = $subdomain.".".$base;
    return do_update_ip($fqdn, 'A', $ipv4).".".do_update_ip($fqdn, 'AAAA', $ipv6);
}
function check_ipv4($ipv4) {
    $parts = explode('.', $ipv4);
    if (count($parts) != 4)
        return false;
    for ($i=0; $i<4; $i++) {
        if (!preg_match('/^[0-9]+$/', $parts[$i]))
            return false;
        if ($parts[$i] < 0 || $parts[$i] >= 256)
            return false;
    }
    return true;
}
function check_ipv6($ipv6) {
    $parts = explode(':', $ipv6);
    if (count($parts) > 8)
        return false;
    $have_empty = false;
    for ($i=0; $i<count($parts); $i++) {
        if ($parts[$i] == "") {
            if ($have_empty)
                return false;
            $have_empty = true;
            continue;
        }
        if (!preg_match('/^[0-9a-fA-F]+$/', $parts[$i]))
            return false;
        if (strlen($parts[$i]) > 4)
            return false;
    }
    if (count($parts) < 8 && !$have_empty)
        return false;
    return true;
}
