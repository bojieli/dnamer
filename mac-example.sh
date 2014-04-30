#!/bin/bash
mac=$(/sbin/ifconfig eth0 | grep HWaddr | grep -o -E '([0-9a-f]{2}:){5}[0-9a-f]{2}' | sed 's/:/-/g')
[ ! -z "$mac" ] && curl -4 -d "domain=${mac}&token=mac" http://dnamer.net/update
