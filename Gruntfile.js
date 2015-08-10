/*jshint node:true */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-jscs' );
	grunt.loadNpmTasks( 'grunt-banana-checker' );

	grunt.initConfig( {
		files: {
			js: 'resources/**/*.js',
			jsTests: 'tests/qunit/**/*.js'
		},
		jshint: {
			options: {
				jshintrc: true
			},
			all: [
				'<%= files.js %>',
				'<%= files.jsTests %>'
			]
		},
		jscs: {
			src: '<%= files.js %>'
		},
		banana: {
			all: 'i18n/'
		},
		jsonlint: {
			all: [
				'**/*.json',
				'!node_modules/**'
			]
		}
	} );

	grunt.registerTask( 'lint', [ 'jshint', 'jscs', 'jsonlint', 'banana' ] );
	grunt.registerTask( 'test', [ 'lint' ] );
	grunt.registerTask( 'default', 'test' );
};
