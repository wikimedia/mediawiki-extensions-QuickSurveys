( function ( $ ) {
	var utils = mw.extQuickSurveys.views.utils;

	/**
	 * @class QuickSurvey
	 * @inherit OO.ui.StackLayout
	 */
	function QuickSurvey( config ) {
		this.initialize( config );
	}
	utils.extend( QuickSurvey, OO.ui.StackLayout, {
		/**
		 * Specifies partials (sub-templates) for use by the widget
		 * @property {Object}
		 */
		templatePartials: {
			initialPanel: mw.template.get( 'ext.quicksurveys.views', 'initialPanel.mustache' ),
			finalPanel: mw.template.get( 'ext.quicksurveys.views', 'finalPanel.mustache' )
		},
		/**
		 * A set of default options that are merged with config passed into the initialize function.
		 * This is likely to change so currently no options are documented.
		 * @cfg {Object} defaults Default options hash.
		 */
		defaults: {
			buttons: [
				{
					label: mw.msg( 'ext-quicksurveys-survey-positive' ),
					data: {
						answer: 'ext-quicksurveys-survey-positive'
					}
				},
				{
					label: mw.msg( 'ext-quicksurveys-survey-neutral' ),
					data: {
						answer: 'ext-quicksurveys-survey-neutral'
					}
				},
				{
					label: mw.msg( 'ext-quicksurveys-survey-negative' ),
					data: {
						answer: 'ext-quicksurveys-survey-negative'
					}
				}
			],
			templateData: {
				finalHeading: mw.msg( 'ext-quicksurveys-survey-confirm-msg' ),
				footer: mw.message( 'ext-quicksurveys-survey-privacy-policy-default-text' ).parse()
			},
			PanelLayout: {
				expanded: false,
				framed: false,
				padded: true,
				classes: [ 'message content' ]
			},
			scrollable: false,
			expanded: false,
			classes: [ 'panel panel-inline visible' ]
		},
		/**
		 * Initialise a widget.
		 * @param {Object} config
		 */
		initialize: function ( config ) {
			this.config = config || {};
			$.extend( true, this.config, this.defaults );

			// setup initial panel
			this.initialPanel = this.widget( 'PanelLayout', 'initialPanel' );

			// setup final panel
			this.finalPanel = this.widget( 'PanelLayout', 'finalPanel' );

			// Set the buttons
			this.renderButtons();

			// setup stack
			QuickSurvey.super.call( this, $.extend( {}, config, {
				items: [ this.initialPanel, this.finalPanel ]
			} ) );
		},
		/**
		 * Render and append buttons to the initial panel
		 */
		renderButtons: function () {
			var buttonSelect, $btnContainer,
				btns = [];

			$btnContainer = this.initialPanel
				.$element.find( '.survey-button-container' );

			$.each( this.config.buttons, function () {
				var btn = new OO.ui.ButtonOptionWidget( this );
				btns.push( btn );
			} );

			buttonSelect = new OO.ui.ButtonSelectWidget( {
				items: btns
			} );
			buttonSelect.connect( this, {
				choose: 'onChoose'
			} );
			buttonSelect.$element.appendTo( $btnContainer );
		},
		/**
		 * Make a brand spanking new oojs ui widget from a template partial
		 * @param {String} widgetName a valid OOJS UI widget
		 * @param {String} [templatePartialName] name of a registered template partial
		 * @param {Object} [options] further options to be passed to the widget
		 */
		widget: function ( widgetName, templatePartialName, options ) {
			var template,
				config = $.extend( {}, this.config[widgetName], options );

			if ( templatePartialName ) {
				template = this.templatePartials[templatePartialName];
				if ( template ) {
					config.$content = template.render( this.config.templateData );
				}
			}
			return new OO.ui[widgetName]( config );
		},
		/**
		 * Log the answer to Schema:QuickSurveysResponses
		 * @see https://meta.wikimedia.org/wiki/Schema:QuickSurveysResponses
		 * @param {String} answer
		 * @return {jQuery.Deferred}
		 */
		log: function ( answer ) {
			var survey = this.config.survey;

			if ( mw.eventLog ) {
				return mw.eventLog.logEvent( 'QuickSurveysResponses', {
					surveyCodeName: survey.name,
					surveyResponseValue: answer,
					platform: 'web',
					presentation: mw.config.get( 'skin' ),
					userLanguage: mw.config.get( 'wgContentLanguage' ),
					isLoggedIn: !mw.user.isAnon(),
					editCount: utils.getEditCountBucket( mw.config.get( 'wgUserEditCount' ) ),
					countryCode: utils.getCountryCode()
				} );
			}
			return $.Deferred().reject( 'EventLogging not installed.' );
		},
		/**
		 * Fired when one of the options are clicked.
		 * @param {OO.ui.ButtonOptionWidget} btn
		 */
		onChoose: function ( btn ) {
			this.log( btn.data.answer );
			/**
			 * @event dismiss fired when any of the buttons in the survey are selected.
			 */
			this.emit( 'dismiss' );
			this.setItem( this.finalPanel );
		}
	} );

	// This always makes me sad... https://phabricator.wikimedia.org/T108655
	mw.extQuickSurveys = mw.extQuickSurveys || {};
	mw.extQuickSurveys.views = mw.extQuickSurveys.views || {};
	mw.extQuickSurveys.views.QuickSurvey = QuickSurvey;
}( jQuery ) );
