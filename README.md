QuickSurveys Extension
========================

The QuickSurveys extension displays and gathers data via EventLogging for configured surveys in both desktop and mobile

Installation
------------

Add the following to your LocalSettings.php file: wfLoadExtension( 'QuickSurveys' );

Example Survey Configuration
------------
```
<?php
/* Example QuickSurveys config */
$wgQuickSurveysConfig[] = array(
	// Survey name
	'name' => 'example',
	// Internal or external link survey
	'type' => 'internal',
	// Survey question message key
	'question' => 'ext-quicksurveys-example-question',
	// Possible answer message keys for positive, neutral, and negative
	'answers' => array(
		'positive' => 'ext-quicksurveys-example-answer-positive',
		'neutral' =>  'ext-quicksurveys-example-answer-neutral',
		'negative' => 'ext-quicksurveys-example-answer-negative',
	),
	// Which schema to log to
	'schema' => 'QuickSurveysResponses',
	// Percentage of users that will see the survey
	'coverage' => '50',
	// Is the survey enabled
	'enabled' => false,
	// For each platform (desktop, mobile), which version of it is targeted (stable, beta, alpha)
	'platform' => array(
		'desktop' => array( 'stable' ),
		'mobile' => array( 'stable', 'beta', 'alpha' ),
	),
);
```