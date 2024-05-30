<?php

namespace QuickSurveys;

use Wikimedia\Assert\ParameterTypeException;

class SurveyQuestion extends Schema {

	/**
	 * A list of question dependencies and their required types.
	 */
	private const VALID_DEPENDS_ON_KEYS = [
		'question' => 'string',
		'answerIsOneOf' => 'array',
	];

	/**
	 * A list of accepted keys for answers and their required types.
	 */
	private const VALID_ANSWER_KEYS = [
		'label' => 'string',
		'freeformTextLabel' => 'string',
	];

	/**
	 * A list of accepted keys for internal survey questions and their required types.
	 */
	private const VALID_INTERNAL_QUESTION_KEYS = [
		'name' => 'string',
		'layout' => 'string',
		'question' => 'string',
		'description' => 'string',
		'shuffleAnswersDisplay' => 'boolean',
		'answers' => [ self::ARRAY_OF, self::VALID_ANSWER_KEYS ],
		'dependsOn' => [ self::ARRAY_OF, self::VALID_DEPENDS_ON_KEYS ],
	];

	/**
	 * A list of accepted keys for external survey questions and their required types.
	 */
	private const VALID_EXTERNAL_QUESTION_KEYS = [
		'name' => 'string',
		'question' => 'string',
		'description' => 'string',
		'link' => 'string',
		'instanceTokenParameterName' => 'string',
		'yesMsg' => 'string',
		'noMsg' => 'string',
	];

	/**
	 * Validate a survey question definition against the provided survey type.
	 *
	 * @param array $questionDefinition defining the question with keys
	 * 	that match the available keys defined in VALID_INTERNAL_QUESTION_KEYS
	 * @param string $surveyType the type of survey this question is meant for
	 * @throws ParameterTypeException when a key has the wrong type
	 */
	public function __construct( array $questionDefinition, string $surveyType ) {
		$surveyType === 'internal'
			? parent::__construct( $questionDefinition, self::VALID_INTERNAL_QUESTION_KEYS )
			: parent::__construct( $questionDefinition, self::VALID_EXTERNAL_QUESTION_KEYS );
	}

	public function getMessages(): array {
		// List the keys that need translation from the main question array
		$translationKeys = [ 'question', 'description', 'link', 'yesMsg', 'noMsg' ];
		$question = $this->toArray();
		$messages = [];

		foreach ( $translationKeys as $key ) {
			if ( isset( $question[ $key ] ) ) {
				$messages[] = $question[ $key ];
			}
		}

		// Handle the `answers` key separately
		if ( isset( $question[ 'answers' ] ) &&
			is_array( $question[ 'answers' ] ) ) {
			foreach ( $question[ 'answers' ] as $answer ) {
				if ( isset( $answer[ 'label' ] ) ) {
					$messages[] = $answer[ 'label' ];
				}
				if ( isset( $answer[ 'freeformTextLabel' ] ) ) {
					$messages[] = $answer[ 'freeformTextLabel' ];
				}
			}
		}

		return $messages;
	}
}
