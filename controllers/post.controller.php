<?php

use Firebase\JWT\JWT;

class PostController
{

	/*=============================================
	Peticion para tomar los nombres de las columnas
	=============================================*/

	public static function getColumnsData($table, $database)
	{
		try {
			// Obter colunas diretamente do banco
			$stmt = Connection::connect()->prepare("SHOW COLUMNS FROM `{$table}`");
			$stmt->execute();

			$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// Filtre colunas indesejadas (ajuste conforme necessário)
			$excludeColumns = ['created_at', 'updated_at', 'deleted_at'];
			$filteredColumns = array_filter($columns, function ($col) use ($excludeColumns) {
				return !in_array($col['Field'], $excludeColumns);
			});

			// Formate no padrão esperado pelo seu código
			$result = array_map(function ($col) {
				return (object)['item' => $col['Field']];
			}, $filteredColumns);

			return $result;
		} catch (PDOException $e) {
			error_log("Erro ao obter colunas: " . $e->getMessage());
			return [];
		}
	}

	/*=============================================
	Peticion POST para crear datos
	=============================================*/

	public function postData($table, $data)
	{

		$response = PostModel::postData($table, $data);

		$return = new PostController();
		$return->fncResponse($response, "postData", null);
	}

	/*=============================================
	Peticion POST para registrar usuario
	=============================================*/

	public function postRegister($table, $data)
	{

		if (isset($data["password_user"]) && $data["password_user"] != null) {

			$crypt = crypt($data["password_user"], '$2a$07$azybxcags23425sdg23sdfhsd$');

			$data["password_user"] = $crypt;

			$response = PostModel::postData($table, $data);

			$return = new PostController();
			$return->fncResponse($response, "postData", null);
		} else {

			$response = PostModel::postData($table, $data);

			if ($response == "The process was successful") {

				$user = GetModel::getFilterData($table, "email_user", $data["email_user"], null, null, null, null, "*");

				if (!empty($user)) {

					/*=============================================
					Creación de JWT
					=============================================*/

					$time = time();
					$key = "azscdvfbgnhmjkl1q2w3e4r5t6y7u8i9o";

					$token = array(

						"iat" => $time,  // Tiempo que inició el token
						"exp" => $time + (60 * 60 * 24), // Tiempo que expirará el token (+1 dia)
						'data' => [
							"id" =>  $user[0]->id_user,
							"email" =>  $user[0]->email_user
						]
					);

					$jwt = JWT::encode($token, $key);

					/*=============================================
					Actualizamos la base de datos con el Token del usuario
					=============================================*/

					$data = array(
						"token_user" => $jwt,
						"token_exp_user" => $token["exp"]
					);

					$update = PutModel::putData($table, $data, $user[0]->id_user, "id_user");

					if ($update == "The process was successful") {

						$return = new PostController();
						$return->fncResponse($response, "postData", null);
					}
				}
			}
		}
	}

	/*=============================================
	Peticion POST para el ingreso de usuario
	=============================================*/

	public function postLogin($table, $data)
	{

		$response = GetModel::getFilterData($table, "email_user", $data["email_user"], null, null, null, null, "*");

		if (!empty($response)) {

			/*=============================================
			Encriptamos la contraseña
			=============================================*/

			$crypt = crypt($data["password_user"], '$2a$07$azybxcags23425sdg23sdfhsd$');

			if ($response[0]->password_user == $crypt) {


				/*=============================================
				Creación de JWT
				=============================================*/

				$time = time();
				$key = "azscdvfbgnhmjkl1q2w3e4r5t6y7u8i9o";

				$token = array(

					"iat" => $time,  // Tiempo que inició el token
					"exp" => $time + (60 * 60 * 24), // Tiempo que expirará el token (+1 dia)
					'data' => [
						"id" =>  $response[0]->id_user,
						"email" =>  $response[0]->email_user
					]
				);

				$jwt = JWT::encode($token, $key);

				/*=============================================
				Actualizamos la base de datos con el Token del usuario
				=============================================*/

				$data = array(
					"token_user" => $jwt,
					"token_exp_user" => $token["exp"]
				);

				$update = PutModel::putData($table, $data, $response[0]->id_user, "id_user");

				if ($update == "The process was successful") {

					$response[0]->token_user = $jwt;
					$response[0]->token_exp_user = $token["exp"];

					$return = new PostController();
					$return->fncResponse($response, "postLogin",  null);
				}
			} else {

				$response = null;
				$return = new PostController();
				$return->fncResponse($response, "postLogin",  "Wrong password");
			}
		} else {

			$response = null;
			$return = new PostController();
			$return->fncResponse($response, "postLogin",  "Wrong email");
		}
	}

	/*=============================================
	Respuestas del controlador
	=============================================*/

	public function fncResponse($response, $method, $error)
	{

		if (!empty($response)) {

			/*=============================================
			Quitamos la contraseña de la respuesta
			=============================================*/

			if (isset($response[0]->password_user)) {

				unset($response[0]->password_user);
			}

			$json = array(
				'status' => 200,
				"results" => $response
			);
		} else {

			if ($error != null) {

				$json = array(
					'status' => 400,
					"results" => $error
				);
			} else {

				$json = array(
					'status' => 404,
					"results" => "Not Found",
					'method' => $method
				);
			}
		}

		echo json_encode($json, http_response_code($json["status"]));

		return;
	}
}
