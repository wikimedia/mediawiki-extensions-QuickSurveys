<?php

namespace QuickSurveys;

class InternalSurvey extends Survey {
	/**
	 * @var string[] The set of i18n message keys of the internal survey
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
		$platforms,
		$privacyPolicy,
		$audience,
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

	public function getMessages() {
		$messages = array_merge( parent::getMessages(), $this->answers );

		if ( $this->freeformTextLabel ) {
			$messages[] = $this->freeformTextLabel;
		}

		return $messages;
	}

	public function toArray() {
		return parent::toArray() + [
			'type' => 'internal',
			'answers' => $this->answers,
			'shuffleAnswersDisplay' => $this->shuffleAnswersDisplay,
			'freeformTextLabel' => $this->freeformTextLabel
		];
	}
}
