<?php

namespace App\Service;

use App\Entity\Player;
use App\Repository\MatchStatisticRepository;

class PlayerScoreService
{
    public function __construct(
        private MatchStatisticRepository $matchStatisticRepository
    ) {}

    /**
     * Calculate total score for a player
     * Formula: (kills × 100) + (assists × 50) + (objectives × 75) + (damage/100) - (deaths × 80)
     */
    public function calculatePlayerScore(Player $player): float
    {
        $statistics = $this->matchStatisticRepository->findBy(['player' => $player]);
        
        if (empty($statistics)) {
            return 0;
        }

        $totalScore = 0;
        foreach ($statistics as $stat) {
            $matchScore = $this->calculateMatchScore($stat);
            $totalScore += $matchScore;
        }

        return round($totalScore, 2);
    }

    /**
     * Calculate score for a single match statistic
     */
    public function calculateMatchScore($matchStatistic): float
    {
        $score = 0;
        
        // Kills: 100 points each
        $score += ($matchStatistic->getKills() ?? 0) * 100;
        
        // Assists: 50 points each
        $score += ($matchStatistic->getAssists() ?? 0) * 50;
        
        // Objectives destroyed: 75 points each
        $score += ($matchStatistic->getObjectivesDestroyed() ?? 0) * 75;
        
        // Damage dealt: 1 point per 100 damage
        $score += ($matchStatistic->getDamageDealt() ?? 0) / 100;
        
        // Deaths: -80 points each
        $score -= ($matchStatistic->getDeaths() ?? 0) * 80;
        
        return max(0, round($score, 2)); // Ensure non-negative score
    }

    /**
     * Get detailed stats for a player
     */
    public function getPlayerStats(Player $player): array
    {
        $statistics = $this->matchStatisticRepository->findBy(['player' => $player]);
        
        $totalKills = 0;
        $totalDeaths = 0;
        $totalAssists = 0;
        $totalDamageDealt = 0;
        $totalObjectives = 0;
        $totalGoldEarned = 0;
        $matchCount = count($statistics);

        foreach ($statistics as $stat) {
            $totalKills += $stat->getKills() ?? 0;
            $totalDeaths += $stat->getDeaths() ?? 0;
            $totalAssists += $stat->getAssists() ?? 0;
            $totalDamageDealt += $stat->getDamageDealt() ?? 0;
            $totalObjectives += $stat->getObjectivesDestroyed() ?? 0;
            $totalGoldEarned += $stat->getGoldEarned() ?? 0;
        }

        $avgKDA = $matchCount > 0 ? round(($totalKills + $totalAssists) / max(1, $totalDeaths), 2) : 0;
        $avKillsPerMatch = $matchCount > 0 ? round($totalKills / $matchCount, 2) : 0;
        $avgDeathsPerMatch = $matchCount > 0 ? round($totalDeaths / $matchCount, 2) : 0;
        $avgAssistsPerMatch = $matchCount > 0 ? round($totalAssists / $matchCount, 2) : 0;
        $avgDamagePerMatch = $matchCount > 0 ? round($totalDamageDealt / $matchCount, 2) : 0;

        return [
            'totalScore' => $this->calculatePlayerScore($player),
            'matchCount' => $matchCount,
            'totalKills' => $totalKills,
            'totalDeaths' => $totalDeaths,
            'totalAssists' => $totalAssists,
            'totalDamageDealt' => round($totalDamageDealt, 2),
            'totalObjectives' => $totalObjectives,
            'totalGoldEarned' => round($totalGoldEarned, 2),
            'avgKDA' => $avgKDA,
            'avgKillsPerMatch' => $avKillsPerMatch,
            'avgDeathsPerMatch' => $avgDeathsPerMatch,
            'avgAssistsPerMatch' => $avgAssistsPerMatch,
            'avgDamagePerMatch' => $avgDamagePerMatch,
        ];
    }
}
