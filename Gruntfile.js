/*jshint node:true */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-jscs' );
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		files: {
			js: 'resources/**/*.js'
		},
		jshint: {
			options: {
				jshintrc: true
			},
			all: [
				'.',
				'!node_modules/**'
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
				'.stylelintrc',
				'!node_modules/**'
			]
		},
		stylelint: {
			all: [
				'**/*.less',
				'!node_modules/**'
			]
		}
	} );

	grunt.registerTask( 'lint', [ 'jshint', 'jscs', 'jsonlint', 'banana', 'stylelint' ] );
	grunt.registerTask( 'test', [ 'lint' ] );
	grunt.registerTask( 'default', 'test' );
};
