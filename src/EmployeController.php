<?php

class EmployeController
{
    public function __construct(private EmployeGateway $gateway)
    {
    }
    

    public function processRequest(string $method, ?string $id): void
    {
        if ($id) {
            
            $this->processResourceRequest($method, $id);
            
        } else {
            
            $this->processCollectionRequest($method);
            
        }
    }
    
    private function processResourceRequest(string $method, string $idEmploye): void
    {
        // Appel de la function d'affichage des demandes de congés pour un employé
        $demandeConges = $this->gateway->getAllDemandesConges($idEmploye);
        
        if ( ! $demandeConges) {
            http_response_code(404);
            echo json_encode(["message" => "Aucune demande de congés trouvé pour cette employé !"]);
            return;
        }
        
        switch ($method) {
            case "GET":
                echo json_encode($demandeConges);
                break;
                
            default:
                http_response_code(405);
                header("Allow: GET");
        }
    }
    
    private function processCollectionRequest(string $method): void
    {
        switch ($method) {

            // Requête de création d'une nouvelle demande de congés 
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                
                //Validation des données
                $errors = $this->getEmpValidationErrors($data);
                
                if ( ! empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }
                
                // Appel de la fonction de création de la demande de congé
                $id = $this->gateway->createDemandeConge($data);
                
                http_response_code(201);
                echo json_encode([
                    "message" => "Demande de congés crée avec succès",
                    "id DC" => $id
                ]);
                break;
            
            default:
                http_response_code(405);
                header("Allow: POST");
        }
    }
    

    // Validateur des données entré par l'utilisateur
    private function getEmpValidationErrors(array $data): array
    {
        $errors = [];
        
        // idEmploye doit être entré et en type entier
        if (array_key_exists("idEmploye", $data)) {

            if (filter_var($data["idEmploye"], FILTER_VALIDATE_INT) === false) {

                $errors[] = "idEmploye doit être un entier";
            }
        }else{
            $errors[] = "idEmploye est obligatoire";
        }
        

        // date_depart doit être entré et en format de date valable
        if (array_key_exists("date_depart", $data)) {

            if ($this->verificationFormatDeDate($data["date_depart"])){

                $errors[] = "date_depart n'est pas sous format de date !";
            }
        }else{
            $errors[] = "date_depart est obligatoire";
        }


        // date_retour doit être entré et en format de date valable
        if (array_key_exists("date_retour", $data)) {

            if ($this->verificationFormatDeDate($data["date_retour"])){

                $errors[] = "date_retour n'est pas sous format de date !";
            }
        }else{
            $errors[] = "date_retour est obligatoire";
        }


        //Le commentaire n'est pas obligatoire lors des demandes de congés, si oui, on décommente la partie ci dessous
        /*if (!array_key_exists("commentaire", $data) || empty($data["commentaire"])) {
            $errors[] = "commentaire est obligatoire et il ne doit pas être vide !";
        }*/
        
        return $errors;
    }

    // Function de vérification du format de la date saisie (4 formats acceptable)
    private function verificationFormatDeDate(String $date): bool
    {
        if( !DateTime::createFromFormat('d/m/Y', $date) || !DateTime::createFromFormat('Y/m/d', $date) || 
            !DateTime::createFromFormat('d-m-Y', $date) || !DateTime::createFromFormat('Y-m-d', $date))
        {
            return false;
        }
        return true;
    }
}









