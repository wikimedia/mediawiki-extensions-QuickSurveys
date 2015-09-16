<?php

namespace QuickSurveys;

class InternalSurvey extends Survey
{
	/**
	 * @var string The name of the internal survey.
	 */
	private $name;
	/**
	 * @var array The set of i18n message keys of the internal survey
	 *  answers.
	 */
	private $answers;

	public function __construct(
		$name,
		$question,
		$description,
		$isEnabled,
		$coverage,
		array $answers
	) {
		parent::__construct( $name, $question, $description, $isEnabled, $coverage );

		$this->name = $name;
		$this->answers = $answers;
	}

	public function getMessages() {
		return array_merge( parent::getMessages(), $this->answers );
	}

	public function toArray() {
		return parent::toArray() + array(
			'name' => $this->name,
			'type' => 'internal',
			'answers' => $this->answers,
		);
	}
}
