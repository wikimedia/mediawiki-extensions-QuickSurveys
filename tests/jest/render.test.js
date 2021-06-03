let vue;
const wvui = require( '@wikimedia/wvui' ).default;

describe( 'QuickSurvey', () => {
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
} );
