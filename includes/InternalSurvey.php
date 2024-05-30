<?php

namespace QuickSurveys;

class InternalSurvey extends Survey {
	/**
	 * @var string[] The set of i18n message keys of the internal survey answers.
	 * @deprecated
	 */
	private $answers;

	/**
	 * @var bool
	 * @deprecated
	 */
	private $shuffleAnswersDisplay;

	/**
	 * @var string|null
	 * @deprecated
	 */
	private $freeformTextLabel;

	/**
	 * @var string|null
	 */
	private $embedElementId;

	/**
	 * @var string
	 * @deprecated
	 */
	private $layout;

	/**
	 * @param string $name
	 * @param string|null $question
	 * @param string|null $description
	 * @param float $coverage
	 * @param array[] $platforms
	 * @param string|null $privacyPolicy
	 * @param string|null $additionalInfo
	 * @param string|null $confirmMsg
	 * @param SurveyAudience $audience
	 * @param SurveyQuestion[] $questions
	 * @param string[] $answers
	 * @param bool $shuffleAnswersDisplay
	 * @param string|null $freeformTextLabel
	 * @param string|null $embedElementId
	 * @param string $layout
	 * @param string|null $confirmDescription
	 */
	public function __construct(
		$name,
		$question,
		$description,
		$coverage,
		array $platforms,
		$privacyPolicy,
		$additionalInfo,
		$confirmMsg,
		SurveyAudience $audience,
		array $questions,
		array $answers,
		$shuffleAnswersDisplay,
		$freeformTextLabel,
		$embedElementId,
		$layout,
		string $confirmDescription = null
	) {
		parent::__construct(
			$name,
			$question,
			$description,
			$coverage,
			$platforms,
			$privacyPolicy,
			$additionalInfo,
			$confirmMsg,
			$audience,
			$questions,
			$confirmDescription
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
