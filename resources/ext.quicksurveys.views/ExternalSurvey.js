( function () {
	var utils = mw.extQuickSurveys.utils,
		QuickSurvey = mw.extQuickSurveys.QuickSurvey;

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

			$.each( buttons, function () {
				var button = new OO.ui.ButtonWidget( this );

				button.$element.on( 'click', function () {
					self.onChoose( button );
				} );
				$btnContainer.append( button.$element );
			} );
		}
	} );

	mw.extQuickSurveys.ExternalSurvey = ExternalSurvey;
}() );
