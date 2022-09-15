/**
 * Extend an object with extra properties.
 *
 * @ignore
 * @param {Object} target Object to extend.
 * @param {Object} mixin Properties to incorporate into the target.
 */
function extend( target, mixin ) {
	let key;
	for ( key in mixin ) {
		target[ key ] = mixin[ key ];
	}
}

/**
 * Get a two-letter country code based on the user's IP-connection.
 *
 * The Geo object is derived from a Cookie response header in the
 * CentralNotice `ext.centralNotice.geoIP` module (loaded on all
 * page views when installed). If the cookie was refused, this
 * falls back to the string "Unknown".
 *
 * @return {string} Two-letter country code, "XX", or "Unknown".
 */
function getCountryCode() {
	/* global Geo */
	if ( window.Geo && typeof Geo.country === 'string' ) {
		return Geo.country;
	}
	return 'Unknown';
}

/**
 * Shuffle answers in place
 *
 * @param {Array} [answers] answers coming from configuration
 * @return {Array} shuffled answers
 */
function shuffleAnswers( answers ) {
	let counter = answers.length,
		i, temp;

	while ( counter > 0 ) {
		i = Math.floor( Math.random() * counter );

		counter--;

		temp = answers[ counter ];
		answers[ counter ] = answers[ i ];
		answers[ i ] = temp;
	}

	return answers;
}

module.exports = {
	shuffleAnswers: shuffleAnswers,
	extend: extend,
	getCountryCode: getCountryCode
};
