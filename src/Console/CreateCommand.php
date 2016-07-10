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
			'database' => null,
			'hosts' => [],
		];

		if ($config->get('documents.enabled')) {
			$project['documents'] = $wd->createDocumentsDirectory($name);
		}

		if ($config->get('web.enabled')) {
			$project['web'] = $wd->createWebDirectory($name);
		}

		if ($config->get('database.enabled')) {
			$project['database'] = $wd->createDatabase($name);
		}
		
		if ($config->get('hosts.enabled')) {
			$project['hosts'] = $wd->addHostsFile($name);
		}

		# add the project
		$config->set('projects.' .$name, $project);

		# save config
		$wd->save();
	}

}