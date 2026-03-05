<?php

namespace App\Tests;

use App\Entity\Game;
use App\Entity\Team;
use App\Service\MatchService;
use PHPUnit\Framework\TestCase;

class MatchServiceTest extends TestCase
{
    private MatchService $matchService;

    protected function setUp(): void
    {
        $this->matchService = new MatchService();
    }

    public function testCanScheduleMatchReturnsFalseForSameTeam(): void
    {
        $team = (new Team())->setName('Team Solo');

        $this->assertFalse($this->matchService->canScheduleMatch($team, $team));
    }

    public function testCanScheduleMatchReturnsTrueForDifferentTeams(): void
    {
        $team1 = (new Team())->setName('Team A');
        $team2 = (new Team())->setName('Team B');

        $this->assertTrue($this->matchService->canScheduleMatch($team1, $team2));
    }

    public function testIsDrawReturnsTrueWhenScoresAreEqual(): void
    {
        $game = $this->createGame(2, 2, 'finished');

        $this->assertTrue($this->matchService->isDraw($game));
    }

    public function testGetWinnerReturnsTeam1WhenTeam1HasHigherScore(): void
    {
        $game = $this->createGame(3, 1, 'finished');

        $this->assertSame($game->getTeam1(), $this->matchService->getWinner($game));
    }

    public function testGetWinnerReturnsNullWhenDraw(): void
    {
        $game = $this->createGame(1, 1, 'finished');

        $this->assertNull($this->matchService->getWinner($game));
    }

    public function testGetScoreDifferenceReturnsAbsoluteDifference(): void
    {
        $game = $this->createGame(1, 4, 'finished');

        $this->assertSame(3, $this->matchService->getScoreDifference($game));
    }

    public function testCanStartMatchReturnsTrueForPendingMatchAtOrAfterDate(): void
    {
        $game = $this->createGame(0, 0, 'pending', new \DateTime('-1 hour'));

        $this->assertTrue($this->matchService->canStartMatch($game, new \DateTime()));
    }

    public function testCanFinishMatchReturnsTrueOnlyForOngoingStatus(): void
    {
        $ongoingGame = $this->createGame(0, 0, 'ongoing');
        $pendingGame = $this->createGame(0, 0, 'pending');

        $this->assertTrue($this->matchService->canFinishMatch($ongoingGame));
        $this->assertFalse($this->matchService->canFinishMatch($pendingGame));
    }

    public function testCanTransitionStatusFromPendingToOngoing(): void
    {
        $this->assertTrue($this->matchService->canTransitionStatus('pending', 'ongoing'));
    }

    public function testCanTransitionStatusFromFinishedToOngoingIsFalse(): void
    {
        $this->assertFalse($this->matchService->canTransitionStatus('finished', 'ongoing'));
    }

    private function createGame(
        int $score1,
        int $score2,
        string $status,
        ?\DateTime $matchDate = null
    ): Game {
        $team1 = (new Team())->setName('Red Team');
        $team2 = (new Team())->setName('Blue Team');

        return (new Game())
            ->setTeam1($team1)
            ->setTeam2($team2)
            ->setScore1($score1)
            ->setScore2($score2)
            ->setStatus($status)
            ->setMatchdate($matchDate ?? new \DateTime('+1 hour'));
    }
}