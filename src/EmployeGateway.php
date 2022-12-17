<?php

class EmployeGateway
{
    private PDO $conn;
    
    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    
    // Fonction de création de demandes de congés
    public function createDemandeConge(array $data): string
    {
        
        // Vérification des dates entrées
        $errorsDate = $this->verificationDatePasse($data["date_depart"], $data["date_retour"]);

        if ( ! empty($errorsDate)) {
            http_response_code(422);
            echo json_encode(["errors" => $errorsDate]);
            exit();
        }


        // Vérification des dates entrées
        $errorsDates = $this->verificationDateDepRetour($data["date_depart"], $data["date_retour"]);

        if ( ! empty($errorsDates)) {
            http_response_code(422);
            echo json_encode(["errors" => $errorsDates]);
            exit();
        }


        // Vérification de deux demandes de congés pour la même periode
        $periodeDejaEnregistree = $this->verificationCongesDatePeriode($data["idEmploye"], $data["date_depart"], $data["date_retour"]);

        if ( $periodeDejaEnregistree ) {
            http_response_code(422);
            echo json_encode(["errors" => "Vous ne pouvez pas placer deux demandes durant la même période !"]);
            exit();
        }


        // Blocage des demandes de congés pour 30 après refus de la dernière demande
        $permissionDC = $this->verificationDateFromLastRejection($data["idEmploye"]);

        if ( !$permissionDC ) {
            http_response_code(422);
            
            echo json_encode(["errors" => "Vous devez attendre 30 jours après le refus !"]);
            exit();
        }


        // Insertion de la demande de congés si seulement si toutes les conditions sont satisfaite
        $sql = "INSERT INTO demandes_conges (id_employe, date_depart, date_retour, commentaire)
                VALUES (:idEmp, :dateDep, :dateRet, :comm)";

        $stmt = $this->conn->prepare($sql);
        

        $stmt->bindValue(":idEmp", $data["idEmploye"], PDO::PARAM_INT);
        $stmt->bindValue(":dateDep",date("Y-m-d", strtotime($data["date_depart"])), PDO::PARAM_STR);
        $stmt->bindValue(":dateRet",date("Y-m-d", strtotime($data["date_retour"])), PDO::PARAM_STR);
        $stmt->bindValue(":comm", $data["commentaire"] ?? null, PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $this->conn->lastInsertId();
        
    }
    

    // Affichage de toutes les demandes de congés pour un employés
    public function getAllDemandesConges(int $idEmploye): array | false
    {
        $sql = "SELECT *
                FROM demandes_conges
                INNER JOIN employe ON demandes_conges.id_employe=employe.id_employe
                WHERE employe.id_employe = :id
                ORDER BY id_dc DESC";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":id", $idEmploye, PDO::PARAM_INT);
        
        $stmt->execute();

        $resultat = $demandesCoges = [];

        $i = 1;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
            $i == 1 ? ($resultat["Employé"] = $row["prenom_emp"] . ", " . $row["nom_emp"]) : null;

            $dc["id_dc"]       =  $row["id_dc"];
            $dc["date_depart"] =  $row["date_depart"];
            $dc["date_retour"] =  $row["date_retour"];
            $dc["commentaire"] =  $row["commentaire"];

            
            // Adapter l'affichage du justificatif selon le statut de la demande
            switch($row["statut"]){
                case 0:
                    $dc["statut"] = "En cours de traitement...";
                    array_key_exists("justificatif_refus", $dc) ? array_splice($dc, 5, null) : null;
                    break;
                
                case 1:
                    $dc["statut"] = "Demande de congé accepté";
                    array_key_exists("justificatif_refus", $dc) ? array_splice($dc, 5, null) : null;
                    break;

                case 2:
                    $dc["statut"] =  "Demande de congé refusé";
                    $dc["justificatif_refus"] =  $row["justificatif_refus"];
                    break;        
            }

            
            array_push($demandesCoges, $dc);

            $i++;
        }

        $resultat["Demandes de congés"] = $demandesCoges;
        
        return $resultat;
    }



    // Function de vérification des periodes de congés
    private function verificationCongesDatePeriode(int $idEmploye, String $dateDepart, String $dateRetour) : bool
    {
        $sql = "SELECT date_depart,date_retour
                FROM demandes_conges
                WHERE id_employe = :idEmpl";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":idEmpl", $idEmploye, PDO::PARAM_INT);
        
        $stmt->execute();

        $result = false;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $depCDate  = date('Y-m-d', strtotime($row["date_depart"]));
            $routCDate = date('Y-m-d', strtotime($row["date_retour"]));

            $dateDepartC = date('Y-m-d', strtotime($dateDepart));
            $dateRetourC = date('Y-m-d', strtotime($dateRetour));
                
            if (((($dateDepartC >= $depCDate) && ($dateDepartC <= $routCDate)) || (($dateRetourC >= $depCDate)  && ($dateRetourC <= $routCDate)))||
                ((($depCDate >= $dateDepartC) && ($depCDate <= $dateRetourC))  || (($routCDate >= $dateDepartC) && ($routCDate <= $dateRetourC)))){
                    $result = true;
            }
        }

        return $result;
    }



    // Function de comparaison de dates
    private function verificationDateDepRetour(String $dateDep, String $dateRet): array
    {
        $erreur = [];
    
        $depCDate  = new DateTime($dateDep);
        $routCDate = new DateTime($dateRet);

        $depCDate > $routCDate ? $erreur[] = "date retour doit être supérieur à la date de départ !" : null; 

        return $erreur;
    }

     // Function de vérification date passée
     private function verificationDatePasse(String $dateDep, String $dateRet): array
     {
         $erreur = [];
     
         $depCDate  = new DateTime($dateDep);
         $routCDate = new DateTime($dateRet);

         $today = new DateTime(date('Y-m-d'));
 
         ($depCDate < $today || $routCDate < $today) ? $erreur[] = "dates des demandes de congés doivent être supérieur à la date d'aujourd'hui'" : null; 
 
         return $erreur;
     }


    // Fonction de vérification de durée après la dernière demande refusé
    private function verificationDateFromLastRejection(int $idEmploye)
    {
        $sql = "SELECT date_traitement,statut
                FROM demandes_conges
                WHERE id_employe = :idEmpl
                ORDER BY id_dc DESC
                LIMIT 1";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":idEmpl", $idEmploye, PDO::PARAM_INT);
        
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            // Statut == 2 => Refus
            if($row["statut"] == 2)
            {
                // Calcule si la durée a dépassée 30 jours 
                $dateTraitement = new DateTime(date('Y-m-d', strtotime($row["date_traitement"])));
                $dateTraitement->add(new DateInterval('P30D'));
                $dateResault = $dateTraitement->format('Y-m-d');

                $today = date("Y-m-d");

                if($today > $dateResault){
                    $permission = 1; 
                }else{
                    $permission = 0;
                }                 
            }else{
                $permission = 1;
            }

            return $permission;
        }
    }

    
    
    
}











