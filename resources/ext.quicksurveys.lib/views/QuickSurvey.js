var utils = require( './utils.js' );

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

OO.inheritClass( QuickSurvey, OO.ui.StackLayout );

utils.extend( QuickSurvey.prototype, {
	/**
	 * Specifies partials (sub-templates) for use by the widget
	 *
	 * @property {Object}
	 */
	templatePartials: {
		// eslint-disable-next-line no-jquery/no-parse-html-literal
		initialPanel: $(
			'<div>' +
				'<strong data-question></strong>' +
				'<p data-description></p>' +
				'<div class="survey-button-container"></div>' +
				'<div class="survey-footer" data-footer></div>' +
			'</div>'
		),
		// eslint-disable-next-line no-jquery/no-parse-html-literal
		finalPanel: $(
			'<div>' +
				'<strong data-finalHeading></strong>' +
				'<div class="survey-footer" data-footer></div>' +
			'</div>'
		)
	},
	/**
	 * A set of default options that are merged with config passed into the initialize function.
	 * This is likely to change so currently no options are documented.
	 *
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
		var event;

		this.config = config || {};
		$.extend( true, this.config, this.defaults );

		if ( config.survey.privacyPolicy ) {
			// eslint-disable-next-line mediawiki/msg-doc
			this.config.templateData.footer = mw.message( config.survey.privacyPolicy ).parse();
		}

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

		event = {
			beaconCapable: !!navigator.sendBeacon,
			surveySessionToken: this.config.surveySessionToken,
			pageviewToken: this.config.pageviewToken,
			surveyCodeName: this.config.survey.name,
			eventName: 'eligible'
		};

		if ( window.performance && performance.now ) {
			event.performanceNow = Math.round( performance.now() );
		}

		mw.eventLog.logEvent( 'QuickSurveyInitiation', event );
	},
	/**
	 * Shuffle answers in place
	 *
	 * @param {Array} [answers] answers coming from configuration
	 * @return {Array} shuffled answers
	 */
	shuffleAnswers: function ( answers ) {
		var counter = answers.length,
			i, temp;

		while ( counter > 0 ) {
			i = Math.floor( Math.random() * counter );

			counter--;

			temp = answers[ counter ];
			answers[ counter ] = answers[ i ];
			answers[ i ] = temp;
		}

		return answers;
	},
	/**
	 * Render the widget and attach event handlers.
	 */
	renderButtons: function () {
		throw new Error( 'Pure virtual method QuickSurvey.renderButtons was called.' );
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
		var templateClone,
			template = this.templatePartials[ templatePartialName ],
			config = $.extend( {}, this.config[ widgetName ], options ),
			templateData = this.config.templateData;

		if ( template ) {
			templateClone = template.clone();
			templateClone.find( '[data-question]' ).text( templateData.question );
			templateClone.find( '[data-description]' ).text( templateData.description );
			templateClone.find( '[data-footer]' ).html( templateData.footer );
			templateClone.find( '[data-finalHeading]' ).text( templateData.finalHeading );
			config.$content = templateClone;
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

		// On mobile differentiate between minerva stable and beta
		// by appending 'beta' to 'minerva'
		if ( skin === 'minerva' && mobileMode === 'beta' ) {
			skin += mobileMode;
		}

		return mw.eventLog.logEvent( 'QuickSurveysResponses', {
			namespaceId: mw.config.get( 'wgNamespaceNumber' ),
			surveySessionToken: this.config.surveySessionToken,
			pageviewToken: this.config.pageviewToken,
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
	},
	/**
	 * Submit user's answer to the backend and show the next panel
	 *
	 * @param {string} answer
	 */
	submit: function ( answer ) {
		this.log( answer );
		/**
		 * @event dismiss fired when any of the buttons in the survey are selected.
		 */
		this.emit( 'dismiss' );
		this.setItem( this.finalPanel );
	}
} );

module.exports = QuickSurvey;
