<?php

namespace App\Exceptions;

use Exception;

class ImageProcessingException extends Exception
{
    public static function fileNotFound(): self
    {
        return new self('Arquivo de captura não encontrado.');
    }

    public static function invalidImageFile(): self
    {
        return new self('Arquivo de imagem inválido ou corrompido.');
    }

    public static function exceedsMaxFileSize(int $maxBytes): self
    {
        return new self(sprintf('A imagem processada excede o limite máximo permitido de %d bytes.', $maxBytes));
    }

    public static function processingFailed(string $reason): self
    {
        return new self('Falha no processamento da imagem: '.$reason);
    }
}
