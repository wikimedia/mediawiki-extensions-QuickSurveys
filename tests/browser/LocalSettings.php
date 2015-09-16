<?php
$wgQuickSurveysConfig = array(
	array(
		"name" => "internal example survey",
		"type" => "internal",
		"question" => "ext-quicksurveys-example-internal-survey-question",
		"answers" => array(
			"ext-quicksurveys-example-internal-survey-answer-positive",
			"ext-quicksurveys-example-internal-survey-answer-neutral",
			"ext-quicksurveys-example-internal-survey-answer-negative",
		),
		"schema" => "QuickSurveysResponses",
		"enabled" => true,
		"coverage" => .5,
		"description" => "ext-quicksurveys-example-internal-survey-description",
		"platform" => array(
			"desktop" => array( "stable" ),
			"mobile" => array( "stable", "beta", "alpha" ),
		),
	),
	array(
		'name' => 'external example survey',
		'type' => 'external',
		"question" => "ext-quicksurveys-example-external-survey-question",
		"description" => "ext-quicksurveys-example-external-survey-description",
		"link" => "ext-quicksurveys-example-external-survey-link",
		"privacyPolicy" => "ext-quicksurveys-example-external-survey-privacy-policy",
		'coverage' => .5,
		'enabled' => true,
		'platform' => array(
			'desktop' => array( 'stable' ),
			'mobile' => array( 'stable', 'beta', 'alpha' ),
		),
	)
);
