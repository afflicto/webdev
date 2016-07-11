<?php

namespace Afflicto\Webdev\Console;

use Afflicto\Webdev\Webdev;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{

	protected function configure()
	{
		$this
			->setName('create')
			->setDescription('Create a new Project.')
			->addArgument('name', InputArgument::REQUIRED, 'Project name, lowercase snake_case is recommended as it will be the directory and domain name.')
			->addOption('no-db', null, InputOption::VALUE_NONE, "Don't create a database.'");
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

					$web['virtualhost'] = $wd->createVirtualhostDirective($file, $name, $vhost_dir);
				}
			}

			# 'git init'?
			if ($config->get('git')) {
				if ($this->ask("Run 'git init' in '" .$project['web'] ."'? (y/n)\n-> ", 'no')) {
					$output->writeln('Ok.');

					$cwd = getcwd();

					# go run git init
					chdir($project['web']);
					shell_exec('git init');

					chdir($cwd);

					$output->writeln('Git initialized!');
				}
			}
		}

		# create datbase
		if ($config->get('database.enabled') && $input->getOption('no-db') == false) {
			$project['database'] = $wd->createDatabase($name);
		}

		# add hosts
		if ($config->get('hosts.enabled')) {
			$project['hosts'] = $wd->addHostsFile($name);
		}

		# add the project
		$config->set('projects.' .$name, $project);

		# save config
		$wd->save();

		# done
		$output->writeln("Done! Generated Project:");
		$output->writeln($name .' => ' .var_export($project, true));
	}

}