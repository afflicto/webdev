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
          'enabled' => true,
          'file' => 'F:/wamp64/bin/apache/apache2.4.17/conf/extra/httpd-vhosts.conf',
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
    'mynewsite' => 
    array (
      'name' => 'mynewsite',
      'documents' => 'F:/Projects/mynewsite',
      'web' => 'F:/wamp64/www/mynewsite',
      'database' => 'mynewsite',
      'hosts' => 
      array (
        0 => 'mynewsite.dev',
        1 => 'www.mynewsite.dev',
      ),
    ),
  ),
  'git' => true,
);