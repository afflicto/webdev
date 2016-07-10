<?php
 return array (
  'web' => 
  array (
    'enabled' => true,
    'default' => 'wamp',
    'providers' => 
    array (
      'wamp' => 
      array (
        'web_root' => 'F:/wamp64/www',
        'virtualhosts' => 
        array (
          'enabled' => false,
          'file' => 'F:\\wamp64\\bin\\apache\\apache2.4.17\\conf\\extra\\httpd-vhosts.conf',
        ),
      ),
    ),
  ),
  'documents' => 
  array (
    'enabled' => true,
    'root' => 'F:/Projects',
  ),
  'hosts' => 
  array (
    'enabled' => true,
    'file' => 'C:/Windows/System32/drivers/etc/hosts',
  ),
  'database' => 
  array (
    'enabled' => true,
    'default' => 'mysql',
    'providers' => 
    array (
      'mysql' => 
      array (
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'kake',
      ),
    ),
  ),
  'projects' => 
  array (
  ),
);