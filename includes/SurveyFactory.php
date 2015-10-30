<?php

namespace QuickSurveys;

use InvalidArgumentException;
use ConfigFactory;

class SurveyFactory
{
	/**
	 * Creates an instance of either the InternalSurvey or ExternalSurvey class
	 * given a specification.
	 *
	 * An exception is thrown if any of the following conditions aren't met:
	 *
	 * <ul>
	 *   <li>A survey must have a question</li>
	 *   <li>A survey must have a description</li>
	 *   <li>A survey's type must be either "internal" or "external"</li>
	 *   <li>A survey must have a coverage</li>
	 *   <li>An internal survey must have a set of questions</li>
	 *   <li>An external survey must have a privacy policy</li>
	 * </ul>
	 *
	 * @throws InvalidArgumentException If the configuration is invalid
	 * @return Survey
	 */
	public static function factory( array $spec ) {
		$name = $spec['name'];

		if ( !isset( $spec['question'] ) ) {
			throw new InvalidArgumentException( "The \"{$name}\" survey doesn't have a question." );
		}

		if ( !isset( $spec['description'] ) ) {
			throw new InvalidArgumentException( "The \"{$name}\" survey doesn't have a description." );
		}

		if (
			!isset( $spec['type'] )
			|| ( $spec['type'] !== 'internal' && $spec['type'] !== 'external' )
		) {
			throw new InvalidArgumentException(
				"The \"{$name}\" survey isn't marked as internal or external."
			);
		}

		if ( !isset( $spec['coverage'] ) ) {
			throw new InvalidArgumentException( "The \"{$name}\" survey doesn't have a coverage." );
		}

		if ( !isset( $spec['enabled' ] ) ) {
			$spec['enabled'] = false;
		}

		if ( $spec['type'] === 'internal' ) {
			return self::factoryInternal( $spec );
		}

		return self::factoryExternal( $spec );
	}

	private static function factoryExternal( $spec ) {
		$name = $spec['name'];

		if ( !isset( $spec['link'] ) ) {
			throw new InvalidArgumentException( "The \"{$name}\" external survey doesn't have a link." );
		}

		if ( !isset( $spec['privacyPolicy'] ) ) {
			throw new InvalidArgumentException(
				"The \"{$name}\" external survey doesn't have a privacy policy."
			);
		}

		return new ExternalSurvey(
			$spec['name'],
			$spec['question'],
			$spec['description'],
			$spec['enabled'],
			$spec['coverage'],
			$spec['link'],
			$spec['privacyPolicy']
		);
	}

	private static function factoryInternal( $spec ) {
		$name = $spec['name'];

		if ( !isset( $spec['answers'] ) ) {
			throw new InvalidArgumentException(
				"The \"{$name}\" internal survey doesn't have any answers."
			);
		}

		return new InternalSurvey(
			$spec['name'],
			$spec['question'],
			$spec['description'],
			$spec['enabled'],
			$spec['coverage'],
			$spec['answers']
		);
	}
}
