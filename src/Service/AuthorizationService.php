<?php

namespace App\Service;

use App\Entity\Team;
use App\Entity\Player;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AuthorizationService
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Check if current user (player/manager) has access to a team
     * Managers can only access their own teams
     */
    public function canAccessTeam(Team $team): bool
    {
        $user = $this->security->getUser();

        // Allow admins to access everything
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Only players/managers can access teams
        if (!$user instanceof Player) {
            return false;
        }

        // Check if player is part of the team
        return $team->getPlayers()->contains($user);
    }

    /**
     * Assert that current user has access to team, throw exception if not
     */
    public function ensureCanAccessTeam(Team $team): void
    {
        if (!$this->canAccessTeam($team)) {
            throw new AccessDeniedException("You don't have access to this team");
        }
    }

    /**
     * Check if current user can manage a team (is manager of that team)
     */
    public function canManageTeam(Team $team): bool
    {
        // Admins can manage everything
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $user = $this->security->getUser();

        // Only players with manager role can manage
        if (!$user instanceof Player) {
            return false;
        }

        // Check if player is part of team AND has ROLE_MANAGER
        if (!$team->getPlayers()->contains($user)) {
            return false;
        }

        return in_array('ROLE_MANAGER', $user->getRoles());
    }

    /**
     * Assert that current user can manage a team
     */
    public function ensureCanManageTeam(Team $team): void
    {
        if (!$this->canManageTeam($team)) {
            throw new AccessDeniedException("You don't have manager access to this team");
        }
    }

    /**
     * Get the current player if logged in
     */
    public function getCurrentPlayer(): ?Player
    {
        $user = $this->security->getUser();
        return ($user instanceof Player) ? $user : null;
    }
}
