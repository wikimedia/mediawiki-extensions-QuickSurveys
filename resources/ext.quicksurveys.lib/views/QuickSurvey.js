var QuickSurveyLogger = require( '../QuickSurveyLogger.js' );

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

/**
 * Specifies partials (sub-templates) for use by the widget
 *
 * @property {Object}
 */
QuickSurvey.prototype.templatePartials = {
	// eslint-disable-next-line no-jquery/no-parse-html-literal
	initialPanel: $(
		'<div>' +
			'<strong data-question></strong>' +
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
};

/**
 * A set of default options that are merged with config passed into the initialize function.
 * This is likely to change so currently no options are documented.
 *
 * @cfg {Object} defaults Default options hash.
 */
QuickSurvey.prototype.defaults = {
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
};

/**
 * Initialise a widget.
 *
 * @param {Object} config
 */
QuickSurvey.prototype.initialize = function ( config ) {
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
	QuickSurveyLogger.logInitialized(
		this.config.survey.name,
		this.config.surveySessionToken,
		this.config.pageviewToken
	);
};

/**
 * Render the widget and attach event handlers.
 */
QuickSurvey.prototype.renderButtons = function () {
	throw new Error( 'Pure virtual method QuickSurvey.renderButtons was called.' );
};

/**
 * Make a brand spanking new OOUI widget from a template partial
 *
 * @param {string} widgetName a valid OOUI widget
 * @param {string} [templatePartialName] name of a registered template partial
 * @param {Object} [options] further options to be passed to the widget
 * @return {*} OOUI widget instance
 */
QuickSurvey.prototype.widget = function ( widgetName, templatePartialName, options ) {
	var templateClone, question,
		template = this.templatePartials[ templatePartialName ],
		config = $.extend( {}, this.config[ widgetName ], options ),
		templateData = this.config.templateData;

	if ( template ) {
		templateClone = template.clone();

		question = templateClone.find( '[data-question]' );
		question.text( templateData.question );

		if ( templateData.description ) {
			question.after(

				// We set the data-description attribute for consistency with the other elements
				// in the template.
				$( '<p>' )
					.attr( 'data-description', '' )
					.text( templateData.description )
			);
		}

		templateClone.find( '[data-footer]' ).html( templateData.footer );
		templateClone.find( '[data-finalHeading]' ).text( templateData.finalHeading );

		config.$content = templateClone;
	}

	return new OO.ui[ widgetName ]( config );
};

/**
 * Submit user's answer to the backend and show the next panel
 *
 * @param {string} answer
 */
QuickSurvey.prototype.submit = function ( answer ) {
	this.log( answer );
	/**
	 * @event dismiss fired when any of the buttons in the survey are selected.
	 */
	this.emit( 'dismiss' );
	this.setItem( this.finalPanel );
};

/**
 * Log the answer to Schema:QuickSurveysResponses
 * See {@link https://meta.wikimedia.org/wiki/Schema:QuickSurveysResponses}
 *
 * @param {string} answer
 */
QuickSurvey.prototype.log = function ( answer ) {
	QuickSurveyLogger.logResponse(
		this.config.survey.name,
		answer,
		this.config.surveySessionToken,
		this.config.pageviewToken,
		!this.config.isMobileLayout
	);
};

module.exports = QuickSurvey;
