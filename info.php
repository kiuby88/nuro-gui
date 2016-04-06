<pre>
<?php

print "NURO CaseStudy (http://seaclouds-project.eu/) System Information (DO NOT DEPLOY ON PRODUCTION SERVERS!)\n\n";

print "Environment _SERVER:\n";
print_r( $_SERVER);

if( getenv('db_connection_uri'))
{
    $sHost = parse_url( getenv('db_connection_uri'), PHP_URL_HOST);
    
    print "\nDB: ping $sHost:\n"
        . `ping -w2 -c2 $sHost` 
        . "\n";
}

if( getenv('nuro_api_uri'))
{
    $sHost = parse_url( getenv('nuro_api_uri'), PHP_URL_HOST);
    
    print "\nAPI: ping $sHost:\n"
        . `ping -w2 -c2 $sHost` 
        . "\n";
}

print "\n\nOS by uname:\n"
      . `uname -a` 
      . "\n";

print "OS by /etc/os-release:\n"
      . `cat /etc/os-release`
      . "\n";

print "OS by /etc/*version*:\n"
      . `ls -ld /etc/*version*` 
      . `cat /etc/*version*` 
      . "\n\n";

print "df:\n" . `df -h` . "\n\n";

print "RAM:\n" . `free -m` . "\n\n";

print "CPU:\n" . `cat /proc/cpuinfo` . "\n\n";

phpinfo();

print "\ntop:\n" . `top -bn1` . "\n\n";
?>