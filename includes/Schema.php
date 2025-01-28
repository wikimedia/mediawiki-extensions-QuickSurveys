<?php

// Abstract checker and schema get functions

namespace QuickSurveys;

use Wikimedia\Assert\Assert;
use Wikimedia\Assert\ParameterTypeException;

class Schema {

	protected const ARRAY_OF = '_arrayOf';
	protected const ARRAY = '_array';

	/**
	 * An internal description of the data that matches a schema.
	 */
	private array $data;

	/**
	 * Validate data against a provided type definition.
	 *
	 * @param array $data data to validate
	 * @param array $typeDefinition the type to validate the data against
	 * @throws ParameterTypeException when a key has the wrong type
	 */
	public function __construct( array $data, array $typeDefinition ) {
		$this->data = self::validateDefinition( $data, $typeDefinition );
	}

	/**
	 * Returns the JSON-encodable, minimal representation of the survey question.
	 */
	public function toArray(): array {
		return $this->data;
	}

	/**
	 * Validates an array against a specified type definition and only includes
	 * keys that align with the definition.
	 *
	 * @throws ParameterTypeException when a key has the wrong type
	 */
	private static function validateDefinition( array $data, array $typeDefinition ): array {
		$newDefinition = [];

		foreach ( $typeDefinition as $name => $type ) {
			if ( array_key_exists( $name, $data ) && isset( $data[$name] ) ) {
				$value = $data[$name];

				if ( is_array( $type ) && $type[0] === self::ARRAY_OF ) {
					Assert::parameterType( 'array', $data[$name], $name );
					foreach ( $value as $subValue ) {
						$newDefinition[$name][] = self::validateDefinition( $subValue, $type[1] );
					}
				} elseif ( is_array( $type ) && $type[0] === self::ARRAY ) {
					Assert::parameterType( 'array', $data[$name], $name );
					$newDefinition[$name] = self::validateDefinition( $value, $type[1] );
				} else {
					Assert::parameterType( $type, $value, $name );
					$newDefinition[$name] = $value;
				}
			}
		}

		return $newDefinition;
	}
}
