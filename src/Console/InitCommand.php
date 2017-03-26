<?php

namespace Afflicto\Webdev\Console;

use Afflicto\Webdev\Webdev;
use Illuminate\Config\Repository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
	/**
	 * @var InputInterface
	 */
	private $in;

	/**
	 * @var OutputInterface
	 */
	private $out;

	/**
	 * @var Webdev
	 */
	private $wd;

	/**
	 * @var Repository
	 */
	private $config;

	protected function configure()
	{
		$this
			->setName('init')
			->setDescription('Initialize (or re-initialize the webdev tool.');
	}

	protected function addWebServer()
	{
		$provider = $this->choice("What web server is it?", ['apache' ,'nginx', 'wamp', 'other'], 'apache');
		$this->config->set('web.enabled', true);
		$this->config->set('web.providers.' .$provider, []);
		$this->config->set('web.default', $provider);
		
		$web_root = null;
		if ($provider == 'wamp') {
			if (is_dir($guess = 'C:/wamp64/www')) {
				$web_root = $guess;
			}else if (is_dir($guess = 'D:/wamp64/www')) {
				$web_root = $guess;
			}else if (is_dir($guess = 'F:/wamp64/www')) {
				$web_root = $guess;
			}
		}

		# if we failed to detect it, or we made a mistake
		if ($web_root == null || $this->confirm("is '$web_root' the web root for your '$provider'?'") == false) {
			$web_root = $this->ask("Ok, where is the web root for $provider?\n-> ");
		}
		
		if ($web_root != null) {
			$web_root = rtrim(str_replace('\\', '/', $web_root), '/');
			$this->config->set('web.providers.' .$provider .'.web_root', $web_root);
		}

		$this->config->set('web.default', $provider);

		if ($provider == 'wamp' || $provider == 'apache') {
			$virtualhosts_enabled = false;

			if ($this->confirm("Would you like me to manage virtualhost directives for your projects? (y/n)\n-> ")) {
				$virtualhosts_enabled = true;
			}

			# enable virtualhosts
			$this->config->set('web.providers.' .$provider .'.virtualhosts.enabled', $virtualhosts_enabled);

			if ($provider == 'wamp') {
				$virtualhosts_file = $this->ask("Where is your virtualhosts file located?\n-> ", null);
				$virtualhosts_file = str_replace('\\', '/', $virtualhosts_file);
				$this->config->set('web.providers.' .$provider .'.virtualhosts.file', $virtualhosts_file);
			}else if ($provider == 'apache') {
				$this->out->writeln("I don't know how to manage virtualhosts for apache under unix-like OS'es yet.");
			}
		}

		if ($this->ask("Would you like to define another web server?(y/n)", 'n') == 'y') {
			return $this->addWebServer();
		}else {
			$this->out->writeln('Ok!');
		}
	}

	protected function addDatabaseProvider()
	{
		$provider = $this->choice("What Database Server are you adding?", ['mysql', 'sqlite']);

		if ($provider == 'mysql') {
			$this->out->writeln("Tell me how to connect to it please...\n");
			$host = $this->ask("host = ? (press return to use 'localhost')\n-> ", 'localhost');
			$username = $this->ask('username = ?"\n -> ');
			$password = $this->ask("password = ? (press return for empty/no password)\n -> ", '');

			$this->config->set('database.providers.mysql', [
				'host' => $host,
				'username' => $username,
				'password' => $password,
			]);

			$this->config->set('database.default', $provider);
		}

		if ($this->ask("Would you like to define another database server?(y/n)", 'n') == 'y') {
			return $this->addDatabaseProvider();
		}else {
			$this->out->writeln('Ok!');
		}
	}

	protected function execute(InputInterface $in, OutputInterface $out)
	{
		$this->in = $in;
		$this->out = $out;

		$this->wd = Webdev::getInstance();
		$this->config = $this->wd->config;

		# I'm lazy.
		$config = $this->config;

		# reset config
		$config->set($this->wd->defaultConfiguration);

		//web servers
		if ($this->ask("First off. Would you like to add a web server? (y/n)\n-> ", 'n') == 'y') {
			$this->addWebServer();

			if (count($config->get('web.providers')) > 1) {
				$providers = array_keys($config->get('web.providers'));
				$default_provider = $this->choice("You have added more than one web server. Which one should be the default for new projects?", $providers);
				$config->set('web.default', $default_provider);
				$out->writeln('Ok!');
			}
		}else {
			$out->writeln('No web server then.');
		}

		//documents
		if ($this->ask("Do you also tend to create a separate folder somewhere else for things like documents, graphics and other resources? (y/n)\n-> ", 'n') == 'y') {
			$config->set('documents.enabled', true);
			$documents_root = $this->ask("Where do you store them?\n-> ");
			$documents_root = rtrim(str_replace('\\', '/', $documents_root), '/');
			$config->set('documents.root', $documents_root);
		}

		//hosts file
		if ($this->ask("Shall I manage the 'hosts' file on your system? For mapping 'newproject.dev' to 127.0.0.1 etc. (y/n)\n-> ", 'n') == 'y') {
			$config->set('hosts.enabled', true);
			
			if ($this->wd->getOS() == Webdev::OS_WINDOWS) {
				$hosts_file = 'C:/Windows/System32/drivers/etc/hosts';
			}else {
				$hosts_file = '/etc/hosts';
			}


			if ($this->ask("Is '" .$hosts_file . "' your hosts file? (y/n)\n-> ", 'no') !== 'y') {
				$hosts_file = $this->ask("Ok. Where is it?\n-> ");
			}

			$hosts_file = rtrim(str_replace('\\', '/', $hosts_file), '/');

			$config->set('hosts.file', $hosts_file);
		}

		//database
		if ($this->ask("Shall I help you setup databases for your projects too? (y/n)\n-> ", 'no') == 'y') {
			$out->writeln("Ok...");
			$this->config->set('database.enabled', true);
			$this->addDatabaseProvider();

			if (count($config->get('database.providers')) > 1) {
				$providers = array_keys($config->get('database.providers'));
				$default_provider = $this->choice("You have added more than one database server. Which one should be the default for new projects?", $providers);
				$config->set('database.default', $default_provider);
				$out->writeln('Ok!');
			}
		}

		if ($this->ask("Do you use git? I can 'git init' automatically, or ask each time, whenever you create a new project. (y/n)\n-> ", 'no')) {
			$this->config->set('git', true);
		}

		$out->writeln("<info>All set! To get started, run 'webdev list' to get a list of available commands. Perhaps try 'webdev create' to create a new project?\n");
		$this->wd->save();
	}

}