( function ( $ ) {
	var qSurveys = mw.extQuickSurveys;

	QUnit.module( 'QuickSurveys', {
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

}( jQuery ) );