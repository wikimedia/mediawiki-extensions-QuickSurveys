'use strict';
const wikimediaTestingUtils = require( '@wikimedia/mw-node-qunit' );

/* https://github.com/jsdom/jsdom/issues/2524 */
const { TextEncoder, TextDecoder } = require( 'util' );
global.TextEncoder = TextEncoder;
global.TextDecoder = TextDecoder;

wikimediaTestingUtils.setUp( false );

const msg = ( value ) => ( {
	parse: () => value
} );
global.mw.message = ( key ) => {
	switch ( key ) {
		case 'qunit-message-link-special-page':
			return msg( 'https://en.wikipedia.org/wiki/Special:QuickSurvey' );
		case 'qunit-message-mylink':
			return msg( 'https://mylink' );
		case 'qunit-message-survey-link':
			return msg( 'https://survey' );
		default:
			return msg( `<<${ key }>>` );
	}
};
