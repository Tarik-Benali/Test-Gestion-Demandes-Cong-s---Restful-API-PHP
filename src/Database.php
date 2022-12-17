<?php

class Database
{
    //Paramètre de connexion
    public function __construct(private string $host = "localhost",
                                private string $name = "gestion_demandes_conges",
                                private string $user = "root",
                                private string $password = "")
    {}

                                
    public function getConnection(): PDO
    {
        $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";
        
        return new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ]);
    }
}









