<template>
	<div :class="rootClasses" :dir="direction">
		<div class="message survey-content">
			<div class="survey-header">
				<template v-if="completed">
					<strong>{{ thankYouMessage }}</strong>
				</template>
				<template v-else>
					<strong>{{ question }}</strong>
				</template>
				<div class="survey-close-button">
					<cdx-button
						weight="quiet"
						@click="dismissAndDestroy">
						<cdx-icon :icon="closeIcon"></cdx-icon>
					</cdx-button>
				</div>
			</div>
			<template v-if="!completed">
				<p v-if="description" class="survey-description">
					{{ description }}
				</p>
				<div class="survey-button-container">
					<template v-if="requiresSingularAnswer">
						<cdx-button
							v-for="( btn, i ) in buttons"
							:key="i"
							:action="btn.action"
							:weight="btn.weight"
							class="survey-button"
							@click="clickButton( btn.answer, btn.href )">
							{{ btn.label }}
						</cdx-button>
					</template>
					<template v-else>
						<div
							v-for="( btn, i ) in buttons"
							:key="i">
							<cdx-checkbox
								v-model="checkedAnswers"
								:input-value="btn.label">
								{{ btn.label }}
							</cdx-checkbox>
						</div>
					</template>
				</div>
				<template v-if="mustBeSubmitted">
					<cdx-text-input
						v-if="requiresSingularAnswer"
						v-model="otherAnswer"
						type="text"
						:placeholder="freeformTextLabel"
						@input="resetSelectedButton"></cdx-text-input>
					<cdx-button
						weight="normal"
						action="progressive"
						@click="submitAnswer">
						{{ submitButtonLabel }}
					</cdx-button>
				</template>
			</template>
			<template v-if="completed && additionalInfo">
				<!-- eslint-disable vue/no-v-html -->
				<div class="survey-footer" v-html="additionalInfo"></div>
			</template>
			<template v-else>
				<!-- eslint-disable vue/no-v-html -->
				<div class="survey-footer" v-html="footer"></div>
			</template>
		</div>
	</div>
</template>

<script>
const codex = require( '@wikimedia/codex' ),
	utils = require( './utils.js' ),
	Vue = require( 'vue' ),
	icons = require( './icons.json' ),
	QuickSurveyLogger = require( './QuickSurveyLogger.js' );

// @vue/component
module.exports = exports = Vue.defineComponent( {
	name: 'QuickSurvey',
	components: {
		CdxButton: codex.CdxButton,
		CdxCheckbox: codex.CdxCheckbox,
		CdxIcon: codex.CdxIcon,
		CdxTextInput: codex.CdxTextInput
	},
	props: {
		shuffleAnswersDisplay: {
			type: Boolean
		},
		layout: {
			type: String,
			default: 'default'
		},
		freeformTextLabel: {
			type: String,
			default: ''
		},
		noAnswerErrorMessage: {
			type: String,
			default: 'error'
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
			default: 'submit'
		},
		noButtonLabel: {
			type: String,
			default: 'no'
		},
		yesButtonLabel: {
			type: String,
			default: 'yes'
		},
		answers: {
			// Note when declaring a survey, a survey's answers is defined by the message key.
			// Here answers are required to be an array of Answer objects.
			type: Array, /* (Answers[]) */
			default: function () {
				return [
					/**
					 * @typedef Answer
					 * @property {string} key used in response to survey to identify answer
					 *  regardless of language
					 * @property {string} label for the answer translated into the users
					 *  language
					 */
				];
			}
		},
		question: {
			type: String,
			required: true
		},
		direction: {
			type: String,
			default: 'auto'
		},
		externalLink: {
			type: String,
			default: ''
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
		name: {
			type: String,
			required: true
		},
		description: {
			type: String,
			default: ''
		},
		closeIcon: {
			type: String,
			default: icons.cdxIconClose
		}
	},
	data: function () {
		return {
			checkedAnswers: [],
			shuffledAnswers: false,
			otherAnswer: '',
			selectedAnswer: '',
			completed: false
		};
	},
	computed: {
		/**
		 * @return {boolean}
		 */
		requiresSingularAnswer: function () {
			return this.layout !== 'multiple-answer';
		},
		/**
		 * @return {boolean}
		 */
		mustBeSubmitted: function () {
			return this.freeformTextLabel || this.layout === 'multiple-answer';
		},
		/**
		 * @return {Array}
		 */
		rootClasses: function () {
			return [
				// default classes that all surveys should have
				'ext-quick-survey-panel panel panel-inline visible',
				'ext-quick-survey-' + this.layout
			];
		},
		/**
		 * @return {Array} of props for rendering buttons
		 */
		buttons: function () {
			const answers = this.externalLink ? this.externalSurveyButtons() : this.answers,
				// Per Vue.js docs safe for this to be computed:
				// "a computed property will only re-evaluate
				// when some of its reactive dependencies have changed."
				answersComputed = this.shuffleAnswersDisplay && !this.shuffledAnswers ?
					utils.shuffleAnswers( answers ) :
					answers;

			// T295681: Only shuffle once to avoid shuffling when a user clicks a button
			this.markAnswersShuffled();

			return answersComputed.map( function ( answer ) {
				const isSelected = answer.key === this.selectedAnswer;
				return {
					label: answer.label,
					weight: isSelected ? 'primary' : 'normal',
					href: answer.href,
					action: answer.action || ( isSelected ? 'progressive' : 'default' ),
					answer: answer.key
				};
			}.bind( this ) );
		}
	},
	methods: {
		/**
		 * Get buttons for an external survey.
		 *
		 * @return {Array} of props for rendering buttons
		 */
		externalSurveyButtons: function () {
			return [
				{
					label: this.yesButtonLabel,
					action: 'progressive',
					href: this.externalLink,
					key: 'ext-quicksurveys-external-survey-yes-button'
				},
				{
					label: this.noButtonLabel,
					key: 'ext-quicksurveys-external-survey-no-button'
				}
			];
		},
		/**
		 * Marks answers as shuffled.
		 */
		markAnswersShuffled: function () {
			this.shuffledAnswers = true;
		},
		/**
		 * Resets the selected answer.
		 */
		resetSelectedButton: function () {
			this.selectedAnswer = '';
		},
		/**
		 * @param {string} answer
		 * @param {string|null} href
		 */
		clickButton: function ( answer, href ) {
			if ( href ) {
				window.open( href );
			}

			if ( this.freeformTextLabel ) {
				this.selectedAnswer = answer;
				this.otherAnswer = '';
			} else {
				this.endSurvey( answer );
			}
		},
		/**
		 * Explicitly submits the currently selected answer.
		 */
		submitAnswer: function () {
			const answer = ( this.checkedAnswers.join( ',' ) ||
				this.otherAnswer || this.selectedAnswer || '' ).trim();
			if ( answer ) {
				this.endSurvey( answer );
			} else {
				// eslint-disable-next-line no-alert
				alert( this.noAnswerErrorMessage );
			}
		},
		/**
		 * Logs the response of a survey and shows the end panel.
		 *
		 * @param {string} answer
		 */
		endSurvey: function ( answer ) {
			const data = QuickSurveyLogger.logResponseData(
				this.name,
				answer,
				this.surveySessionToken,
				this.pageviewToken,
				!this.isMobileLayout
			);
			this.$emit( 'logEvent', 'QuickSurveysResponses', data );
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
		dismissAndDestroy: function () {
			this.$emit( 'dismiss' );
			this.$emit( 'destroy' );
		}
	}
} );
</script>

<style lang="less">
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

	.survey-content {
		padding: (@spacing - @close-button-displacement) @spacing;
		line-height: @line-height;

		> :not( :first-child ) {
			margin: @spacing-inner 0 0;
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
	}

	.survey-header {
		display: flex;

		strong {
			margin-top: @header-align-displacement;
			flex: 1;
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
