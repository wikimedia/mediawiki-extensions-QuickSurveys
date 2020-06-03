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
		array $platforms,
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

	public function getMessages() : array {
		return array_merge( parent::getMessages(), [ $this->link ] );
	}

	/**
	 * @return bool True if the link does *not* use the https protocol
	 */
	private function getIsInsecure() : bool {
		if ( $this->isInsecure === null ) {
			$url = wfMessage( $this->link )->inContentLanguage()->plain();
			$this->isInsecure = strpos( $url, 'http:' ) === 0;
		}
		return $this->isInsecure;
	}

	public function toArray() : array {
		return parent::toArray() + [
			'type' => 'external',
			'link' => $this->link,
			'instanceTokenParameterName' => $this->instanceTokenParameterName,
			'isInsecure' => $this->getIsInsecure(),
		];
	}
}
