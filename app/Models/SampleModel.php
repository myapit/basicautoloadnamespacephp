<?php
namespace Models;

class SampleModel {

	public function __construct()
	{
		echo("sample model constructor");
	}

	public function connect()
	{
		$con =  new \Libraries\Database();

		try {
			$con->insertSQL(
			"posts",
			array(
				"title"=>"testing tajuk".rand(10,99999), 
				"content"=>"kandungan apa-apa".rand(10,99999)
				)
			);
		} catch (\Exception $e) {
			die($e->getMessage());
		}
		

		echo "<pre>";
		print_r($con->runSQL("SELECT * from posts"));
		echo "</pre>";

		

		

			$testObj = (object) [
						"title"=>"testing tajuk", 
						"content"=>"kandungan apa-apa"
						];


		 print_r($testObj);

		 $t = new \stdClass;
		 $t->title = "testing";
		 $t->content = "kandungan";

		 print_r($t);
	}
}