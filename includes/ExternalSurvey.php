<?php

namespace QuickSurveys;

class ExternalSurvey extends Survey {
	/**
	 * @var bool whether the survey runs on https or not.
	 */
	private $isInsecure;

	/**
	 * @var string The name of the external survey.
	 */
	private $name;

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
			$privacyPolicy
		);

		$this->name = $name;
		$this->link = $link;
		$this->instanceTokenParameterName = $instanceTokenParameterName;
		$url = wfMessage( $this->link )->inContentLanguage()->plain();
		$this->isInsecure = strpos( $url, 'http:' ) === 0;
	}

	public function getMessages() {
		return array_merge( parent::getMessages(), array( $this->link ) );
	}

	public function toArray() {
		return parent::toArray() + array(
			'name' => $this->name,
			'type' => 'external',
			'link' => $this->link,
			'instanceTokenParameterName' => $this->instanceTokenParameterName,
			'isInsecure' => $this->isInsecure,
		);
	}
}
