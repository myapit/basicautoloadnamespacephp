<?php
namespace Libraries;

class Application {

	public $app_name;
	public $app_version;

	public function __construct($file=__DIR__ . "/../configuration.php") 
	{
		if (!$cfg = parse_ini_file($file, TRUE)) 
			throw new \exception('Unable to open '.$file.'.');
		try {
			$this->app_name= $cfg['application']['name'];
			$this->app_version= $cfg['application']['version'];
		}catch(\Exception $e){
            die($e->getMessage());
        }
	}
}
