<?php
/*
 * Virtual host config automation for XAMPP development server
 *
 * @copyright (c) 2013-2018 Eugene Dementyev <devg@ya.ru>
 * @license MIT
*/
$root = '../../www';
$domainSuffix = '.localhost';
$updateHostsFile = false;

$apacheHostsConfig = '../apache/conf/extra/httpd-vhosts.conf';
$systemHostsFile = 'C:\\Windows\\System32\\drivers\\etc\\hosts';

$out = <<<'EOT'
# Virtual Hosts
#
# Required modules: mod_log_config

# If you want to maintain multiple domains/hostnames on your
# machine you can setup VirtualHost containers for them. Most configurations
# use only name-based virtual hosts so the server doesn't need to worry about
# IP addresses. This is indicated by the asterisks in the directives below.
#
# Please see the documentation at 
# <URL:http://httpd.apache.org/docs/2.4/vhosts/>
# for further details before you try to setup virtual hosts.
#
# You may use the command line option '-S' to verify your virtual host
# configuration.

#
# Use name-based virtual hosting.
#
##NameVirtualHost *:80
#
# VirtualHost example:
# Almost any Apache directive may go into a VirtualHost container.
# The first VirtualHost section is used for all requests that do not
# match a ##ServerName or ##ServerAlias in any <VirtualHost> block.
#
##<VirtualHost *:80>
    ##ServerAdmin webmaster@dummy-host.example.com
    ##DocumentRoot "/xampp72/htdocs/dummy-host.example.com"
    ##ServerName dummy-host.example.com
    ##ServerAlias www.dummy-host.example.com
    ##ErrorLog "logs/dummy-host.example.com-error.log"
    ##CustomLog "logs/dummy-host.example.com-access.log" common
##</VirtualHost>

##<VirtualHost *:80>
    ##ServerAdmin webmaster@dummy-host2.example.com
    ##DocumentRoot "/xampp72/htdocs/dummy-host2.example.com"
    ##ServerName dummy-host2.example.com
    ##ErrorLog "logs/dummy-host2.example.com-error.log"
    ##CustomLog "logs/dummy-host2.example.com-access.log" common
##</VirtualHost>

#localhost
<VirtualHost *:80>
  ServerAdmin root@localhost
  DocumentRoot "/xampp72/htdocs"
  ServerName localhost
  #ErrorLog logs/localhost-error_log
  #CustomLog logs/localhost-access_log common
  <Directory "/xampp72/htdocs">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
  </Directory>
  <IfModule alias_module>
    Alias /adminer "/xampp72/xampp-tools/adminer"
    <Directory "/xampp72/xampp-tools/adminer">
        AllowOverride AuthConfig
        Require local
    </Directory>
  </IfModule>
</VirtualHost>

EOT;

// Handle file permission warnings as Exception.
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

echo "Update Apache virtual hosts setting:\r\n";

$files = scandir($root);
$folders = [];
$hostnames = [];

if (count($files)) {
    foreach ($files as $file) {
        // dirs started with `_` will be ignored
  if (
    is_dir($root.'/'.$file) &&
    $file!='.' && $file!='..' &&
    strpos($file, '_')!==0
  ) {
      $folders[] = $file;
  }
    }
}

echo "-------\r\n[+] Hosts found (".count($folders)."):\r\n";

if (count($folders)) {
    foreach ($folders as $folder) {
        $hostnames[] = $hostname = $folder.$domainSuffix;
        if (is_dir($root.'/'.$folder.'/public')) {
            $folder = $folder.'/public';
        }

// Virtual Host Template
$out .= '
#'.$hostname.'
<VirtualHost *:80>
  ServerAdmin root@localhost
  DocumentRoot "/www/'.$folder.'"
  ServerName '.$hostname.'
  #ErrorLog logs/'.$hostname.'-error_log
  #CustomLog logs/'.$hostname.'-access_log common
  <Directory "/www/'.$folder.'">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>
';
        echo "- ".str_pad($hostname, 32)." /www/".$folder."\r\n";
    }
}

// writing config file
$f = fopen($apacheHostsConfig, 'wb');
fwrite($f, $out);
fclose($f);

echo "-------\r\n[+] Virtual hosts setting updated.\r\n\r\n";


// Hosts file updating.
if ($updateHostsFile) {
    echo "-------\r\nUpdate hosts file:\r\n";

    $lines = file($systemHostsFile);
    $result = [];

    if (count($lines)) {
        foreach ($lines as $line) {
            if (strpos($line, '#laragon magic!') === false && strpos($line, '#xampp-tools') === false) {
                $result[] = $line;
            }
        }
    }

    if (count($hostnames)) {
        foreach ($hostnames as $host) {
            $result[] = '127.0.0.1    '.str_pad($host, 32)."#xampp-tools\r\n";
        }
    }

    try {
        if (file_put_contents($systemHostsFile, $result)) {
            echo "[+] Hosts file updated.\r\n\r\n";
        }
    } catch (Throwable $e) {
        echo "[!] Cant't update hosts file. Please run this script as Administrator.\r\n\r\n";
    }
}
// if(file_put_contents('hosts.txt', $result)) {
    // echo "Hosts copy file updated.\r\n\r\n";
// }
