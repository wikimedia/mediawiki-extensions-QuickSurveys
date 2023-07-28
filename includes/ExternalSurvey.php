<?php

namespace QuickSurveys;

class ExternalSurvey extends Survey {
	/**
	 * @var bool Whether the survey runs on HTTPS or not.
	 */
	private $isInsecure = null;

	/**
	 * @var string The key of the message containing the URL of the external survey.
	 */
	private $link;

	/**
	 * @var string The name of the URL parameter filled with the instance token appended to $link.
	 */
	private $instanceTokenParameterName;

	/**
	 * @var string
	 */
	private $yesMsg;

	/**
	 * @var string
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
	 * @param string $link
	 * @param string $instanceTokenParameterName
	 * @param ?string $yesMsg
	 * @param ?string $noMsg
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
		$link,
		$instanceTokenParameterName,
		?string $yesMsg = null,
		?string $noMsg = null
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
			$audience
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
		return array_merge( parent::getMessages(), [
			$this->link,
			$this->yesMsg,
			$this->noMsg,
		] );
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
