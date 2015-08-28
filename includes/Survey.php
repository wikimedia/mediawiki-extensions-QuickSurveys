<?php

namespace QuickSurveys;

abstract class Survey
{
	/**
	 * @var string The friendly name of the survey
	 */
	private $name;

	/**
	 * @var string The question that the survey is posing to the user
	 */
	private $question;

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

	public function __construct(
		$name,
		$question,
		$description,
		$isEnabled,
		$coverage
	) {
		$this->name = $name;
		$this->question = $question;
		$this->description = $description;
		$this->isEnabled = $isEnabled;
		$this->coverage = $coverage;
	}

	// --------
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
	 * @return [string]
	 */
	public function getMessages() {
		return array(
			$this->question,

			// FIXME: Should description be optional?
			$this->description,
		);
	}
	// --------

	/**
	 * Returns the JSON-encodable, minimal representation of the survey
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			'name' => $this->name,
			'question' => $this->question,
			'description' => $this->description,
			'module' => $this->getResourceLoaderModuleName(),
			'coverage' => $this->coverage,
		);
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
