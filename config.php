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
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => 'kake',
      ),
    ),
  ),
  'projects' => 
  array (
    'example-project' => 
    array (
      'name' => 'example-project',
      'documents' => 'F:/Projects/example-project',
      'web' => 'F:/wamp64/www/example-project',
      'database' => 'example-project',
      'hosts' => 
      array (
        0 => 'example-project.dev',
        1 => 'www.example-project.dev',
      ),
    ),
  ),
);