( function ( $ ) {
	var utils = mw.extQuickSurveys.views.utils,
		QuickSurvey = mw.extQuickSurveys.views.QuickSurvey;

	/**
	 * @class ExternalSurvey
	 * @inherit QuickSurvey
	 */
	function ExternalSurvey( config ) {
		this.initialize( config );
	}

	utils.extend( ExternalSurvey, QuickSurvey, {
		/**
		 * @inheritdoc
		 */
		initialize: function ( config ) {
			this.defaults.templateData.footer = mw.message( config.survey.privacyPolicy ).parse();
			QuickSurvey.prototype.initialize.call( this, config );
		},
		/**
		 * @inheritdoc
		 */
		renderButtons: function () {
			var $btnContainer = this.initialPanel.$element.find( '.survey-button-container' ),
				buttons = [
					{
						href: mw.message( this.config.survey.link ).parse(),
						target: '_blank',
						label: mw.msg( 'ext-quicksurveys-external-survey-yes-button' ),
						flags: 'constructive',
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
				],
				self = this;

			$.each( buttons, function () {
				var button = new OO.ui.ButtonWidget( this );

				button.$element.on( 'click', function () {
					self.onChoose( button );
				} );
				$btnContainer.append( button.$element );
			} );
		}
	} );

	mw.extQuickSurveys.views.ExternalSurvey = ExternalSurvey;
}( jQuery ) );
