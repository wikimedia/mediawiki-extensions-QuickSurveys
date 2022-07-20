/**
 * Logs an event if possible.
 *
 * @param {string} schemaName
 * @param {Object} eventData
 */
module.exports = function ( schemaName, eventData ) {
	mw.eventLog.logEvent( schemaName, eventData );
};
