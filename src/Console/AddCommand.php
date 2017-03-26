<?php

namespace Afflicto\Webdev\Console;

use Afflicto\Webdev\Webdev;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddCommand extends Command
{

	protected function configure()
	{
		$this
			->setName('add')
			->setDescription('Add an existing project.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$wd = Webdev::getInstance();
		$config = $wd->config;

		$project = [
			'name' => null,
			'documents' => null,
			'web' => null,
			'database' => null,
			'hosts' => [],
		];

		# web
		if ($config->get('web.enabled')) {

			$name = $this->ask("What's the name of the project's web root directory? This will be the project name as well.\n-> ");

			$project['name'] = $name;

			# do we have multiple web providers?
			if (count($config->get('web.providers')) > 1) {
				# get all webRoots
				$places = [];

				foreach($config->get('web.providers') as $name => $provider) {
					$places[$name] = $provider['web_root'];
				}

				$web = $this->choice("Where's the project located?", $places);
				$project['web'] = ['root' => $web .'/' .$name];
			}else {
				$defaultProvider = $config->get('web.default');

				$web = $config->get('web.providers.' .$defaultProvider .'.web_root') .'/' .$name;

				$output->writeln("Assumed location is '$web'.");

				$project['web'] = ['root' => $web, 'provider' => $defaultProvider];
			}

			# virtualhosts?
			if ($project['web']['provider'] == 'wamp' && $config->get('web.providers.wamp.virtualhosts.enabled') && $this->confirm("Create virtualhost directive?")) {

				$choice = $this->choice("<question>Should wamp serve the web root of your project, or a sub-directory like '/public'?.</question>\n",
					[
						'It should serve the project web root (' .$project['web']['root'] .').',
						'A sub-directory of my choosing.',
					]
				);

				if ($choice == 'A sub-directory of my choosing.') {
					$vhost_dir = $this->ask("Tell me the name of the sub-directory (default is 'public')\n-> ", 'public');
				}else {
					$vhost_dir = '';
				}

				$vhost_dir = rtrim($project['web']['root'] .'/' .$vhost_dir, '/');

				$project['web']['virtualhost'] = $wd->createVirtualhostDirective($config->get('web.providers.wamp.virtualhosts.file'), $project['name'], $vhost_dir);
			}
		}else {
			$project['name'] = $this->ask("What's the name of the project at least?\n-> ");
		}

		# hosts file
		if ($config->get('hosts.enabled') && $this->confirm('Update hosts file?')) {
			$project['hosts'] = $wd->addHostsFile($project['name']);
		}

		# database
		if ($config->get('database.enabled') && $this->confirm("Does this project use a database?")) {

			# determine database provider
			$database_provider = null;

			if (count($config->get('database.providers')) > 1) {
				$providers = array_keys($config->get('database.providers'));
				$database_provider = $this->choice("Choose a provider", $providers, $config->get('database.default'));
			}else {
				$database_provider = $config->get('database.default');
			}

			# get database name, or create one.
			if ($this->confirm("Does the database exist?")) {
				$dbName = $this->ask("What's the name of the database?\n-> ", $project['name']);
				$project['database'] = ['provider' => $database_provider, 'name' => $dbName];
			}else {
				if ($this->confirm("Shall I create one named '" .$project['name'] . "'?\n->")) {
					$project['database'] = $wd->createDatabase($project['name'], $database_provider);
				}else {
					$output->writeln('Ok.');
				}
			}
		}

		$config->set('projects.' .$project['name'], $project);

		# save
		$wd->save();
	}

}