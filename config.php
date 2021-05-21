<?php

return array(
	'product'=>array(
		'name'=>'MyApp',
		'company'=>'MyCompany, Inc.',
		'privary_url'=>'#',
		'terms_url'=>'#',
	),
	'header'=>array(
		'app_logo'=>'https://i.imgur.com/Ubmu1J7.png',
		'appsumo_logo'=>'https://i.imgur.com/fhKS6eK.png',
		'title'=>'Hey Sumo-lings,',
		'description'=>'<p>Please fill in the form below to activate your AppSumo code for <b>lifetime access</b>.</p><p>After your submission, you will receive a confirmation email with license keys and then you\'re ready to start using the software!</p><p>Thank you!</p><p><b>The founder</b></p>',
	),
	'form'=>array(
	    'fields' => array(
	    	array(
	    		"name"=>"name", // this field is required, if removed the script will not process the request.
	    		"label"=>"Name:",
	    		"type"=>"text",
	    		"required"=>true,
	    	),
	    	array(
	    		"name"=>"email",  // this field is required, if removed the script will not process the request.
	    		"label"=>"AppSumo Email Address:",
	    		"type"=>"email",
	    		"required"=>true,
	    	),
	    	// array(
	    	// 	"name"=>"password", 
	    	// 	"label"=>"Password:",
	    	// 	"type"=>"password",
	    	// 	"required"=>false,
	    	// ),
	    	array(
	    		"name"=>"appsumo_code",  // this field is required, if removed the script will not process the request.
	    		"label"=>"AppSumo Code:",
	    		"type"=>"text",
	    		"required"=>true,
	    	),
	    	// More custom fields can be added
	    ),
	    'submit_btn_label' => 'YES ! LET ME IN !',
	),
    'email' =>array(
    	"notifications"=>array( // Support dynamic variables {f_FIELDNAME}
	    	"valid_code"=> array(
	    		"enabled"=>true,
				"subject"=>"AppSumo code {f_appsumo_code} activation",
				"messsage"=>"<p>Hi {f_name},</p><p>Your AppSumo code <b>{f_appsumo_code}</b> has been activated successfully, please check your inbox (and spam folder just in case) for the confimation email.</p><p>Thanks!</p><p>The founder</p>"
	    	),
	    	"invalid_code"=> array(
	    		"enabled"=>true,
				"subject"=>"{f_appsumo_code} is not valid",
				"messsage"=>"<p>Hi {f_name},</p><p>Sorry but your AppSumo code <b>{f_appsumo_code}</b> is not valid.</p><p>Thanks!</p><p>The founder</p>"
	    	),
    	),
    	"smtp"=>array(
    		"enabled"=>false,
    		"host"=>'smtp.gmail.com',
    		"port"=>587,
    		"username"=>"my.emailgmail.com",
    		"password"=>"mypassword",
    		"from_email"=>"my.emailgmail.com",
    		"from_name"=>"My name",
    	)
    ),
    'page_alerts' =>array(// Doesn't support dynamic variables
    	"success"=> array(
			"title"=>"AppSumo code activation",
			"messsage"=>"Your AppSumo code has been activated successfully, please check your inbox (and spam folder just in case) for the confimation email."
    	),
    	"error"=> array(
			"title"=>"Invalid request",
			"messsage"=>"An unexpected error has occurred. Please retry your request."
    	),
    ),
    'appsumo_codes' => array(// A new csv file with random codes will be created only if the file is not found
		"path"=>'../storage/appsumo_codes.csv', // Make sure that the chosen folder is outside the public folder
		"count"=>10000,
		"length"=>8,
    ),
    'appsumo_activations_path' => '../storage/appsumo_activations.csv', // Make sure that the chosen folder is outside the public folder
    'webhook' => array(
    	'enabled'=>true,
    	'method'=>"GET", // GET / POST
    	'url'=>"", // All fields will be sent as parameters to this url
    ),
    'limit_attempts' => array(
    	'tries'=> 3, // Enter 0 to disable verifications
    	'interval'=> 1, // Interval is in minutes
    ),
);