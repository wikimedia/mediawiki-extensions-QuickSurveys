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

	QUnit.test( 'showSurvey: Placement (infobox)', 3, function ( assert ) {
		var minervaTemplate = mw.template.get( 'ext.quicksurveys.lib.tests', 'minerva-1.html' ),
			$locationVector = mw.template.get( 'ext.quicksurveys.lib.tests', 'vector-1.html' ).render(),
			$locationMinerva = minervaTemplate.render(),
			$locationMinervaTablet = minervaTemplate.render();

		qSurveys._insertPanel( $locationVector, this.getPanel(), false );
		qSurveys._insertPanel( $locationMinerva, this.getPanel(), true );
		qSurveys._insertPanel( $locationMinervaTablet, this.getPanel() );

		assert.ok( this.isPanelElement( $locationVector.find( '#mw-content-text' ).children().eq( 1 ) ),
			'Check on desktop page it is inserted in correct place (before infobox)' );
		assert.ok( this.isPanelElement( $locationMinerva.find( '> div' ).children().eq( 4 ) ),
			'Check on mobile page it is inserted in correct place (after first paragraph)' );
		assert.ok( this.isPanelElement( $locationMinervaTablet.find( '> div' ).children().eq( 1 ) ),
			'Check on mobile page it is inserted in correct place (before infobox)' );
	} );

	QUnit.test( 'showSurvey: Placement (image)', 3, function ( assert ) {
		var template = mw.template.get( 'ext.quicksurveys.lib.tests', 'minerva-2.html' ),
			$locationVector = mw.template.get( 'ext.quicksurveys.lib.tests', 'vector-2.html' ).render(),
			$locationMinerva = template.render(),
			$locationMinervaTablet = template.render();

		qSurveys._insertPanel( $locationVector, this.getPanel() );
		qSurveys._insertPanel( $locationMinerva, this.getPanel(), true );
		qSurveys._insertPanel( $locationMinervaTablet, this.getPanel() );


		assert.ok( this.isPanelElement( $locationVector.find( '> div' ).eq( 0 ).children().eq( 1 ) ),
			'Check it is inserted in correct place on vector (after first paragraph)' );
		assert.ok( this.isPanelElement( $locationMinervaTablet.find( '> div' ).eq( 0 ).children().eq( 1 ) ),
			'Check it is inserted in correct place (before image)' );
		assert.ok( this.isPanelElement( $locationMinerva.find( '> div' ).eq( 0 ).children().eq( 1 ) ),
			'Check it is inserted in correct place (after first paragraph)' );
	} );

	QUnit.test( 'showSurvey: Placement (no headings)', 6, function ( assert ) {
		var template = mw.template.get( 'ext.quicksurveys.lib.tests', 'minerva-3.html' ),
			$locationVector = mw.template.get( 'ext.quicksurveys.lib.tests', 'vector-3.html' ).render(),
			$locationMinerva = template.render(),
			$locationMinervaTablet = template.render();

		qSurveys._insertPanel( $locationVector, this.getPanel() );
		qSurveys._insertPanel( $locationMinerva, this.getPanel(), true );
		qSurveys._insertPanel( $locationMinervaTablet, this.getPanel() );

		assert.ok( $locationVector.find( '.test-panel' ).length === 1,
			'Check only one panel got added.' );
		assert.ok( $locationMinervaTablet.find( '.test-panel' ).length === 1,
			'Check only one panel got added.' );
		assert.ok( $locationMinerva.find( '.test-panel' ).length === 1,
			'Check only one panel got added.' );
		assert.ok( this.isPanelElement( $locationMinerva.find( '> div' ).eq( 0 ).children().eq( 1 ) ),
			'Check it is inserted in correct place on mobile (after first paragraph)' );
		assert.ok( this.isPanelElement( $locationVector.find( '> div' ).eq( 0 ).children().eq( 1 ) ),
			'Check it is inserted in correct place on vector (after first paragraph)' );
		assert.ok( this.isPanelElement( $locationMinervaTablet.find( '> div' ).eq( 0 ).children().eq( 1 ) ),
			'Check it is inserted in correct place on tablet (after first paragraph)' );
	} );

	QUnit.test( 'showSurvey: Placement (plain)', 3, function ( assert ) {
		var template = mw.template.get( 'ext.quicksurveys.lib.tests', 'minerva-4.html' ),
			$locationVector = mw.template.get( 'ext.quicksurveys.lib.tests', 'vector-4.html' ).render(),
			$locationMinerva = template.render(),
			$locationMinervaTablet = template.render();

		qSurveys._insertPanel( $locationVector, this.getPanel() );
		qSurveys._insertPanel( $locationMinerva, this.getPanel(), true );
		qSurveys._insertPanel( $locationMinervaTablet, this.getPanel() );

		assert.ok( this.isPanelElement( $locationMinerva.find( '> div' ).eq( 1 ) ),
			'Check it is inserted in correct place on mobile (before first heading)' );
		assert.ok( this.isPanelElement( $locationVector.find( '> div' ).children().eq( 1 ) ),
			'Check it is inserted in correct place on vector (before first heading)' );
		assert.ok( this.isPanelElement( $locationMinervaTablet.find( '> div' ).eq ( 1 ) ),
			'Check it is inserted in correct place on tablet (before first heading)' );
	} );

}( jQuery ) );
