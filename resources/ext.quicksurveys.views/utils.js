( function () {
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
	 * Return two letter country code from the GeoIP cookie.
	 *
	 * Return 'Unknown' if the cookie is not set or code is invalid.
	 * Country codes should be 1-3 characters per ISO 3166-1.
	 *
	 * @return {string}
	 */
	function getCountryCode() {
		var geoIP = mw.cookie.get( 'GeoIP', '' ),
			countryCode;

		if ( geoIP ) {
			countryCode = geoIP.split( ':' )[ 0 ];
			if ( countryCode.length <= 3 ) {
				return countryCode;
			}
		}
		return 'Unknown';
	}

	mw.extQuickSurveys.utils = {
		extend: extend,
		getEditCountBucket: getEditCountBucket,
		getCountryCode: getCountryCode
	};
}() );
