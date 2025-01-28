<?php

namespace QuickSurveys;

abstract class Survey {
	/**
	 * @var string The friendly name of the survey
	 */
	private $name;

	/**
	 * @var string|null The question that the survey is posing to the user
	 * @deprecated use questions array instead
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
	 * Describes the audience who can participate in a survey
	 */
	private SurveyAudience $audience;

	/**
	 * @var string|null A user-friendly description of, or introduction to, the question
	 * @deprecated this field has been moved to SurveyQuestion
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
	private array $platforms;

	/**
	 * @var string|null The description of the privacy policy of the website that hosts the survey.
	 */
	private $privacyPolicy;

	/**
	 * @var SurveyQuestion[] The questions that the survey is posing to the user
	 */
	private array $questions;

	/**
	 * @var string|null
	 */
	private $confirmDescription;

	/**
	 * @param string $name
	 * @param float $coverage
	 * @param array[] $platforms
	 * @param string|null $privacyPolicy
	 * @param string|null $additionalInfo
	 * @param string|null $confirmMsg
	 * @param SurveyAudience $audience
	 * @param SurveyQuestion[] $questions
	 * @param string|null $question
	 * @param string|null $description
	 * @param string|null $confirmDescription
	 */
	public function __construct(
		$name,
		$coverage,
		array $platforms,
		$privacyPolicy,
		$additionalInfo,
		$confirmMsg,
		SurveyAudience $audience,
		array $questions,
		?string $question = null,
		?string $description = null,
		?string $confirmDescription = null
	) {
		if ( $question ) {
			wfDeprecated( 'QuickSurveys survey with question parameter', '1.43' );
		}
		if ( $description ) {
			wfDeprecated( 'QuickSurveys survey with description parameter', '1.43' );
		}

		$this->name = $name;
		$this->question = $question;
		$this->description = $description;
		$this->coverage = $coverage;
		$this->platforms = $platforms;
		$this->privacyPolicy = $privacyPolicy;
		$this->additionalInfo = $additionalInfo;
		$this->confirmMsg = $confirmMsg;
		$this->audience = $audience;
		$this->questions = $questions;
		$this->confirmDescription = $confirmDescription;
	}

	/**
	 * Returns the name of the ResourceLoader module
	 */
	public function getResourceLoaderModuleName(): string {
		return 'ext.quicksurveys.survey.' . str_replace( ' ', '.', $this->name );
	}

	public function getAudience(): SurveyAudience {
		return $this->audience;
	}

	/**
	 * Gets the list of i18n message keys that the survey uses
	 *
	 * @return string[]
	 */
	public function getMessages(): array {
		$messages = [];

		if ( $this->question !== null ) {
			$messages[] = $this->question;
		}

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

		if ( $this->confirmDescription !== null ) {
			$messages[] = $this->confirmDescription;
		}

		foreach ( $this->questions as $questionItem ) {
			$messages = array_merge( $messages, $questionItem->getMessages() );
		}
		return $messages;
	}

	/**
	 * Returns the JSON-encodable, minimal representation of the survey
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
			'questions' => array_map(
				static fn ( $question ) => $question->toArray(),
				$this->questions
			),
			'confirmDescription' => $this->confirmDescription
		];
	}

}
