<?php

namespace Afflicto\Webdev\Console;

use Afflicto\Webdev\Webdev;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{

	protected function configure()
	{
		$this
			->setName('create')
			->setDescription('Create a new Project.')
			->addArgument('name', InputArgument::REQUIRED, 'Project name, lowercase snake_case is recommended as it will be the directory and domain name.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$wd = Webdev::getInstance();
		$config = $wd->config;

		$name = $input->getArgument('name');

		$output->writeln('Generating project "' .$name .'"...');

		$project = [
			'name' => $name,
			'documents' => null,
			'web' => null,
			'virtualhost' => null,
			'database' => null,
			'hosts' => [],
		];

		if ($config->get('documents.enabled')) {
			$project['documents'] = $wd->createDocumentsDirectory($name);
		}

		if ($config->get('web.enabled')) {
			$project['web'] = $wd->createWebDirectory($name);

			# is it a wamp web server?
			if ($config->get('web.default') == 'wamp') {

				# is virtualhosts managed?
				if ($config->get('web.providers.wamp.virtualhosts.enabled')) {
					$file = $config->get('web.providers.wamp.virtualhosts.file');
					$output->writeln('Generating wamp virtualhost directive in "' .$file .'"...');

					$choice = $this->choice("<question>Should wamp serve the web root of your project, or a sub-directory like 'public'?.</question>\n",
						[
							'It should serve the project web root (' .$project['web'] .').',
							'A sub-directory of my choosing.',
						]
					);

					if ($choice == 'A sub-directory of my choosing.') {
						$vhost_dir = $this->ask("Tell me the name of the sub-directory (default is 'public')\n-> ", 'public');
					}else {
						$vhost_dir = '';
					}

					$vhost_dir = rtrim($project['web'] .'/' .$vhost_dir, '/');

					$web['virtualhost'] = $wd->createVirtualhostDirective($name, $vhost_dir);
				}
			}
		}

		if ($config->get('database.enabled')) {
			$project['database'] = $wd->createDatabase($name);
		}
		
		if ($config->get('hosts.enabled')) {
			$project['hosts'] = $wd->addHostsFile($name);
		}

		# add the project
		$config->set('projects.' .$name, $project);

		$output->writeln("Your project looks like this:");
		$output->writeln($name .' => ' .var_export($project, true));

		if ($this->io->confirm('Does it look ok?', true)) {
			# save config
			$wd->save();

			$output->writeln('Done!');
		}
	}

}