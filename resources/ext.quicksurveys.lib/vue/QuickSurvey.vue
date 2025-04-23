<template>
	<div :class="rootClasses" :dir="direction">
		<div class="message survey-content">
			<div class="survey-header">
				<template v-if="completed">
					<div>
						<!-- eslint-disable vue/no-v-html -->
						<strong v-html="thankYouMessage"></strong>
						<!-- eslint-disable vue/no-v-html -->
						<div
							v-if="thankYouDescription"
							class="thank-you-description"
							v-html="thankYouDescription"></div>
					</div>
				</template>
				<template v-else>
					<strong>{{ currentQuestion.question }}</strong>
				</template>
				<div class="survey-close-button">
					<cdx-button
						weight="quiet"
						aria-hidden="true"
						@click="dismissAndDestroy">
						<cdx-icon :icon="closeIcon"></cdx-icon>
					</cdx-button>
				</div>
			</div>
			<template v-if="!completed">
				<p v-if="currentQuestion.description" class="survey-description">
					{{ currentQuestion.description }}
				</p>
				<div class="survey-button-container">
					<template v-if="questionHasExternalLink">
						<cdx-button
							v-for="( btn, i ) in externalSurveyButtons"
							:key="i"
							:action="btn.action"
							:weight="btn.weight"
							class="survey-button"
							@click="clickExternalSurveyButton( btn.key, btn.href )">
							{{ btn.label }}
						</cdx-button>
					</template>
					<template v-else-if="requiresSingularAnswer">
						<keep-alive>
							<answer-radio-option
								v-for="option in currentAnswerOptions"
								:key="`${ currentQuestion.question }-${ option.key }`"
								v-model="singleAnswer"
								:text-input-placeholder="option.freeformTextLabel"
								:label="option.label"
								:radio-input-value="option.key"></answer-radio-option>
						</keep-alive>
					</template>
					<template v-else>
						<div
							v-for="option in currentAnswerOptions"
							:key="`${ currentQuestion.question }-${ option.key }`">
							<cdx-checkbox
								v-model="checkedAnswers"
								:input-value="option.key">
								{{ option.label }}
							</cdx-checkbox>
							<cdx-text-input
								v-if="shouldShowCheckboxTextInput( option )"
								v-model="freeformTextAnswers[option.key]"
								type="text"
								class="quick-survey-checkbox-free-text"
								:placeholder="option.freeformTextLabel"></cdx-text-input>
						</div>
					</template>
				</div>
				<div class="survey-action-buttons">
					<cdx-button
						v-if="currentQuestionIndex > 0"
						weight="normal"
						action="default"
						@click="backToPreviousQuestion">
						{{ backButtonLabel }}
					</cdx-button>
					<cdx-button
						v-if="!questionHasExternalLink"
						weight="primary"
						action="progressive"
						@click="submitAnswer">
						{{ submitButtonLabel }}
					</cdx-button>
				</div>
			</template>
			<!-- eslint-disable vue/no-v-html -->
			<hr>
			<div class="survey-footer" v-html="footerText"></div>
		</div>
	</div>
</template>

<script>
const {
		CdxButton,
		CdxCheckbox,
		CdxIcon,
		CdxTextInput
	} = require( '@wikimedia/codex' ),
	Vue = require( 'vue' ),
	icons = require( './icons.json' ),
	utils = require( './utils.js' ),
	QuickSurveyLogger = require( './QuickSurveyLogger.js' ),
	AnswerRadioOption = require( './components/AnswerRadioOption.vue' );

// @vue/component
module.exports = exports = Vue.defineComponent( {
	name: 'QuickSurvey',
	components: {
		CdxButton,
		CdxCheckbox,
		CdxIcon,
		CdxTextInput,
		AnswerRadioOption
	},
	props: {
		noAnswerErrorMessage: {
			type: String,
			default: mw.msg( 'ext-quicksurveys-internal-freeform-survey-no-answer-alert' )
		},
		isMobileLayout: {
			type: Boolean
		},
		pageviewToken: {
			type: String,
			required: true
		},
		surveySessionToken: {
			type: String,
			required: true
		},
		submitButtonLabel: {
			type: String,
			default: mw.msg( 'ext-quicksurveys-internal-freeform-survey-submit-button' )
		},
		backButtonLabel: {
			type: String,
			default: mw.msg( 'ext-quicksurveys-internal-freeform-survey-back-button' )
		},
		direction: {
			type: String,
			default: document.documentElement.getAttribute( 'dir' ) || 'auto'
		},
		additionalInfo: {
			type: String,
			default: ''
		},
		footer: {
			type: String,
			default: ''
		},
		thankYouMessage: {
			type: String,
			required: true
		},
		thankYouDescription: {
			type: String,
			required: false,
			default: null
		},
		name: {
			type: String,
			required: true
		},
		closeIcon: {
			type: String,
			default: icons.cdxIconClose
		},
		questions: {
			type: Array,
			default: () => []
		},
		surveyPreferencesDisclaimer: {
			type: String,
			default: mw.message(
				'ext-quicksurveys-survey-change-preferences-disclaimer'
			).parse()
		}
	},
	emits: [ 'destroy', 'dismiss', 'logEvent' ],
	data() {
		return {
			// `currentQuestionIndex` tracks what question we're currently on.
			currentQuestionIndex: 0,
			// `completed` is set to true when the survey is completed.
			completed: false,
			// `checkedAnswers` is a list of answer labels corresponding to
			// checkboxes that were checked on a survey question.
			checkedAnswers: [],
			// `singleAnswer` is used to store the result of single-answer
			// questions using radio boxes.
			singleAnswer: { radio: null, text: '' },
			// `freeformTextAnswers` is a mapping that's bound to freeform
			// answers under checkboxes that contain the checked answer as the key
			// and the freeform text value as the value.
			//
			// eg: { 'ext-survey1-q1-a-yes': 'some fake text answer' }
			//     in the example above, `checkedAnswers`: [ 'ext-survey1-q1-a-yes' ]
			freeformTextAnswers: {},
			// `surveyAnswerHistory` is a list of objects containing the values
			// of `checkedAnswers`, `singleAnswer` and `freeformTextAnswers`
			// for all previously answered questions. This exists to support
			// the "back" functionality.
			surveyAnswerHistory: []
		};
	},
	computed: {
		/**
		 * @return {Object}
		 */
		currentQuestion() {
			return this.questions[ this.currentQuestionIndex ] || {};
		},
		/**
		 * @return {Object} Array of SurveyAnswer with key prop
		 */
		currentAnswerOptions() {
			return this.currentQuestion.answers || [];
		},
		/**
		 * @return {boolean}
		 */
		requiresSingularAnswer() {
			return this.currentQuestion.layout === 'single-answer';
		},
		/**
		 * @return {boolean}
		 */
		questionHasExternalLink() {
			return this.currentQuestion.externalLink &&
				this.currentQuestion.externalLink.length > 0;
		},
		/**
		 * @return {Array} of props for rendering buttons
		 */
		externalSurveyButtons() {
			return [
				{
					label: this.currentQuestion.noMsg,
					action: 'default',
					weight: 'normal',
					key: 'ext-quicksurveys-external-survey-no-button'
				},
				{
					label: this.currentQuestion.yesMsg,
					action: 'progressive',
					weight: 'primary',
					href: this.currentQuestion.externalLink,
					key: 'ext-quicksurveys-external-survey-yes-button'
				}
			];
		},
		/**
		 * @return {string} text that goes into the survey footer.
		 */
		footerText() {
			let footerText;
			if ( this.completed && this.additionalInfo ) {
				footerText = this.additionalInfo;
			} else {
				footerText = this.footer;
			}
			return `${ footerText } ${ this.surveyPreferencesDisclaimer }`;
		},
		/**
		 * The answers to the current survey question being presented.
		 *
		 * Since we obtain answers from a variety of different form controls
		 * with different types for their models, we need to merge them
		 * together into a unified object in order to to pass them into the
		 * submission function.
		 *
		 * @return {Object} a map with the key being the question label and the
		 * value being the fill-in answer if the question has one.
		 */
		answers() {
			return this.getQuestionAnswerMapping(
				this.checkedAnswers,
				this.singleAnswer,
				this.freeformTextAnswers
			);
		},
		/**
		 * @return {Array}
		 */
		rootClasses() {
			return [
				// default classes that all surveys should have
				'ext-quick-survey-panel panel panel-inline visible',
				'ext-quick-survey-' + this.currentQuestion.layout,
				/*
				TODO: Temporary workaround prevents QuickSurvey from scaling with text size T391890.
				Remove the workaround after font modes is fully integrated in Vector.
				*/
				'vector-feature-custom-font-size-clientpref--excluded'
			];
		}
	},
	methods: {
		/**
		 * Returns a map of questions to answers for a specific set of answers.
		 *
		 * @param {Array<string>} checkedAnswers
		 * @param {{radio: string, text: string}} singleAnswer
		 * @param {Object<string, string>} freeformTextAnswers
		 * @return {Object<string, string>}
		 */
		getQuestionAnswerMapping( checkedAnswers, singleAnswer, freeformTextAnswers ) {
			// Collect all of the checked question labels between the
			// checkboxes and the radio buttons.
			const combinedCheckedAnswers = new Set( [].concat(
				checkedAnswers || [],
				[ singleAnswer.radio ]
			).filter( ( answer ) => !!answer ) );

			// Return the freeform text answers from the multi-select model and
			// the radio answer if it has one. Any answer with no freeform text
			// gets assigned null.
			return Array.from( combinedCheckedAnswers )
				.reduce( ( previous, current ) => {
					previous[ current ] = freeformTextAnswers[ current ] ||
						singleAnswer.text ||
						null;
					return previous;
				}, {} );
		},
		/**
		 * Resets the current answers (single & multi select + free text)
		 */
		clearCurrentAnswer() {
			this.checkedAnswers = [];
			this.singleAnswer = { radio: null, text: '' };
			this.freeformTextAnswers = {};
		},
		/**
		 * Logs the survey response.
		 *
		 * @param {Object} freeformTextAnswers
		 */
		logAnswers() {
			const data = QuickSurveyLogger.logResponseData(
				this.name,
				this.currentQuestion.questionKey,
				this.answers,
				this.surveySessionToken,
				this.pageviewToken,
				!this.isMobileLayout
			);
			this.$emit( 'logEvent', 'QuickSurveysResponses', data );
		},
		/**
		 * Returns to the previous question.
		 */
		backToPreviousQuestion() {
			this.clearCurrentAnswer();

			// Have to check from the surveyAnswers because of possible condition routes
			const previousQuestion = this.surveyAnswerHistory.pop();
			this.currentQuestionIndex = this.questions.findIndex( ( question ) => question.name === previousQuestion.questionName );

			this.checkedAnswers = Array.from( previousQuestion.checkedAnswers );
			this.singleAnswer = Object.assign( {}, previousQuestion.singleAnswer );
			this.freeformTextAnswers = Object.assign(
				{},
				previousQuestion.freeformTextAnswers
			);
		},
		/**
		 * Explicitly submits the currently selected answer.
		 */
		submitAnswer() {
			if ( Object.keys( this.answers ).length === 0 ) {
				// eslint-disable-next-line no-alert
				alert( this.noAnswerErrorMessage );
				return;
			}

			this.surveyAnswerHistory.push( {
				questionKey: this.currentQuestion.questionKey,
				questionName: this.currentQuestion.name,
				checkedAnswers: Array.from( this.checkedAnswers ),
				singleAnswer: Object.assign( {}, this.singleAnswer ),
				freeformTextAnswers: Object.assign( {}, this.freeformTextAnswers )
			} );
			this.logAnswers();

			const allPreviousAnswers = this.surveyAnswerHistory
				.reduce( ( previous, current ) => {
					previous[ current.questionKey ] = this.getQuestionAnswerMapping(
						current.checkedAnswers,
						current.singleAnswer,
						current.freeformTextAnswers
					);
					return previous;
				}, {} );

			// next index for the question list or null if end of survey
			const nextQuestionIndex = utils.getNextQuestionIndex(
				this.currentQuestionIndex,
				this.questions,
				allPreviousAnswers
			);
			this.clearCurrentAnswer();

			if ( nextQuestionIndex === null ) {
				this.$emit( 'dismiss' );
				this.completed = true;
			} else {
				this.currentQuestionIndex = nextQuestionIndex;
			}
		},
		/**
		 * @param {string} answer
		 * @param {string|null} href
		 */
		clickExternalSurveyButton( answer, href ) {
			if ( href ) {
				window.open( href );
			}

			this.singleAnswer = { radio: answer, text: '' };
			this.logAnswers();

			if ( answer === 'ext-quicksurveys-external-survey-no-button' ) {
				this.dismissAndDestroy();
			} else {
				this.$emit( 'dismiss' );
				this.completed = true;
			}
		},
		/**
		 * Dismisses the current survey and removes it from the page
		 */
		dismissAndDestroy() {
			this.$emit( 'dismiss' );
			this.$emit( 'destroy' );
		},
		/**
		 * Verifies if a checkbox option should display its free form text input
		 *
		 * @param {Object} option
		 * @return {boolean}
		 */
		shouldShowCheckboxTextInput( option ) {
			return option.freeformTextLabel &&
				this.checkedAnswers.includes( option.key );
		}
	}
} );
</script>

<style lang="less">
@import 'mediawiki.skin.variables.less';

.ext-quick-survey-panel {
	@spacing: 16px;
	@spacing-inner: 8px;
	/* Space to remove from top spacing in the content due to close-button spacing */
	@close-button-displacement: 2px;
	@close-button-size: 24px;
	/* line-height: 20px, with base font-size 14px = */
	@line-height: 1.42857143;
	/*
	We need to move the header down to align with the button's baseline, because
	the text has a line-height of 20px and the button of 24px.
	In order to keep the margin to align the text dependent on size in case the base font size
	is different than 14px per browser settings, we convert those 24px-20px into
	em.
	*/
	@header-align-displacement: 1em * (@close-button-size - (14px * @line-height)) / 14px;
	/*
	Explicitly set the font-size and line-height.
	Otherwise, `vector-body` font-size rules apply.
	*/
	font-size: @font-size-medium;
	line-height: @line-height-medium;

	.survey-content {
		padding: (@spacing - @close-button-displacement) @spacing;
		line-height: @line-height;

		> :not( :first-child ) {
			margin: @spacing-75 0 0;
		}

		.survey-description {
			/*
			Request from design to reduce the spacing between the description and
			header due to line-height whitespace already present in the header.
			*/
			margin-top: 4px;
			/*
			Request from design to prevent description from flowing beneath close button.
			*/
			margin-right: @close-button-size;
		}
	}

	.survey-button-container {
		> :not( :first-child ) {
			margin: @spacing-inner 0 0;
		}

		> .survey-button {
			width: 100%;
			/* Disable Codex button max-width */
			max-width: none;
		}

		.quick-survey-checkbox-free-text {
			margin-inline-start: @spacing-150;

			.cdx-text-input__input {
				width: calc( 100% - @spacing-100 );
			}
		}
	}

	.survey-action-buttons {
		display: flex;
		gap: @spacing-50;
	}

	.survey-header {
		display: flex;

		strong {
			margin-top: @header-align-displacement;
			flex: 1;
		}

		.thank-you-description {
			margin-top: @spacing-75;
		}

		.survey-close-button {
			margin-left: @spacing;
			display: block;
			float: right;

			.cdx-button {
				padding: 0;
			}

			.cdx-button,
			.cdx-icon {
				min-width: @close-button-size;
				min-height: @close-button-size;
			}

			.cdx-icon svg {
				@close-button-icon-size: 14px;
				width: @close-button-icon-size;
				height: @close-button-icon-size;
			}
		}
	}

	.survey-footer {
		font-size: /* 1 = 14px, 13px = */ 0.928571428571em;
		line-height: /* 1 = 13px, 18px = */ 1.38;
	}
}
</style>
