# Linux Core Billing Service

## Requirements
* Perl
* Perl JSON
* Perl LWP UserAgent
* Perl LWP Protocol Https
* lsb_release command
```
dnf -y install perl-JSON perl-LWP-UserAgent-Determined perl-LWP-Protocol-https redhat-lsb-core
```

## Installation
* Download CoreBillingService.pl from the '''Download''' page in Core Billing Application into /usr/local/sbin
```
wget https://example.com/corebilling/download/CoreBillingService.pl
```
* Create Device in '''Devices''' Page in Core Billing Application
* Add to crontab to run every minute
```
0 0 0 0 0 root source /etc/profile && perl /usr/local/sbin/CoreBillingService.pl --url URL --device DEVICE_ID --key KEY
```

