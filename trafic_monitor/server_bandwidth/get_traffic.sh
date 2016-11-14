#!/bin/bash
# $1: time
# $2: ip
# $3: communitystring





### ### ### ### ### ### ### ### ###
# get traffic of each server      #
# $1 : ip                         #
# $2 : communitystring            #
### ### ### ### ### ### ### ### ###
func_getdata() {
    #get index
    index_lst=`snmpwalk -v1 -c $3 $2 ifName |grep 'STRING: eth' | awk '{print $1}' |tr '.' ' ' | awk '{print $2}'`
    if [ $? -gt 0 ]; then
        echo -1
        exit
    fi

    #calculator total traffic for each server
    IFS=$'\n'
    for index in $index_lst; do
		item_name=`snmpwalk -v2c -c $3 $2 ifName.$index | awk '{print $4}'`
        item_out=`snmpwalk -v2c -c $3 $2 ifHCOutOctets.$index | awk '{print $4}'`
		item_in=`snmpwalk -v2c -c $3 $2 ifHCInOctets.$index | awk '{print $4}'`
		#echo $item_name $item_in $item_out
		
		#push memcache: [yyyymmddHHiiss] [ip server] [interface] [in traffic] [out traffic]
		if [ $item_in -ne 0 ] || [ $item_out -ne 0 ]; then
			/zserver/php/bin/php -f main.php $1 $2 $item_name $item_in $item_out
		fi
    done
    #echo $total
}

### main ###
func_getdata $1 $2 $3
