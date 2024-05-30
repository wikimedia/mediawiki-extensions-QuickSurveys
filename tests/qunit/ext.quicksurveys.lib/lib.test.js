const qSurveys = require( 'ext.quicksurveys.lib' ).test;

QUnit.module( 'ext.quicksurveys.lib', {
	beforeEach: function () {
		this.getPanel = function () {
			return $( '<div class="test-panel"></div>' );
		};
		this.isPanelElement = function ( $el ) {
			return $el.hasClass( 'test-panel' );
		};
	}
} );

QUnit.test( 'getSurveyFromQueryString', function ( assert ) {
	[
		[ 'true', [], undefined ],
		[ 'foo', [], undefined ],
		[ 'true', [ { name: 'foo' } ], { name: 'foo' } ],
		[ 'foo', [ { name: 'foo' } ], { name: 'foo' } ],
		[ 'foo', [ { name: 'bar' }, { name: 'foo' } ], { name: 'foo' } ],
		[ 'foo', [ { name: 'foo', type: 'internal' } ], { name: 'foo', type: 'internal' } ],
		[ 'external-survey-foo', [ { name: 'foo', type: 'internal' } ], { name: 'foo', type: 'internal' } ],
		[ 'internal-survey-foo', [ { name: 'foo', type: 'internal' } ], { name: 'foo', type: 'internal' } ]
	].forEach( ( [ queryString, availableSurveys, expectedResult ] ) =>
		assert.deepEqual(
			qSurveys.getSurveyFromQueryString( queryString, availableSurveys ),
			expectedResult
		)
	);
} );

QUnit.test( 'showSurvey: Placement (infobox)', function ( assert ) {
	const minervaTemplate = mw.template.get( 'test.QuickSurveys', 'minerva-1.html' ),
		$locationVector = mw.template.get( 'test.QuickSurveys', 'vector-1.html' ).render(),
		$locationMinerva = minervaTemplate.render(),
		$locationMinervaTablet = minervaTemplate.render();

	qSurveys.insertPanel( $locationVector, this.getPanel(), null, false );
	qSurveys.insertPanel( $locationMinerva, this.getPanel(), null, true );
	qSurveys.insertPanel( $locationMinervaTablet, this.getPanel(), null );

	assert.true( this.isPanelElement( $locationVector.find( '.infobox' ).eq( 0 ).prev() ),
		'Check on desktop page it is inserted in correct place (before infobox)' );
	assert.true( this.isPanelElement( $locationMinerva.find( '#firstp' ).next() ),
		'Check on mobile page it is inserted in correct place (after first paragraph)' );
	assert.true( this.isPanelElement( $locationMinervaTablet.find( '.infobox' ).eq( 0 ).prev() ),
		'Check on mobile page it is inserted in correct place (before infobox)' );
} );

QUnit.test( 'showSurvey: Placement (image)', function ( assert ) {
	const template = mw.template.get( 'test.QuickSurveys', 'minerva-2.html' ),
		$locationVector = mw.template.get( 'test.QuickSurveys', 'vector-2.html' ).render(),
		$locationMinerva = template.render(),
		$locationMinervaTablet = template.render();

	qSurveys.insertPanel( $locationVector, this.getPanel(), null );
	qSurveys.insertPanel( $locationMinerva, this.getPanel(), null, true );
	qSurveys.insertPanel( $locationMinervaTablet, this.getPanel(), null );

	assert.true( this.isPanelElement( $locationVector.find( '#firstp' ).next() ),
		'Check it is inserted in correct place on vector (after first paragraph)' );
	assert.true( this.isPanelElement( $locationMinerva.find( '#firstp' ).next() ),
		'Check it is inserted in correct place (after first paragraph)' );
	assert.true( this.isPanelElement( $locationMinervaTablet.find( '.thumb' ).eq( 0 ).prev() ),
		'Check it is inserted in correct place (before image)' );
} );

QUnit.test( 'showSurvey: Placement (no headings)', function ( assert ) {
	const template = mw.template.get( 'test.QuickSurveys', 'minerva-3.html' ),
		$locationVector = mw.template.get( 'test.QuickSurveys', 'vector-3.html' ).render(),
		$locationMinerva = template.render(),
		$locationMinervaTablet = template.render();

	qSurveys.insertPanel( $locationVector, this.getPanel(), null );
	qSurveys.insertPanel( $locationMinerva, this.getPanel(), null, true );
	qSurveys.insertPanel( $locationMinervaTablet, this.getPanel(), null );

	assert.strictEqual( $locationVector.find( '.test-panel' ).length, 1,
		'Check only one panel got added on Vector' );
	assert.strictEqual( $locationMinerva.find( '.test-panel' ).length, 1,
		'Check only one panel got added on mobile' );
	assert.strictEqual( $locationMinervaTablet.find( '.test-panel' ).length, 1,
		'Check only one panel got added on tablet' );
	assert.true( this.isPanelElement( $locationVector.find( '#firstp' ).next() ),
		'Check it is inserted in correct place on Vector (after first paragraph)' );
	assert.true( this.isPanelElement( $locationMinerva.find( '#firstp' ).next() ),
		'Check it is inserted in correct place on mobile (after first paragraph)' );
	assert.true( this.isPanelElement( $locationMinervaTablet.find( '#firstp' ).next() ),
		'Check it is inserted in correct place on tablet (after first paragraph)' );
} );

QUnit.test( 'surveyMatchesPlatform', function ( assert ) {
	const testCases = [
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
		assert.strictEqual(
			qSurveys.surveyMatchesPlatform( { platforms: test[ 0 ] }, undefined ), test[ 1 ]
		);
		assert.strictEqual( qSurveys.surveyMatchesPlatform( { platforms: test[ 0 ] }, 'stable' ), test[ 2 ] );
		assert.strictEqual( qSurveys.surveyMatchesPlatform( { platforms: test[ 0 ] }, 'beta' ), test[ 3 ] );
	} );
} );

QUnit.test( 'showSurvey: Placement (plain)', function ( assert ) {
	const template = mw.template.get( 'test.QuickSurveys', 'minerva-4.html' ),
		$locationVector = mw.template.get( 'test.QuickSurveys', 'vector-4.html' ).render(),
		$locationMinerva = template.render(),
		$locationMinervaTablet = template.render();

	qSurveys.insertPanel( $locationVector, this.getPanel(), null );
	qSurveys.insertPanel( $locationMinerva, this.getPanel(), null, true );
	qSurveys.insertPanel( $locationMinervaTablet, this.getPanel(), null );

	assert.true( this.isPanelElement( $locationMinerva.find( '#firsth2' ).prev() ),
		'Check it is inserted in correct place on mobile (before first heading)' );
	assert.true( this.isPanelElement( $locationVector.find( '#firsth2' ).prev() ),
		'Check it is inserted in correct place on vector (before first heading)' );
	assert.true( this.isPanelElement( $locationMinervaTablet.find( '#firsth2' ).prev() ),
		'Check it is inserted in correct place on tablet (before first heading)' );
} );

QUnit.test( 'showSurvey: Placement (embedded)', function ( assert ) {
	const $location = mw.template.get( 'test.QuickSurveys', 'embedded-1.html' ).render(),
		embedElementId = 'embed-survey-location';

	qSurveys.insertPanel( $location, this.getPanel(), embedElementId );

	assert.true( this.isPanelElement( $location.find( '#secondh2' ).next().next() ),
		'Check embedded survey is inserted in correct place' );
} );

QUnit.test( 'isInAudience (user, minEdits, maxEdits, geo, pageIds, firstEdit, lastEdit)', function ( assert ) {
	const audienceAnyUser = {},
		anonUser = {
			isAnon: function () {
				return true;
			},
			getRegistration: function () {
				return false;
			}
		},
		userRegisteredOn20170103 = {
			getRegistration: function () {
				return new Date( '2017-01-03T20:20:00+01:00' );
			}
		},
		userRegisteredOn20170104 = {
			getRegistration: function () {
				return new Date( '2017-01-04T20:20:00+01:00' );
			}
		},
		userRegisteredOn20170105 = {
			getRegistration: function () {
				return new Date( '2017-01-05T20:20:00+01:00' );
			}
		},
		userRegisteredOn20170106 = {
			getRegistration: function () {
				return new Date( '2017-01-06T20:20:00+01:00' );
			}
		},
		loggedInUser = {
			isAnon: function () {
				return false;
			}
		},
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
		audienceNotPowerUser = { maxEdits: 1000 },
		audienceLoggedInUser = { anons: false },
		audienceAnonUser = { anons: true },
		audienceRegistrationStart20170104 = { registrationStart: '2017-01-04' },
		audienceRegistrationStart20170105 = { registrationStart: '2017-01-05' },
		audienceRegistrationStart20170106 = { registrationStart: '2017-01-06' },
		audienceRegistrationEnd20170104 = { registrationEnd: '2017-01-04' },
		audienceRegistrationEnd20170105 = { registrationEnd: '2017-01-05' },
		audienceRegistrationEnd20170106 = { registrationEnd: '2017-01-06' },
		audienceRegisteredInJan2017 = { registrationStart: '2017-01-01', registrationEnd: '2017-01-31' },
		audienceRegisteredInFeb2017 = { registrationStart: '2017-02-01', registrationEnd: '2017-01-28' },
		audienceFirstEditAfter20170104 = { firstEdit: { from: '2017-01-04', to: null } },
		audienceFirstEditAfter20170105 = { firstEdit: { from: '2017-01-05', to: null } },
		audienceFirstEditAfter20170106 = { firstEdit: { from: '2017-01-06', to: null } },
		audienceFirstEditRange20170104To20170106 = { firstEdit: { from: '2017-01-04', to: '2017-01-06' } },
		audienceFirstEditRange20170105To20170106 = { firstEdit: { from: '2017-01-05', to: '2017-01-06' } },
		audienceFirstEditRange20170104To20170105 = { firstEdit: { from: '2017-01-04', to: '2017-01-05' } },
		audienceLastEditBefore20170104 = { lastEdit: { from: null, to: '2017-01-04' } },
		audienceLastEditBefore20170105 = { lastEdit: { from: null, to: '2017-01-05' } },
		audienceLastEditAfter20170105 = { lastEdit: { from: '2017-01-05', to: null } },
		audienceLastEditRange20170104To20170106 = { lastEdit: { from: '2017-01-04', to: '2017-01-06' } },
		audienceLastEditRange20170105To20170106 = { lastEdit: { from: '2017-01-05', to: '2017-01-06' } },
		audienceLastEditRange20170104To20170105 = { lastEdit: { from: '2017-01-04', to: '2017-01-05' } },
		audiencePages = { pageIds: [ 123, 456 ] },
		audienceChrome = { userAgent: [ 'Chrome' ] };
	[
		// User registration targeting
		[ audienceRegistrationStart20170104, anonUser, editCount.noneditor, undefined, false,
			'hide survey for anon if registrationStart is set' ],
		[ audienceRegistrationEnd20170104, anonUser, editCount.noneditor, undefined, false,
			'hide survey for anon if registrationEnd is set' ],
		[ audienceRegisteredInJan2017, anonUser, editCount.noneditor, undefined, false,
			'hide survey for anon if both registrationEnd and registrationStart are set' ],
		[ audienceRegistrationStart20170104,
			userRegisteredOn20170105, editCount.noneditor, undefined, true,
			'show survey for user registered on 2017-01-05 if registrationStart is set to 2017-01-04' ],
		[ audienceRegistrationStart20170105, userRegisteredOn20170105, editCount.noneditor,
			undefined, true,
			'show survey for user registered on 2017-01-05 if registrationStart is set to 2017-01-05' ],
		[ audienceRegistrationStart20170106, userRegisteredOn20170105, editCount.noneditor,
			undefined, false,
			'hide survey for user registered on 2017-01-05 if registrationStart is set to 2017-01-06' ],
		[ audienceRegistrationEnd20170104, userRegisteredOn20170105, editCount.noneditor,
			undefined, false,
			'hide survey for user registered on 2017-01-05 if registrationEnd is set to 2017-01-04' ],
		[ audienceRegistrationEnd20170105, userRegisteredOn20170105, editCount.noneditor,
			undefined, true,
			'show survey for user registered on 2017-01-05 if registrationEnd is set to 2017-01-05' ],
		[ audienceRegistrationEnd20170106, userRegisteredOn20170105, editCount.noneditor,
			undefined, true,
			'show survey for user registered on 2017-01-05 if registrationEnd is set to 2017-01-06' ],
		[ audienceRegisteredInJan2017, userRegisteredOn20170105, editCount.noneditor,
			undefined, true,
			'show survey for user registered on 2017-01-05 if registration constraints are set to Jan 2017' ],
		[ audienceRegisteredInFeb2017, userRegisteredOn20170105, editCount.noneditor,
			undefined, false,
			'hide survey for user registered on 2017-01-05 if registration constraints are set to Feb 2017' ],
		// User first and last edit range checking
		[ audienceFirstEditAfter20170104, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-04', true,
			'show survey for user that made their first edit on 2017-01-04' ],
		[ audienceFirstEditAfter20170104, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-05', '2017-01-05', true,
			'show survey for user that made their first edit (5th) after 2017-01-04' ],
		[ audienceFirstEditAfter20170104, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-06', '2017-01-06', true,
			'show survey for user that made their first edit (6th) after 2017-01-04' ],
		[ audienceFirstEditAfter20170104, userRegisteredOn20170103, editCount.newbie, undefined, undefined, '2017-01-03', '2017-01-03', false,
			'hide survey for user that made their first edit before 2017-01-04' ],
		[ audienceFirstEditAfter20170105, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-04', false,
			'hide survey for user that made their first edit before 2017-01-05' ],
		[ audienceFirstEditAfter20170106, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-05', '2017-01-05', false,
			'hide survey for user that made their first edit before 2017-01-06' ],
		[ audienceLastEditAfter20170105, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-05', true,
			'show survey for user that made their last edit on 2017-01-05' ],
		[ audienceLastEditAfter20170105, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-06', true,
			'show survey for user that made their last edit after 2017-01-05' ],
		[ audienceLastEditBefore20170104, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-05', false,
			'hide survey for user that made their last edit on 2017-01-05' ],
		[ audienceLastEditBefore20170105, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-06', false,
			'hide survey for user that made their last edit after 2017-01-05' ],
		[ audienceFirstEditRange20170104To20170106, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-04', true,
			'show survey for user that made their first edit (4th) between 2017-01-04 and 2017-01-06' ],
		[ audienceFirstEditRange20170104To20170106, userRegisteredOn20170105, editCount.newbie, undefined, undefined, '2017-01-05', '2017-01-05', true,
			'show survey for user that made their first edit (5th) between 2017-01-04 and 2017-01-06' ],
		[ audienceFirstEditRange20170104To20170106, userRegisteredOn20170106, editCount.newbie, undefined, undefined, '2017-01-06', '2017-01-06', true,
			'show survey for user that made their first edit (6th) between 2017-01-04 and 2017-01-06' ],
		[ audienceFirstEditRange20170105To20170106, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-04', false,
			'hide survey for user that made their first edit (4th) not between 2017-01-05 and 2017-01-06' ],
		[ audienceFirstEditRange20170104To20170105, userRegisteredOn20170106, editCount.newbie, undefined, undefined, '2017-01-06', '2017-01-06', false,
			'hide survey for user that made their first edit (6th) not between 2017-01-04 and 2017-01-05' ],
		[ audienceLastEditRange20170104To20170106, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-04', true,
			'show survey for user that made their last edit (4th) between 2017-01-04 and 2017-01-06' ],
		[ audienceLastEditRange20170104To20170106, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-05', true,
			'show survey for user that made their last edit (5th) between 2017-01-04 and 2017-01-06' ],
		[ audienceLastEditRange20170104To20170106, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-06', true,
			'show survey for user that made their last edit (6th) between 2017-01-04 and 2017-01-06' ],
		[ audienceLastEditRange20170105To20170106, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-04', false,
			'hide survey for user that made their last edit (4th) not between 2017-01-05 and 2017-01-06' ],
		[ audienceLastEditRange20170104To20170105, userRegisteredOn20170104, editCount.newbie, undefined, undefined, '2017-01-04', '2017-01-06', false,
			'hide survey for user that made their last edit (6th) not between 2017-01-04 and 2017-01-05' ],
		[ audienceLastEditRange20170104To20170105, userRegisteredOn20170104, editCount.newbie, undefined, undefined, undefined, undefined, false,
			'hide survey for registered user that has zero edits in total (2017-01-04 and 2017-01-05)' ],
		[ audienceLastEditRange20170104To20170105, anonUser, editCount.newbie, undefined, undefined, undefined, undefined, false,
			'hide survey for anonymous user that has zero edits in total (2017-01-04 and 2017-01-05)' ],
		// Country targeting
		[ audienceSpain, loggedInUser, editCount.noneditor, undefined, false,
			'If Geo is undefined, we do not know the country so do not show the survey'
		],
		[ audienceSpain, loggedInUser, editCount.noneditor, geo.spain, true,
			'Users in Spain are seeing Spain targeted surveys'
		],
		[ audienceSpain, loggedInUser, editCount.noneditor, geo.france, false,
			'Users in France are not seeing Spain targeted surveys'
		],
		[ audienceFrenchSpeakers, loggedInUser, editCount.noneditor, geo.spain, false,
			'Users in Spain are not seeing French targeted surveys'
		],
		[ audienceFrenchSpeakers, loggedInUser, editCount.noneditor, geo.canada, true,
			'Users in Canada are seeing French targeted surveys'
		],
		[ audienceSpainPowerUsers, loggedInUser, editCount.powerUser, geo.spain, true,
			'Power users in Spain are seeing Spanish power user targeted surveys'
		],
		[ audienceSpainPowerUsers, loggedInUser, editCount.noneditors, geo.spain, true,
			'Non-editors in Spain are not seeing Spanish power user targeted surveys'
		],
		[ audienceSpainPowerUsers, loggedInUser, editCount.noneditors, geo.spain, true,
			'Non-editors in Spain are not seeing Spanish power user targeted surveys'
		],
		[ audienceNonEditorsSpain, loggedInUser, editCount.noneditor, geo.spain, true,
			'Non-editors in Spain are seeing the spanish non-editor survey'
		],
		[ audienceNonEditorsSpain, loggedInUser, editCount.noneditor, geo.france, false,
			'Non-editors in France are not seeing the spanish non-editor survey'
		],
		[ audienceNonEditorsSpain, loggedInUser, editCount.powerUser, geo.spain, false,
			'Power users in Spain are not seeing the spanish non-editor survey'
		],
		// new editors
		[ audienceNonEditors, loggedInUser, editCount.noneditor, true,
			'you can target users with edit count 0' ],
		// Anons
		// user type, edit count, user is shown survey, explanation of test
		[ audienceAnyUser, anonUser, editCount.anon, true,
			'anon user is shown survey where no audience defined'
		],
		[ audienceRecentlyNewUser, anonUser, editCount.anon, false,
			'anons are not targetted if minEdits/maxEdits defined' ],
		[ audiencePowerUser, anonUser, editCount.anon, false,
			'anons are not targetted if minEdits defined' ],
		[ audienceNotPowerUser, anonUser, editCount.anon, true,
			'anons are targetted if only maxEdits defined' ],
		[ audienceAnyUser, anonUser, 1000, true, 'anons are part of all the audience!' ],
		// New users
		[ audienceNewUser, loggedInUser, editCount.newbie, true,
			'the audience is newbies!' ],
		[ audienceRecentlyNewUser, loggedInUser, editCount.newbie, false,
			'a newbie has an edit count < audienceRecentlyNewUser.minEdits'
		],
		[ audienceExperiencedUser, loggedInUser, editCount.newbie, false,
			'a newbie does not see survey for experienced users'
		],
		[ audienceNotPowerUser, loggedInUser, editCount.newbie, true,
			'a newbie is not a power user so sees the survey'
		],
		[ audiencePowerUser, loggedInUser, editCount.newbie, false,
			'a newbie is not a power user so does not see the survey'
		],
		// power users
		[ audienceNotPowerUser, loggedInUser, editCount.powerUser, false,
			'the audience is not power users'
		],
		[ audiencePowerUser, loggedInUser, editCount.powerUser, true,
			'the audience is power users'
		],
		// target logged-in users
		[ audienceLoggedInUser, anonUser, editCount.anon, false,
			'logged-in only: anon shouldn\'t see the survey'
		],
		[ audienceLoggedInUser, loggedInUser, editCount.noneditor, true,
			'logged-in only: logged-in user should see the survey'
		],
		// target anons
		[ audienceAnonUser, loggedInUser, editCount.noneditor, false,
			'anon only: logged-in user should\'t see the survey'
		],
		[ audienceAnonUser, anonUser, editCount.noneditor, true,
			'anon only: anon should see the survey'
		],
		[ audienceAnonUser, anonUser, editCount.noneditor, undefined, 987, true,
			'pages: still see the survey when a page number is present but no filter exists'
		],
		[ audiencePages, anonUser, editCount.noneditor, undefined, 456, true,
			'pages: see the survey when page ID matches'
		],
		[ audiencePages, anonUser, editCount.noneditor, undefined, 987, false,
			'pages: don\'t see the survey when page ID is not matched'
		],
		// target specific user agent
		[ audienceChrome, loggedInUser, editCount.noneditor, true,
			'user agent: show survey when target user agent matches audience user agent'
		]
	].forEach( function ( test ) {
		assert.strictEqual(
			qSurveys.isInAudience.apply( qSurveys, test.slice( 0, test.length - 2 ) ),
			test[ test.length - 2 ],
			test[ test.length - 1 ]
		);
	} );
} );

QUnit.test( 'isQuickSurveysPrefEnabled', function ( assert ) {
	mw.user.options.set( 'displayquicksurveys', false );
	assert.false( qSurveys.isQuickSurveysPrefEnabled(),
		'QuickSurvey preference is disabled (displayquicksurveys)' );

	mw.user.options.set( 'displayquicksurveys', true );
	assert.true( qSurveys.isQuickSurveysPrefEnabled(),
		'QuickSurvey preference is enabled (displayquicksurveys)' );
} );
