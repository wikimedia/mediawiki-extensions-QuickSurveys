<template>
	<div :class="rootClasses">
		<div class="message content">
			<div v-if="completed">
				<strong>{{ thankYouMessage }}</strong>
			</div>
			<div v-else>
				<strong>{{ question }}</strong>
				<p v-if="description">
					{{ description }}
				</p>
				<div v-if="requiresSingularAnswer"
					class="survey-button-container">
					<wvui-button v-for="(btn, i) in buttons"
						:key="i"
						:action="btn.action"
						:type="btn.type"
						@click="clickButton(btn.answer, btn.href)">
						{{ btn.label }}
					</wvui-button>
				</div>
				<div v-else
					class="survey-button-container">
					<div v-for="(btn, i) in buttons"
						:key="i">
						<wvui-checkbox
							v-model="checkedAnswers"
							:input-value="btn.label">
							{{ btn.label }}
						</wvui-checkbox>
					</div>
				</div>
				<div v-if="mustBeSubmitted" class="survey-submit">
					<wvui-input v-if="requiresSingularAnswer"
						v-model="otherAnswer"
						type="text"
						:placeholder="freeformTextLabel"
						@input="resetSelectedButton"></wvui-input>
					<wvui-button type="normal"
						action="progressive"
						@click="submitAnswer">
						{{ submitButtonLabel }}
					</wvui-button>
				</div>
			</div>
			<!-- eslint-disable vue/no-v-html -->
			<div class="survey-footer" v-html="footer"></div>
		</div>
	</div>
</template>

<script>
var wvui = require( 'wvui' ),
	utils = require( './utils.js' ),
	QuickSurveyLogger = require( './QuickSurveyLogger.js' );

// @vue/component
module.exports = {
	name: 'QuickSurvey',
	components: {
		WvuiCheckbox: wvui.WvuiCheckbox,
		WvuiInput: wvui.WvuiInput,
		WvuiButton: wvui.WvuiButton
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
		externalLink: {
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
		}
	},
	data: function () {
		return {
			checkedAnswers: [],
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
			var answers = this.externalLink ? this.externalSurveyButtons() : this.answers,
				// Per Vue.js docs safe for this to be computed:
				// "a computed property will only re-evaluate
				// when some of its reactive dependencies have changed."
				answersComputed = this.shuffleAnswersDisplay ?
					utils.shuffleAnswers( answers ) :
					answers;

			return answersComputed.map( function ( answer ) {
				var isSelected = answer.key === this.selectedAnswer;
				return {
					label: answer.label,
					type: isSelected ? 'primary' : 'normal',
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
			var answer = ( this.checkedAnswers.join( ',' ) ||
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
			QuickSurveyLogger.logResponse(
				this.name,
				answer,
				this.surveySessionToken,
				this.pageviewToken,
				!this.isMobileLayout
			);
			this.$emit( 'dismiss' );
			if ( answer === 'ext-quicksurveys-external-survey-no-button' ) {
				this.$emit( 'destroy' );
			} else {
				this.completed = true;
			}
		}
	}
};
</script>

<style>
.ext-quick-survey-panel .wvui-checkbox {
	margin: 12px 0;
}

.ext-quick-survey-panel .wvui-input {
	margin-bottom: 12px;
}

.ext-quick-survey-panel .survey-submit {
	margin-top: 12px;
}

.ext-quick-survey-panel .content {
	padding: 12px 16px 16px;
}

.ext-quick-survey-single-answer .survey-button-container button {
	width: 100%;
}
</style>
