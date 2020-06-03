<?php

namespace QuickSurveys;

use Wikimedia\Assert\Assert;
use Wikimedia\Assert\ParameterTypeException;

class SurveyAudience {
	/**
	 * a list of accepted keys and their required types
	 */
	private const VALID_KEYS = [
		'minEdits' => 'integer',
		'maxEdits' => 'integer',
		'countries' => 'array',
		'anons' => 'boolean',
		'registrationStart' => 'string',
		'registrationEnd' => 'string',
	];

	/**
	 * an internal description of the audience with validated data.
	 * @var array
	 */
	private $audience;

	/**
	 * @param array $audienceDefinition defining the audience with keys
	 * 	that match the available keys defined in VALID_KEYS
	 * @throws ParameterTypeException when a key has the wrong type
	 */
	public function __construct( array $audienceDefinition ) {
		$audienceData = [];
		foreach ( self::VALID_KEYS as $name => $type ) {
			if ( array_key_exists( $name, $audienceDefinition ) ) {
				Assert::parameterType( $type, $audienceDefinition[ $name ], $name );
				// data is in the correct form so add.
				$audienceData[$name] = $audienceDefinition[$name];
			}
		}
		$this->audience = $audienceData;
	}

	/**
	 * Returns the JSON-encodable, minimal representation of the survey audience
	 *
	 * @return array
	 */
	public function toArray() : array {
		return $this->audience;
	}
}
