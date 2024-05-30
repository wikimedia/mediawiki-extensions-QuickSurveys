<?php

namespace QuickSurveys;

class ExternalSurvey extends Survey {
	/**
	 * @var bool Whether the survey runs on HTTPS or not.
	 */
	private $isInsecure = null;

	/**
	 * @var string The key of the message containing the URL of the external survey.
	 * @deprecated
	 */
	private $link;

	/**
	 * @var string The name of the URL parameter filled with the instance token appended to $link.
	 * @deprecated
	 */
	private $instanceTokenParameterName;

	/**
	 * @var string
	 * @deprecated
	 */
	private $yesMsg;

	/**
	 * @var string
	 * @deprecated
	 */
	private $noMsg;

	/**
	 * @param string $name
	 * @param string $question
	 * @param string $description
	 * @param float $coverage
	 * @param array[] $platforms
	 * @param string $privacyPolicy
	 * @param string $additionalInfo
	 * @param string $confirmMsg
	 * @param SurveyAudience $audience
	 * @param SurveyQuestion[] $questions
	 * @param string $link
	 * @param string $instanceTokenParameterName
	 * @param ?string $yesMsg
	 * @param ?string $noMsg
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
		$link,
		$instanceTokenParameterName,
		?string $yesMsg = null,
		?string $noMsg = null,
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

		$this->link = $link;
		$this->instanceTokenParameterName = $instanceTokenParameterName;
		$this->yesMsg = $yesMsg ?? 'ext-quicksurveys-external-survey-yes-button';
		$this->noMsg = $noMsg ?? 'ext-quicksurveys-external-survey-no-button';
	}

	/**
	 * @return string[]
	 */
	public function getMessages(): array {
		$messages = array_merge( parent::getMessages(), [
			$this->yesMsg,
			$this->noMsg,
		] );

		if ( $this->link !== null ) {
			$messages[] = $this->link;
		}

		return $messages;
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
