const utils = require( './utils.js' );

module.exports = {
	/**
	 *
	 * @param {string} name
	 * @param {string} answer
	 * @param {string} surveySessionToken
	 * @param {string} pageviewToken
	 * @param {boolean} isTablet
	 * @return {Array} of data to be passed to logger
	 */
	logResponseData: function ( name, answer, surveySessionToken, pageviewToken, isTablet ) {
		let skin = mw.config.get( 'skin' );
		// FIXME: remove this when SkinMinervaBeta is renamed to 'minerva-beta'.
		const mobileMode = mw.config.get( 'wgMFMode' );

		// On mobile differentiate between minerva stable and beta
		// by appending 'beta' to 'minerva'
		if ( skin === 'minerva' && mobileMode === 'beta' ) {
			skin += mobileMode;
		}

		const event = {
			namespaceId: mw.config.get( 'wgNamespaceNumber' ),
			surveySessionToken: surveySessionToken,
			pageviewToken: pageviewToken,
			pageId: mw.config.get( 'wgArticleId' ),
			pageTitle: mw.config.get( 'wgPageName' ),
			surveyCodeName: name,
			surveyResponseValue: answer,
			platform: 'web',
			skin: skin,
			isTablet: isTablet,
			userLanguage: mw.config.get( 'wgContentLanguage' ),
			isLoggedIn: !mw.user.isAnon(),
			countryCode: utils.getCountryCode()
		};
		const editCountBucket = mw.config.get( 'wgUserEditCountBucket' );
		if ( editCountBucket ) {
			event.editCountBucket = editCountBucket;
		}
		return event;
	}
};
