<?php

namespace Afflicto\Webdev;

use Afflicto\Webdev\Console\ConfigCommand;
use Afflicto\Webdev\Console\CreateCommand;
use Afflicto\Webdev\Console\InitCommand;
use Exception;
use Illuminate\Config\Repository;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Singleton class for Webdev.
 * @package Afflicto
 */
class Webdev
{

	/**
	 * @var Webdev|null
	 */
	private static $__instance = null;

	/**
	 * Lets us read/write from the config.php array with ease (with dot-notation syntax)
	 * @var Repository
	 */
	public $config;

	/**
	 * @var Application
	 */
	public $console;

	/**
	 * The default configuration.
	 * @var array
	 */
	public $defaultConfiguration = [
		# Web Server config (currently apache only)
		'web' => [
			'enabled' => false,
			'default' => 'apache',
			'providers' => [],
		],
		# documents config
		'documents' => [
			'enabled' => false,
			'root' => 'C:/Users/Someone/My Documents/Projects',
		],
		# hosts file config
		'hosts' => [
			'enabled' => false,
			'file' => 'C:/Windows/System32/drivers/etc/hosts',
		],
		# database config
		'database' => [
			'enabled' => false,
			'default' => 'mysql',
			'providers' => []
		],
		# the projects that Webdev manages
		'projects' => [

		],
	];

	/**
	 * Singleton.
	 * @return Webdev
	 */
	public static function getInstance()
	{
		if ( ! isset(static::$__instance)) static::$__instance = new Webdev();
		return static::$__instance;
	}

	private function __construct() {}

	public function run()
	{
		# we leverage the Illuminate/Config part of Laravel for managing our simple config!
		# create our console app & add the commands
		$this->config = new Repository();
		$this->console = new Application('Webdev', WEBDEV_VERSION);

		if ( ! WEBDEV_CONFIGURED) {
			$this->console->add(new InitCommand);

			echo "Looks like you just installed the webdev tool. I'm gonna run the 'init' command!\n";

			$this->console->run(new ArrayInput([
				'command' => 'init',
			]));
		}else {
			$this->config->set(include(WEBDEV_CONFIG_FILE));

			# add all commmands
			$this->console->add(new InitCommand);
			$this->console->add(new ConfigCommand);
			$this->console->add(new CreateCommand);

			$this->console->run();
		}
	}

	public function createWebDirectory($project, $webServer = null)
	{
		if ($webServer == null) $webServer = $this->config->get('web.default');
		$root = $this->config->get('web.providers.' .$webServer .'.web_root');

		if (! is_dir($root)) {
			throw new \Exception('The web root "' .$root .'" does not exist for the ' .$webServer .' Web Server Provider!');
		}

		if (mkdir($root .'/' .$project, 0775)) {
			return $root .'/' .$project;
		}
	}

	public function createDocumentsDirectory($project)
	{
		$docRoot = $this->config->get('documents.root');

		if ( ! is_dir($docRoot)) {
			throw new \Exception('The documents root doesn\'t exist! ("' .$docRoot .'")!');
		}

		if (mkdir($docRoot .'/' .$project, 0755)) {
			return $docRoot .'/' .$project;
		}
	}

	public function createDatabase($project, $provider = null)
	{
		# get the provider & credentials
		if ($provider == null) $provider = $this->config->get('database.default');
		$credentials = $this->config->get('database.providers.' .$provider);

		if ($provider == 'mysql') {
			$pdo = new \PDO('mysql:host=' .$credentials['host'], $credentials['username'], $credentials['password']);
			return ($pdo->exec("CREATE DATABASE `$project`;") > 0) ? $project : false;
		}else if ($provider == 'sqlite') {
			#$pdo = new \PDO('sqlite');
		}
	}

	public function addHostsFile($project, $subdomains = ['www'])
	{
		$hosts = [$project .'.dev'];

		$h = fopen($this->config->get('hosts.file'), 'a');
		fwrite($h, "\n\n#---- begin_wedev:$project ----");
		fwrite($h, "\n127.0.0.1 " .$project .'.dev');
		foreach($subdomains as $subdomain) {
			fwrite($h, "\n127.0.0.1 " .$subdomain .'.' .$project .'.dev');
			$hosts[] = $subdomain .'.' .$project .'.dev';
		}
		fwrite($h, "\n#----- end_wedev:$project -----");

		return $hosts;
	}

	public function removeHostsFile($project)
	{
		#$str = file_get_contents($this->config->get('hosts.file'));
		#preg_replace
	}

	/**
	 * Saves the current configuration to WEBDEV_CONFIG_FILE, like this: "<?php return array(...);".
	 */
	public function save()
	{
		$h = fopen(WEBDEV_CONFIG_FILE, 'w');
		fwrite($h, "<?php\n return " .var_export($this->config->all(), true) .';');
		fclose($h);
	}
	
}