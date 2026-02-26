<?php

namespace App\Service;

use App\Entity\DemandeRecompense;

class WorkflowService
{
    /**
     * États possibles d'une demande
     */
    public const STATE_SUBMITTED = 'soumise';          // Nouvelle demande
    public const STATE_PRE_ANALYZED = 'pre_analysee';  // IA a analysé
    public const STATE_ADMIN_REVIEW = 'en_revision';   // Admin vérifie
    public const STATE_APPROVED = 'approuvée';         // Approuvée
    public const STATE_REJECTED = 'rejetée';           // Rejetée
    public const STATE_CANCELLED = 'annulee';          // Annulée par utilisateur

    /**
     * Transitions autorisées entre états
     */
    private array $transitions = [
        self::STATE_SUBMITTED => [self::STATE_PRE_ANALYZED, self::STATE_CANCELLED],
        self::STATE_PRE_ANALYZED => [self::STATE_ADMIN_REVIEW, self::STATE_APPROVED],
        self::STATE_ADMIN_REVIEW => [self::STATE_APPROVED, self::STATE_REJECTED],
        self::STATE_APPROVED => [],
        self::STATE_REJECTED => [],
        self::STATE_CANCELLED => [],
    ];

    /**
     * Vérifier si une transition est possible
     */
    public function canTransition(string $fromState, string $toState): bool
    {
        return isset($this->transitions[$fromState]) && 
               in_array($toState, $this->transitions[$fromState]);
    }

    /**
     * Obtenir les transitions possibles depuis un état
     */
    public function getPossibleTransitions(string $fromState): array
    {
        return $this->transitions[$fromState] ?? [];
    }

    /**
     * Transitionner une demande vers un nouvel état
     */
    public function transitionDemande(DemandeRecompense $demande, string $newState): void
    {
        $currentState = $demande->getStatut();
        
        if (!$this->canTransition($currentState, $newState)) {
            throw new \InvalidArgumentException(
                "Transition de '$currentState' à '$newState' n'est pas autorisée"
            );
        }

        // Logging de la transition
        error_log("🔄 WORKFLOW: $currentState → $newState (Demande #{$demande->getId()})");

        $demande->setStatut($newState);
    }

    /**
     * Obtenir le label humain d'un état
     */
    public function getStateLabel(string $state): string
    {
        return match($state) {
            self::STATE_SUBMITTED => '📝 Soumise',
            self::STATE_PRE_ANALYZED => '🧠 Pré-analysée',
            self::STATE_ADMIN_REVIEW => '👨‍💼 En révision',
            self::STATE_APPROVED => '✅ Approuvée',
            self::STATE_REJECTED => '❌ Rejetée',
            self::STATE_CANCELLED => '🚫 Annulée',
            default => $state,
        };
    }

    /**
     * Obtenir la couleur Bootstrap d'un état
     */
    public function getStateBadgeClass(string $state): string
    {
        return match($state) {
            self::STATE_SUBMITTED => 'badge-info',
            self::STATE_PRE_ANALYZED => 'badge-primary',
            self::STATE_ADMIN_REVIEW => 'badge-warning',
            self::STATE_APPROVED => 'badge-success',
            self::STATE_REJECTED => 'badge-danger',
            self::STATE_CANCELLED => 'badge-secondary',
            default => 'badge-light',
        };
    }

    /**
     * Vérifier si une demande peut être modifiée
     */
    public function canEditDemande(DemandeRecompense $demande): bool
    {
        // Seules les demandes soumises peuvent être modifiées
        return $demande->getStatut() === self::STATE_SUBMITTED;
    }

    /**
     * Vérifier si une demande peut avoir des preuves ajoutées
     */
    public function canAddProofs(DemandeRecompense $demande): bool
    {
        $editableStates = [
            self::STATE_SUBMITTED,
            self::STATE_PRE_ANALYZED,
            self::STATE_ADMIN_REVIEW,
        ];
        return in_array($demande->getStatut(), $editableStates);
    }
}
