#!/bin/ash

number_conf=0
count=0
#clear configList
cat /dev/null > /tmp/configList

#mounting partition with config files
mount -t ext4 /dev/sda6 /mnt

#searching for config files on partition
config_list=`ls -p /mnt | grep -v / | sed 's/\/mnt\///'`

#counting number of avaliable config files
for i in  $(echo "$config_list"); do
	number_conf=$((number_conf+1))
done

#JSON format
for i in $config_list; do
	count=$((count+1))
	echo -e "\"conf_$count\": \"$i\"," >> /tmp/configList
done
strout=`cat /tmp/configList | tr '\n' ' '| sed 's/.$//' | sed 's/.$//'`
echo $strout
umount /dev/sda6

