<?php

namespace QuickSurveys;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class SurveyFactory {
	private const VALID_PLATFORM_MODES = [
		'desktop' => [
			'stable',
		],
		'mobile' => [
			'stable',
			'beta',
		],
	];

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * Inject services.
	 *
	 * @param LoggerInterface $logger
	 */
	public function __construct( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @param array[] $specs Raw configuration from $wgQuickSurveysConfig
	 * @return Survey[] List of valid and enabled surveys
	 */
	public function parseSurveyConfig( array $specs ): array {
		$surveysOrInvalid = [];

		foreach ( $specs as $spec ) {
			if ( $this->validateUniqueName( $spec, $specs ) ) {
				$surveysOrInvalid[] = $this->newSurvey( $spec );
			}
		}
		$enabledSurveys = array_filter(
			$surveysOrInvalid,
			static function ( ?Survey $survey ): bool {
				return $survey && $survey->isEnabled();
			}
		);

		// @phan-suppress-next-line PhanTypeMismatchReturn array_filter removes null entries
		return array_values( $enabledSurveys );
	}

	/**
	 * checks QuickSurveys name for duplications
	 *
	 * @param array $spec
	 * @param array $specs
	 * @return bool
	 */
	private function validateUniqueName( $spec, $specs ) {
		if ( !isset( $spec[ 'name' ] ) ) {
			$this->logger->error( "Bad survey configuration: The survey name does not have a value",
						[ 'exception' => "Bad survey configuration: The survey name does not have a value" ] );
			return false;
		}
		$retBool = false;
		$name = trim( $spec[ 'name' ] );
		$currentName = strtoupper( $name );
		$enabledNameArray = [];

		// get array of current enabled quicksurveys name
		foreach ( $specs as $specArray ) {
			$enabled = array_key_exists( 'enabled', $specArray ) && $specArray[ 'enabled' ];
			$surveyName = array_key_exists( 'name', $specArray ) ? strtoupper( trim( $specArray[ 'name' ] ) ) : null;
			if ( $enabled && $surveyName !== null ) {
				$enabledNameArray[] = $surveyName;
			}
		}

		// make sure there are enabled surveys, then check
		if ( !empty( $enabledNameArray ) ) {
			// verify that $currentName is in the $enabledNameArray only 0 or 1 time
			$matches = preg_grep( '/' . $currentName . '/i', $enabledNameArray );
			// get the count
			$numberDuplicates = ( !is_array( $matches ) && $matches === false ) ? 0 : count( $matches );
			// if there is more than one copy of the item, it is a duplicate, enter log message
			if ( $numberDuplicates <= 1 ) {
				$retBool = true;
			} else {
				// write out to logger
				$this->logger->error( "Bad survey configuration: The survey name \"{$name}\" is not unique",
									[ 'exception' => "The \"{$name}\" survey name is not unique" ] );
			}
		} else {
			$retBool = true;
		}

		return $retBool;
	}

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
	 *   <li>An internal survey must have a layout of either "single-answer" or "multiple-answer"</li>
	 * </ul>
	 *
	 * @param array $spec
	 * @return Survey|null
	 */
	public function newSurvey( array $spec ): ?Survey {
		try {
			$this->validateSpec( $spec );

			if ( !isset( $spec['enabled'] ) ) {
				$spec['enabled'] = false;
			}

			$survey = $spec['type'] === 'internal'
				? $this->factoryInternal( $spec )
				: $this->factoryExternal( $spec );

			return $survey;
		} catch ( InvalidArgumentException $ex ) {
			$this->logger->error( "Bad survey configuration: " . $ex->getMessage(), [ 'exception' => $ex ] );
			return null;
		}
	}

	/**
	 * @param array $spec
	 * @throws InvalidArgumentException
	 */
	private function validateSpec( array $spec ) {
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

		if ( $spec['type'] === 'external' && isset( $spec['link'] ) ) {
			$link = $spec['link'];
			$url = wfMessage( $link )->inContentLanguage()->plain();
			$bit = parse_url( $url, PHP_URL_SCHEME );

			if ( $bit !== 'https' ) {
				throw new InvalidArgumentException( "The \"{$name}\" external survey must have a secure url." );
			}
		}

		$this->validatePlatforms( $spec );
	}

	/**
	 * @param array $spec
	 * @throws InvalidArgumentException
	 */
	private function validatePlatforms( array $spec ) {
		foreach ( self::VALID_PLATFORM_MODES as $platform => $validModes ) {
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

	/**
	 * @param array $spec
	 * @throws InvalidArgumentException
	 * @return ExternalSurvey
	 */
	private function factoryExternal( $spec ): ExternalSurvey {
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
			$name,
			$spec['question'],
			$spec['description'] ?? null,
			$spec['enabled'],
			$spec['coverage'],
			$spec['platforms'],
			$spec['privacyPolicy'],
			$spec['additionalInfo'] ?? null,
			$spec['confirmMsg'] ?? null,
			new SurveyAudience( $spec['audience'] ?? [] ),
			$spec['link'],
			$spec['instanceTokenParameterName'] ?? ''
		);
	}

	/**
	 * @param array $spec
	 * @throws InvalidArgumentException
	 * @return InternalSurvey
	 */
	private function factoryInternal( $spec ): InternalSurvey {
		$name = $spec['name'];

		if ( !isset( $spec['answers'] ) ) {
			throw new InvalidArgumentException(
				"The \"{$name}\" internal survey doesn't have any answers."
			);
		}

		// TODO: Remove default value after a deprecation period.  See T255130.
		$layout = $spec['layout'] ?? 'single-answer';
		if ( !in_array( $layout, [ 'single-answer', 'multiple-answer' ] ) ) {
			throw new InvalidArgumentException(
				"The \"{$name}\" internal survey layout is not one of \"single-answer\" or " .
				"\"multiple-answer\"."
			);
		}

		return new InternalSurvey(
			$name,
			$spec['question'],
			$spec['description'] ?? null,
			$spec['enabled'],
			$spec['coverage'],
			$spec['platforms'],
			$spec['privacyPolicy'] ?? null,
			$spec['additionalInfo'] ?? null,
			$spec['confirmMsg'] ?? null,
			new SurveyAudience( $spec['audience'] ?? [] ),
			$spec['answers'],
			$spec['shuffleAnswersDisplay'] ?? true,
			$spec['freeformTextLabel'] ?? null,
			$spec['embedElementId'] ?? null,
			$layout
		);
	}
}
