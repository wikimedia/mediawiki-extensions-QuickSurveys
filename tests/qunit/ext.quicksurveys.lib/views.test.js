( function () {
	var qSurveys = mw.extQuickSurveys;

	QUnit.module( 'ext.quicksurveys.lib/views' );

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

		survey = new qSurveys.SingleAnswerSurvey( config );

		buttonLabels = survey.initialPanel.$element.find( '.survey-button-container span.oo-ui-labelElement-label' ).text();

		assert.strictEqual( buttonLabels,
			'⧼ext-quicksurveys-example-internal-survey-answer-positive⧽' +
			'⧼ext-quicksurveys-example-internal-survey-answer-neutral⧽' +
			'⧼ext-quicksurveys-example-internal-survey-answer-negative⧽' );

		this.sandbox.stub( Math, 'random' ).returns( 0.11494871760616443 );
		config.survey.shuffleAnswersDisplay = true;

		survey = new qSurveys.SingleAnswerSurvey( config );

		buttonLabels = survey.initialPanel.$element.find( '.survey-button-container span.oo-ui-labelElement-label' ).text();

		assert.strictEqual( buttonLabels,
			'⧼ext-quicksurveys-example-internal-survey-answer-neutral⧽' +
			'⧼ext-quicksurveys-example-internal-survey-answer-negative⧽' +
			'⧼ext-quicksurveys-example-internal-survey-answer-positive⧽' );
	} );

	QUnit.test( 'multiple-answer checkbox layout', function ( assert ) {
		var config = {
				survey: {
					answers: [
						'ext-quicksurveys-example-embedded-survey-answer-1',
						'ext-quicksurveys-example-embedded-survey-answer-2',
						'ext-quicksurveys-example-embedded-survey-answer-3',
						'ext-quicksurveys-example-embedded-survey-answer-4'
					]
				}
			},
			buttonLabels,
			survey;

		survey = new qSurveys.MultipleAnswerSurvey( config );

		buttonLabels = survey.initialPanel.$element.find(
			'.survey-button-container .oo-ui-checkboxMultioptionWidget .oo-ui-labelElement-label' ).text();

		assert.strictEqual( buttonLabels,
			'⧼ext-quicksurveys-example-embedded-survey-answer-1⧽' +
			'⧼ext-quicksurveys-example-embedded-survey-answer-2⧽' +
			'⧼ext-quicksurveys-example-embedded-survey-answer-3⧽' +
			'⧼ext-quicksurveys-example-embedded-survey-answer-4⧽'
		);

		assert.strictEqual(
			survey.initialPanel.$element.find( '.oo-ui-buttonElement-button' ).text(),
			mw.msg( 'ext-quicksurveys-internal-freeform-survey-submit-button' )
		);
	} );

}() );
