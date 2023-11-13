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
		if ( !$this->arrayIsList( $specs ) ) {
			$this->logger->error( 'Bad surveys configuration: The surveys configuration is not a list.' );

			return [];
		}

		$surveys = [];
		foreach ( $specs as $spec ) {
			$enabled = $spec['enabled'] ?? false;
			if ( $this->validateUniqueName( $spec, $specs ) && $enabled ) {
				$survey = $this->newSurvey( $spec );
				if ( $survey ) {
					$surveys[] = $survey;
				}
			}
		}
		return $surveys;
	}

	/**
	 * Gets whether the array is a list, i.e. an integer-indexed array with indices starting at 0.
	 *
	 * As written, this method trades performance for elegance. This method should not be called on
	 * large arrays.
	 *
	 * TODO: Replace this with array_is_list when MediaWiki supports PHP >= 8.1
	 *
	 * @param array $array
	 * @return bool
	 */
	private function arrayIsList( array $array ): bool {
		$array = array_keys( $array );

		return $array === array_keys( $array );
	}

	/**
	 * checks QuickSurveys name for duplications
	 *
	 * @param array $spec
	 * @param array[] $specs
	 * @return bool
	 */
	private function validateUniqueName( array $spec, array $specs ): bool {
		if ( !isset( $spec[ 'name' ] ) ) {
			$this->logger->error( "Bad survey configuration: The survey name does not have a value",
						[ 'exception' => "Bad survey configuration: The survey name does not have a value" ] );
			return false;
		}
		if ( count( $specs ) < 2 ) {
			return true;
		}

		$name = trim( $spec[ 'name' ] );
		$numberDuplicates = 0;

		foreach ( $specs as $specArray ) {
			// if there is more than one copy of the item, it is a duplicate, enter log message
			if ( ( $specArray['enabled'] ?? false ) &&
				strcasecmp( trim( $specArray['name'] ?? '' ), $name ) === 0 &&
				$numberDuplicates++
			) {
				// write out to logger
				$this->logger->error( "Bad survey configuration: The survey name \"{$name}\" is not unique",
									[ 'exception' => "The \"{$name}\" survey name is not unique" ] );
				return false;
			}
		}

		return true;
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
	private function factoryExternal( array $spec ): ExternalSurvey {
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
			$spec['coverage'],
			$spec['platforms'],
			$spec['privacyPolicy'],
			$spec['additionalInfo'] ?? null,
			$spec['confirmMsg'] ?? null,
			new SurveyAudience( $spec['audience'] ?? [] ),
			$spec['link'],
			$spec['instanceTokenParameterName'] ?? '',
			$spec['yesMsg'] ?? null,
			$spec['noMsg'] ?? null
		);
	}

	/**
	 * @param array $spec
	 * @throws InvalidArgumentException
	 * @return InternalSurvey
	 */
	private function factoryInternal( array $spec ): InternalSurvey {
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
