<?php

namespace QuickSurveys;

class ExternalSurvey extends Survey
{
	/**
	 * @var string The URL of the external survey.
	 */
	private $link;

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
		$link,
		$privacyPolicy
	) {
		parent::__construct( $name, $question, $description, $isEnabled, $coverage );

		$this->link = $link;
		$this->privacyPolicy = $privacyPolicy;
	}

	public function getMessages() {
		return array_merge( parent::getMessages(), array( $this->privacyPolicy ) );
	}

	public function toArray() {
		return parent::toArray() + array(
			'type' => 'external',
			'link' => $this->link,
			'privacyPolicy' => $this->privacyPolicy,
		);
	}
}
