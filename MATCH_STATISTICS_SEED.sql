-- ===================================================
-- MATCH STATISTICS DATA
-- Based on database_seed.sql
-- 6 Teams × 5 Players per Team = 30 Players
-- Statistics for finished and ongoing games
-- ===================================================

-- TOURNAMENT 2: Regional Finals 2025 - Game 9 (Phoenix Legends 2 vs Shadow Assassins 0) - FINISHED
INSERT INTO match_statistic (player_id, game_id, kills, deaths, assists, damage_dealt, damage_taken, objectives_destroyed, gold_earned, role, notes, created_at, updated_at) VALUES
(2, 9, 18, 2, 8, 3200.5, 800.2, 6, 14500, 'Mid', 'Dominant mid lane control', NOW(), NOW()),
(3, 9, 12, 3, 14, 2100.3, 950.5, 3, 11200, 'Support', 'Perfect teamfight engagement', NOW(), NOW()),
(4, 9, 15, 1, 10, 2800.2, 600.3, 8, 13200, 'Carry', 'Clean teamfight execution', NOW(), NOW()),
(5, 9, 10, 4, 12, 1950.1, 1200.2, 5, 10500, 'Top', 'Strong side pressure', NOW(), NOW()),
(6, 9, 14, 3, 11, 2250.5, 850.1, 7, 12100, 'Jungler', 'Excellent objective control', NOW(), NOW()),
(12, 9, 8, 8, 3, 1200.4, 2400.3, 1, 6800, 'Mid', 'Couldn\'t find openings', NOW(), NOW()),
(13, 9, 5, 9, 2, 900.2, 2650.1, 0, 5100, 'Support', 'Lacked communication', NOW(), NOW()),
(14, 9, 7, 7, 4, 1100.3, 2200.2, 2, 6200, 'Carry', 'Underfarmed and weak', NOW(), NOW()),
(15, 9, 6, 8, 3, 950.1, 2450.5, 1, 5500, 'Top', 'Got countered hard', NOW(), NOW()),
(16, 9, 4, 10, 1, 650.5, 2800.2, 0, 3900, 'Jungler', 'No presence in teamfights', NOW(), NOW());

-- TOURNAMENT 2: Regional Finals 2025 - Game 10 (Dragon Warriors 1 vs Rising Stars 2) - FINISHED
INSERT INTO match_statistic (player_id, game_id, kills, deaths, assists, damage_dealt, damage_taken, objectives_destroyed, gold_earned, role, notes, created_at, updated_at) VALUES
(7, 10, 14, 4, 9, 2850.2, 1100.3, 4, 12800, 'Mid', 'Good scaling into teamfights', NOW(), NOW()),
(8, 10, 10, 5, 11, 1800.5, 1350.2, 2, 9200, 'Support', 'Solid macro play', NOW(), NOW()),
(9, 10, 11, 6, 7, 1950.3, 1600.1, 3, 10100, 'Carry', 'Late game drop-off', NOW(), NOW()),
(10, 10, 8, 7, 8, 1550.2, 1950.5, 2, 8200, 'Top', 'Did not adapt well', NOW(), NOW()),
(11, 10, 9, 6, 10, 1700.1, 1450.3, 4, 8900, 'Jungler', 'Okay decision making', NOW(), NOW()),
(22, 10, 16, 3, 10, 2650.3, 900.2, 5, 13500, 'Mid', 'Outplayed laning phase', NOW(), NOW()),
(23, 10, 13, 4, 12, 2050.2, 1050.5, 3, 11800, 'Support', 'Great playmaking', NOW(), NOW()),
(24, 10, 17, 2, 11, 2950.1, 700.3, 7, 14200, 'Carry', 'MVP performance', NOW(), NOW()),
(25, 10, 11, 5, 13, 1800.5, 1400.2, 4, 10200, 'Top', 'Consistent damage output', NOW(), NOW()),
(26, 10, 15, 3, 14, 2350.4, 950.1, 6, 12600, 'Jungler', 'Controlled the map', NOW(), NOW());

-- TOURNAMENT 2: Regional Finals 2025 - Game 11 (Nordic Vikings 2 vs Tokyo Ninjas 1) - ONGOING
INSERT INTO match_statistic (player_id, game_id, kills, deaths, assists, damage_dealt, damage_taken, objectives_destroyed, gold_earned, role, notes, created_at, updated_at) VALUES
(17, 11, 12, 3, 9, 2300.2, 950.1, 4, 11200, 'Mid', 'Strong laning phase', NOW(), NOW()),
(18, 11, 10, 4, 11, 1750.3, 1150.2, 2, 9100, 'Support', 'Good warding placement', NOW(), NOW()),
(19, 11, 14, 2, 8, 2600.1, 750.5, 6, 12800, 'Carry', 'CS lead advantage', NOW(), NOW()),
(20, 11, 9, 5, 10, 1600.4, 1300.3, 3, 8500, 'Top', 'Trading well in lane', NOW(), NOW()),
(21, 11, 11, 3, 12, 1950.2, 1000.1, 5, 10200, 'Jungler', 'Frequent ganking', NOW(), NOW()),
(27, 11, 10, 5, 9, 2000.5, 1250.2, 3, 10100, 'Mid', 'Respectable play', NOW(), NOW()),
(28, 11, 8, 6, 10, 1500.3, 1450.1, 2, 8200, 'Support', 'Defensive posture', NOW(), NOW()),
(29, 11, 12, 4, 7, 2250.2, 1050.5, 5, 11500, 'Carry', 'Farming efficiently', NOW(), NOW()),
(30, 11, 7, 7, 8, 1350.1, 1600.3, 2, 7500, 'Top', 'Getting zoned out', NOW(), NOW()),
(31, 11, 9, 5, 11, 1700.4, 1200.2, 4, 9100, 'Jungler', 'Balanced approach', NOW(), NOW());

-- TOURNAMENT 2: Regional Finals 2025 - Game 13 (Shadow Assassins 1 vs Phoenix Legends 2) - FINISHED
INSERT INTO match_statistic (player_id, game_id, kills, deaths, assists, damage_dealt, damage_taken, objectives_destroyed, gold_earned, role, notes, created_at, updated_at) VALUES
(12, 13, 11, 5, 8, 2150.3, 1300.2, 3, 10800, 'Mid', 'Well-timed engages', NOW(), NOW()),
(13, 13, 9, 6, 10, 1650.2, 1550.1, 2, 8900, 'Support', 'Good follow-up', NOW(), NOW()),
(14, 13, 10, 7, 6, 1850.1, 1700.5, 3, 9200, 'Carry', 'Farming under pressure', NOW(), NOW()),
(15, 13, 7, 8, 7, 1400.3, 2000.2, 1, 7100, 'Top', 'Fell off late game', NOW(), NOW()),
(16, 13, 8, 9, 5, 1300.2, 2200.1, 2, 6800, 'Jungler', 'Minimal impact', NOW(), NOW()),
(2, 13, 16, 2, 11, 2750.5, 800.3, 5, 13200, 'Mid', 'Perfect rotation timing', NOW(), NOW()),
(3, 13, 13, 3, 13, 2200.2, 950.2, 3, 11100, 'Support', 'Dominant gaming', NOW(), NOW()),
(4, 13, 17, 1, 9, 3000.1, 600.5, 7, 14500, 'Carry', 'Clean as whistle', NOW(), NOW()),
(5, 13, 11, 4, 11, 1950.3, 1100.1, 4, 10700, 'Top', 'Strong split pushing', NOW(), NOW()),
(6, 13, 15, 3, 12, 2400.2, 900.3, 6, 12800, 'Jungler', 'Pressure everywhere', NOW(), NOW());

-- TOURNAMENT 2: Regional Finals 2025 - Game 14 (Phoenix Legends 2 vs Rising Stars 0) - FINISHED
INSERT INTO match_statistic (player_id, game_id, kills, deaths, assists, damage_dealt, damage_taken, objectives_destroyed, gold_earned, role, notes, created_at, updated_at) VALUES
(2, 14, 19, 1, 12, 3300.2, 700.1, 7, 14800, 'Mid', 'Absolute domination', NOW(), NOW()),
(3, 14, 14, 2, 14, 2250.3, 850.2, 4, 11800, 'Support', 'Perfect engages', NOW(), NOW()),
(4, 14, 18, 0, 11, 3100.1, 500.5, 8, 14200, 'Carry', 'Untouched performance', NOW(), NOW()),
(5, 14, 12, 3, 13, 2050.2, 950.3, 5, 11200, 'Top', 'Consistent presence', NOW(), NOW()),
(6, 14, 16, 2, 14, 2600.5, 750.1, 7, 12900, 'Jungler', 'Perfect coordination', NOW(), NOW()),
(22, 14, 5, 10, 2, 950.3, 2500.2, 0, 5200, 'Mid', 'Got swept', NOW(), NOW()),
(23, 14, 3, 11, 1, 600.2, 2800.1, 0, 3100, 'Support', 'Couldn\'t help', NOW(), NOW()),
(24, 14, 6, 9, 3, 1100.1, 2300.5, 1, 5800, 'Carry', 'No resources', NOW(), NOW()),
(25, 14, 4, 10, 2, 800.3, 2600.2, 0, 4100, 'Top', 'Heavy losses', NOW(), NOW()),
(26, 14, 5, 9, 1, 950.2, 2400.3, 0, 4900, 'Jungler', 'Lost map control', NOW(), NOW());

-- TOURNAMENT 2: Regional Finals 2025 - Game 15 & 16 - Previous matches (finished)
INSERT INTO match_statistic (player_id, game_id, kills, deaths, assists, damage_dealt, damage_taken, objectives_destroyed, gold_earned, role, notes, created_at, updated_at) VALUES
-- Game 15: Dragon Warriors vs Nordic Vikings 1-1 (FINISHED)
(7, 15, 13, 4, 10, 2400.2, 1100.1, 4, 11800, 'Mid', 'Even laning phase', NOW(), NOW()),
(8, 15, 10, 5, 12, 1850.3, 1350.2, 2, 9200, 'Support', 'Good teamwork', NOW(), NOW()),
(9, 15, 12, 6, 8, 2150.1, 1600.5, 3, 10200, 'Carry', 'Stable output', NOW(), NOW()),
(10, 15, 8, 7, 9, 1500.2, 1800.3, 2, 8100, 'Top', 'Teamfight engagement', NOW(), NOW()),
(11, 15, 10, 5, 11, 1850.3, 1250.2, 4, 9500, 'Jungler', 'Good map sense', NOW(), NOW()),
(17, 15, 12, 4, 11, 2250.5, 1050.1, 4, 11200, 'Mid', 'Strong rotations', NOW(), NOW()),
(18, 15, 11, 5, 10, 1950.2, 1200.3, 3, 10100, 'Support', 'Defensive wards', NOW(), NOW()),
(19, 15, 13, 3, 9, 2500.3, 900.2, 5, 12100, 'Carry', 'Fine execution', NOW(), NOW()),
(20, 15, 9, 6, 10, 1650.1, 1400.5, 3, 9200, 'Top', 'Solid teamplay', NOW(), NOW()),
(21, 15, 11, 4, 12, 2100.2, 1050.1, 4, 10800, 'Jungler', 'Good synergy', NOW(), NOW());

-- TOURNAMENT 3: Season 5 League - Game 16 (Shadow Assassins 3 vs Nordic Vikings 1) - FINISHED
INSERT INTO match_statistic (player_id, game_id, kills, deaths, assists, damage_dealt, damage_taken, objectives_destroyed, gold_earned, role, notes, created_at, updated_at) VALUES
(12, 16, 15, 3, 11, 2650.2, 950.1, 5, 12500, 'Mid', 'Lane dominance', NOW(), NOW()),
(13, 16, 12, 4, 13, 2100.3, 1100.2, 3, 10800, 'Support', 'Engaging presence', NOW(), NOW()),
(14, 16, 16, 2, 10, 2850.1, 750.5, 6, 13200, 'Carry', 'Powerful carry', NOW(), NOW()),
(15, 16, 11, 5, 12, 1950.2, 1250.3, 4, 11100, 'Top', 'Roaming impact', NOW(), NOW()),
(16, 16, 13, 3, 14, 2300.5, 900.1, 5, 12100, 'Jungler', 'Map control', NOW(), NOW()),
(17, 16, 9, 6, 8, 1650.3, 1600.2, 2, 8900, 'Mid', 'Fell flat early', NOW(), NOW()),
(18, 16, 7, 7, 6, 1350.2, 1850.1, 1, 7200, 'Support', 'Weak initiation', NOW(), NOW()),
(19, 16, 8, 8, 5, 1500.1, 2000.5, 2, 8100, 'Carry', 'Poor farming', NOW(), NOW()),
(20, 16, 6, 9, 4, 1100.3, 2150.2, 1, 6800, 'Top', 'Got outtraded', NOW(), NOW()),
(21, 16, 7, 8, 3, 1250.2, 1950.1, 1, 7500, 'Jungler', 'Limited ganks', NOW(), NOW());

-- TOURNAMENT 3: Season 5 League - Game 31 (Phoenix Legends 0 vs Dragon Warriors 0) - ONGOING
INSERT INTO match_statistic (player_id, game_id, kills, deaths, assists, damage_dealt, damage_taken, objectives_destroyed, gold_earned, role, notes, created_at, updated_at) VALUES
(2, 31, 10, 2, 7, 2200.5, 850.2, 3, 10800, 'Mid', 'Leading in CS', NOW(), NOW()),
(3, 31, 8, 3, 9, 1650.3, 1000.1, 2, 8200, 'Support', 'Setting up kills', NOW(), NOW()),
(4, 31, 12, 1, 8, 2400.2, 600.5, 5, 11200, 'Carry', 'Farming well', NOW(), NOW()),
(5, 31, 7, 4, 8, 1500.1, 1250.3, 2, 7800, 'Top', 'In lane still', NOW(), NOW()),
(6, 31, 9, 2, 10, 1850.3, 750.2, 4, 9100, 'Jungler', 'Securing kills', NOW(), NOW()),
(7, 31, 11, 3, 6, 2150.2, 900.1, 3, 10200, 'Mid', 'Matching pressure', NOW(), NOW()),
(8, 31, 9, 4, 8, 1750.3, 1100.2, 2, 8700, 'Support', 'Responding well', NOW(), NOW()),
(9, 31, 13, 2, 7, 2500.1, 700.5, 4, 12100, 'Carry', 'Even with opponent', NOW(), NOW()),
(10, 31, 8, 5, 7, 1450.2, 1400.3, 2, 8000, 'Top', 'Trading efficiently', NOW(), NOW()),
(11, 31, 10, 3, 9, 2000.5, 800.1, 3, 9500, 'Jungler', 'Contesting objectives', NOW(), NOW());
