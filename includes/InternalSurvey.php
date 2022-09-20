<?php

namespace QuickSurveys;

class InternalSurvey extends Survey {
	/**
	 * @var string[] The set of i18n message keys of the internal survey
	 *  answers.
	 */
	private $answers;

	/**
	 * @var bool
	 */
	private $shuffleAnswersDisplay;

	/**
	 * @var string|null
	 */
	private $freeformTextLabel;

	/**
	 * @var string|null
	 */
	private $embedElementId;

	/**
	 * @var string
	 */
	private $layout;

	/**
	 * @param string $name
	 * @param string $question
	 * @param string|null $description
	 * @param bool $isEnabled
	 * @param float $coverage
	 * @param array[] $platforms
	 * @param string|null $privacyPolicy
	 * @param string|null $additionalInfo
	 * @param string|null $confirmMsg
	 * @param SurveyAudience $audience
	 * @param string[] $answers
	 * @param bool $shuffleAnswersDisplay
	 * @param string|null $freeformTextLabel
	 * @param string|null $embedElementId
	 * @param string $layout
	 */
	public function __construct(
		$name,
		$question,
		$description,
		$isEnabled,
		$coverage,
		array $platforms,
		$privacyPolicy,
		$additionalInfo,
		$confirmMsg,
		SurveyAudience $audience,
		array $answers,
		$shuffleAnswersDisplay,
		$freeformTextLabel,
		$embedElementId,
		$layout
	) {
		parent::__construct(
			$name,
			$question,
			$description,
			$isEnabled,
			$coverage,
			$platforms,
			$privacyPolicy,
			$additionalInfo,
			$confirmMsg,
			$audience
		);

		$this->answers = $answers;
		$this->shuffleAnswersDisplay = $shuffleAnswersDisplay;
		$this->freeformTextLabel = $freeformTextLabel;
		$this->embedElementId = $embedElementId;
		$this->layout = $layout;
	}

	/** @inheritDoc */
	public function getMessages(): array {
		$messages = array_merge( parent::getMessages(), $this->answers );

		if ( $this->freeformTextLabel ) {
			$messages[] = $this->freeformTextLabel;
		}

		return $messages;
	}

	public function toArray(): array {
		return parent::toArray() + [
			'type' => 'internal',
			'answers' => $this->answers,
			'shuffleAnswersDisplay' => $this->shuffleAnswersDisplay,
			'freeformTextLabel' => $this->freeformTextLabel,
			'embedElementId' => $this->embedElementId,
			'layout' => $this->layout,
		];
	}
}
