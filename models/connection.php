<?php 

class Connection{

	static public function connect(){

		try{

			$link = new PDO("mysql:host=yamanote.proxy.rlwy.net;dbname=railway","root", "dkwmLeVkChfxLmfIYOzgcuEItsjRogdN");

			$link->exec("set names utf8");

		}catch(PDOException $e){

			die("Error: ".$e->getMessage());

		}

		return $link;
		
	}

}
