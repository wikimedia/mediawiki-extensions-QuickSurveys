<?php

namespace QuickSurveys;

use Wikimedia\Assert\ParameterTypeException;

class SurveyAudience extends Schema {

	/**
	 * A list of keys that need to be defined in a date range.
	 */
	private const VALID_DATE_RANGE_KEYS = [
		'from' => 'string',
		'to' => 'string',
	];

	/**
	 * A list of accepted audience keys and their required types.
	 */
	private const VALID_AUDIENCE_KEYS = [
		'minEdits' => 'integer',
		'maxEdits' => 'integer',
		'countries' => 'array',
		'anons' => 'boolean',
		'registrationStart' => 'string',
		'registrationEnd' => 'string',
		'pageIds' => 'array',
		'userAgent' => 'array',
		'firstEdit' => [ self::ARRAY, self::VALID_DATE_RANGE_KEYS ],
		'lastEdit' => [ self::ARRAY, self::VALID_DATE_RANGE_KEYS ],
	];

	/**
	 * Validate a survey audience definition.
	 *
	 * @param array $audienceDefinition defining the audience with keys
	 * 	that match the available keys defined in VALID_KEYS
	 * @throws ParameterTypeException when a key has the wrong type
	 */
	public function __construct( array $audienceDefinition ) {
		parent::__construct( $audienceDefinition, self::VALID_AUDIENCE_KEYS );
	}
}
