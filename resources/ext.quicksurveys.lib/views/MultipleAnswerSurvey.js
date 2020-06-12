var utils = require( './utils.js' ),
	QuickSurvey = require( './QuickSurvey.js' );

/**
 * @class MultipleAnswerSurvey
 * @extends QuickSurvey
 *
 * @constructor
 * @param {Object} config
 */
function MultipleAnswerSurvey( config ) {
	this.initialize( config );
}

OO.inheritClass( MultipleAnswerSurvey, QuickSurvey );

utils.extend( MultipleAnswerSurvey.prototype, {
	/**
	 * Render and append checkboxes
	 */
	renderButtons: function () {
		var $btnContainer = this.initialPanel.$element.find( '.survey-button-container' ),
			answers = this.config.survey.answers,
			answerCheckboxes,
			answerOptions,
			submitButton;

		answerOptions = answers.map( function ( answer ) {
			return {
				data: answer,
				// eslint-disable-next-line mediawiki/msg-doc
				label: mw.msg( answer )
			};
		} );
		answerCheckboxes = new OO.ui.CheckboxMultiselectInputWidget( {
			options: answerOptions
		} );

		answerCheckboxes.$element.appendTo( $btnContainer );

		submitButton = new OO.ui.ButtonWidget( {
			label: mw.msg( 'ext-quicksurveys-internal-freeform-survey-submit-button' ),
			flags: 'progressive'
		} );
		submitButton.$element.appendTo( $btnContainer );

		submitButton.connect( this, {
			click: [ 'onClickSubmitButton', answerCheckboxes ]
		} );
	},

	/**
	 * @param {OO.ui.CheckboxMultiselectInputWidget} checkboxes
	 * @private
	 */
	onClickSubmitButton: function ( checkboxes ) {
		var selections = checkboxes.getValue(),
			surveyResponseValue = selections.join( ',' );

		this.submit( surveyResponseValue );
	}
} );

module.exports = MultipleAnswerSurvey;
