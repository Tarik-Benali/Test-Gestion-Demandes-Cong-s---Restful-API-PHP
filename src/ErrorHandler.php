<?php

//Class pour gestion des erreurs et des exceptions
class ErrorHandler
{
    //Exceptions
    public static function handleException(Throwable $exception): void
    {
        http_response_code(500);
        
        echo json_encode([
            "code" => $exception->getCode(),
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine()
        ]);
    }
    
    // Erreurs
    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): bool
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}










