/**
 * Logs an event if possible.
 *
 * @param {string} schemaName
 * @param {Object} eventData
 */
module.exports = function ( schemaName, eventData ) {
	switch ( schemaName ) {
		case 'QuickSurveyInitiation':
		case 'QuickSurveysResponses':
			mw.track( 'event.' + schemaName, eventData );
			return;
		default:
			throw new Error( 'Unknown event logged' );
	}
};
