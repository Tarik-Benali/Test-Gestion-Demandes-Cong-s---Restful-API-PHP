<?php

class AdminController
{
    public function __construct(private AdminGateway $gateway)
    {
    }
    
    // Function pour distinguer les méthodes avec et sans id
    public function processRequest(string $method, ?string $id): void
    {
        if ($id) {
            
            $this->processResourceRequest($method, $id);
            
        } else {
            
            $this->processCollectionRequest($method);
            
        }
    }
    
    private function processResourceRequest(string $method, string $idManager): void
    {
        //Requête pou afficher tout les utilisateurs de la plateforme
        $emplyesDuManager = $this->gateway->getAllUsers($idManager);
        
        if ( ! $emplyesDuManager) {
            
            http_response_code(404);
            echo json_encode(["message" => "Aucun utilisateurs trouvé !"]);
            return;
        }
        
        switch ($method) {
            case "GET":
                echo json_encode($emplyesDuManager);
                break;
                
            default:
                http_response_code(405);
                header("Allow: GET");
        }
    }

    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            
            //Requête pou consulte un utilisateur de la plateforme
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                
                $errors = $this->getAdminValidationErrors($data);
                
                if ( ! empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }
                
                $id_user = $this->gateway->getUser($data);
                
                http_response_code(200);
                echo json_encode([$id_user]);
                break;
            
            default:
                http_response_code(405);
                header("Allow: POST");
        }
    }

    // Gestion du validateur des données saisi par l'utilisateur
    private function getAdminValidationErrors(array $data): array
    {
        $errors = [];

        // l'administrateur doit saisir son id en type Int 
        if (empty($data["id_admin"])) {

            $errors[] = "id_admin est obligatoire";
        }
        elseif (filter_var($data["id_admin"], FILTER_VALIDATE_INT) === false) {

            $errors[] = "id_admin doit être un entier";
        }


        // Donnée saisie doit être un profil valable: employe,manager, admin 
        if (empty($data["profil"])) {

            $errors[] = "profil est obligatoire";

        } elseif (!is_string($data["profil"])) {

            $errors[] = "profil doit être une chaine de caractère";
        }
        elseif($data["profil"] != "employe" && $data["profil"] != "manager" && $data["profil"] != "admin"){

            $errors[] = "Les profils supportés sont: 'employe', 'manager', 'admin'";
        }


        // Vérification du type et de l'existance de la variable id_user 
        if (empty($data["id_user"])) {

            $errors[] = "id_user est obligatoire";
        }
        else if (filter_var($data["id_user"], FILTER_VALIDATE_INT) === false) {

            $errors[] = "id_user doit être un entier";
        }
        
 

        return $errors;
    }
}









