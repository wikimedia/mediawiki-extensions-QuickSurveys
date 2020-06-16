var utils = require( './utils.js' ),
	QuickSurvey = require( './QuickSurvey.js' );

/**
 * @class ExternalSurvey
 * @constructor
 * @param {Object} config
 */
function ExternalSurvey( config ) {
	this.initialize( config );
}

OO.inheritClass( ExternalSurvey, QuickSurvey );

utils.extend( ExternalSurvey.prototype, {
	/**
	 * @inheritdoc
	 */
	renderButtons: function () {
		var $btnContainer = this.initialPanel.$element.find( '.survey-button-container' ),
			btnHref,
			buttons,
			self = this;

		// eslint-disable-next-line mediawiki/msg-doc
		btnHref = new mw.Uri( mw.message( this.config.survey.link ).parse() );

		if ( this.config.survey.instanceTokenParameterName ) {
			btnHref.query[ this.config.survey.instanceTokenParameterName ] =
				this.config.pageviewToken;
		}

		buttons = [
			{
				href: btnHref.toString(),
				target: '_blank',
				label: mw.msg( 'ext-quicksurveys-external-survey-yes-button' ),
				flags: 'progressive',
				data: {
					answer: 'ext-quicksurveys-external-survey-yes-button'
				}
			},
			{
				label: mw.msg( 'ext-quicksurveys-external-survey-no-button' ),
				data: {
					answer: 'ext-quicksurveys-external-survey-no-button'
				}
			}
		];

		buttons.forEach( function ( options ) {
			var button = new OO.ui.ButtonWidget( options );

			button.$element.on( 'click', function () {
				self.submit( button.data.answer );
			} );
			$btnContainer.append( button.$element );
		} );
	}
} );

module.exports = ExternalSurvey;
