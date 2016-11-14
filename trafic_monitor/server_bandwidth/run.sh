#!/bin/bash

#NOW=`date +"%Y%m%d%H%M"`
NOW=`date +%s`

IFS=$'\n'
list_server_interface=`cat $1`

for line in $list_server_interface; do
	IFS=$' '
	#nohup bash get_each_server.sh $NOW $line &
	bash get_traffic.sh $NOW $line

	#traffic=`bash get_each_server.sh $NOW $line`
	#echo traffic: $traffic
done
