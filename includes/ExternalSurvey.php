<?php

namespace QuickSurveys;

class ExternalSurvey extends Survey {
	/**
	 * @var bool Whether the survey runs on HTTPS or not.
	 */
	private $isInsecure = null;

	/**
	 * @var string|null The key of the message containing the URL of the external survey.
	 * @deprecated this field has been moved to SurveyQuestion
	 */
	private $link;

	/**
	 * @var string|null The name of the URL parameter filled with the instance token appended to $link.
	 * @deprecated this field has been moved to SurveyQuestion
	 */
	private $instanceTokenParameterName;

	/**
	 * @var string|null
	 * @deprecated this field has been moved to SurveyQuestion
	 */
	private $yesMsg;

	/**
	 * @var string|null
	 * @deprecated this field has been moved to SurveyQuestion
	 */
	private $noMsg;

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
	 * @param string|null $link
	 * @param string|null $instanceTokenParameterName
	 * @param string|null $yesMsg
	 * @param string|null $noMsg
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
		?string $link = null,
		?string $instanceTokenParameterName = null,
		?string $yesMsg = null,
		?string $noMsg = null
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

		if ( $link ) {
			wfDeprecated( 'QuickSurveys survey with link parameter', '1.43' );
		}
		if ( $instanceTokenParameterName ) {
			wfDeprecated( 'QuickSurveys survey with instanceTokenParameterName parameter', '1.43' );
		}
		if ( $yesMsg ) {
			wfDeprecated( 'QuickSurveys survey with yesMsg parameter', '1.43' );
		}
		if ( $noMsg ) {
			wfDeprecated( 'QuickSurveys survey with noMsg parameter', '1.43' );
		}

		$this->link = $link;
		$this->instanceTokenParameterName = $instanceTokenParameterName;
		$this->yesMsg = $yesMsg;
		$this->noMsg = $noMsg;
	}

	/**
	 * @return string[]
	 */
	public function getMessages(): array {
		$messages = [];

		if ( $this->link !== null ) {
			$messages[] = $this->link;
		}

		if ( $this->yesMsg !== null ) {
			$messages[] = $this->yesMsg;
		}

		if ( $this->noMsg !== null ) {
			$messages[] = $this->noMsg;
		}

		return array_merge( parent::getMessages(), $messages );
	}

	public function toArray(): array {
		return parent::toArray() + [
			'type' => 'external',
			'link' => $this->link,
			'instanceTokenParameterName' => $this->instanceTokenParameterName,
			'yesMsg' => $this->yesMsg,
			'noMsg' => $this->noMsg,
		];
	}
}
