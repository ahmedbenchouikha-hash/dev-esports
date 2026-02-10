-- ===================================================
-- ESPORTS TOURNAMENT MANAGEMENT SYSTEM - DATABASE SEED
-- ===================================================
-- This SQL script populates the database with test data
-- Copy and paste into phpMyAdmin or MySQL CLI

-- ===================================================
-- TOURNAMENTS (3 records)
-- ===================================================
INSERT INTO tournament (name, description, start_date, end_date, location, prize_pool, status, created_at, updated_at) VALUES
('World Championship 2025', 'The biggest esports tournament of the year with teams from around the world competing for $1 million prize pool.', '2025-02-15', '2025-02-28', 'Dubai, UAE', 1000000, 'pending', NOW(), NOW()),
('Regional Finals 2025', 'Regional qualifiers for the world championship with 16 teams battling for spots.', '2025-02-01', '2025-02-10', 'Berlin, Germany', 250000, 'ongoing', NOW(), NOW()),
('Season 5 League', 'Regular season matches where teams earn points towards playoff qualification.', '2025-01-01', '2025-04-30', 'Online', 500000, 'ongoing', NOW(), NOW());

-- ===================================================
-- TEAMS (6 records)
-- ===================================================
INSERT INTO team (name, country, description, created_at, updated_at) VALUES
('Phoenix Legends', 'United States', 'One of the most dominant teams in esports history. Phoenix Legends has won multiple international tournaments and consistently ranks in the top 5 globally.', NOW(), NOW()),
('Dragon Warriors', 'South Korea', 'Korean esports powerhouse known for exceptional mechanical skill and strategic gameplay. Multiple world champions among their roster.', NOW(), NOW()),
('Shadow Assassins', 'United Kingdom', 'European team known for aggressive playstyle and creative strategies. Underdog story that made it to multiple finals.', NOW(), NOW()),
('Nordic Vikings', 'Sweden', 'Scandinavian representatives with a talented young roster. Rising stars in the competitive scene.', NOW(), NOW()),
('Rising Stars', 'France', 'Emerging team with promising young talent. Known for high-speed gameplay and innovative tactics.', NOW(), NOW()),
('Tokyo Ninjas', 'Japan', 'Japanese esports organization with excellent support staff and training facilities.', NOW(), NOW());

-- ===================================================
-- PLAYERS (30 records - 5 per team)
-- ===================================================
-- Phoenix Legends (team_id = 1)
INSERT INTO player (nickname, first_name, last_name, birth_date, role, team_id, created_at, updated_at) VALUES
('AlexChen#742', 'Alex', 'Chen', '1999-05-15', 'Mid', 1, NOW(), NOW()),
('MarcusJ#351', 'Marcus', 'Johnson', '2000-08-22', 'Support', 1, NOW(), NOW()),
('EmmaR#894', 'Emma', 'Rodriguez', '1998-03-10', 'Carry', 1, NOW(), NOW()),
('KaiTan#567', 'Kai', 'Tanaka', '2001-11-08', 'Top', 1, NOW(), NOW()),
('SarahW#423', 'Sarah', 'Williams', '1999-07-19', 'Jungler', 1, NOW(), NOW());

INSERT INTO player (nickname, first_name, last_name, birth_date, role, team_id, created_at, updated_at) VALUES
('LinZhang#816', 'Lin', 'Zhang', '2000-02-27', 'Mid', 2, NOW(), NOW()),
('JamesMurphy#734', 'James', 'Murphy', '1998-09-14', 'Support', 2, NOW(), NOW()),
('SophieL#562', 'Sophie', 'Lambert', '1999-04-21', 'Carry', 2, NOW(), NOW()),
('YukiYam#441', 'Yuki', 'Yamamoto', '2001-06-05', 'Top', 2, NOW(), NOW()),
('MariaSan#888', 'Maria', 'Santos', '2000-10-12', 'Jungler', 2, NOW(), NOW());

INSERT INTO player (nickname, first_name, last_name, birth_date, role, team_id, created_at, updated_at) VALUES
('DavidKim#725', 'David', 'Kim', '1999-01-30', 'Mid', 3, NOW(), NOW()),
('AnnaPet#613', 'Anna', 'Petrov', '1998-12-08', 'Support', 3, NOW(), NOW()),
('LuisGonz#449', 'Luis', 'Gonzalez', '2000-05-17', 'Carry', 3, NOW(), NOW()),
('OliviaM#597', 'Olivia', 'Mitchell', '1999-09-23', 'Top', 3, NOW(), NOW()),
('MaxHarr#751', 'Max', 'Harris', '2001-03-11', 'Jungler', 3, NOW(), NOW());

INSERT INTO player (nickname, first_name, last_name, birth_date, role, team_id, created_at, updated_at) VALUES
('JohanN#364', 'Johan', 'Nilsson', '2000-07-02', 'Mid', 4, NOW(), NOW()),
('SaraL#812', 'Sara', 'Larsson', '1999-02-18', 'Support', 4, NOW(), NOW()),
('PatrickB#678', 'Patrick', 'Bergström', '1998-08-25', 'Carry', 4, NOW(), NOW()),
('AnneLi#541', 'Anne', 'Lindqvist', '2001-04-09', 'Top', 4, NOW(), NOW()),
('AntonS#789', 'Anton', 'Sundström', '2000-11-14', 'Jungler', 4, NOW(), NOW());

INSERT INTO player (nickname, first_name, last_name, birth_date, role, team_id, created_at, updated_at) VALUES
('ThomasD#426', 'Thomas', 'Dupont', '2000-06-21', 'Mid', 5, NOW(), NOW()),
('LaurenceB#835', 'Laurence', 'Bernard', '1999-01-03', 'Support', 5, NOW(), NOW()),
('NatalieM#562', 'Natalie', 'Martin', '1998-10-16', 'Carry', 5, NOW(), NOW()),
('PierreL#714', 'Pierre', 'Laurent', '2001-05-20', 'Top', 5, NOW(), NOW()),
('SabineR#698', 'Sabine', 'Roux', '2000-09-07', 'Jungler', 5, NOW(), NOW());

INSERT INTO player (nickname, first_name, last_name, birth_date, role, team_id, created_at, updated_at) VALUES
('HarutoT#573', 'Haruto', 'Tanaka', '2000-04-12', 'Mid', 6, NOW(), NOW()),
('YumiY#421', 'Yumi', 'Yamada', '1999-08-29', 'Support', 6, NOW(), NOW()),
('KentoS#687', 'Kento', 'Sato', '1998-07-05', 'Carry', 6, NOW(), NOW()),
('AkiraM#739', 'Akira', 'Matsumoto', '2001-02-14', 'Top', 6, NOW(), NOW()),
('MikaY#552', 'Mika', 'Yamamoto', '2000-12-22', 'Jungler', 6, NOW(), NOW());

-- ===================================================
-- GAMES/MATCHES (40 records)
-- ===================================================

-- Tournament 1: World Championship 2025 (8 matches)
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (1, 2, 1, DATE_ADD(NOW(), INTERVAL 3 DAY), 2, 1, 'pending', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (3, 4, 1, DATE_ADD(NOW(), INTERVAL 5 DAY), 1, 2, 'pending', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (5, 6, 1, DATE_ADD(NOW(), INTERVAL 7 DAY), 0, 0, 'pending', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (2, 3, 1, DATE_ADD(NOW(), INTERVAL 10 DAY), 2, 2, 'pending', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (1, 5, 1, DATE_ADD(NOW(), INTERVAL 12 DAY), 1, 1, 'ongoing', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (4, 6, 1, DATE_ADD(NOW(), INTERVAL 14 DAY), 2, 0, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (2, 4, 1, DATE_ADD(NOW(), INTERVAL 16 DAY), 1, 2, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (3, 6, 1, DATE_ADD(NOW(), INTERVAL 18 DAY), 2, 1, 'finished', NOW(), NOW());

-- Tournament 2: Regional Finals 2025 (12 matches)
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (1, 3, 2, DATE_ADD(NOW(), INTERVAL 2 DAY), 2, 0, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (2, 5, 2, DATE_ADD(NOW(), INTERVAL 3 DAY), 1, 2, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (4, 6, 2, DATE_ADD(NOW(), INTERVAL 4 DAY), 2, 1, 'ongoing', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (1, 4, 2, DATE_ADD(NOW(), INTERVAL 5 DAY), 1, 1, 'pending', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (3, 5, 2, DATE_ADD(NOW(), INTERVAL 6 DAY), 2, 2, 'pending', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (2, 6, 2, DATE_ADD(NOW(), INTERVAL 7 DAY), 1, 0, 'pending', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (1, 6, 2, DATE_ADD(NOW(), INTERVAL 8 DAY), 2, 1, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (2, 4, 2, DATE_ADD(NOW(), INTERVAL 9 DAY), 0, 2, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (3, 1, 2, DATE_SUB(NOW(), INTERVAL 1 DAY), 1, 2, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (5, 4, 2, DATE_SUB(NOW(), INTERVAL 2 DAY), 2, 1, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (6, 3, 2, DATE_SUB(NOW(), INTERVAL 3 DAY), 1, 1, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (1, 5, 2, DATE_SUB(NOW(), INTERVAL 4 DAY), 2, 0, 'finished', NOW(), NOW());

-- Tournament 3: Season 5 League (20 matches)
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (1, 2, 3, DATE_SUB(NOW(), INTERVAL 30 DAY), 2, 1, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (3, 4, 3, DATE_SUB(NOW(), INTERVAL 28 DAY), 1, 3, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (5, 6, 3, DATE_SUB(NOW(), INTERVAL 26 DAY), 3, 2, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (2, 3, 3, DATE_SUB(NOW(), INTERVAL 24 DAY), 2, 2, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (1, 5, 3, DATE_SUB(NOW(), INTERVAL 22 DAY), 1, 2, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (4, 6, 3, DATE_SUB(NOW(), INTERVAL 20 DAY), 2, 0, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (2, 4, 3, DATE_SUB(NOW(), INTERVAL 18 DAY), 1, 2, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (3, 6, 3, DATE_SUB(NOW(), INTERVAL 16 DAY), 2, 1, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (1, 4, 3, DATE_SUB(NOW(), INTERVAL 14 DAY), 3, 2, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (5, 2, 3, DATE_SUB(NOW(), INTERVAL 12 DAY), 1, 1, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (3, 1, 3, DATE_SUB(NOW(), INTERVAL 10 DAY), 2, 2, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (6, 5, 3, DATE_SUB(NOW(), INTERVAL 8 DAY), 1, 3, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (2, 6, 3, DATE_SUB(NOW(), INTERVAL 6 DAY), 2, 1, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (1, 3, 3, DATE_SUB(NOW(), INTERVAL 4 DAY), 2, 0, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (4, 5, 3, DATE_SUB(NOW(), INTERVAL 2 DAY), 1, 2, 'finished', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (2, 1, 3, NOW(), 0, 0, 'ongoing', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (3, 5, 3, DATE_ADD(NOW(), INTERVAL 2 DAY), 0, 0, 'pending', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (6, 4, 3, DATE_ADD(NOW(), INTERVAL 4 DAY), 0, 0, 'pending', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (1, 6, 3, DATE_ADD(NOW(), INTERVAL 6 DAY), 0, 0, 'pending', NOW(), NOW());
INSERT INTO game (team1_id, team2_id, tournament_id, matchdate, score1, score2, status, created_at, updated_at) VALUES (2, 5, 3, DATE_ADD(NOW(), INTERVAL 8 DAY), 0, 0, 'pending', NOW(), NOW());

-- ===================================================
-- SUMMARY
-- ===================================================
-- Created:
--   ✓ 3 Tournaments
--   ✓ 6 Teams
--   ✓ 30 Players (5 per team)
--   ✓ 40 Matches
-- ===================================================
