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

	public function __construct(
		$name,
		$question,
		$description,
		$isEnabled,
		$coverage,
		$platforms,
		$privacyPolicy,
		SurveyAudience $audience,
		$link,
		$instanceTokenParameterName
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

		$this->link = $link;
		$this->instanceTokenParameterName = $instanceTokenParameterName;
	}

	public function getMessages() {
		return array_merge( parent::getMessages(), [ $this->link ] );
	}

	private function getIsInsecure() {
		if ( $this->isInsecure === null ) {
			$url = wfMessage( $this->link )->inContentLanguage()->plain();
			$this->isInsecure = strpos( $url, 'http:' ) === 0;
		}
		return $this->isInsecure;
	}

	public function toArray() {
		return parent::toArray() + [
			'type' => 'external',
			'link' => $this->link,
			'instanceTokenParameterName' => $this->instanceTokenParameterName,
			'isInsecure' => $this->getIsInsecure(),
		];
	}
}
