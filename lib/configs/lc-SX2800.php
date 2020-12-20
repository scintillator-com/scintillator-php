<?php
$this->isDeveloper = true;
$this->mongoDB = array(
	'database' => 'scintillator',
	'uri'      => 'mongodb://192.168.1.31:27017/scintillator'
);
$this->session = array(
	// 3600s = 1 hour
	'duration_default' =>  3600,
	//86400s = 1 day
	'duration_max'     => 86400,
	// 300s = 5 min
	'duration_short'   =>   300,
	// 300s = 5 min
	'sunset'           =>   300
);