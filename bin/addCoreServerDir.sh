#!/bin/bash
gid=$1
pi=$2
user=$3

OUTPUT=`ssh root@localhost "mkcoredir -g $gid -p $pi -u $user"`

if [ $? -eq 1 ]
then
	echo $OUTPUT
	exit 1
fi

