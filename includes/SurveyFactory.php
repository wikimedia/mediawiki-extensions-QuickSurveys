<?php

namespace QuickSurveys;

use InvalidArgumentException;

class SurveyFactory {
	private static $VALID_PLATFORM_MODES = [
		'desktop' => [
			'stable',
		],
		'mobile' => [
			'stable',
			'beta',
		],
	];

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
	 * @param array $spec
	 * @throws InvalidArgumentException If the configuration is invalid
	 * @return Survey
	 */
	public static function factory( array $spec ) {
		$name = $spec['name'];

		if ( !isset( $spec['question'] ) ) {
			throw new InvalidArgumentException( "The \"{$name}\" survey doesn't have a question." );
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

		if ( !isset( $spec['platforms'] ) ) {
			throw new InvalidArgumentException( "The \"{$name}\" survey doesn't have any platforms." );
		}

		self::validatePlatforms( $spec );

		if ( !isset( $spec['enabled'] ) ) {
			$spec['enabled'] = false;
		}

		$survey = $spec['type'] === 'internal'
			? self::factoryInternal( $spec )
			: self::factoryExternal( $spec );

		return $survey;
	}

	private static function validatePlatforms( $spec ) {
		foreach ( self::$VALID_PLATFORM_MODES as $platform => $validModes ) {
			if ( !isset( $spec['platforms'][$platform] ) ) {
				continue;
			}

			$modes = $spec['platforms'][$platform];

			if (
				!is_array( $modes ) ||
				array_diff(
					$modes,
					array_intersect(
						$validModes,
						$modes
					)
				)
			) {
				throw new InvalidArgumentException(
					"The \"{$spec['name']}\" survey has specified an invalid platform. " .
					"Please specify one or more of the following for the \"{$platform}\" platform: " .
					implode( ', ', $validModes ) .
					'.'
				);
			}
		}
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

		if ( !isset( $spec['instanceTokenParameterName'] ) ) {
			$spec['instanceTokenParameterName'] = "";
		}

		return new ExternalSurvey(
			$spec['name'],
			$spec['question'],
			!empty( $spec['description'] ) ? $spec['description'] : null,
			$spec['enabled'],
			$spec['coverage'],
			$spec['platforms'],
			$spec['privacyPolicy'],
			new SurveyAudience( $spec['audience'] ?? [] ),
			$spec['link'],
			$spec['instanceTokenParameterName']
		);
	}

	private static function factoryInternal( $spec ) {
		$audience = new SurveyAudience( $spec['audience'] ?? [] );
		$name = $spec['name'];

		if ( !isset( $spec['answers'] ) ) {
			throw new InvalidArgumentException(
				"The \"{$name}\" internal survey doesn't have any answers."
			);
		}

		return new InternalSurvey(
			$name,
			$spec['question'],
			!empty( $spec['description'] ) ? $spec['description'] : null,
			$spec['enabled'],
			$spec['coverage'],
			$spec['platforms'],
			!empty( $spec['privacyPolicy'] ) ? $spec['privacyPolicy'] : null,
			$audience,
			$spec['answers'],
			!empty( $spec['shuffleAnswersDisplay'] ) ? $spec['shuffleAnswersDisplay'] : true,
			!empty( $spec['freeformTextLabel'] ) ? $spec['freeformTextLabel'] : null
		);
	}
}
