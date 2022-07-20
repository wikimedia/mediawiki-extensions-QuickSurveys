// For a detailed explanation regarding each configuration property, visit:
// https://jestjs.io/docs/en/configuration.html

'use strict';

module.exports = {
	// Automatically clear mock calls and instances between every test
	clearMocks: true,

	// Indicates whether the coverage information should be collected while executing the test
	collectCoverage: true,

	// An array of glob patterns indicating a set of files for
	// which coverage information should be collected
	collectCoverageFrom: [
		'resources/ext.quicksurveys.lib/vue/*.(js|vue)'
	],

	// The directory where Jest should output its coverage files
	coverageDirectory: 'coverage',

	// An array of regexp pattern strings used to skip coverage collection
	coveragePathIgnorePatterns: [
		'/node_modules/'
	],

	// An object that configures minimum threshold enforcement for coverage results
	coverageThreshold: {
		global: {
			branches: 76,
			functions: 80,
			lines: 77,
			statements: 77
		}
	},

	// A set of global variables that need to be available in all test environments
	globals: {
		'vue-jest': {
			babelConfig: false,
			hideStyleWarn: true,
			experimentalCSSCompile: true
		}
	},

	// An array of file extensions your modules use
	moduleFileExtensions: [
		'js',
		'json',
		'vue'
	],

	// The paths to modules that run some code to configure or
	// set up the testing environment before each test
	setupFiles: [
		'./jest.setup.js'
	],

	testEnvironment: 'jsdom',

	transform: {
		'.*\\.(vue)$': '<rootDir>/node_modules/@vue/vue3-jest'
	}
};
