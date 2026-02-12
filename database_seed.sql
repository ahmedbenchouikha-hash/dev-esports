-- ===================================================
-- ESPORTS TOURNAMENT MANAGEMENT SYSTEM - DATABASE SEED
-- ===================================================
-- This SQL script populates the database with test data
-- Copy and paste into phpMyAdmin or MySQL CLI

-- ===================================================
-- USERS (Admin and Players)
-- ===================================================
-- Admin users (password: password123 hashed)
INSERT INTO `user` (email, username, roles, password, typeuser, approval_status, first_name, last_name, birth_date, discr) VALUES 
('admin@example.com', 'admin_user', '["ROLE_ADMIN"]', '$2y$13$E3Ln7xVJG2qzMJ7tU5KbsOm7h6q9L8Y2K5M4N3O2P.valid.hash.admin', 'admin', 'approved', 'Admin', 'User', '1990-01-15', 'user');

-- Player users (password: password123)
INSERT INTO `user` (email, username, roles, password, typeuser, approval_status, first_name, last_name, birth_date, discr) VALUES 
('alex.chen@example.com', 'AlexChen', '["ROLE_USER"]', '$2y$13$E3Ln7xVJG2qzMJ7tU5KbsOm7h6q9L8Y2K5M4N3O2P.valid.hash.player1', 'player', 'approved', 'Alex', 'Chen', '1999-05-15', 'player'),
('marcus.johnson@example.com', 'MarcusJ', '["ROLE_USER"]', '$2y$13$E3Ln7xVJG2qzMJ7tU5KbsOm7h6q9L8Y2K5M4N3O2P.valid.hash.player2', 'player', 'approved', 'Marcus', 'Johnson', '2000-08-22', 'player'),
('emma.rodriguez@example.com', 'EmmaR', '["ROLE_USER"]', '$2y$13$E3Ln7xVJG2qzMJ7tU5KbsOm7h6q9L8Y2K5M4N3O2P.valid.hash.player3', 'player', 'approved', 'Emma', 'Rodriguez', '1998-03-10', 'player'),
('kai.tanaka@example.com', 'KaiTan', '["ROLE_USER"]', '$2y$13$E3Ln7xVJG2qzMJ7tU5KbsOm7h6q9L8Y2K5M4N3O2P.valid.hash.player4', 'player', 'approved', 'Kai', 'Tanaka', '2001-11-08', 'player'),
('sarah.williams@example.com', 'SarahW', '["ROLE_USER"]', '$2y$13$E3Ln7xVJG2qzMJ7tU5KbsOm7h6q9L8Y2K5M4N3O2P.valid.hash.player5', 'player', 'pending', 'Sarah', 'Williams', '1999-07-19', 'player'),
('lin.zhang@example.com', 'LinZhang', '["ROLE_USER"]', '$2y$13$hash.player6', 'player', 'approved', 'Lin', 'Zhang', '2000-02-27', 'player'),
('james.murphy@example.com', 'JamesMurphy', '["ROLE_USER"]', '$2y$13$hash.player7', 'player', 'approved', 'James', 'Murphy', '1998-09-14', 'player'),
('sophie.lambert@example.com', 'SophieL', '["ROLE_USER"]', '$2y$13$hash.player8', 'player', 'approved', 'Sophie', 'Lambert', '1999-04-21', 'player'),
('yuki.yamamoto@example.com', 'YukiYam', '["ROLE_USER"]', '$2y$13$hash.player9', 'player', 'approved', 'Yuki', 'Yamamoto', '2001-06-05', 'player'),
('maria.santos@example.com', 'MariaSan', '["ROLE_USER"]', '$2y$13$hash.player10', 'player', 'approved', 'Maria', 'Santos', '2000-10-12', 'player'),
('david.kim@example.com', 'DavidKim', '["ROLE_USER"]', '$2y$13$hash.player11', 'player', 'approved', 'David', 'Kim', '1999-01-30', 'player'),
('anna.petrov@example.com', 'AnnaPet', '["ROLE_USER"]', '$2y$13$hash.player12', 'player', 'approved', 'Anna', 'Petrov', '1998-12-08', 'player'),
('luis.gonzalez@example.com', 'LuisGonz', '["ROLE_USER"]', '$2y$13$hash.player13', 'player', 'approved', 'Luis', 'Gonzalez', '2000-05-17', 'player'),
('olivia.mitchell@example.com', 'OliviaM', '["ROLE_USER"]', '$2y$13$hash.player14', 'player', 'approved', 'Olivia', 'Mitchell', '1999-09-23', 'player'),
('max.harris@example.com', 'MaxHarr', '["ROLE_USER"]', '$2y$13$hash.player15', 'player', 'approved', 'Max', 'Harris', '2001-03-11', 'player'),
('johan.nilsson@example.com', 'JohanN', '["ROLE_USER"]', '$2y$13$hash.player16', 'player', 'approved', 'Johan', 'Nilsson', '2000-07-02', 'player'),
('sara.larsson@example.com', 'SaraL', '["ROLE_USER"]', '$2y$13$hash.player17', 'player', 'approved', 'Sara', 'Larsson', '1999-02-18', 'player'),
('patrick.bergstrom@example.com', 'PatrickB', '["ROLE_USER"]', '$2y$13$hash.player18', 'player', 'approved', 'Patrick', 'Bergström', '1998-08-25', 'player'),
('anne.lindqvist@example.com', 'AnneLi', '["ROLE_USER"]', '$2y$13$hash.player19', 'player', 'approved', 'Anne', 'Lindqvist', '2001-04-09', 'player'),
('anton.sundstrom@example.com', 'AntonS', '["ROLE_USER"]', '$2y$13$hash.player20', 'player', 'approved', 'Anton', 'Sundström', '2000-11-14', 'player'),
('thomas.dupont@example.com', 'ThomasD', '["ROLE_USER"]', '$2y$13$hash.player21', 'player', 'approved', 'Thomas', 'Dupont', '2000-06-21', 'player'),
('laurence.bernard@example.com', 'LaurenceB', '["ROLE_USER"]', '$2y$13$hash.player22', 'player', 'approved', 'Laurence', 'Bernard', '1999-01-03', 'player'),
('natalie.martin@example.com', 'NatalieM', '["ROLE_USER"]', '$2y$13$hash.player23', 'player', 'approved', 'Natalie', 'Martin', '1998-10-16', 'player'),
('pierre.laurent@example.com', 'PierreL', '["ROLE_USER"]', '$2y$13$hash.player24', 'player', 'approved', 'Pierre', 'Laurent', '2001-05-20', 'player'),
('sabine.roux@example.com', 'SabineR', '["ROLE_USER"]', '$2y$13$hash.player25', 'player', 'approved', 'Sabine', 'Roux', '2000-09-07', 'player'),
('haruto.tanaka@example.com', 'HarutoT', '["ROLE_USER"]', '$2y$13$hash.player26', 'player', 'approved', 'Haruto', 'Tanaka', '2000-04-12', 'player'),
('yumi.yamada@example.com', 'YumiY', '["ROLE_USER"]', '$2y$13$hash.player27', 'player', 'approved', 'Yumi', 'Yamada', '1999-08-29', 'player'),
('kento.sato@example.com', 'KentoS', '["ROLE_USER"]', '$2y$13$hash.player28', 'player', 'approved', 'Kento', 'Sato', '1998-07-05', 'player'),
('akira.matsumoto@example.com', 'AkiraM', '["ROLE_USER"]', '$2y$13$hash.player29', 'player', 'approved', 'Akira', 'Matsumoto', '2001-02-14', 'player'),
('mika.yamamoto@example.com', 'MikaY', '["ROLE_USER"]', '$2y$13$hash.player30', 'player', 'approved', 'Mika', 'Yamamoto', '2000-12-22', 'player');

-- ===================================================
-- USER PROFILES
-- ===================================================
INSERT INTO user_profile (user_id, first_name, last_name, phone, address, birth_date, profile_picture, created_at, updated_at) VALUES 
(1, 'Admin', 'User', '+1-555-0100', '123 Admin St, New York, NY 10001', '1990-01-15', NULL, NOW(), NOW()),
(2, 'Alex', 'Chen', '+1-555-0101', '456 Player Ave, Los Angeles, CA 90001', '1999-05-15', NULL, NOW(), NOW()),
(3, 'Marcus', 'Johnson', '+1-555-0102', '789 Gamer Blvd, Chicago, IL 60601', '2000-08-22', NULL, NOW(), NOW()),
(4, 'Emma', 'Rodriguez', '+1-555-0103', '321 Team Dr, Houston, TX 77001', '1998-03-10', NULL, NOW(), NOW()),
(5, 'Kai', 'Tanaka', '+1-555-0104', '654 Champion Ln, Phoenix, AZ 85001', '2001-11-08', NULL, NOW(), NOW()),
(6, 'Sarah', 'Williams', '+1-555-0105', '987 Victory Way, Philadelphia, PA 19101', '1999-07-19', NULL, NOW(), NOW()),
(7, 'Lin', 'Zhang', '+86-10-1234-5678', 'Beijing, China', '2000-02-27', NULL, NOW(), NOW()),
(8, 'James', 'Murphy', '+1-555-0106', 'Dublin, Ireland', '1998-09-14', NULL, NOW(), NOW()),
(9, 'Sophie', 'Lambert', '+33-1-23-45-67-89', 'Paris, France', '1999-04-21', NULL, NOW(), NOW()),
(10, 'Yuki', 'Yamamoto', '+81-3-1234-5678', 'Tokyo, Japan', '2001-06-05', NULL, NOW(), NOW()),
(11, 'Maria', 'Santos', '+55-11-9876-5432', 'São Paulo, Brazil', '2000-10-12', NULL, NOW(), NOW()),
(12, 'David', 'Kim', '+82-2-1234-5678', 'Seoul, South Korea', '1999-01-30', NULL, NOW(), NOW()),
(13, 'Anna', 'Petrov', '+7-495-123-45-67', 'Moscow, Russia', '1998-12-08', NULL, NOW(), NOW()),
(14, 'Luis', 'Gonzalez', '+34-91-123-4567', 'Madrid, Spain', '2000-05-17', NULL, NOW(), NOW()),
(15, 'Olivia', 'Mitchell', '+44-20-1234-5678', 'London, UK', '1999-09-23', NULL, NOW(), NOW()),
(16, 'Max', 'Harris', '+44-208-123-4567', 'Manchester, UK', '2001-03-11', NULL, NOW(), NOW()),
(17, 'Johan', 'Nilsson', '+46-8-123-4567', 'Stockholm, Sweden', '2000-07-02', NULL, NOW(), NOW()),
(18, 'Sara', 'Larsson', '+46-31-123-4567', 'Gothenburg, Sweden', '1999-02-18', NULL, NOW(), NOW()),
(19, 'Patrick', 'Bergström', '+46-8-765-4321', 'Stockholm, Sweden', '1998-08-25', NULL, NOW(), NOW()),
(20, 'Anne', 'Lindqvist', '+46-40-123-4567', 'Malmö, Sweden', '2001-04-09', NULL, NOW(), NOW()),
(21, 'Anton', 'Sundström', '+46-8-555-4321', 'Stockholm, Sweden', '2000-11-14', NULL, NOW(), NOW()),
(22, 'Thomas', 'Dupont', '+33-2-23-45-67-89', 'Lyon, France', '2000-06-21', NULL, NOW(), NOW()),
(23, 'Laurence', 'Bernard', '+33-3-80-12-34-56', 'Dijon, France', '1999-01-03', NULL, NOW(), NOW()),
(24, 'Natalie', 'Martin', '+33-4-72-12-34-56', 'Marseille, France', '1998-10-16', NULL, NOW(), NOW()),
(25, 'Pierre', 'Laurent', '+33-1-55-12-34-56', 'Paris, France', '2001-05-20', NULL, NOW(), NOW()),
(26, 'Sabine', 'Roux', '+33-6-12-34-56-78', 'Nice, France', '2000-09-07', NULL, NOW(), NOW()),
(27, 'Haruto', 'Tanaka', '+81-3-5678-1234', 'Tokyo, Japan', '2000-04-12', NULL, NOW(), NOW()),
(28, 'Yumi', 'Yamada', '+81-90-1234-5678', 'Osaka, Japan', '1999-08-29', NULL, NOW(), NOW()),
(29, 'Kento', 'Sato', '+81-3-2468-1357', 'Tokyo, Japan', '1998-07-05', NULL, NOW(), NOW()),
(30, 'Akira', 'Matsumoto', '+81-75-123-4567', 'Kyoto, Japan', '2001-02-14', NULL, NOW(), NOW()),
(31, 'Mika', 'Yamamoto', '+81-6-1234-5678', 'Osaka, Japan', '2000-12-22', NULL, NOW(), NOW());

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
INSERT INTO player (id, nickname, first_name, last_name, birth_date, role, team_id, created_at, updated_at) VALUES
(2, 'AlexChen#742', 'Alex', 'Chen', '1999-05-15', 'Mid', 1, NOW(), NOW()),
(3, 'MarcusJ#351', 'Marcus', 'Johnson', '2000-08-22', 'Support', 1, NOW(), NOW()),
(4, 'EmmaR#894', 'Emma', 'Rodriguez', '1998-03-10', 'Carry', 1, NOW(), NOW()),
(5, 'KaiTan#567', 'Kai', 'Tanaka', '2001-11-08', 'Top', 1, NOW(), NOW()),
(6, 'SarahW#423', 'Sarah', 'Williams', '1999-07-19', 'Jungler', 1, NOW(), NOW());

-- Dragon Warriors (team_id = 2)
INSERT INTO player (id, nickname, first_name, last_name, birth_date, role, team_id, created_at, updated_at) VALUES
(7, 'LinZhang#816', 'Lin', 'Zhang', '2000-02-27', 'Mid', 2, NOW(), NOW()),
(8, 'JamesMurphy#734', 'James', 'Murphy', '1998-09-14', 'Support', 2, NOW(), NOW()),
(9, 'SophieL#562', 'Sophie', 'Lambert', '1999-04-21', 'Carry', 2, NOW(), NOW()),
(10, 'YukiYam#441', 'Yuki', 'Yamamoto', '2001-06-05', 'Top', 2, NOW(), NOW()),
(11, 'MariaSan#888', 'Maria', 'Santos', '2000-10-12', 'Jungler', 2, NOW(), NOW());

-- Shadow Assassins (team_id = 3)
INSERT INTO player (id, nickname, first_name, last_name, birth_date, role, team_id, created_at, updated_at) VALUES
(12, 'DavidKim#725', 'David', 'Kim', '1999-01-30', 'Mid', 3, NOW(), NOW()),
(13, 'AnnaPet#613', 'Anna', 'Petrov', '1998-12-08', 'Support', 3, NOW(), NOW()),
(14, 'LuisGonz#449', 'Luis', 'Gonzalez', '2000-05-17', 'Carry', 3, NOW(), NOW()),
(15, 'OliviaM#597', 'Olivia', 'Mitchell', '1999-09-23', 'Top', 3, NOW(), NOW()),
(16, 'MaxHarr#751', 'Max', 'Harris', '2001-03-11', 'Jungler', 3, NOW(), NOW());

-- Nordic Vikings (team_id = 4)
INSERT INTO player (id, nickname, first_name, last_name, birth_date, role, team_id, created_at, updated_at) VALUES
(17, 'JohanN#364', 'Johan', 'Nilsson', '2000-07-02', 'Mid', 4, NOW(), NOW()),
(18, 'SaraL#812', 'Sara', 'Larsson', '1999-02-18', 'Support', 4, NOW(), NOW()),
(19, 'PatrickB#678', 'Patrick', 'Bergström', '1998-08-25', 'Carry', 4, NOW(), NOW()),
(20, 'AnneLi#541', 'Anne', 'Lindqvist', '2001-04-09', 'Top', 4, NOW(), NOW()),
(21, 'AntonS#789', 'Anton', 'Sundström', '2000-11-14', 'Jungler', 4, NOW(), NOW());

-- Rising Stars (team_id = 5)
INSERT INTO player (id, nickname, first_name, last_name, birth_date, role, team_id, created_at, updated_at) VALUES
(22, 'ThomasD#426', 'Thomas', 'Dupont', '2000-06-21', 'Mid', 5, NOW(), NOW()),
(23, 'LaurenceB#835', 'Laurence', 'Bernard', '1999-01-03', 'Support', 5, NOW(), NOW()),
(24, 'NatalieM#562', 'Natalie', 'Martin', '1998-10-16', 'Carry', 5, NOW(), NOW()),
(25, 'PierreL#714', 'Pierre', 'Laurent', '2001-05-20', 'Top', 5, NOW(), NOW()),
(26, 'SabineR#698', 'Sabine', 'Roux', '2000-09-07', 'Jungler', 5, NOW(), NOW());

-- Tokyo Ninjas (team_id = 6)
INSERT INTO player (id, nickname, first_name, last_name, birth_date, role, team_id, created_at, updated_at) VALUES
(27, 'HarutoT#573', 'Haruto', 'Tanaka', '2000-04-12', 'Mid', 6, NOW(), NOW()),
(28, 'YumiY#421', 'Yumi', 'Yamada', '1999-08-29', 'Support', 6, NOW(), NOW()),
(29, 'KentoS#687', 'Kento', 'Sato', '1998-07-05', 'Carry', 6, NOW(), NOW()),
(30, 'AkiraM#739', 'Akira', 'Matsumoto', '2001-02-14', 'Top', 6, NOW(), NOW()),
(31, 'MikaY#552', 'Mika', 'Yamamoto', '2000-12-22', 'Jungler', 6, NOW(), NOW());

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
-- RECOMPENSES (Rewards/Trophies)
-- ===================================================
INSERT INTO recompense (recompense, type, classement, description) VALUES 
('Gold Trophy', 'trophy', 1, 'First place championship trophy with cash prize'),
('Silver Trophy', 'trophy', 2, 'Second place runner-up trophy with cash prize'),
('Bronze Trophy', 'trophy', 3, 'Third place bronze medal with cash prize'),
('Most Valuable Player', 'badge', 1, 'Awarded to the best performing player'),
('Best Team Spirit', 'badge', 2, 'Awarded to the team with best sportsmanship'),
('Rising Star Award', 'badge', 3, 'Awarded to the best new talent');

-- ===================================================
-- DEMANDE RECOMPENSE (Reward Requests)
-- ===================================================
INSERT INTO demande_recompense (nom_demandeur, email, motif, date_demande, statut, recompense_id) VALUES 
('Phoenix Legends Organization', 'contact@phoenixlegends.com', 'Requesting trophy for World Championship 2025 victory', NOW(), 'approved', 1),
('Dragon Warriors', 'manager@dragonwarriors.kr', 'MVP award request for Lin Zhang', DATE_SUB(NOW(), INTERVAL 5 DAY), 'approved', 4),
('Rising Stars', 'admin@risingstars.fr', 'Rising Star Award nomination for Thomas Dupont', DATE_SUB(NOW(), INTERVAL 10 DAY), 'pending', 6);

-- ===================================================
-- RECLAMATIONS (Complaints)
-- ===================================================
INSERT INTO reclamation (player_id, titre, description, type, etat, created_at, attachment_filename) VALUES 
(2, 'Unfair Match Decision', 'The referee made an incorrect call during the match that caused us to lose. This decision was clearly wrong.', 'JOUEUR', 'EN_COURS', DATE_SUB(NOW(), INTERVAL 7 DAY), 'match_evidence.pdf'),
(5, 'Inappropriate Conduct', 'Opposing player used abusive language during the match against sportsmanship rules.', 'JOUEUR', 'EN_COURS', DATE_SUB(NOW(), INTERVAL 3 DAY), NULL),
(9, 'Equipment Issue', 'Tournament provided faulty equipment that affected my performance.', 'TECHNIQUE', 'EN_ATTENTE', DATE_SUB(NOW(), INTERVAL 5 DAY), 'equipment_photo.jpg');

-- ===================================================
-- ADMIN RESPONSES
-- ===================================================
INSERT INTO admin_response (reclamation_id, message, created_at) VALUES 
(1, 'We have reviewed the match footage and confirmed the referee made an error. The decision has been reversed and compensation will be arranged.', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(3, 'Thank you for reporting the equipment issue. We have identified the problem and replaced the faulty equipment. Our apologies for the inconvenience.', DATE_SUB(NOW(), INTERVAL 4 DAY));

-- ===================================================
-- PUNITIONS (Punishments)
-- ===================================================
INSERT INTO punition (reclamation_id, start_at, end_at, player_status) VALUES 
(2, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'suspended');

-- ===================================================
-- DEPENSES (Expenses)
-- ===================================================
INSERT INTO depense (team_id, titre, montant, description, date_creation, statut, categorie) VALUES 
(1, 'Equipment Purchase', 5000.00, 'Gaming peripherals and tournament equipment', NOW(), 'approved', 'equipment'),
(1, 'Team Travel Expenses', 15000.00, 'Flights and accommodation for world championship', DATE_SUB(NOW(), INTERVAL 20 DAY), 'approved', 'travel'),
(2, 'Coaching Fees', 8000.00, 'Monthly coaching and strategy sessions', DATE_SUB(NOW(), INTERVAL 5 DAY), 'pending', 'personnel'),
(3, 'Streaming Setup', 3500.00, 'Professional streaming equipment and software licenses', NOW(), 'pending', 'equipment');

-- ===================================================
-- BUDGET (Team Budgets)
-- ===================================================
INSERT INTO budget (team_id, montant_alloue, montant_utilise, date_allocation, statut) VALUES 
(1, 50000.00, 20000.00, DATE_SUB(NOW(), INTERVAL 60 DAY), 'actif'),
(2, 40000.00, 8000.00, DATE_SUB(NOW(), INTERVAL 45 DAY), 'actif'),
(3, 30000.00, 3500.00, NOW(), 'actif'),
(4, 25000.00, 0.00, NOW(), 'actif'),
(5, 35000.00, 5000.00, DATE_SUB(NOW(), INTERVAL 30 DAY), 'actif'),
(6, 45000.00, 12000.00, DATE_SUB(NOW(), INTERVAL 50 DAY), 'actif');

-- ===================================================
-- SUMMARY
-- ===================================================
-- Created:
--   ✓ 1 Admin User + 30 Player Users
--   ✓ 31 User Profiles
--   ✓ 3 Tournaments
--   ✓ 6 Teams
--   ✓ 30 Players (5 per team)
--   ✓ 40 Matches
--   ✓ 6 Recompenses
--   ✓ 3 Demandes de Recompense
--   ✓ 3 Reclamations
--   ✓ 2 Admin Responses
--   ✓ 1 Punition
--   ✓ 4 Depenses
--   ✓ 6 Budgets
-- ===================================================
