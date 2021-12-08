#!/usr/bin/env perl

use strict;
use warnings;
use LWP::UserAgent;

my $noUserLogged = "0";
my $deviceId = ; 
my $deviceKey = "";
my $apiUrl = "";
my $interactiveUserQuery = `who | grep "[(]:[0-9]"`;
my @connectedUsers = split /\s+/, $interactiveUserQuery;
my $connectedUserName = $connectedUsers[0];

if($connectedUserName) {
	#do nothing
}
else {
	$connectedUserName=$noUserLogged;
}

my $ua = LWP::UserAgent->new();

my %form;
$form{'username'} = $connectedUserName;
$form{'key'} = $deviceKey;

my $response = $ua->post($apiUrl,\%form);

if ($response->is_success) {
	print "success\n";
}
else {
	print STDERR $response->status_line, "\n";
}

