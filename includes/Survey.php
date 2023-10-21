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
	 * @var string|null
	 */
	private $additionalInfo;

	/**
	 * @var string|null
	 */
	private $confirmMsg;

	/**
	 * @var SurveyAudience describes the audience who can participate in a survey
	 */
	private $audience;

	/**
	 * @var string|null A user-friendly description of, or introduction to, the question
	 */
	private $description;

	/**
	 * @var float The percentage of users that will see the survey expressed as a fraction
	 */
	private $coverage;

	/**
	 * A platform can operate in one or more modes: mobile operates in 'stable' or 'beta' mode;
	 * and desktop only operates in 'stable' mode.
	 *
	 * The platforms that the survey can be displayed on, therefore, are represented as a map of
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
	 * @var array[] The platforms that the survey can be displayed on
	 */
	private $platforms;

	/**
	 * @var string|null The description of the privacy policy of the website that hosts the survey.
	 */
	private $privacyPolicy;

	/**
	 * @param string $name
	 * @param string $question
	 * @param string|null $description
	 * @param float $coverage
	 * @param array[] $platforms
	 * @param string|null $privacyPolicy
	 * @param string|null $additionalInfo
	 * @param string|null $confirmMsg
	 * @param SurveyAudience $audience
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
		SurveyAudience $audience
	) {
		$this->name = $name;
		$this->question = $question;
		$this->description = $description;
		$this->coverage = $coverage;
		$this->platforms = $platforms;
		$this->privacyPolicy = $privacyPolicy;
		$this->additionalInfo = $additionalInfo;
		$this->confirmMsg = $confirmMsg;
		$this->audience = $audience;
	}

	/**
	 * Returns the name of the ResourceLoader module
	 *
	 * @return string
	 */
	public function getResourceLoaderModuleName(): string {
		return 'ext.quicksurveys.survey.' . str_replace( ' ', '.', $this->name );
	}

	/**
	 * @return SurveyAudience
	 */
	public function getAudience(): SurveyAudience {
		return $this->audience;
	}

	/**
	 * Gets the list of i18n message keys that the survey uses
	 *
	 * @return string[]
	 */
	public function getMessages(): array {
		$messages = [
			$this->question,
		];

		if ( $this->description !== null ) {
			$messages[] = $this->description;
		}

		if ( $this->privacyPolicy !== null ) {
			$messages[] = $this->privacyPolicy;
		}

		if ( $this->additionalInfo !== null ) {
			$messages[] = $this->additionalInfo;
		}

		if ( $this->confirmMsg !== null ) {
			$messages[] = $this->confirmMsg;
		}
		return $messages;
	}

	/**
	 * Returns the JSON-encodable, minimal representation of the survey
	 *
	 * @return array
	 */
	public function toArray(): array {
		return [
			'audience' => $this->audience->toArray(),
			'name' => $this->name,
			'question' => $this->question,
			'description' => $this->description,
			'module' => $this->getResourceLoaderModuleName(),
			'coverage' => $this->coverage,
			'platforms' => $this->platforms,
			'privacyPolicy' => $this->privacyPolicy,
			'additionalInfo' => $this->additionalInfo,
			'confirmMsg' => $this->confirmMsg,
		];
	}

}
