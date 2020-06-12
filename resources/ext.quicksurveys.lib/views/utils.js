/**
 * Extend an object with extra properties.
 *
 * @ignore
 * @param {Object} target Object to extend.
 * @param {Object} mixin Properties to incorporate into the target.
 */
function extend( target, mixin ) {
	var key;
	for ( key in mixin ) {
		target[ key ] = mixin[ key ];
	}
}

/**
 * Get edit count bucket name, based on the number of edits made.
 *
 * @param {number|null} editCount
 * @return {string}
 */
function getEditCountBucket( editCount ) {
	if ( editCount >= 1000 ) {
		return '1000+ edits';
	}
	if ( editCount >= 100 ) {
		return '100-999 edits';
	}
	if ( editCount >= 5 ) {
		return '5-99 edits';
	}
	if ( editCount >= 1 ) {
		return '1-4 edits';
	}
	return '0 edits';
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

module.exports = {
	extend: extend,
	getEditCountBucket: getEditCountBucket,
	getCountryCode: getCountryCode
};
