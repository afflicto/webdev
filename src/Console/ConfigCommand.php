<?php

namespace Afflicto\Webdev\Console;

use Afflicto\Webdev\Webdev;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends Command
{
	protected function configure()
	{
		$this
			->setName('config')
			->setDescription('Configure the webdev tool.')
			->addArgument('key', InputArgument::REQUIRED, 'The key of the configuration element.')
			->addArgument('value', InputArgument::OPTIONAL, 'New value to set.', null);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$wd = Webdev::getInstance();

		$key = $input->getArgument('key');

		$value = $input->getArgument('value');

		if ($value !== null) {
			$wd->config->set($key, $value);
			$wd->save();
			$output->writeln('updated ' .$key .' to ' .$value);
		}else {
			if ($wd->config->has($key)) {
				$value = $wd->config->get($key);
				if (is_array($value)) {
					$value = var_export($value, true);
				}
				$output->writeln($key .' => ' .$value);
			}else {
				$output->writeln('Unknown key.');
			}
		}
	}

}