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
	 * @var string The URL of the external survey.
	 */
	private $link;

	/**
	 * @var string The name of the URL parameter filled with the instance token appended to $link.
	 */
	private $instanceTokenParameterName;

	/**
	 * @var string The description of the privacy policy of the website that hosts the external survey.
	 */
	private $privacyPolicy;

	public function __construct(
		$name,
		$question,
		$description,
		$isEnabled,
		$coverage,
		$platforms,
		$link,
		$instanceTokenParameterName,
		$privacyPolicy
	) {
		parent::__construct( $name, $question, $description, $isEnabled, $coverage, $platforms );

		$this->name = $name;
		$this->link = $link;
		$this->instanceTokenParameterName = $instanceTokenParameterName;
		$this->privacyPolicy = $privacyPolicy;
		$this->isInsecure = !preg_match( '/https/i', wfMessage( $this->link )->plain() ) ? true : false;
	}

	public function getMessages() {
		return array_merge(
			parent::getMessages(), array( $this->privacyPolicy, $this->link )
		);
	}

	public function toArray() {
		return parent::toArray() + array(
			'name' => $this->name,
			'type' => 'external',
			'link' => $this->link,
			'instanceTokenParameterName' => $this->instanceTokenParameterName,
			'isInsecure' => $this->isInsecure,
			'privacyPolicy' => $this->privacyPolicy,
		);
	}
}
