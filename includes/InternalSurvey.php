<?php

namespace QuickSurveys;

class InternalSurvey extends Survey {
	/**
	 * @var string[]|null The set of i18n message keys of the internal survey answers.
	 * @deprecated
	 */
	private $answers;

	/**
	 * @var bool|null
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
	 * @var string|null
	 * @deprecated
	 */
	private $layout;

	/**
	 * @param string $name
	 * @param float $coverage
	 * @param array[] $platforms
	 * @param string|null $privacyPolicy
	 * @param string|null $additionalInfo
	 * @param string|null $confirmMsg
	 * @param SurveyAudience $audience
	 * @param string|SurveyQuestion[] $questions
	 * @param string|null $question
	 * @param string|null $description
	 * @param string|null $confirmDescription
	 * @param string[]|null $answers
	 * @param bool|null $shuffleAnswersDisplay
	 * @param string|null $freeformTextLabel
	 * @param string|null $embedElementId
	 * @param string|null $layout
	 */
	public function __construct(
		$name,
		$coverage,
		array $platforms,
		$privacyPolicy,
		$additionalInfo,
		$confirmMsg,
		SurveyAudience $audience,
		$questions,
		?string $question = null,
		?string $description = null,
		?string $confirmDescription = null,
		?array $answers = null,
		?bool $shuffleAnswersDisplay = null,
		?string $freeformTextLabel = null,
		?string $embedElementId = null,
		?string $layout = null
	) {
		parent::__construct(
			$name,
			$coverage,
			$platforms,
			$privacyPolicy,
			$additionalInfo,
			$confirmMsg,
			$audience,
			$questions,
			$question,
			$description,
			$confirmDescription
		);

		if ( $answers ) {
			wfDeprecated( 'QuickSurveys survey with answers parameter', '1.43' );
		}
		if ( $shuffleAnswersDisplay ) {
			wfDeprecated( 'QuickSurveys survey with shuffleAnswersDisplay parameter', '1.43' );
		}
		if ( $freeformTextLabel ) {
			wfDeprecated( 'QuickSurveys survey with freeformTextLabel parameter', '1.43' );
		}
		if ( $layout ) {
			wfDeprecated( 'QuickSurveys survey with layout parameter', '1.43' );
		}

		$this->answers = $answers;
		$this->shuffleAnswersDisplay = $shuffleAnswersDisplay;
		$this->freeformTextLabel = $freeformTextLabel;
		$this->embedElementId = $embedElementId;
		$this->layout = $layout;
	}

	/** @inheritDoc */
	public function getMessages(): array {
		$messages = array_merge( parent::getMessages(), $this->answers ?? [] );

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
