<?php

namespace QuickSurveys;

abstract class Survey {
	/**
	 * @var string The friendly name of the survey
	 */
	private $name;

	/**
	 * @var string The question that the survey is posing to the user
	 */
	private $question;

	/**
	 * @var SurveyAudience describes the audience who can participate in a survey
	 */
	private $audience;

	/**
	 * @var string A user-friendly description of, or introduction to, the question
	 */
	private $description;

	/**
	 * @var bool Whether the survey is enabled
	 */
	private $isEnabled;

	/**
	 * @var float The percentage of users that will see the survey expressed as a fraction
	 */
	private $coverage;

	/**
	 * A platform can operate in one or more modes: mobile operates in 'stable' or 'beta' mode;
	 * and desktop only operates in 'stable' mode.
	 *
	 * The platforms that the survey can be displayed on, therefore, are repsented as a map of
	 * platform to a set of platform modes, i.e.
	 *
	 * <code><pre>
	 * <?php
	 * $platform = array(
	 *   'desktop' => array(
	 *   	'stable',
	 *   ),
	 *   'mobile' => array(
	 *   	'stable',
	 *   	'beta',
	 *   ),
	 * );
	 * </pre></code>
	 *
	 * @var array The platforms that the survey can be displayed on
	 */
	private $platforms;

	/**
	 * @var string The description of the privacy policy of the website that hosts the survey.
	 */
	private $privacyPolicy;

	public function __construct(
		$name,
		$question,
		$description,
		$isEnabled,
		$coverage,
		$platforms,
		$privacyPolicy,
		SurveyAudience $audience
	) {
		$this->name = $name;
		$this->question = $question;
		$this->description = $description;
		$this->isEnabled = $isEnabled;
		$this->coverage = $coverage;
		$this->platforms = $platforms;
		$this->privacyPolicy = $privacyPolicy;
		$this->audience = $audience;
	}

	/**
	 * Returns the name of the ResourceLoader module
	 *
	 * @return string
	 */
	public function getResourceLoaderModuleName() {
		return 'ext.quicksurveys.survey.' . str_replace( ' ', '.', $this->name );
	}

	/**
	 * Gets the list of i18n message keys that the survey uses
	 *
	 * @return string[]
	 */
	public function getMessages() {
		$messages = [
			$this->question,
		];

		if ( !empty( $this->description ) ) {
			$messages[] = $this->description;
		}

		if ( !empty( $this->privacyPolicy ) ) {
			$messages[] = $this->privacyPolicy;
		}
		return $messages;
	}

	/**
	 * Returns the JSON-encodable, minimal representation of the survey
	 *
	 * @return array
	 */
	public function toArray() {
		return [
			'audience' => $this->audience->toArray(),
			'name' => $this->name,
			'question' => $this->question,
			'description' => $this->description,
			'module' => $this->getResourceLoaderModuleName(),
			'coverage' => $this->coverage,
			'platforms' => $this->platforms,
			'privacyPolicy' => $this->privacyPolicy,
		];
	}

	/**
	 * Gets whether the survey is enabled
	 *
	 * @return bool
	 */
	public function isEnabled() {
		return $this->isEnabled;
	}
}
