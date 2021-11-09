'use strict';

const wvui = require( '@wikimedia/wvui' ).default;

describe( 'QuickSurvey', () => {
	let vue;

	beforeEach( () => {
		jest.mock( 'wvui', () => wvui );
		vue = require( '../../resources/ext.quicksurveys.lib/vue/render.js' );
		window.open = jest.fn();
		mw.eventLog = {
			logEvent: jest.fn()
		};
	} );

	it( 'renders without answers if externalLink is defined', () => {
		return vue.render(
			document.createElement( 'div' ),
			{
				link: 'https://survey',
				name: 'Hello world',
				description: 'Description',
				question: 'Question',
				externalLink: 'https://mylink'
			},
			() => {},
			'ss', 'pp', false
		).then( ( node ) => {
			expect(
				node.innerHTML
			).toContain( 'ext-quicksurveys-external-survey-yes-button' );
		} );
	} );

	it( 'renders with description', () => {
		return vue.render(
			document.createElement( 'div' ),
			{
				link: 'https://survey',
				name: 'Hello world',
				description: 'Description',
				question: 'Question',
				answers: []
			},
			() => {},
			'ss', 'pp', false
		).then( ( node ) => {
			expect(
				node.innerHTML
			).toContain( 'Description' );
		} );
	} );

	it( 'renders without description', () => {
		return vue.render(
			document.createElement( 'div' ),
			{
				link: 'https://survey',
				name: 'Hello world',
				question: 'Question',
				answers: [],
				instanceTokenParameterName: '111'
			},
			() => {},
			'ss', 'pp', false
		).then( ( node ) => {
			expect(
				node.innerHTML
			).not.toContain( 'Description' );
		} );
	} );

	it( 'renders with freeform text label', () => {
		return vue.render(
			document.createElement( 'div' ),
			{
				name: 'Hello world',
				question: 'Question',
				freeformTextLabel: 'Type some text',
				answers: [ 'yes', 'no' ]
			},
			() => {},
			'ss', 'pp', false
		).then( ( node ) => {
			expect(
				node.innerHTML
			).not.toContain( '&lt;Type some text&gt;' );
		} );
	} );

	it( 'calls dismissSurvey when no-thanks is clicked in an external survey', () => {
		const container = document.createElement( 'div' );
		const el = document.createElement( 'div' );
		container.appendChild( el );

		const dismissSurvey = jest.fn( () => {} );
		return vue.render(
			el,
			{
				link: 'https://survey',
				name: 'Hello world',
				description: 'Description',
				question: 'Question',
				externalLink: 'https://mylink'
			},
			() => {},
			'ss', 'pp', false
		).then( ( node ) => {
			const buttons = Array.from( node.querySelectorAll( 'button' ) ).filter( ( b ) => b.textContent.includes( 'ext-quicksurveys-external-survey-no-button' ) );
			expect(
				buttons.length
			).toBe( 1 );
			const noThanks = buttons[ 0 ];

			noThanks.click();

			// Wait for next frame for the event to bubble up
			requestAnimationFrame( () =>
				expect(
					dismissSurvey.mock.calls.length
				).toBe( 1 ) );
		} );
	} );
} );
