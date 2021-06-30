var utils = require( './utils.js' ),
	QuickSurvey = require( './QuickSurvey.js' );

/**
 * @class SingleAnswerSurvey
 * @extends QuickSurvey
 *
 * @constructor
 * @param {Object} config
 */
function SingleAnswerSurvey( config ) {
	this.initialize( config );
}

OO.inheritClass( SingleAnswerSurvey, QuickSurvey );

utils.extend( SingleAnswerSurvey.prototype, {
	/**
	 * Render and append buttons (and a freeform input if set) to
	 * the initial panel
	 */
	renderButtons: function () {
		var $btnContainer = this.initialPanel.$element.find( '.survey-button-container' ),
			answers = this.config.survey.answers,
			freeformTextLabel = this.config.survey.freeformTextLabel,
			buttonSelect,
			answerButtons,
			freeformInput,
			submitButton;

		if ( this.config.survey.shuffleAnswersDisplay ) {
			answers = utils.shuffleAnswers( answers );
		}

		answerButtons = answers.map( function ( answer ) {
			return new OO.ui.ButtonOptionWidget( {
				// eslint-disable-next-line mediawiki/msg-doc
				label: mw.msg( answer ),
				data: {
					answer: answer
				}
			} );
		} );

		buttonSelect = new OO.ui.ButtonSelectWidget( {
			items: answerButtons
		} );
		buttonSelect.$element.appendTo( $btnContainer );

		if ( freeformTextLabel ) {
			freeformInput = new OO.ui.MultilineTextInputWidget( {
				// eslint-disable-next-line mediawiki/msg-doc
				placeholder: mw.msg( freeformTextLabel ),
				multiline: true,
				autosize: true,
				maxRows: 5
			} );
			freeformInput.$element.appendTo( $btnContainer );

			submitButton = new OO.ui.ButtonWidget( {
				label: mw.msg( 'ext-quicksurveys-internal-freeform-survey-submit-button' ),
				flags: 'progressive'
			} );
			submitButton.$element.appendTo( $btnContainer );

			buttonSelect.connect( this, {
				choose: [ 'resetFreeformInput', freeformInput ]
			} );
			freeformInput.$input.on( 'focus', {
				buttonSelect: buttonSelect
			}, this.resetAnswerButton );
			submitButton.connect( this, {
				click: [ 'onClickSubmitButton', buttonSelect, freeformInput ]
			} );
		} else {
			buttonSelect.connect( this, {
				choose: 'submitAnswerButton'
			} );
		}
	},

	/**
	 * Fired when one of the options are clicked.
	 *
	 * @param {OO.ui.ButtonOptionWidget|OO.ui.ButtonWidget} btn
	 * @private
	 */
	submitAnswerButton: function ( btn ) {
		this.submit( btn.data.answer );
	},

	/**
	 * Unselect the selected answer button
	 *
	 * @param {jQuery.event} event
	 * @private
	 */
	resetAnswerButton: function ( event ) {
		event.data.buttonSelect.unselectItem();
	},

	/**
	 * Clear the free form input text and focus out of it
	 *
	 * @param {OO.ui.MultilineTextInputWidget} freeformInput
	 * @private
	 */
	resetFreeformInput: function ( freeformInput ) {
		freeformInput.setValue( '' );
		freeformInput.blur();
	},

	/**
	 * Get the user answer either from the answer buttons or free
	 * form text and submit
	 *
	 * @param {OO.ui.ButtonSelectWidget} buttonSelect
	 * @param {OO.ui.MultilineTextInputWidget} freeformInput
	 * @private
	 */
	onClickSubmitButton: function ( buttonSelect, freeformInput ) {
		var selectedButton = buttonSelect.findSelectedItem(),
			freeformInputValue = freeformInput.getValue().trim();

		if ( selectedButton ) {
			this.submit( selectedButton.data.answer );
		} else if ( freeformInputValue ) {
			this.submit( freeformInputValue );
		} else {
			// eslint-disable-next-line no-alert
			alert( mw.msg( 'ext-quicksurveys-internal-freeform-survey-no-answer-alert' ) );
		}
	}
} );

module.exports = SingleAnswerSurvey;
