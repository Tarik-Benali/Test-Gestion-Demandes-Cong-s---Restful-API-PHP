<?php

class ManagerController
{
    public function __construct(private ManagerGateway $gateway)
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
    
    private function processResourceRequest(string $method, string $idManager): void
    {
        // Appel à la fonction d'affichage de toute les demandes de congés des employés d'un manager spécifique 
        $emplyesDuManager = $this->gateway->getAllDemandesConges($idManager);
        
        if ( ! $emplyesDuManager) {
            http_response_code(404);
            echo json_encode(["message" => "Aucun employé affecté à ce manager !"]);
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

            // Acceptation ou Refus des demandes de congés
            case "PATCH":

                $data = (array) json_decode(file_get_contents("php://input"), true);
                

                // Validation des données reçu
                $errors = $this->getEmpValidationErrors($data);
                
                if ( ! empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }
                
                // Appel à la fonction du traitement des demandes de congés
                $row = $this->gateway->traiterDemandeConge($data);
                
                http_response_code(200);
                echo json_encode([
                    "message" => "Demande de congés " . $data["id_dc"] . " traité avec succès",
                    "Demande de congés" => $row
                ]);
                break;
            
            default:
                http_response_code(405);
                header("Allow: POST");
        }
    }
    

    // Validateur de données
    private function getEmpValidationErrors(array $data): array
    {
        $errors = [];
        
        // idii_manager doit être saisi, et ton type doit être entier
        if (array_key_exists("id_manager", $data)) {

            if (filter_var($data["id_manager"], FILTER_VALIDATE_INT) === false) {

                $errors[] = "id_manager doit être un entier";
            }
        }else{

            $errors[] = "id_manager est obligatoire";
        }


        // id_dc doit être saisi, et ton type doit être entier
        if (array_key_exists("id_dc", $data)) {

            if (filter_var($data["id_dc"], FILTER_VALIDATE_INT) === false) {

                $errors[] = "id_dc doit être un entier";
            }

        }else{

            $errors[] = "id_dc est obligatoire";
        }


        // statut doit être saisi, et ton type doit être entier compris entre 0 et 2
        if (array_key_exists("statut", $data)) {

            if (filter_var($data["statut"], FILTER_VALIDATE_INT) === false) {

                $errors[] = "statut doit être un entier";

            }else{
                // Expression réguliare indiquant un nombre entier entre 0 et 2
                if(!preg_match("/^[0-2]{1}$/", $data["statut"])){

                    $errors[] = "Valeur non accepté ! Les valuers possibles pour statut: 0 => Demande en cours de traitement, 1 => Demande Accepté ou 2 => Demande Refusé"; 
                }
                else{
                    // justificatif_refus doit être saisi si le statut = 2 'refus'
                    empty($data["justificatif_refus"]) ? $errors[] = "justificatif_refus est obligatoire pour un refus" : null;
                }
            }
        }else{
            $errors[] = "statut est obligatoire";
        }
        
        return $errors;
    }
}









