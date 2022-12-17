<?php

class ManagerGateway
{
    private PDO $conn;
    
    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    
    // Fonction de traitement des demande de congés
    public function traiterDemandeConge(array $data): array
    {
        // Vérification si le l'utilisateur est le manager de l'employé saisi
        $hisAllowed = $this-> verificationSiSontEmploye($data["id_dc"],$data["id_manager"]);

        if ( ! $hisAllowed) {
            http_response_code(422);
            echo json_encode(["errors" => "Opération échoué, un manager ne peut modifier que ses employés !"]);
            exit();
        }

        // Acceptation ou mettre en attente
        if($data["statut"] == 1 || $data["statut"] == 0)
        {
            
            $sql = "UPDATE demandes_conges
                SET statut = :stat, date_traitement = :dateTrait, justificatif_refus = null
                WHERE id_dc = :idDC";
        

            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(":stat", $data["statut"], PDO::PARAM_INT);
            $stmt->bindValue(":dateTrait", date("Y-m-d H:i:s"), PDO::PARAM_STR);
            $stmt->bindValue(":idDC", $data["id_dc"], PDO::PARAM_STR);
            
            $stmt->execute();
        }
        
        // Traitement des refus des demandes
        elseif($data["statut"] == 2)
        {
            
            $sql = "UPDATE demandes_conges
                SET statut = :stat, date_traitement = :dateTrait, justificatif_refus = :justif
                WHERE id_dc = :idDC";
        

            $stmt = $this->conn->prepare($sql);
            

            $stmt->bindValue(":stat", $data["statut"], PDO::PARAM_INT);
            $stmt->bindValue(":dateTrait", date("Y-m-d H:i:s"), PDO::PARAM_STR);
            $stmt->bindValue(":justif", $data["justificatif_refus"], PDO::PARAM_STR);
            $stmt->bindValue(":idDC", $data["id_dc"], PDO::PARAM_STR);
            
            $stmt->execute();
        }
        else{
            http_response_code(422);
            exit();
        }

        return $this->getDemandesConges($data["id_dc"]); // retourné la demande de congés traitée.
        
    }
    
    // Affichage de toute les demandes de congés de employés d'un manager
    public function getAllDemandesConges(int $idManager): array | false
    {
        $sql = "SELECT *
                FROM demandes_conges
                INNER JOIN employe ON demandes_conges.id_employe=employe.id_employe
                WHERE employe.id_manager = :idManag
                ORDER BY employe.id_employe, id_dc DESC";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":idManag", $idManager, PDO::PARAM_INT);
        
        $stmt->execute();

        $resultat = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $dc["nom_employé"]      =  $row["nom_emp"];
            $dc["prénom_employé"]   =  $row["prenom_emp"];
            $dc["id_dc"]            =  $row["id_dc"];
            $dc["date_depart"]      =  $row["date_depart"];
            $dc["date_retour"]      =  $row["date_retour"];
            $dc["commentaire"]      =  $row["commentaire"];


            // Gestion d'affichage des justificatif selon le statut de la demande
            switch($row["statut"]){
                case 0:
                    $dc["statut"] = "En cours de traitement...";
                    array_key_exists("justificatif_refus", $dc) ? array_splice($dc, 7, null) : null;
                    break;
                
                case 1:
                    $dc["statut"] = "Demande de congé accepté";
                    array_key_exists("justificatif_refus", $dc) ? array_splice($dc, 7, null) : null;
                    break;

                case 2:
                    $dc["statut"] =  "Demande de congé refusé";
                    $dc["justificatif_refus"] =  $row["justificatif_refus"];
                    break;        
            }

            array_push($resultat, $dc);
        }
        
        return $resultat;
    }
    
    
    // affichage d'une demande avec son ID
    public function getDemandesConges(int $idDC): array | false
    {
        $sql = "SELECT *
                FROM demandes_conges
                INNER JOIN employe ON demandes_conges.id_employe=employe.id_employe
                WHERE demandes_conges.id_dc = :idDC";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":idDC", $idDC, PDO::PARAM_INT);
        
        $stmt->execute();

        $resultat = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $dc["nom_employé"]      =  $row["nom_emp"];
            $dc["prénom_employé"]   =  $row["prenom_emp"];
            $dc["id_dc"]            =  $row["id_dc"];
            $dc["date_depart"]      =  $row["date_depart"];
            $dc["date_retour"]      =  $row["date_retour"];
            $dc["commentaire"]      =  $row["commentaire"];

            switch($row["statut"]){
                case 0:
                    $dc["statut"] = "En cours de traitement...";
                    array_key_exists("justificatif_refus", $dc) ? array_splice($dc, 7, null) : null;
                    break;
                
                case 1:
                    $dc["statut"] = "Demande de congé accepté";
                    array_key_exists("justificatif_refus", $dc) ? array_splice($dc, 7, null) : null;
                    break;

                case 2:
                    $dc["statut"] =  "Demande de congé refusé";
                    $dc["justificatif_refus"] =  $row["justificatif_refus"];
                    break;        
            }

            array_push($resultat, $dc);
        }
        
        return $resultat;
    }


    // Vérification des prévilage du manager sur un employé saisi
    public function verificationSiSontEmploye(int $idDC,int $idManager)
    {
        $sql = "SELECT employe.id_manager as manager_id
                FROM demandes_conges
                INNER JOIN employe ON demandes_conges.id_employe=employe.id_employe
                WHERE demandes_conges.id_dc = :idDC";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":idDC", $idDC, PDO::PARAM_INT);
        
        $stmt->execute();


        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data !== false) {
            
            $managerID = $data["manager_id"];

            $managerID == $idManager ? $result = 1 : $result = 0;
            
            return $result;
        }
    }
    
}











