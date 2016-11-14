#!/usr/bin/env python
import sys
import netsnmp
import re
import memcache
import datetime
import MySQLdb
import time

def insert_db(server_ip, interface, update_date):
    db = MySQLdb.connect(host="10.30.12.110", user="rs_monitor", passwd="QIM1NQ0G3rij", db="resource_monitor")
    cur = db.cursor()
    query = """REPLACE INTO server_traffic(`server_ip`, `interface`, `update_date`) VALUES ('%s', '%s', '%s')""" % (server_ip, interface, update_date)
    cur.execute(query)
    db.commit()
    cur.close()
    db.close()


def getsnmpinfo(host, community):
    args = {
        "Version": 2,
        "DestHost": host,
        "Community": community,
        "Timeout": 1000000
    }
    p = re.compile(r'em[0-9]+|eth[0-9]+')
    speed_in_current = 0
    speed_out_current = 0
    timetmp = 0
    for idx in netsnmp.snmpwalk(netsnmp.Varbind("IF-MIB::ifIndex"), **args):
        descr, oper, cin, cout = netsnmp.snmpget(
            netsnmp.Varbind("IF-MIB::ifDescr", idx),
            netsnmp.Varbind("IF-MIB::ifOperStatus", idx),
            netsnmp.Varbind("IF-MIB::ifHCInOctets", idx),
            netsnmp.Varbind("IF-MIB::ifHCOutOctets", idx),
            **args)
        descr = p.findall(descr)
        assert(descr is not None and cin is not None and cout is not None)
        if not descr:
            continue
        if oper != "1":
            continue

        print("%s %s %s %s" % (host, descr[0], cin, cout))
        insert_db(host, descr[0], timestamp)
        mc.set(host + "_" + descr[0] + "_" + str(timestamp), cin + "_" + cout, time=int(time.time()) + 5184000)
        print host + "_" + descr[0] + "_" + str(timestamp), cin + "_" + cout
        if mc.get(host + "_" + descr[0] + "_" + str(timestamp-300)) is not None:
            rx_now = int(mc.get(host + "_" + descr[0] + "_" + str(timestamp)).split("_")[0])
            rx_prev = int(mc.get(host + "_" + descr[0] + "_" + str(timestamp-300)).split("_")[0])

            tx_now = int(mc.get(host + "_" + descr[0] + "_" + str(timestamp)).split("_")[1])
            tx_prev = int(mc.get(host + "_" + descr[0] + "_" + str(timestamp-300)).split("_")[1])

            timetmp = 300
        elif mc.get(host + "_" + descr[0] + "_" + str(timestamp-360)) is not None:
            rx_now = int(mc.get(host + "_" + descr[0] + "_" + str(timestamp)).split("_")[0])
            rx_prev = int(mc.get(host + "_" + descr[0] + "_" + str(timestamp-360)).split("_")[0])

            tx_now = int(mc.get(host + "_" + descr[0] + "_" + str(timestamp)).split("_")[1])
            tx_prev = int(mc.get(host + "_" + descr[0] + "_" + str(timestamp-360)).split("_")[1])

            timetmp = 360
        elif mc.get(host + "_" + descr[0] + "_" + str(timestamp-240)) is not None:
            rx_now = int(mc.get(host + "_" + descr[0] + "_" + str(timestamp)).split("_")[0])
            rx_prev = int(mc.get(host + "_" + descr[0] + "_" + str(timestamp-240)).split("_")[0])

            tx_now = int(mc.get(host + "_" + descr[0] + "_" + str(timestamp)).split("_")[1])
            tx_prev = int(mc.get(host + "_" + descr[0] + "_" + str(timestamp-240)).split("_")[1])

            timetmp = 240
        else:
            continue
        print timetmp
        speed_in_current = (rx_now - rx_prev)/timetmp
        speed_out_current = (tx_now - tx_prev)/timetmp

        if speed_in_current < 0 :
           speed_in_current = (-speed_in_current)
        if speed_out_current < 0:
           speed_out_current = (-speed_out_current)

        mc.set(host + "_" + descr[0] + "_" + timestamp + "_speed", str(speed_in_current) + "_" + str(speed_out_current), time=int(time.time()) + 5184000)
        print "Current incomming traffic of server %s on card %s are  %s Kbits/s" % (host, descr[0], speed_in_current/1024)
        print "Current outgoing traffic of server %s on card %s are %s Kbits/s" % (host, descr[0], speed_out_current/1024)


if __name__ == '__main__':
    mc = memcache.Client(['10.30.22.49:11216'], debug=0)
    timestamp = int(time.time())
    # year = datetime.datetime.now().strftime("%y")
    # month = datetime.datetime.now().strftime("%m")
    # day = datetime.datetime.now().strftime("%d")
    # hour = datetime.datetime.now().strftime("%H")
    # minute = datetime.datetime.now().strftime("%M")

    serverlist = open("server.lst")
    for line in serverlist.readlines():
        community_string = line.strip().split(" ")[-1]
        ip = line.strip().split(" ")[0]
        getsnmpinfo(ip, community_string)
    serverlist.close()
    sys.exit(0)
