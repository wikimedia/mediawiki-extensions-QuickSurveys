<?php

namespace QuickSurveys;

use Wikimedia\Assert\Assert;
use Wikimedia\Assert\ParameterTypeException;

class SurveyAudience {
	/**
	 * a list of accepted keys and their required types
	 * @var array
	 */
	private $validKeys = [
		'minEdits' => 'integer',
		'maxEdits' => 'integer',
		'countries' => 'array',
	];

	/**
	 * an internal description of the audience with validated data.
	 * @var array
	 */
	private $audience;

	/**
	 * @param array $audienceDefinition defining the audience with keys
	 * 	that match the available keys defined in $validKeys
	 * @throws ParameterTypeException when a key has the wrong type
	 */
	public function __construct( $audienceDefinition ) {
		$audienceData = [];
		foreach ( $this->validKeys as $name => $type ) {
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
	public function toArray() {
		return $this->audience;
	}
}
