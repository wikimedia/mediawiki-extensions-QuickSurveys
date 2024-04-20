'use strict';
const wikimediaTestingUtils = require( '@wikimedia/mw-node-qunit' );

/* https://github.com/jsdom/jsdom/issues/2524 */
const { TextEncoder, TextDecoder } = require( 'util' );
global.TextEncoder = TextEncoder;
global.TextDecoder = TextDecoder;

wikimediaTestingUtils.setUp( false );
