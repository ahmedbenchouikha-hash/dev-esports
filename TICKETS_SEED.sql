-- Tickets table seed data
-- This assumes matches/games already exist in the database

-- Sample tickets for Match 1 (team1 vs team2)
INSERT INTO ticket (game_id, ticket_number, type, price, quantity, sold, status, created_at, updated_at) VALUES
(1, 'TKT-2026-001-REG', 'regular', 25.00, 500, 480, 'sold_out', NOW(), NOW()),
(1, 'TKT-2026-001-VIP', 'vip', 75.00, 100, 98, 'sold_out', NOW(), NOW()),
(1, 'TKT-2026-001-STU', 'student', 15.00, 200, 150, 'available', NOW(), NOW());

-- Sample tickets for Match 2 (team3 vs team4)
INSERT INTO ticket (game_id, ticket_number, type, price, quantity, sold, status, created_at, updated_at) VALUES
(2, 'TKT-2026-002-REG', 'regular', 30.00, 400, 320, 'available', NOW(), NOW()),
(2, 'TKT-2026-002-VIP', 'vip', 85.00, 80, 65, 'available', NOW(), NOW()),
(2, 'TKT-2026-002-STU', 'student', 18.00, 150, 120, 'available', NOW(), NOW());

-- Sample tickets for Match 3 (team1 vs team3)
INSERT INTO ticket (game_id, ticket_number, type, price, quantity, sold, status, created_at, updated_at) VALUES
(3, 'TKT-2026-003-REG', 'regular', 28.00, 450, 420, 'available', NOW(), NOW()),
(3, 'TKT-2026-003-VIP', 'vip', 80.00, 90, 85, 'available', NOW(), NOW()),
(3, 'TKT-2026-003-STU', 'student', 16.00, 180, 160, 'available', NOW(), NOW());

-- Sample tickets for Match 4 (team2 vs team4)
INSERT INTO ticket (game_id, ticket_number, type, price, quantity, sold, status, created_at, updated_at) VALUES
(4, 'TKT-2026-004-REG', 'regular', 32.00, 350, 200, 'available', NOW(), NOW()),
(4, 'TKT-2026-004-VIP', 'vip', 90.00, 75, 40, 'available', NOW(), NOW()),
(4, 'TKT-2026-004-STU', 'student', 20.00, 120, 60, 'available', NOW(), NOW());

-- Sample tickets for Match 5 (team5 vs team1)
INSERT INTO ticket (game_id, ticket_number, type, price, quantity, sold, status, created_at, updated_at) VALUES
(5, 'TKT-2026-005-REG', 'regular', 35.00, 500, 250, 'available', NOW(), NOW()),
(5, 'TKT-2026-005-VIP', 'vip', 95.00, 100, 50, 'available', NOW(), NOW()),
(5, 'TKT-2026-005-STU', 'student', 22.00, 200, 80, 'available', NOW(), NOW());

-- Sample tickets for Match 6 (team3 vs team5)
INSERT INTO ticket (game_id, ticket_number, type, price, quantity, sold, status, created_at, updated_at) VALUES
(6, 'TKT-2026-006-REG', 'regular', 40.00, 600, 595, 'sold_out', NOW(), NOW()),
(6, 'TKT-2026-006-VIP', 'vip', 100.00, 120, 119, 'sold_out', NOW(), NOW()),
(6, 'TKT-2026-006-STU', 'student', 25.00, 250, 240, 'available', NOW(), NOW());

-- Sample tickets for Match 7 (team2 vs team5)
INSERT INTO ticket (game_id, ticket_number, type, price, quantity, sold, status, created_at, updated_at) VALUES
(7, 'TKT-2026-007-REG', 'regular', 38.00, 550, 300, 'available', NOW(), NOW()),
(7, 'TKT-2026-007-VIP', 'vip', 110.00, 110, 60, 'available', NOW(), NOW()),
(7, 'TKT-2026-007-STU', 'student', 23.00, 220, 100, 'available', NOW(), NOW());

-- Sample tickets for Match 8 (team4 vs team3)
INSERT INTO ticket (game_id, ticket_number, type, price, quantity, sold, status, created_at, updated_at) VALUES
(8, 'TKT-2026-008-REG', 'regular', 30.00, 400, 0, 'available', NOW(), NOW()),
(8, 'TKT-2026-008-VIP', 'vip', 85.00, 80, 0, 'available', NOW(), NOW()),
(8, 'TKT-2026-008-STU', 'student', 18.00, 150, 0, 'available', NOW(), NOW());
