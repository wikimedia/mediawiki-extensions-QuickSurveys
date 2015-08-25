( function () {
	/**
	 * Extends a class with new methods and member properties.
	 *
	 * @param {OO.Class} ParentClass to extend.
	 * @param {Object} prototype Prototype that should be incorporated into the new Class.
	 * @ignore
	 * FIXME: Talk about upstreaming this to oojs ui as a utility function.
	 * @return {Class}
	 */
	function extend( ChildClass, ParentClass, prototype ) {
		var key;

		OO.inheritClass( ChildClass, ParentClass );
		for ( key in prototype ) {
			ChildClass.prototype[key] = prototype[key];
		}
		return ChildClass;
	}

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
		extend: extend,
		getEditCountBucket: getEditCountBucket,
		getCountryCode: getCountryCode
	};
}( jQuery ) );

