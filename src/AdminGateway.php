<?php

class AdminGateway
{
    private PDO $conn;
    
    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    
    // Function pour lister tout les utilisateurs du système
    public function getAllUsers(int $idManager): array | false
    {

        // Vérification si l'utilisateur est un administrateur
        $checkRole = $this->isAdmin($idManager);


        if ( ! $checkRole) {
            http_response_code(403);
            echo json_encode(["message" => "Accès refusé, seul privilège administrateur permis !"]);
            exit();
        }
        
        $managers = $this->getManagers();

        $employes = $this->getEmployes();

        $users = array_merge($managers, $employes);


        return $users; //List des utlisateurs (manager et employés)
    }



    // Function pour afficher un seul utilisateur du système
    public function getUser(array $data): array | false
    {
        $idAdmin = $data["id_admin"];
        
        // Vérification si l'utilisateur est un administrateur
        $checkRole = $this->isAdmin($idAdmin);

        if ( ! $checkRole) {
            http_response_code(403);
            echo json_encode(["message" => "Accès refusé, seul privilège administrateur permis !"]);
            exit();
        }

        $profil  = $data["profil"];
        $idUser  = $data["id_user"];


        // Afficher l'utilisateur selon son profil
        switch ($profil) {
            case 'employe':
                $user = $this->getEmployeByID($idUser);
                break;
            case 'manager':
                $user = $this->getManagerByID($idUser);
                break;
            case 'admin':
                $user = $this->getAdminByID($idUser);
                break;

            default:
                http_response_code(422);
                echo json_encode(["message" => "Profil inexistant, réessayer avec 'employe', 'manager' ou 'admin' !"]);
                break;
        }

        return $user;
    }


    // Function de vérification si user est un admin
    private function isAdmin(int $idManager) : bool
    {
        $sql = "SELECT `role`
                FROM manager
                WHERE id_manager = :idMan";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":idMan", $idManager, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (bool) $data["role"];
    }


    // Function pour lister tout les managers du système
    public function getManagers(): array
    {
        $sql = "SELECT *
                FROM manager";
                
        $stmt = $this->conn->query($sql);

        $nbrManagers = $stmt->rowCount();
        
        $data = $manags = [];

        $data["Profils"]            = "Manager, Employés";
        $data["Nombre Managers"]    = $nbrManagers;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
            $manager["ID"]          = $row["id_manager"];
            $manager["Nom"]         = $row["nom_manager"];
            $manager["Prénom"]      = $row["prenom_manager"];
            $manager["Téléphone"]   = $row["telephone"];
            $manager["Email"]       =  $row["email_manager"];

            $row["role"] == 0 ? $manager["Role"] = "Manager" : $manager["Role"] = "Administrateur";

            array_push($manags, $manager);            
        }

        $data["Managers"] = $manags;

        return $data;
    }


    // Function pour lister tout les employés du système
    public function getEmployes(): array
    {
        $sql = "SELECT *
                FROM employe";
                
        $stmt = $this->conn->query($sql);

        $nbrEmploye = $stmt->rowCount();
        
        $data = $employes = [];

        $data["Nombre Employés"]   = $nbrEmploye;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
            $emp["ID"]          = $row["id_employe"];
            $emp["Nom"]         = $row["nom_emp"];
            $emp["Prénom"]      = $row["prenom_emp"];
            $emp["Téléphone"]   = $row["telephone_emp"];
            $emp["Email"]       =  $row["email_emp"];
            $emp["Manager"]     =  $row["id_manager"];

            array_push($employes, $emp);            
        }

        $data["Employés"] = $employes;

        return $data;
    }


    // Affichage d'un seul employé
    public function getEmployeByID(int $id): array | false
    {
        $sql = "SELECT *
                FROM employe
                WHERE id_employe = :idEmpl";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":idEmpl", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data !== false) {

            $emp["ID"]          = $data["id_employe"];
            $emp["Nom"]         = $data["nom_emp"];
            $emp["Prénom"]      = $data["prenom_emp"];
            $emp["Téléphone"]   = $data["telephone_emp"];
            $emp["Email"]       = $data["email_emp"];
            $emp["Manager"]     = $data["id_manager"];
        }
        
        return $emp;
    }


    // Affichage d'un seul Manager
    public function getManagerByID(int $id): array | false
    {
        $sql = "SELECT *
                FROM manager
                WHERE id_manager = :idManager";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":idManager", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data !== false) {
            
            $manager["ID"]          = $data["id_manager"];
            $manager["Nom"]         = $data["nom_manager"];
            $manager["Prénom"]      = $data["prenom_manager"];
            $manager["Téléphone"]   = $data["telephone"];
            $manager["Email"]       = $data["email_manager"];

            $data["role"] == 0 ? $manager["Role"] = "Manager" : $manager["Role"] = "Administrateur";
        }
        
        return $manager;
    }


    // Affichage d'un seul administrateur
    public function getAdminByID(int $id): array | false
    {
        $sql = "SELECT *
                FROM manager
                WHERE id_manager = :idAdmin AND `role` = 1";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":idAdmin", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data !== false) {
            $admin["ID"]          = $data["id_manager"];
            $admin["Nom"]         = $data["nom_manager"];
            $admin["Prénom"]      = $data["prenom_manager"];
            $admin["Téléphone"]   = $data["telephone"];
            $admin["Email"]       = $data["email_manager"];
            $admin["Role"]        = "Administrateur";
        }
        
        return $admin;
    }


    
}











