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

	private $freeformTextLabel;

	public function __construct(
		$name,
		$question,
		$description,
		$isEnabled,
		$coverage,
		array $platforms,
		$privacyPolicy,
		SurveyAudience $audience,
		array $answers,
		$shuffleAnswersDisplay,
		$freeformTextLabel
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
		$this->freeformTextLabel = $freeformTextLabel;
	}

	public function getMessages() : array {
		$messages = array_merge( parent::getMessages(), $this->answers );

		if ( $this->freeformTextLabel ) {
			$messages[] = $this->freeformTextLabel;
		}

		return $messages;
	}

	public function toArray() : array {
		return parent::toArray() + [
			'name' => $this->name,
			'type' => 'internal',
			'answers' => $this->answers,
			'shuffleAnswersDisplay' => $this->shuffleAnswersDisplay,
			'freeformTextLabel' => $this->freeformTextLabel
		];
	}
}
