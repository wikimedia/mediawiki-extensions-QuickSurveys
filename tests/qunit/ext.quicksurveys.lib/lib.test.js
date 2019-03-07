( function () {
	var qSurveys = mw.extQuickSurveys;

	QUnit.module( 'ext.quicksurveys.lib', {
		setup: function () {
			this.getPanel = function () {
				return $( '<div class="test-panel"></div>' );
			};
			this.isPanelElement = function ( $el ) {
				return $el.hasClass( 'test-panel' );
			};
		}
	} );

	QUnit.test( 'showSurvey: Placement (infobox)', function ( assert ) {
		var minervaTemplate = mw.template.get( 'ext.quicksurveys.lib.tests', 'minerva-1.html' ),
			$locationVector = mw.template.get( 'ext.quicksurveys.lib.tests', 'vector-1.html' ).render(),
			$locationMinerva = minervaTemplate.render(),
			$locationMinervaTablet = minervaTemplate.render();

		qSurveys._insertPanel( $locationVector, this.getPanel(), false );
		qSurveys._insertPanel( $locationMinerva, this.getPanel(), true );
		qSurveys._insertPanel( $locationMinervaTablet, this.getPanel() );

		assert.ok( this.isPanelElement( $locationVector.find( '.infobox' ).eq( 0 ).prev() ),
			'Check on desktop page it is inserted in correct place (before infobox)' );
		assert.ok( this.isPanelElement( $locationMinerva.find( '#firstp' ).next() ),
			'Check on mobile page it is inserted in correct place (after first paragraph)' );
		assert.ok( this.isPanelElement( $locationMinervaTablet.find( '.infobox' ).eq( 0 ).prev() ),
			'Check on mobile page it is inserted in correct place (before infobox)' );
	} );

	QUnit.test( 'showSurvey: Placement (image)', function ( assert ) {
		var template = mw.template.get( 'ext.quicksurveys.lib.tests', 'minerva-2.html' ),
			$locationVector = mw.template.get( 'ext.quicksurveys.lib.tests', 'vector-2.html' ).render(),
			$locationMinerva = template.render(),
			$locationMinervaTablet = template.render();

		qSurveys._insertPanel( $locationVector, this.getPanel() );
		qSurveys._insertPanel( $locationMinerva, this.getPanel(), true );
		qSurveys._insertPanel( $locationMinervaTablet, this.getPanel() );


		assert.ok( this.isPanelElement( $locationVector.find( '#firstp' ).next() ),
			'Check it is inserted in correct place on vector (after first paragraph)' );
		assert.ok( this.isPanelElement( $locationMinerva.find( '#firstp' ).next() ),
			'Check it is inserted in correct place (after first paragraph)' );
		assert.ok( this.isPanelElement( $locationMinervaTablet.find( '.thumb' ).eq( 0 ).prev() ),
			'Check it is inserted in correct place (before image)' );
	} );

	QUnit.test( 'showSurvey: Placement (no headings)', function ( assert ) {
		var template = mw.template.get( 'ext.quicksurveys.lib.tests', 'minerva-3.html' ),
			$locationVector = mw.template.get( 'ext.quicksurveys.lib.tests', 'vector-3.html' ).render(),
			$locationMinerva = template.render(),
			$locationMinervaTablet = template.render();

		qSurveys._insertPanel( $locationVector, this.getPanel() );
		qSurveys._insertPanel( $locationMinerva, this.getPanel(), true );
		qSurveys._insertPanel( $locationMinervaTablet, this.getPanel() );

		assert.equal( $locationVector.find( '.test-panel' ).length, 1,
			'Check only one panel got added on Vector' );
		assert.equal( $locationMinerva.find( '.test-panel' ).length, 1,
			'Check only one panel got added on mobile' );
		assert.equal( $locationMinervaTablet.find( '.test-panel' ).length, 1,
			'Check only one panel got added on tablet' );
		assert.ok( this.isPanelElement( $locationVector.find( '#firstp' ).next() ),
			'Check it is inserted in correct place on Vector (after first paragraph)' );
		assert.ok( this.isPanelElement( $locationMinerva.find( '#firstp' ).next() ),
			'Check it is inserted in correct place on mobile (after first paragraph)' );
		assert.ok( this.isPanelElement( $locationMinervaTablet.find( '#firstp' ).next() ),
			'Check it is inserted in correct place on tablet (after first paragraph)' );
	} );

	QUnit.test( 'surveyMatchesPlatform', function ( assert ) {
		var testCases = [
			[
				// desktop only
				{
					desktop: [ 'stable', 'beta' ],
					mobile: []
				},
				true, false, false
			],
			// desktop only
			[
				{
					desktop: [ 'stable', 'beta' ]
				},
				true, false, false
			],
			// mobile only
			[
				{
					mobile: [ 'stable', 'beta' ]
				},
				false, true, true
			],
			// mobile only
			[
				{
					desktop: [],
					mobile: [ 'stable', 'beta' ]
				},
				false, true, true
			],
			// mobile beta only
			[
				{
					desktop: [],
					mobile: [ 'beta' ]
				},
				false, false, true
			],
			// mobile beta only
			[
				{
					mobile: [ 'beta' ]
				},
				false, false, true
			]
		];
		testCases.forEach( function ( test ) {
			assert.ok( qSurveys.surveyMatchesPlatform( { platforms: test[0] }, undefined ) === test[1] );
			assert.ok( qSurveys.surveyMatchesPlatform( { platforms: test[0] }, 'stable' ) === test[2] );
			assert.ok( qSurveys.surveyMatchesPlatform( { platforms: test[0] }, 'beta' ) === test[3] );
		} );
		assert.expect( testCases.length * 3 );
	} );

	QUnit.test( 'showSurvey: Placement (plain)', function ( assert ) {
		var template = mw.template.get( 'ext.quicksurveys.lib.tests', 'minerva-4.html' ),
			$locationVector = mw.template.get( 'ext.quicksurveys.lib.tests', 'vector-4.html' ).render(),
			$locationMinerva = template.render(),
			$locationMinervaTablet = template.render();

		qSurveys._insertPanel( $locationVector, this.getPanel() );
		qSurveys._insertPanel( $locationMinerva, this.getPanel(), true );
		qSurveys._insertPanel( $locationMinervaTablet, this.getPanel() );

		assert.ok( this.isPanelElement( $locationMinerva.find( '#firsth2' ).prev() ),
			'Check it is inserted in correct place on mobile (before first heading)' );
		assert.ok( this.isPanelElement( $locationVector.find( '#firsth2' ).prev() ),
			'Check it is inserted in correct place on vector (before first heading)' );
		assert.ok( this.isPanelElement( $locationMinervaTablet.find( '#firsth2' ).prev() ),
			'Check it is inserted in correct place on tablet (before first heading)' );
	} );

	QUnit.test( 'isInAudience (minEdits, maxEdits, geo)', function ( assert ) {
		var audienceAnyUser = {},
			editCount = {
				anon: null,
				noneditor: 0,
				newbie: 4,
				powerUser: 10000
			},
			geo = {
				spain: {
					country: 'ES'
				},
				france: {
					country: 'FR'
				},
				canada: {
					country: 'CA'
				}
			},
			audienceSpain = { countries: [ 'ES' ] },
			audienceFrenchSpeakers = { countries: [ 'FR', 'CA' ] },
			audienceNonEditors = { minEdits: 0, maxEdits: 0 },
			audienceNonEditorsSpain = { minEdits: 0, maxEdits: 0, countries: [ 'ES' ] },
			audienceNewUser = { minEdits: 1, maxEdits: 4 },
			audienceRecentlyNewUser = { minEdits: 5, maxEdits: 99 },
			audienceExperiencedUser = { minEdits: 100, maxEdits: 999 },
			audiencePowerUser = { minEdits: 1000 },
			audienceSpainPowerUsers = { countries: [ 'ES' ], minEdits: 1000 },
			audienceNotPowerUser = { maxEdits: 1000 };

		[
			// Country targetting
			[ audienceSpain, editCount.noneditor, undefined, false,
				'If Geo is undefined, we do not know the country so do not show the survey'
			],
			[ audienceSpain, editCount.noneditor, geo.spain, true,
				'Users in Spain are seeing Spain targeted surveys'
			],
			[ audienceSpain, editCount.noneditor, geo.france, false,
				'Users in France are not seeing Spain targeted surveys'
			],
			[ audienceFrenchSpeakers, editCount.noneditor, geo.spain, false,
				'Users in Spain are not seeing French targeted surveys'
			],
			[ audienceFrenchSpeakers, editCount.noneditor, geo.canada, true,
				'Users in Canada are seeing French targeted surveys'
			],
			[ audienceSpainPowerUsers, editCount.powerUser, geo.spain, true,
				'Power users in Spain are seeing Spanish power user targeted surveys'
			],
			[ audienceSpainPowerUsers, editCount.noneditors, geo.spain, true,
				'Non-editors in Spain are not seeing Spanish power user targeted surveys'
			],
			[ audienceSpainPowerUsers, editCount.noneditors, geo.spain, true,
				'Non-editors in Spain are not seeing Spanish power user targeted surveys'
			],
			[ audienceNonEditorsSpain, editCount.noneditor, geo.spain, true,
				'Non-editors in Spain are seeing the spanish non-editor survey'
			],
			[ audienceNonEditorsSpain, editCount.noneditor, geo.france, false,
				'Non-editors in France are not seeing the spanish non-editor survey'
			],
			[ audienceNonEditorsSpain, editCount.powerUser, geo.spain, false,
				'Power users in Spain are not seeing the spanish non-editor survey'
			],
			// new editors
			[ audienceNonEditors, editCount.noneditor, true,
				'you can target users with edit count 0' ],
			// Anons
			// user type, edit count, user is shown survey, explanation of test
			[ audienceAnyUser, editCount.anon, true,
				'anon user is shown survey where no audience defined'
			],
			[ audienceRecentlyNewUser, editCount.anon, false,
				'anons are not targetted if minEdits/maxEdits defined' ],
			[ audiencePowerUser, editCount.anon, false,
				'anons are not targetted if minEdits defined' ],
			[ audienceNotPowerUser, editCount.anon, true,
				'anons are targetted if only maxEdits defined' ],
			[ audienceAnyUser, 1000, true, 'anons are part of all the audience!' ],
			// New users
			[ audienceNewUser, editCount.newbie, true,
				'the audience is newbies!' ],
			[ audienceRecentlyNewUser, editCount.newbie, false,
				'a newbie has an edit count < audienceRecentlyNewUser.minEdits'
			],
			[ audienceExperiencedUser, editCount.newbie, false,
				'a newbie does not see survey for experienced users'
			],
			[ audienceNotPowerUser, editCount.newbie, true,
				'a newbie is not a power user so sees the survey'
			],
			[ audiencePowerUser, editCount.newbie, false,
				'a newbie is not a power user so does not see the survey'
			],
			// power users
			[ audienceNotPowerUser, editCount.powerUser, false,
				'the audience is not power users'
			],
			[ audiencePowerUser, editCount.powerUser, true,
				'the audience is power users'
			]

		].forEach( function ( test ) {
			assert.ok(
				qSurveys.isInAudience.apply( qSurveys, test.slice( 0, test.length - 2 ) ) === test[ test.length - 2 ],
				test[ test.length - 1 ]
			);
		} );
	} );
}() );
