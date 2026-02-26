<?php
namespace App\DTO;

class NewResponseNotification
{
    public function __construct(
        public int $reclamationId,
        public string $reclamationTitle,
        public string $adminName
    ) {}
}
