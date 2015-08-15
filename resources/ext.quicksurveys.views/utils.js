( function () {
	/**
	 * Return edit count bucket based on the number of edits
	 * @param {Number} editCount
	 * @returns {String}
	 */
	function getEditCountBucket( editCount ) {
		var bucket;

		if ( editCount === 0 || editCount === null ) {
			bucket = '0';
		} else if ( editCount >= 1 && editCount <= 4 ) {
			bucket = '1-4';
		} else if ( editCount >= 5 && editCount <= 99 ) {
			bucket = '5-99';
		} else if ( editCount >= 100 && editCount <= 999 ) {
			bucket = '100-999';
		} else if ( editCount >= 1000 ) {
			bucket = '1000+';
		}
		bucket += ' edits';
		return bucket;
	}

	/**
	 * Return two letter country code from the GeoIP cookie.
	 * Return 'Unknown' if the cookie is not set.
	 * @returns {String}
	 */
	function getCountryCode() {
		var geoIP = mw.cookie.get( 'GeoIP', '' );

		if ( geoIP ) {
			return geoIP.split( ':' )[0];
		}
		return 'Unknown';
	}

	mw.extQuickSurveys = mw.extQuickSurveys || {};
	mw.extQuickSurveys.views = mw.extQuickSurveys.views || {};
	mw.extQuickSurveys.views.utils = {
		getEditCountBucket: getEditCountBucket,
		getCountryCode: getCountryCode
	};
}( jQuery ) );

