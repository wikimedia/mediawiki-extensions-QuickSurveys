<?php

namespace QuickSurveys;

class InternalSurvey extends Survey {
	/**
	 * @var string The name of the internal survey.
	 */
	private $name;
	/**
	 * @var array The set of i18n message keys of the internal survey
	 *  answers.
	 */
	private $answers;

	private $shuffleAnswersDisplay;

	public function __construct(
		$name,
		$question,
		$description,
		$isEnabled,
		$coverage,
		$platforms,
		$privacyPolicy,
		$audience,
		array $answers,
		$shuffleAnswersDisplay
	) {
		parent::__construct(
			$name,
			$question,
			$description,
			$isEnabled,
			$coverage,
			$platforms,
			$privacyPolicy,
			$audience
		);

		$this->answers = $answers;
		$this->shuffleAnswersDisplay = $shuffleAnswersDisplay;
	}

	public function getMessages() {
		return array_merge( parent::getMessages(), $this->answers );
	}

	public function toArray() {
		return parent::toArray() + [
			'name' => $this->name,
			'type' => 'internal',
			'answers' => $this->answers,
			'shuffleAnswersDisplay' => $this->shuffleAnswersDisplay
		];
	}
}
