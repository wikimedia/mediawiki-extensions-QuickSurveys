( function ( $, mw ) {
	var utils = mw.extQuickSurveys.views.utils;

	/**
	 * @class QuickSurvey
	 * @extends OO.ui.StackLayout
	 *
	 * @constructor
	 * @param {Object} config
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
			initialPanel: mw.template.get( 'ext.quicksurveys.views', 'initialPanel.muhogan' ),
			finalPanel: mw.template.get( 'ext.quicksurveys.views', 'finalPanel.muhogan' )
		},
		/**
		 * A set of default options that are merged with config passed into the initialize function.
		 * This is likely to change so currently no options are documented.
		 * @cfg {Object} defaults Default options hash.
		 */
		defaults: {
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
			classes: [ 'panel panel-inline visible ext-quick-survey-panel' ]
		},
		/**
		 * Initialise a widget.
		 *
		 * @param {Object} config
		 */
		initialize: function ( config ) {
			this.config = config || {};
			$.extend( true, this.config, this.defaults );

			if ( config.survey.privacyPolicy ) {
				this.config.templateData.footer = mw.message( config.survey.privacyPolicy ).parse();
			}

			// setup initial panel
			this.initialPanel = this.widget( 'PanelLayout', 'initialPanel' );

			// setup final panel
			this.finalPanel = this.widget( 'PanelLayout', 'finalPanel' );

			// Set the buttons
			this.renderButtons();

			// setup stack
			// eslint-disable-next-line dot-notation
			QuickSurvey.super.call( this, $.extend( {}, config, {
				items: [ this.initialPanel, this.finalPanel ]
			} ) );

			if ( mw.eventLog ) {
				mw.eventLog.logEvent( 'QuickSurveyInitiation', {
					beaconCapable: $.isFunction( navigator.sendBeacon ),
					surveySessionToken: this.config.surveySessionToken,
					surveyInstanceToken: this.config.surveyInstanceToken,
					surveyCodeName: this.config.survey.name,
					eventName: 'eligible'
				} );
			}
		},
		/**
		 * Render and append buttons to the initial panel
		 */
		renderButtons: function () {
			var $btnContainer = this.initialPanel.$element.find( '.survey-button-container' ),
				buttonSelect,
				buttons;

			buttons = $.map( this.config.survey.answers, function ( answer ) {
				return new OO.ui.ButtonOptionWidget( {
					label: mw.msg( answer ),
					data: {
						answer: answer
					}
				} );
			} );

			buttonSelect = new OO.ui.ButtonSelectWidget( {
				items: buttons
			} );
			buttonSelect.connect( this, {
				choose: 'onChoose'
			} );
			buttonSelect.$element.appendTo( $btnContainer );
		},
		/**
		 * Make a brand spanking new OOUI widget from a template partial
		 *
		 * @param {string} widgetName a valid OOUI widget
		 * @param {string} [templatePartialName] name of a registered template partial
		 * @param {Object} [options] further options to be passed to the widget
		 * @return {*} OOUI widget instance
		 */
		widget: function ( widgetName, templatePartialName, options ) {
			var template,
				config = $.extend( {}, this.config[ widgetName ], options );

			if ( templatePartialName ) {
				template = this.templatePartials[ templatePartialName ];
				if ( template ) {
					config.$content = template.render( this.config.templateData );
				}
			}
			return new OO.ui[ widgetName ]( config );
		},
		/**
		 * Log the answer to Schema:QuickSurveysResponses
		 * See {@link https://meta.wikimedia.org/wiki/Schema:QuickSurveysResponses}
		 *
		 * @param {string} answer
		 * @return {jQuery.Deferred}
		 */
		log: function ( answer ) {
			var survey = this.config.survey,
				skin = mw.config.get( 'skin' ),
				// FIXME: remove this when SkinMinervaBeta is renamed to 'minerva-beta'.
				mobileMode = mw.config.get( 'wgMFMode' );

			// On mobile differentiate between minerva stable and beta by appending 'beta' to 'minerva'
			if ( skin === 'minerva' && mobileMode === 'beta' ) {
				skin += mobileMode;
			}

			if ( mw.eventLog ) {
				return mw.eventLog.logEvent( 'QuickSurveysResponses', {
					namespaceId: mw.config.get( 'wgNamespaceNumber' ),
					surveySessionToken: this.config.surveySessionToken,
					surveyInstanceToken: this.config.surveyInstanceToken,
					pageId: mw.config.get( 'wgArticleId' ),
					pageTitle: mw.config.get( 'wgPageName' ),
					surveyCodeName: survey.name,
					surveyResponseValue: answer,
					platform: 'web',
					skin: skin,
					isTablet: !this.config.isMobileLayout,
					userLanguage: mw.config.get( 'wgContentLanguage' ),
					isLoggedIn: !mw.user.isAnon(),
					editCountBucket: utils.getEditCountBucket( mw.config.get( 'wgUserEditCount' ) ),
					countryCode: utils.getCountryCode()
				} );
			}
			return $.Deferred().reject( 'EventLogging not installed.' );
		},
		/**
		 * Fired when one of the options are clicked.
		 *
		 * @param {OO.ui.ButtonOptionWidget|OO.ui.ButtonWidget} btn
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
	mw.extQuickSurveys.views.QuickSurvey = QuickSurvey;
}( jQuery, mediaWiki ) );
