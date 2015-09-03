// Register the Hogan compiler with MediaWiki.
( function () {
	var compiler;
	/*
	 * Mustache/Hogan template compiler
	 */
	try {
		compiler = mw.template.getCompiler( 'mustache' );
	} catch ( e ) {
		compiler = mw.template.getCompiler( 'hogan' );
	}

	// register hybrid compiler with core
	mw.template.registerCompiler( 'muhogan', compiler );
}() );
