#!/bin/bash
directory=$1

OUTPUT=`ssh root@localhost "if [ -d \"$directory\" ]; then echo 1; else echo 0; fi"`
echo $OUTPUT

if [ $? -eq 1 ]
then
	exit 1
fi

