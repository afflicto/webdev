<?php

namespace Arakash\Webdev\Console;

use Arakash\Webdev\Webdev;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends Command
{
    private $indent = 0;

	protected function configure()
	{
		$this
			->setName('config')
			->setDescription('Configure the webdev tool.')
			->addArgument('key', InputArgument::OPTIONAL, 'The key of the configuration element.')
			->addArgument('value', InputArgument::OPTIONAL, 'New value to set.', null);
	}

    protected function renderValue($value)
    {
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_null($value)) return 'null';
        if (is_string($value)) return '<info>\'' .$value .'\'</info>';
        return $value;
    }

	protected function renderArray($key, $value)
    {
        $str = "";

        if (is_array($value) && ! empty($value)) {
            $str .= "\n";
            $this->indent++;

            foreach($value as $k => $v)
            {
                $str .= str_repeat('  ', $this->indent-1) .$k .' => ' .$this->renderArray($k, $v) ."\n";
            }

            $this->indent--;

            return rtrim($str);
        }else if (is_array($value)) {
            return "[]";
        }else {
            return $this->renderValue($value);
        }
    }

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$wd = Webdev::getInstance();

		$key = $input->getArgument('key');

		$value = $input->getArgument('value');

		if ($value !== null) {
		    if ($value === 'true') $value = true;
		    if ($value === 'false') $value = false;

			$wd->config->set($key, $value);
			$wd->save();
			$output->writeln('updated ' .$key .' to ' .$value);
		}else {
		    $value = null;

		    if ($key !== null && $wd->config->has($key)) {
		        $value = $wd->config->get($key);
            }else {
                $value = $wd->config->all();
            }

            if ( ! $value) {
		        $output->writeln('Unknown key.');
            }else {
                $value = $wd->config->get($key);
                $output->writeln(trim($this->renderArray($key, $value)));
            }
        }
	}

}