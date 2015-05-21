<?php
	$output = null;

	/*
		arguments
		1 - project name/folder name
	*/

	$project_name = $argv[1];

	// Set path
	$WWWROOT_PATH = 'PATH_TO_PRODUCTION_FOLDER';

	// Set development path
	$DEV_PATH = 'PATH_TO_DEVELOPMENT_FOLDER';

	// Set log path
	$LOG_PATH = 'PATH_TO_LOG_FOLDER';

	// Check last commit
	//exec("git log --decorate", $output);
	exec('git log -1 --pretty=format:"%d"', $output);
	// $output = array('(develop)');

	// Get last committed branches
	$branches_str = preg_replace('/[()]/i', '', $output[0]);
	$branches = explode(', ', $branches_str);

	// Set repository
	// chdir('..');
	$repository = getcwd();

	$log_str = "=====================================================\n";
	$log_str .= "Repository:	".$repository."\n";
	// $log_str .= "-----------------------------------------------------\n";
	$log_str .= 'Received pushed commit at '.date('D, d M Y h:i:s O')."\n";

	foreach ($branches as $branch) {
		$branch = trim($branch);

		// Skip HEAD
		if ($branch == 'HEAD') continue;

		$log_str .= 'Branch '.$branch."\n";

		// Set target branch directory
		if ($branch == 'master')
			$directory = $WWWROOT_PATH.$project_name;
		else
			$directory = $DEV_PATH.$project_name.'\\'.$branch;

		// Check existence of directory
		if (!file_exists($directory)) {
			// Create directory
			mkdir($directory, 0777, true);
			$log_str .= 'Created directory '.$directory."\n";

			// Git Clone
			chdir($directory);
			exec('git clone -l -b '.$branch.' '.$repository.' .');
			$log_str .= 'Cloned repository '.$repository."\n";
		} else {
			// Pull request
			chdir($directory);
			exec('git pull --quiet origin '.$branch);
			$log_str .= 'Pulled repository '.$directory."\n";
		}
	}

	// Save Log
	$file = $LOG_PATH.'log.txt';
	$current = file_get_contents($file);
	$current .= $log_str;
	file_put_contents($file, $current);
?>