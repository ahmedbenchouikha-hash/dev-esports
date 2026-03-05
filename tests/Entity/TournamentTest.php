<?php

namespace App\Tests\Entity;

use App\Entity\Game;
use App\Entity\Tournament;
use PHPUnit\Framework\TestCase;

class TournamentTest extends TestCase
{
    private Tournament $tournament;

    protected function setUp(): void
    {
        $this->tournament = new Tournament();
    }

    public function testIdIsNullByDefault(): void
    {
        $this->assertNull($this->tournament->getId());
    }

    public function testSetAndGetName(): void
    {
        $this->tournament->setName('World Championship');
        $this->assertSame('World Championship', $this->tournament->getName());
    }

    public function testSetAndGetDescription(): void
    {
        $this->tournament->setDescription('Annual esports championship');
        $this->assertSame('Annual esports championship', $this->tournament->getDescription());
    }

    public function testDescriptionIsNullByDefault(): void
    {
        $this->assertNull($this->tournament->getDescription());
    }

    public function testSetAndGetStartDate(): void
    {
        $date = new \DateTime('2026-06-01');
        $this->tournament->setStartDate($date);
        $this->assertSame($date, $this->tournament->getStartDate());
    }

    public function testSetAndGetEndDate(): void
    {
        $date = new \DateTime('2026-06-15');
        $this->tournament->setEndDate($date);
        $this->assertSame($date, $this->tournament->getEndDate());
    }

    public function testDefaultStatusIsPending(): void
    {
        $this->assertSame('pending', $this->tournament->getStatus());
    }

    public function testSetAndGetStatus(): void
    {
        $this->tournament->setStatus('ongoing');
        $this->assertSame('ongoing', $this->tournament->getStatus());
    }

    public function testSetAndGetLocation(): void
    {
        $this->tournament->setLocation('Paris, France');
        $this->assertSame('Paris, France', $this->tournament->getLocation());
    }

    public function testSetAndGetPrizePool(): void
    {
        $this->tournament->setPrizePool(50000.00);
        $this->assertSame(50000.00, $this->tournament->getPrizePool());
    }

    public function testPrizePoolIsNullByDefault(): void
    {
        $this->assertNull($this->tournament->getPrizePool());
    }

    public function testSetAndGetRules(): void
    {
        $rules = ['No cheating', 'Best of 3'];
        $this->tournament->setRules($rules);
        $this->assertSame($rules, $this->tournament->getRules());
    }

    public function testAddRule(): void
    {
        $this->tournament->setRules([]);
        $this->tournament->addRule('No cheating');
        $this->tournament->addRule('Best of 3');
        $this->assertSame(['No cheating', 'Best of 3'], $this->tournament->getRules());
    }

    public function testAddRuleWhenRulesIsNull(): void
    {
        $this->tournament->addRule('First rule');
        $this->assertSame(['First rule'], $this->tournament->getRules());
    }

    public function testRemoveRule(): void
    {
        $this->tournament->setRules(['Rule A', 'Rule B', 'Rule C']);
        $this->tournament->removeRule('Rule B');
        $this->assertSame(['Rule A', 'Rule C'], $this->tournament->getRules());
    }

    public function testRemoveRuleReindexesArray(): void
    {
        $this->tournament->setRules(['Rule A', 'Rule B', 'Rule C']);
        $this->tournament->removeRule('Rule A');
        $rules = $this->tournament->getRules();
        $this->assertSame([0, 1], array_keys($rules));
        $this->assertSame('Rule B', $rules[0]);
    }

    public function testRemoveNonExistentRuleDoesNothing(): void
    {
        $this->tournament->setRules(['Rule A']);
        $this->tournament->removeRule('Non existent');
        $this->assertSame(['Rule A'], $this->tournament->getRules());
    }

    public function testSetCreatedAtValue(): void
    {
        $this->tournament->setCreatedAtValue();
        $this->assertInstanceOf(\DateTime::class, $this->tournament->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $this->tournament->getUpdatedAt());
    }

    public function testSetUpdatedAtValue(): void
    {
        $this->tournament->setCreatedAtValue();
        $originalUpdatedAt = $this->tournament->getUpdatedAt();

        // Ensure a tiny time difference
        usleep(1000);
        $this->tournament->setUpdatedAtValue();

        $this->assertGreaterThanOrEqual($originalUpdatedAt, $this->tournament->getUpdatedAt());
    }

    public function testGamesCollectionIsEmptyByDefault(): void
    {
        $this->assertCount(0, $this->tournament->getGames());
    }

    public function testAddGame(): void
    {
        $game = $this->createMock(Game::class);
        $game->expects($this->once())->method('setTournament')->with($this->tournament);

        $this->tournament->addGame($game);
        $this->assertCount(1, $this->tournament->getGames());
        $this->assertTrue($this->tournament->getGames()->contains($game));
    }

    public function testAddSameGameTwiceDoesNotDuplicate(): void
    {
        $game = $this->createMock(Game::class);
        $game->expects($this->once())->method('setTournament');

        $this->tournament->addGame($game);
        $this->tournament->addGame($game);
        $this->assertCount(1, $this->tournament->getGames());
    }

    public function testRemoveGame(): void
    {
        $game = $this->createMock(Game::class);
        $game->method('getTournament')->willReturn($this->tournament);
        $game->expects($this->exactly(2))
            ->method('setTournament')
            ->withConsecutive([$this->tournament], [null]);

        $this->tournament->addGame($game);
        $this->assertCount(1, $this->tournament->getGames());

        $this->tournament->removeGame($game);
        $this->assertCount(0, $this->tournament->getGames());
    }

    public function testToStringReturnsName(): void
    {
        $this->tournament->setName('League Finals');
        $this->assertSame('League Finals', (string) $this->tournament);
    }

    public function testToStringReturnsFallbackWhenNameIsNull(): void
    {
        $this->assertSame('Tournament', (string) $this->tournament);
    }

    public function testFluentInterface(): void
    {
        $result = $this->tournament
            ->setName('Test')
            ->setDescription('Desc')
            ->setLocation('Tunis')
            ->setStatus('ongoing')
            ->setPrizePool(1000.0)
            ->setStartDate(new \DateTime())
            ->setEndDate(new \DateTime('+1 day'));

        $this->assertSame($this->tournament, $result);
    }
}
