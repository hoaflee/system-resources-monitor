#!/usr/bin/env python
import sys
import commands
import re
import time
import datetime
import subprocess


def getinfo(community, ipaddress):
    snmp_ifout = "1.3.6.1.2.1.31.1.1.1.6."
    snmp_ifin = "1.3.6.1.2.1.31.1.1.1.10."
    #p = re.compile(r'eth[0-9]+|em[0-9]+|bond[0-9]+')
    p = re.compile(r'eth[0-9]+|em[0-9]+')
    ifacelist = dict()

    snmpcmd_get_iface = "snmpwalk -v2c -c" + " " + community + " " + ipaddress + " ifName"
    (returncode, output) = commands.getstatusoutput(snmpcmd_get_iface)

    for inf in output.split("\n"):
        int_name = p.findall(inf.split(":")[-1].strip())
        if int_name:
            int_index = inf.split("=")[0].split(".")[-1]
            ifacelist[int_name[-1]] = (snmp_ifin + int_index, snmp_ifout + int_index)

    snmpcmd_get_iface_info = "snmpget -v2c -c" + " " + community + " " + ipaddress
    int_statistic = []
    for key in ifacelist.keys():
        (returncode, trafficin) = commands.getstatusoutput(snmpcmd_get_iface_info + " " + ifacelist[key][0])
        (returncode, trafficout) = commands.getstatusoutput(snmpcmd_get_iface_info + " " + ifacelist[key][1])
        if trafficin.split(":")[-1].strip() != "0" and trafficout.split(":")[-1].strip() != "0":
            int_statistic.append((key, trafficin.split(":")[-1].strip(), trafficout.split(":")[-1].strip()))
    return int_statistic

if __name__ == '__main__':
    f = open('server.lst', 'r')
    mainfile = "main.php"
    #dbfile = open("/tmp/traffic.db", 'a')
    host_info = dict()
    now = time.mktime(datetime.datetime.now().timetuple())
    now = time.strftime("%s")
    count = 0
    cmd = "/zserver/php/bin/php" + " " + "main.php" + " " + str(now)
    for line in f.readlines():
        community_string = line.strip().split(" ")[-1]
        host = line.strip().split(" ")[0]
        host_info[host] = getinfo(community_string, host)
        count += 1
        (returncode, cmdresult) = commands.getstatusoutput(cmd + " " + host)
        for intface_info in host_info[host]:
            cmd_full = cmd + " " + host + " " + intface_info[0] + " " + intface_info[1] + " " + intface_info[-1]
            print subprocess.Popen(cmd_full, stdout=subprocess.PIPE, stderr=subprocess.STDOUT, shell=True).stdout.readline()
    f.close()
    #dbfile.close()
    sys.exit(0)
