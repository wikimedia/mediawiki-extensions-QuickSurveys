'use strict';
const Vue = require( 'vue' );

describe( 'QuickSurvey', () => {
	let vue;
	const VueMock = {
		h: Vue.h,
		createMwApp: ( options ) => Vue.createApp( options )
	};

	beforeEach( () => {
		vue = require( '../../resources/ext.quicksurveys.lib/vue/render.js' );
		window.open = jest.fn();
		mw.eventLog = {
			logEvent: jest.fn()
		};
	} );

	it( 'renders ExternalSurvey if externalLink is defined', () => vue.render(
		VueMock,
		document.createElement( 'div' ),
		{
			name: 'Hello world',
			questions: [
				{
					name: 'Question',
					description: 'Description',
					question: 'Question',
					link: 'https://mylink',
					yesMsg: 'Visit survey',
					noMsg: 'No thanks'
				}
			]
		},
		() => {},
		'ss', 'pp', false,
		'ltr',
		mw.eventLog.logEvent
	).then( ( node ) => {
		expect(
			node.innerHTML
		).toContain( 'Visit survey' );
	} ) );

	it( 'renders with description', () => vue.render(
		VueMock,
		document.createElement( 'div' ),
		{
			name: 'Hello world',
			questions: [
				{
					name: 'Question',
					description: 'Description',
					question: 'Question',
					answers: []
				}
			]
		},
		() => {},
		'ss', 'pp', false,
		'ltr',
		mw.eventLog.logEvent
	).then( ( node ) => {
		expect(
			node.innerHTML
		).toContain( 'Description' );
	} ) );

	it( 'renders without description', () => vue.render(
		VueMock,
		document.createElement( 'div' ),
		{
			name: 'Hello world',
			questions: [
				{
					name: 'Question',
					question: 'Question',
					answers: []
				}
			]
		},
		() => {},
		'ss', 'pp', false,
		'ltr',
		mw.eventLog.logEvent
	).then( ( node ) => {
		expect(
			node.innerHTML
		).not.toContain( 'Description' );
	} ) );

	it( 'renders with freeform text label', () => vue.render(
		VueMock,
		document.createElement( 'div' ),
		{
			name: 'Hello world',
			question: 'Question',
			freeformTextLabel: 'Type some text',
			answers: [ 'yes', 'no' ]
		},
		() => {},
		'ss', 'pp', false,
		'ltr',
		mw.eventLog.logEvent
	).then( ( node ) => {
		expect(
			node.innerHTML
		).not.toContain( '&lt;Type some text&gt;' );
	} ) );

	it( 'calls dismissSurvey when no-thanks is clicked in an external survey', () => {
		const container = document.createElement( 'div' );
		const el = document.createElement( 'div' );
		container.appendChild( el );

		const dismissSurvey = jest.fn( () => {} );
		return vue.render(
			VueMock,
			el,
			{
				link: 'https://survey',
				name: 'Hello world',
				description: 'Description',
				question: 'Question',
				externalLink: 'https://mylink',
				yesMsg: 'Visit survey',
				noMsg: 'No thanks',
				questions: [
					{
						name: 'Question',
						link: 'https://survey',
						description: 'Description',
						question: 'Question',
						externalLink: 'https://mylink',
						yesMsg: 'Visit survey',
						noMsg: 'No thanks'
					}
				]
			},
			() => {},
			'ss', 'pp', false,
			'ltr',
			mw.eventLog.logEvent
		).then( ( node ) => {
			const buttons = Array.from( node.querySelectorAll( 'button' ) ).filter( ( b ) => b.textContent.includes( 'No thanks' ) );
			expect(
				buttons.length
			).toBe( 1 );
			const noThanks = buttons[ 0 ];

			noThanks.click();

			// Wait for next frame for the event to bubble up
			requestAnimationFrame( () => expect(
				dismissSurvey.mock.calls.length
			).toBe( 1 ) );
		} );
	} );
} );
