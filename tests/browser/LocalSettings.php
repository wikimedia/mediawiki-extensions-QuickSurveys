<?php
$wgQuickSurveysConfig = array(
	array(
		"name" => "internal example survey",
		"type" => "internal",
		"question" => "ext-quicksurveys-example-internal-survey-question",
		"answers" => array(
			"positive" => "ext-quicksurveys-example-internal-survey-answer-positive",
			"neutral" => "ext-quicksurveys-example-internal-survey-answer-neutral",
			"negative"=> "ext-quicksurveys-example-internal-survey-answer-negative"
		),
		"schema" => "QuickSurveysResponses",
		"enabled" => true,
		"coverage" => "100",
		"description" => "yo",
		"platform" => array(
			"desktop" => array( "stable" ),
			"mobile" => array( "stable", "beta", "alpha" ),
		),
	),
);
