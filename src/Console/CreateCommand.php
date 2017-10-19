<?php

namespace Arakash\Webdev\Console;

use Arakash\Webdev\Webdev;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{

	protected function configure()
	{
		$wd = Webdev::getInstance();
		$web_default = $wd->config->get('web.default');
		$database_default = $wd->config->get('database.default');

		$this
			->setName('create')
			->setDescription('Create a new Project.')
			->addArgument('name', InputArgument::REQUIRED, 'Project name, lowercase snake_case is recommended as it will be the directory and domain name.')
			->addArgument('web server provider', InputArgument::OPTIONAL, 'Use a web server other than the default (' .$web_default .'). Use "none" to disable for this project.', $web_default)
			->addArgument('database provider', InputArgument::OPTIONAL, 'Use a database provider other than the default (' .$database_default .'). Use "none" to disable for this project.', $database_default)
			->addArgument('database name', InputArgument::OPTIONAL, 'Database name. Defaults to the name of the project.');

	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$wd = Webdev::getInstance();
		$config = $wd->config;

		# input
		$name = $input->getArgument('name');
		$web_provider = $input->getArgument('web server provider');
		$database_provider = $input->getArgument('database provider');

		$database_name = $input->getArgument('database name');
		if ($database_name == null) $database_name = $name;

		$project = [
			'name' => $name,
			'documents' => null,
			'web' => [],
			'database' => [],
			'hosts' => [],
		];

		$output->writeln('Generating project "' .$name .'"...');

		if ($config->get('documents.enabled')) {
			$project['documents'] = $wd->createDocumentsDirectory($name);
		}

		if ($config->get('web.enabled') && $web_provider !== 'none') {
			$project['web'] = $wd->createWebDirectory($name, $web_provider);
			
			# is it a wamp web server?
			if ($config->get('web.default') == 'wamp') {
				
				# is virtualhosts managed?
				if ($config->get('web.providers.wamp.virtualhosts.enabled')) {
					$file = $config->get('web.providers.wamp.virtualhosts.file');
					$output->writeln('Generating wamp virtualhost directive in "' .$file .'"...');

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

					$project['web']['virtualhost'] = $wd->createVirtualhostDirective($file, $name, $vhost_dir);
				}
			}

			# is it apache?
            if ($config->get('web.default') == 'apache') {
			    if ($config->get('web.providers.apache.manage_sites')) {
			        $paths = $config->get('web.providers.apache.paths');

			        $availableFile = $paths['sites-available'] .'/80-' .$name .'.conf';
			        $enabledFile = $paths['sites-enabled'] .'/80-' .$name .'.conf';

			        # create the virtualhosts file
                    $h = fopen($availableFile, 'w+');
                    $str = "<VirtualHost *:80>\n\tServerName $name.dev";
                    $str .= "\n\tDocumentRoot " .$project['web']['root'];
                    $str .= "\n\n\t<Directory \"" .$project['web']['root'] ."\">";
                    $str .= "\n\t\tOrder allow,deny";
                    $str .= "\n\t\tAllow from 127.0.0.1";
                    $str .= "\n\t\tRequire all granted";
                    $str .= "\n\t</Directory>";
                    $str .= "\n</VirtualHost>";

			        fwrite($h, $str);
			        fclose($h);

			        # We could probably just run the 'a2ensite' apache command
                    # but let's do it manually. Simply symlink the virtualhost
                    # config file in sites_available to sites_enabled

                    shell_exec('ln -sf ' .$availableFile .' ' .$enabledFile);

                    # let's reload apache2
                    $output->writeln(shell_exec($config->get('web.providers.apache.reloadCommand')));
                }
            }

			# 'git init'?
			if ($config->get('git')) {
				if ($this->ask("Run 'git init' in '" .$project['web']['root'] ."'? (y/n)\n-> ", 'no')) {
					$output->writeln('Ok.');

					$cwd = getcwd();

					# go run git init
					chdir($project['web']['root']);
					shell_exec('git init');

					chdir($cwd);

					$output->writeln('Git initialized!');
				}
			}
		}

		# create datbase
		if ($config->get('database.enabled') && $database_provider !== 'none') {
			$project['database'] = $wd->createDatabase($database_name, $database_provider);
		}

		# add hosts
		if ($config->get('hosts.enabled')) {
			if ($this->confirm('Add hosts file?')) {
				$project['hosts'] = $wd->addHostsFile($name);
			}
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