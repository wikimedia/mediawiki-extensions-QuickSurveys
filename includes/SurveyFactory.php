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

	private LoggerInterface $logger;

	/**
	 * Inject services.
	 */
	public function __construct( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @param array[] $specs Raw configuration from $wgQuickSurveysConfig
	 * @return Survey[] List of valid and enabled surveys
	 */
	public function parseSurveyConfig( array $specs ): array {
		if ( !array_is_list( $specs ) ) {
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
	 */
	public function newSurvey( array $spec ): ?Survey {
		try {
			$this->validateSpec( $spec );
			return $spec['type'] === 'internal'
				? $this->factoryInternal( $spec )
				: $this->factoryExternal( $spec );
		} catch ( InvalidArgumentException $ex ) {
			$this->logger->error( "Bad survey configuration: " . $ex->getMessage(), [ 'exception' => $ex ] );
			return null;
		}
	}

	/**
	 * @param array $spec
	 * @throws InvalidArgumentException
	 */
	private function validateSpec( array $spec ): void {
		$name = $spec['name'];

		if ( !isset( $spec['question'] ) && ( !isset( $spec['questions'] ) || !$spec['questions'] ) ) {
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

		if ( $spec['type'] === 'external' ) {
			$link = null;
			if ( isset( $spec['link'] ) ) {
				$link = $spec['link'];
			} elseif (
				isset( $spec['questions'] ) &&
				isset( $spec['questions'][0] ) &&
				isset( $spec['questions'][0]['link'] )
			) {
				$link = $spec['questions'][0]['link'];
			}

			if ( $link !== null ) {
				$url = wfMessage( $link )->inContentLanguage()->plain();
				$bit = parse_url( $url, PHP_URL_SCHEME );

				if ( $bit !== 'https' ) {
					throw new InvalidArgumentException( "The \"{$name}\" external survey must have a secure url." );
				}
			}
		}

		$this->validatePlatforms( $spec );
	}

	/**
	 * @param array $spec
	 * @throws InvalidArgumentException
	 */
	private function validatePlatforms( array $spec ): void {
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
	 */
	private function validateExternalSurveyQuestions( array $spec ): void {
		$surveyName = $spec['name'];
		$questions = $spec['questions'] ?? [];

		if ( count( $questions ) !== 1 ) {
			throw new InvalidArgumentException(
				"The \"{$surveyName}\" external survey should only have one question."
			);
		}

		$question = $questions[0];

		$name = $question['name'] ?? null;
		if ( !$name ) {
			throw new InvalidArgumentException(
				"The \"{$surveyName}\" external survey doesn't have a question name."
			);
		}

		$questionText = $question['question'] ?? null;
		if ( !$questionText ) {
			throw new InvalidArgumentException(
				"The \"{$surveyName}\" external survey doesn't have a question."
			);
		}

		$link = $question['link'] ?? null;
		if ( !$link ) {
			throw new InvalidArgumentException(
				"The \"{$surveyName}\" external survey doesn't have a link."
			);
		}
	}

	/**
	 * @param array $spec
	 * @throws InvalidArgumentException
	 */
	private function validateInternalSurveyQuestions( array $spec ): void {
		$surveyName = $spec['name'];
		$questions = $spec['questions'] ?? [];
		$questionsByName = [];

		foreach ( $questions as $key => $question ) {
			$name = $question['name'] ?? null;
			if ( !$name ) {
				throw new InvalidArgumentException(
					"Question at index \"{$key}\" in the \"{$surveyName}\" internal survey " .
					"doesn't have a name."
				);
			}

			if ( array_key_exists( $name, $questionsByName ) ) {
				throw new InvalidArgumentException(
					"Question at index \"{$key}\" in the \"{$surveyName}\" internal survey " .
					"has a name that's used by a previous question."
				);
			}
			$questionsByName[$name] = $question;

			$layout = $question['layout'] ?? null;
			if ( !$layout || !in_array( $layout, [ 'single-answer', 'multiple-answer' ] ) ) {
				throw new InvalidArgumentException(
					"Question at index \"{$key}\" in the \"{$surveyName}\" internal survey " .
					"has a layout that's not one of \"single-answer\" or \"multiple-answer\"."
				);
			}

			$surveyQuestion = $question['question'] ?? null;
			if ( !$surveyQuestion ) {
				throw new InvalidArgumentException(
					"Question at index \"{$key}\" in the \"{$surveyName}\" internal survey " .
					"doesn't have a question."
				);
			}

			$answers = $question['answers'] ?? [];
			if ( !$answers ) {
				throw new InvalidArgumentException(
					"Question at index \"{$key}\" in the \"{$surveyName}\" internal survey " .
					"has no answers."
				);
			}
			foreach ( $answers as $answer ) {
				$label = $answer['label'] ?? null;
				if ( $label === null ) {
					throw new InvalidArgumentException(
						"Question at index \"{$key}\" in the \"{$surveyName}\" internal survey " .
						"has an answer with no label."
					);
				}
			}

			$dependsOn = $question['dependsOn'] ?? [];
			if ( $dependsOn ) {
				foreach ( $dependsOn as $dependency ) {
					$dependencyName = $dependency['question'] ?? null;
					$answerIsOneOf = $dependency['answerIsOneOf'] ?? [];

					if ( !$dependencyName ) {
						throw new InvalidArgumentException(
							"Question at index \"{$key}\" in the \"{$surveyName}\" internal survey " .
							"has a dependency that is not referencing any question."
						);
					}

					if ( !array_key_exists( $dependencyName, $questionsByName ) ) {
						throw new InvalidArgumentException(
							"Question at index \"{$key}\" in the \"{$surveyName}\" internal survey " .
							"depends on a question that does not exist prior to itself."
						);
					} elseif ( $dependencyName === $name ) {
						throw new InvalidArgumentException(
							"Question at index \"{$key}\" in the \"{$surveyName}\" internal survey " .
							"is referencing itself as a question it depends on."
						);
					}

					$referencedQuestion = $questionsByName[$dependencyName];
					$referencedAnswers = array_map(
						static fn ( array $answer ): string => $answer['label'],
						$referencedQuestion['answers']
					);

					foreach ( $answerIsOneOf as $answer ) {
						if ( !in_array( $answer, $referencedAnswers ) ) {
							throw new InvalidArgumentException(
								"Question at index \"{$key}\" in the \"{$surveyName}\" internal survey " .
								"depends on an answer that doesn't exist on the referenced question."
							);
						}
					}
				}
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
		$questions = $spec['questions'] ?? [];

		// Deprecated fields.
		$question = $spec['question'] ?? null;
		$description = $spec['description'] ?? null;
		$link = $spec['link'] ?? null;
		$privacyPolicy = $spec['privacyPolicy'] ?? null;
		$yesMsg = $spec['yesMsg'] ?? null;
		$noMsg = $spec['noMsg'] ?? null;
		$instanceTokenParameterName = $spec['instanceTokenParameterName'] ?? null;

		if ( !$link && !$questions ) {
			throw new InvalidArgumentException(
				"The \"{$name}\" external survey doesn't have a link."
			);
		}

		if ( !$privacyPolicy ) {
			throw new InvalidArgumentException(
				"The \"{$name}\" external survey doesn't have a privacy policy."
			);
		}

		$surveyQuestions = [];
		if ( $questions ) {
			foreach ( $questions as $surveyQuestion ) {
				$surveyQuestions[] = new SurveyQuestion( $surveyQuestion, 'external' );
			}
		}

		// Backwards compatibility: Map the deprecated field values to newer
		// 'questions' array if they're defined and 'questions' is!
		if ( !$surveyQuestions && $question ) {
			$surveyQuestions[] = new SurveyQuestion( [
				'name' => 'question-1',
				'question' => $question,
				'description' => $description,
				'link' => $link,
				// Set defaults for yes and no messages for compatibility.
				'yesMsg' => $yesMsg ?? 'ext-quicksurveys-external-survey-yes-button',
				'noMsg' => $noMsg ?? 'ext-quicksurveys-external-survey-no-button',
				'instanceTokenParameterName' => $instanceTokenParameterName,
			], 'external' );
		}

		$survey = new ExternalSurvey(
			$name,
			$spec['coverage'],
			$spec['platforms'],
			$spec['privacyPolicy'],
			$spec['additionalInfo'] ?? null,
			$spec['confirmMsg'] ?? null,
			new SurveyAudience( $spec['audience'] ?? [] ),
			$surveyQuestions,
			$question,
			$description,
			$spec['confirmDescription'] ?? null,
			$link,
			$instanceTokenParameterName,
			$yesMsg,
			$noMsg,
			$spec['embedElementId'] ?? null
		);
		$this->validateExternalSurveyQuestions( $survey->toArray() );

		return $survey;
	}

	/**
	 * @param array $spec
	 * @throws InvalidArgumentException
	 * @return InternalSurvey
	 */
	private function factoryInternal( array $spec ): InternalSurvey {
		$name = $spec['name'];
		$questions = $spec['questions'] ?? [];

		// Deprecated fields.
		$question = $spec['question'] ?? null;
		$answers = $spec['answers'] ?? null;
		$description = $spec['description'] ?? null;
		$layout = $spec['layout'] ?? null;
		$shuffleAnswersDisplay = $spec['shuffleAnswersDisplay'] ?? null;
		$freeformTextLabel = $spec['freeformTextLabel'] ?? null;

		if ( !$questions && !$answers ) {
			throw new InvalidArgumentException(
				"The \"{$name}\" internal survey doesn't have any answers."
			);
		}

		$surveyQuestions = [];
		if ( $questions ) {
			foreach ( $questions as $surveyQuestion ) {
				$surveyQuestions[] = new SurveyQuestion( $surveyQuestion, 'internal' );
			}
		} else {
			// Only make the deprecated top-level layout field required if the
			// corresponding deprecated question field is in use.
			if ( !in_array( $layout, [ 'single-answer', 'multiple-answer' ] ) ) {
				throw new InvalidArgumentException(
					"The \"{$name}\" internal survey layout is not one of \"single-answer\" or " .
					"\"multiple-answer\"."
				);
			}
		}

		// Backwards compatibility: Map the deprecated field values to newer
		// 'questions' array if they're defined and 'questions' is empty.
		if ( !$surveyQuestions && $question && $answers ) {
			$surveyQuestions[] = new SurveyQuestion( [
				'name' => 'question-1',
				'layout' => $layout,
				'question' => $question,
				'description' => $description,
				'shuffleAnswersDisplay' => $shuffleAnswersDisplay,
				'answers' => array_map( static fn ( string $answer ): array => [
					'label' => $answer,
					'freeformTextLabel' => $freeformTextLabel,
				], $answers ),
			], 'internal' );
		}

		$survey = new InternalSurvey(
			$name,
			$spec['coverage'],
			$spec['platforms'],
			$spec['privacyPolicy'] ?? null,
			$spec['additionalInfo'] ?? null,
			$spec['confirmMsg'] ?? null,
			new SurveyAudience( $spec['audience'] ?? [] ),
			$surveyQuestions,
			$question,
			$description,
			$spec['confirmDescription'] ?? null,
			$answers,
			$shuffleAnswersDisplay,
			$freeformTextLabel,
			$spec['embedElementId'] ?? null,
			$layout
		);
		$this->validateInternalSurveyQuestions( $survey->toArray() );

		return $survey;
	}
}
