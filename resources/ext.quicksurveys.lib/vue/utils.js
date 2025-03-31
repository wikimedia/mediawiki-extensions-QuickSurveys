/**
 * Extend an object with extra properties.
 *
 * @ignore
 * @param {Object} target Object to extend.
 * @param {Object} mixin Properties to incorporate into the target.
 */
function extend( target, mixin ) {
	let key;
	for ( key in mixin ) {
		target[ key ] = mixin[ key ];
	}
}

/**
 * Get a two-letter country code based on the user's IP-connection.
 *
 * The Geo object is derived from a Cookie response header in the
 * CentralNotice `ext.centralNotice.geoIP` module (loaded on all
 * page views when installed). If the cookie was refused, this
 * falls back to the string "Unknown".
 *
 * @return {string} Two-letter country code, "XX", or "Unknown".
 */
function getCountryCode() {
	/* global Geo */
	if ( window.Geo && typeof Geo.country === 'string' ) {
		return Geo.country;
	}
	return 'Unknown';
}

/**
 * Shuffle answers in place
 *
 * @param {Array} [answers] answers coming from configuration
 * @return {Array} shuffled answers
 */
function shuffleAnswers( answers ) {
	let counter = answers.length,
		i, temp;

	while ( counter > 0 ) {
		i = Math.floor( Math.random() * counter );

		counter--;

		temp = answers[ counter ];
		answers[ counter ] = answers[ i ];
		answers[ i ] = temp;
	}

	return answers;
}

/**
 * Processes the questions array before sending it to the Vue component.
 * - Translate messages (questions, answer options, placeholders, etc.)
 * - Shuffle answers
 * - Add keys to questions and answers (to mount event response)
 *
 * @param {Array} questions
 * @param {string} [pageViewToken]
 * @return {Array} questions translated
 */
function processSurveyQuestions( questions, pageViewToken ) {
	if ( !questions || !questions.length ) {
		return [];
	}

	return questions.map( ( question ) => {
		let externalLink;
		try {
			externalLink = question.link ? new URL(
				// eslint-disable-next-line mediawiki/msg-doc
				mw.message( question.link ).parse()
			) : '';
		} catch ( e ) {
			// unable to parse.
		}

		if ( externalLink && question.instanceTokenParameterName ) {
			externalLink.searchParams.set( question.instanceTokenParameterName, pageViewToken );
		}

		const answers = ( question.answers || [] ).map( ( answer ) => ( {
			key: answer.label,
			// eslint-disable-next-line mediawiki/msg-doc
			label: mw.msg( answer.label ),
			freeformTextLabel: answer.freeformTextLabel ?
			// eslint-disable-next-line mediawiki/msg-doc
				mw.msg( answer.freeformTextLabel ) :
				undefined
		} ) );

		return {
			name: question.name,
			layout: question.layout,
			dependsOn: question.dependsOn,
			questionKey: question.question,
			question: mw.msg( question.question ),
			answers: question.shuffleAnswersDisplay ? shuffleAnswers( answers ) : answers,
			externalLink: externalLink.toString(),
			yesMsg: question.yesMsg ? mw.msg( question.yesMsg ) : '',
			noMsg: question.noMsg ? mw.msg( question.noMsg ) : '',
			description: question.description ? mw.msg( question.description ) : ''
		};
	} );
}

/**
 * Returns the index for the next question considering the dependency engine, or
 * `null` if there is not next question.
 *
 * @param {number} currentIndex the index of the question currently being
 * presented to the user.
 * @param {{
 *  questionKey: string,
 *  name: string,
 *  dependsOn: {
 *   question: string,
 *   answerIsOneOf: string[]
 *  }[]
 * }[]} questions a list of all of the questions in the survey currently being
 * displayed.
 * @param {Object<string, Object<string, string>>} answers an object
 * containing a mapping of all previously answered questions and answers. This
 * is used to determine if question dependencies are satisfied.
 * @return {number}
 */
function getNextQuestionIndex( currentIndex, questions, answers ) {
	let indexToEvaluate = currentIndex + 1;
	if ( questions.length <= indexToEvaluate ) {
		return null;
	}

	let currentQuestion = questions[ indexToEvaluate ];
	if ( !currentQuestion.dependsOn || !currentQuestion.dependsOn.length ) {
		return indexToEvaluate;
	}

	let currentQuestionIsAccepted = false;
	while (
		!currentQuestionIsAccepted &&
		questions.length > indexToEvaluate &&
		currentQuestion
	) {
		const conditions = currentQuestion.dependsOn || [];

		// Question is accepted when all conditions in `dependsOn` are accepted
		currentQuestionIsAccepted = conditions.every( ( condition ) => {
			// Condition is accepted when one answer from the list matches with
			// one of the expected answers.
			const conditionIsAccepted = questions.some( ( question ) => {
				const isSameQuestion = question.name === condition.question;

				const answerIsInCondition = condition.answerIsOneOf.some(
					( oneOfAnswer ) => {
						const answersForQuestion = Object.keys(
							answers[ question.questionKey ] || {}
						);
						return answersForQuestion.includes( oneOfAnswer );
					}
				);

				return isSameQuestion && answerIsInCondition;
			} );

			return conditionIsAccepted;
		} );

		if ( !currentQuestionIsAccepted ) {
			indexToEvaluate = indexToEvaluate + 1;
			currentQuestion = questions[ indexToEvaluate ] || null;
		}
	}

	return questions.length <= indexToEvaluate ? null : indexToEvaluate;
}

module.exports = {
	shuffleAnswers,
	extend,
	getCountryCode,
	processSurveyQuestions,
	getNextQuestionIndex
};
