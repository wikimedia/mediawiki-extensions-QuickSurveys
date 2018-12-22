( function () {
	var qSurveys = mw.extQuickSurveys;

	QUnit.module( 'ext.quicksurveys.views' );

	QUnit.test( 'shuffleAnswersDisplay', function ( assert ) {
		var config = {
				survey: {
					answers: [
						'ext-quicksurveys-example-internal-survey-answer-positive',
						'ext-quicksurveys-example-internal-survey-answer-neutral',
						'ext-quicksurveys-example-internal-survey-answer-negative'
					],
					shuffleAnswersDisplay: false
				}
			},
			buttonLabels,
			survey;

		survey = new qSurveys.QuickSurvey( config );

		buttonLabels = survey.initialPanel.$element.find( '.survey-button-container span.oo-ui-labelElement-label' ).text();

		assert.equal( buttonLabels,
			'⧼ext-quicksurveys-example-internal-survey-answer-positive⧽' +
			'⧼ext-quicksurveys-example-internal-survey-answer-neutral⧽' +
			'⧼ext-quicksurveys-example-internal-survey-answer-negative⧽' );

		this.sandbox.stub( Math, 'random' ).returns( 0.11494871760616443 );
		config.survey.shuffleAnswersDisplay = true;

		survey = new qSurveys.QuickSurvey( config );

		buttonLabels = survey.initialPanel.$element.find( '.survey-button-container span.oo-ui-labelElement-label' ).text();

		assert.equal( buttonLabels,
			'⧼ext-quicksurveys-example-internal-survey-answer-neutral⧽' +
			'⧼ext-quicksurveys-example-internal-survey-answer-negative⧽' +
			'⧼ext-quicksurveys-example-internal-survey-answer-positive⧽' );
	} );

}() );
