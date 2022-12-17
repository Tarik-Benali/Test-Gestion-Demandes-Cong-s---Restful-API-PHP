<?php

	declare(strict_types=1);

	// autoloader des class
	spl_autoload_register(function ($class) {
		require __DIR__ . "/src/$class.php";
	});


	// Gestionnaire d'erreurs par défaut
	set_error_handler("ErrorHandler::handleError");

	// Gestionnaire d'exceptions par défaut
	set_exception_handler("ErrorHandler::handleException"); 


	// Indication du format JSON car il sera le format de toute nos requêtes
	header("Content-type: application/json; charset=UTF-8");


	//Récupérer et fragmenter l'url
	$parts = explode("/", $_SERVER["REQUEST_URI"]);


	// Appels des class et exécution du code selon le endpoint
	// Seulement 3 endpoint autorisé : employe, manager et admin 
	// chaqun des 3 profils mentionés possède sa propre class de fonction et constructeur
	switch ($parts[2]) {
		
		case 'admin':
				$id = $parts[3] ?? null; // $id est nullable car il n'est pas toujours présent dans les requêtes 

				$database = new Database(); // Base de données unique pour tous.

				$gateway = new AdminGateway($database);  // Fonctions dédier au profil Admin.

				$controller = new AdminController($gateway); // Controlleur dédier au profil Admin.

				$controller->processRequest($_SERVER["REQUEST_METHOD"], $id); 
			break;


		case 'employe':
				$id = $parts[3] ?? null;

				$database = new Database();

				$gateway = new EmployeGateway($database);

				$controller = new EmployeController($gateway);

				$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);
			break;

			
		case 'manager':
				$id = $parts[3] ?? null;

				$database = new Database();

				$gateway = new ManagerGateway($database);

				$controller = new ManagerController($gateway);

				$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);
			break;		
		

		default:
				http_response_code(404); // Retourner une erreur 404 si le endpoint est inconnu
			break;
	}
















