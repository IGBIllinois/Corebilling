#!/usr/bin/env perl

use strict;
use warnings;
use LWP::UserAgent;
use JSON;
use Socket;
use Getopt::Long;
use Data::Dumper;

sub help() {
        print "Usage: $0\n";
	print "Gets current logged in user and sends to CoreBilling Website using Rest API\n";
	print "\t-u|--url		URL\n";
	print "\t-d|--device		Device ID\n";
	print "\t-k|--key		Device Key\n";
        print "\t-h|--help		Prints this help\n";
        exit 0;
}

sub get_ipaddress() {
        my $address = `hostname -i`;
	chomp $address;
	return $address;
}

sub get_os() {
	my $os = `lsb_release -d | cut -f 2`;
	chomp $os;
	return $os;


}

sub get_harddrives() {
	my @disks = `df -l -x tmpfs -x devtmpfs --output=target,size,avail -B1 | tail -n +2`;
	my %disks_hash;
	my $i = 0;
	for my $disk (@disks) {
		$disk =~ s/^\s+|\s+$//g;
		my @tokens = split(' ',$disk);
		$disks_hash{$i}->{'volume'} = $tokens[0];
		$disks_hash{$i}->{'size'} = $tokens[1];
		$disks_hash{$i}->{'free'} = $tokens[2];
		$i++;
	}
	return %disks_hash;
}
sub get_user() {
	my $users = `who | grep "[(]:[0-9]"`;
	my @users_array = split /\s+/, $users;
	return $users_array[0];
}

my $version = "2.0.0";
my $apiversion = "v1";
my $noun = "session";

my $deviceId = 0; 
my $deviceKey = "";
my $url = "";

GetOptions ("u|url=s" => \$url,
	"d|device=i" => \$deviceId,
	"k|key=s"=>\$deviceKey,
	"h|help" => sub { help() },
) or die("\n");

if (!$url || !$deviceId || !$deviceKey) {
	help();
	die("\n");
}

my $apiUrl = $url . "/api/" . $apiversion . "/index.php/" . $noun . "/" . $deviceId;

my $connectedUserName = get_user();

if($connectedUserName) {
	#do nothing
}
else {
	$connectedUserName="";
}

my $ua = LWP::UserAgent->new();
my %harddrives = get_harddrives();

my %json_hash = ('username'=>$connectedUserName,
	'ipaddress'=>get_ipaddress(),
	'os'=>get_os(),
	'version'=>$version,
	'hard_drives'=>\%harddrives
);

my $json = encode_json \%json_hash;
my $request = HTTP::Request->new(POST => $apiUrl);
$request->content($json);
$request->authorization_basic($deviceId,$deviceKey); 
$request->header('Content-Type'=>'application/json');
$request->header('Accept'=>'application/json');

my $response = $ua->request($request); 

if ($response->is_success) {
	print $response->decoded_content;
}
else {
	if (!$response->is_success) {
		print STDERR $response->status_line, "\n";
		print "Error contacting address $apiUrl\n";
	}
	else {
		print $response->decoded_content, "\n";
	}
}



