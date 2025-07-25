<?php 

class Connection{

	static public function connect(){

		try{

			$link = new PDO("mysql:host=sql213.infinityfree.com;dbname=if0_39110490_bluehouse","if0_39110490", "Umdois3quatro");

			$link->exec("set names utf8");

		}catch(PDOException $e){

			die("Error: ".$e->getMessage());

		}

		return $link;
		
	}

}

