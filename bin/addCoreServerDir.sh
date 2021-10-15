#!/bin/bash
gid=$1
pi=$2
user=$3

ssh root@localhost "mkcoredir -g $gid -p $pi -u $user"
