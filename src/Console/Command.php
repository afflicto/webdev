<?php

namespace Afflicto\Webdev\Console;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class Command extends \Symfony\Component\Console\Command\Command
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
	 * @var SymfonyStyle
	 */
	protected $io;

	protected function initialize(InputInterface $input, OutputInterface $output)
	{
		parent::initialize($input, $output);
		$this->in = $input;
		$this->out = $output;
		$this->io = new SymfonyStyle($this->in, $this->out);
	}

	public function ask($question, $default = null)
	{
		/** @var QuestionHelper $q */
		$q = $this->getHelper('question');

		if ( ! $question instanceof Question) {
			$question = new Question($question, $default);
		}

		return $q->ask($this->in, $this->out, $question);
	}

	public function choice($question, $choices, $default = null, $errorMessage = null)
	{
		/** @var QuestionHelper $q */
		$q = $this->getHelper('question');

		if ( ! $question instanceof ChoiceQuestion) {
			$question = new ChoiceQuestion($question, $choices, $default);
		}

		if ($errorMessage !== null) $question->setErrorMessage($errorMessage);

		return $q->ask($this->in, $this->out, $question);
	}

	/**
	 * @param string $question
	 * @param bool|null $default
	 * @return bool|string
	 */
	public function confirm($question, $default = true)
	{
		return $this->io->confirm($question, $default);
	}

}