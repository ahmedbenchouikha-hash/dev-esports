<?php

namespace App\Tests;

use App\Entity\Team;
use App\Entity\User;
use App\Service\TeamService;
use PHPUnit\Framework\TestCase;

class TeamServiceTest extends TestCase
{
    private TeamService $teamService;

    protected function setUp(): void
    {
        $this->teamService = new TeamService();
    }

    public function testNormalizeTeamNameTrimsAndCollapsesSpaces(): void
    {
        $result = $this->teamService->normalizeTeamName('   Team    Alpha   Esports   ');

        $this->assertSame('Team Alpha Esports', $result);
    }

    public function testIsTeamNameValidReturnsFalseForTooShortName(): void
    {
        $this->assertFalse($this->teamService->isTeamNameValid('A'));
    }

    public function testIsTeamNameValidReturnsTrueForValidName(): void
    {
        $this->assertTrue($this->teamService->isTeamNameValid('Team Phoenix'));
    }

    public function testCanAddMemberWhenTeamIsNotFull(): void
    {
        $this->assertTrue($this->teamService->canAddMember(4, 5));
    }

    public function testCanAddMemberWhenTeamIsFull(): void
    {
        $this->assertFalse($this->teamService->canAddMember(5, 5));
    }

    public function testGetAvailableSlotsNeverNegative(): void
    {
        $this->assertSame(0, $this->teamService->getAvailableSlots(7, 5));
    }

    public function testCanManageTeamWhenUserIsCreator(): void
    {
        $creator = (new User())
            ->setEmail('creator@test.com')
            ->setUsername('creator')
            ->setPassword('secret');

        $team = (new Team())->setCreator($creator);

        $this->assertTrue($this->teamService->canManageTeam($creator, $team));
    }

    public function testCanManageTeamWhenUserIsAdmin(): void
    {
        $admin = (new User())
            ->setEmail('admin@test.com')
            ->setUsername('admin')
            ->setPassword('secret')
            ->setRoles(['ROLE_ADMIN']);

        $team = new Team();

        $this->assertTrue($this->teamService->canManageTeam($admin, $team));
    }

    public function testCanTransitionStatusFromPendingToApproved(): void
    {
        $this->assertTrue($this->teamService->canTransitionStatus('en attente', 'approuvé'));
    }

    public function testCannotTransitionStatusFromApprovedToPending(): void
    {
        $this->assertFalse($this->teamService->canTransitionStatus('approuvé', 'en attente'));
    }
}