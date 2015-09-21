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
						flags: 'constructive'
					},
					{
						label: mw.msg( 'ext-quicksurveys-external-survey-no-button' )
					}
				],
				self = this;

			$.each( buttons, function () {
				var button = new OO.ui.ButtonWidget( this );

				button.$element.on( 'click', $.proxy( self.onChoose, self ) );
				$btnContainer.append( button.$element );
			} );
		},
		/**
		 * @inheritdoc
		 */
		onChoose: function () {
			/**
			 * @event dismiss fired when any of the buttons in the survey are selected.
			 */
			this.emit( 'dismiss' );
			this.setItem( this.finalPanel );
		}
	} );

	mw.extQuickSurveys.views.ExternalSurvey = ExternalSurvey;
}( jQuery ) );
