'use strict';

const logger = require( '../../resources/ext.quicksurveys.lib/vue/QuickSurveyLogger.js' );

describe( 'QuickSurveyLogger', () => {

	beforeEach( () => {
		mw.config.get = jest.fn( ( key ) => {
			switch ( key ) {
				case 'wgArticleId':
					return 42;
				case 'wgPageName':
					return 'Hitchhikers';
				case 'wgUserEditCountBucket':
					return '0-50';
				case 'wgContentLanguage':
					return 'fr';
				case 'wgNamespaceNumber':
					return 0;
				default:
					return null;
			}
		} );
	} );

	it( 'logs information', () => {
		const log = logger.logResponseData(
			'foo',
			'Question',
			{
				Meaning: '42'
			},
			'sessionToken',
			'pageviewToken',
			false
		);
		const logSensitive = logger.logResponseData(
			'foo',
			'Question',
			{
				Meaning: '42'
			},
			'sessionToken',
			'pageviewToken',
			false,
			true
		);
		const expected = {
			countryCode: 'Unknown',
			editCountBucket: '0-50',
			isLoggedIn: true,
			isTablet: false,
			namespaceId: 0,
			pageviewToken: 'pageviewToken',
			platform: 'web',
			skin: null,
			surveyAnswers: [
				'Meaning'
			],
			surveyCodeName: 'foo',
			surveyQuestionLabel: 'Question',
			surveyResponseFreeText: {
				Meaning: '42'
			},
			surveyResponseValue: 'Meaning',
			surveySessionToken: 'sessionToken',
			userLanguage: 'fr'
		};
		expect( log ).toStrictEqual( expected );
		expect( logSensitive ).toStrictEqual(
			Object.assign( {}, expected, {
				pageId: 42,
				pageTitle: 'Hitchhikers'
			} )
		);
	} );
} );
