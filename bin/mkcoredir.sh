#! /bin/bash
# Creates Group and User folders
path="/core-server/groups";
admin_group="core_admins";
group=""
pi_username=""
username=""
verbose=0
args_valid=1

usage() {
	echo "usage: mkcoredir.sh"
	echo "Creates group and/or user folder"
	echo "	-g	Group Name"
	echo "	-p	PI Username"
	echo "	-u	Username"
	echo "	-v	Verbose"
	echo "	-h	Output this help menu"
}

while getopts ":hvg:p:u:" opt; do
  case $opt in
    h)
      usage
      exit 0
      ;;
    g)
      group=$OPTARG
      ;;
    p)
      pi_username=$OPTARG
      ;;
    u)
      username=$OPTARG
      ;;
    v)
      verbose=1
      ;;
    \?)
      echo "Invalid option: -$OPTARG" >&2
      exit 1
      ;;
    :)
      echo "Option -$OPTARG requires an argument." >&2
      exit 1
      ;;
  esac
done

if [ -z $group ]; then
  args_valid=0
  echo "Missing required argument: -g Group Name"
fi
if [ -z $pi_username ]; then
  args_valid=0
  echo "Missing required argument: -p PI Username"
fi
if [ -z $username ]; then
  args_valid=0
  echo "Missing required argument: -u Username"
fi
if [ $args_valid -eq 0 ]; then
  exit 1
fi

# Check if group exists
getent group $group > /dev/null
if [ $? -ne 0 ]; then
  echo "Group $group does not exist on `hostname`."
  exit 1
fi

# Create the directories
pi_directory="$path/$pi_username"
user_directory="$pi_directory/$username"

if [ $verbose -gt 0 ]; then
  echo "Creating directories..."
fi
if [ ! -d $pi_directory ]; then
	if [ $verbose -gt 0 ]; then
		echo "Creating $pi_directory";
	fi
	mkdir $pi_directory
	if [ $verbose -gt 0 ]; then
		echo "Setting $pi_directory permissions";
	fi
	chown root.$group $pi_directory
	chmod 2770 $pi_directory
	setfacl -m g:$admin_group:rx $pi_directory
fi

if [ ! -d $user_directory ]; then
	if [ $verbose -gt 0 ]; then
		echo "Creating $user_directory";
	fi
	mkdir $user_directory
	if [ $verbose -gt 0 ]; then
		echo "Setting $user_directory permissions";	
	fi
	chmod -R 2770 $user_directory
	chown root.$group $user_directory
	setfacl -m g:$admin_group:rwx $user_directory
	setfacl -d -m g:$admin_group:rwx $user_directory

fi


if [ $verbose -gt 0 ]; then
  echo "Done"
fi

exit 0
