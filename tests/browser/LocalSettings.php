<?php
$wgQuickSurveysConfig = [
	[
		"name" => "drink-survey",
		"type" => "internal",
		"question" => "anne-survey-question",
		"answers" => [
			"anne-survey-answer-one",
			"anne-survey-answer-two",
			"anne-survey-answer-three",
			"anne-survey-answer-four"
		],
		"schema" => "QuickSurveysResponses",
		"enabled" => true,
		"coverage" => 0,
		"description" => "anne-survey-description",
		"platforms" => [
			"desktop" => [ "stable" ],
			"mobile" => [ "stable", "beta" ],
		],
	],
	[
		"name" => "internal example survey",
		"type" => "internal",
		"question" => "ext-quicksurveys-example-internal-survey-question",
		"answers" => [
			"ext-quicksurveys-example-internal-survey-answer-positive",
			"ext-quicksurveys-example-internal-survey-answer-neutral",
			"ext-quicksurveys-example-internal-survey-answer-negative",
		],
		"schema" => "QuickSurveysResponses",
		"enabled" => true,
		"coverage" => .5,
		"description" => "ext-quicksurveys-example-internal-survey-description",
		"platforms" => [
			"desktop" => [ "stable" ],
			"mobile" => [ "stable", "beta" ],
		],
	],
	[
		"name" => "external example survey",
		"type" => "external",
		"question" => "ext-quicksurveys-example-external-survey-question",
		"description" => "ext-quicksurveys-example-external-survey-description",
		"link" => "ext-quicksurveys-example-external-survey-link",
		"instanceTokenParameterName" => "parameterName",
		"privacyPolicy" => "ext-quicksurveys-example-external-survey-privacy-policy",
		"coverage" => .5,
		"enabled" => true,
		"platforms" => [
			"desktop" => [ "stable" ],
			"mobile" => [ "stable", "beta" ],
		],
	]
];
// Allow users to edit privacy link. Don't do this in production!
$wgGroupPermissions["user"]["editinterface"] = true;
