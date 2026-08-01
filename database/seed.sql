-- =============================================================
-- ClubHub v1.0.0 - Seed (donnees de demonstration)
-- 3 comptes demo + 12 clubs + 11 events + inscriptions
-- =============================================================

-- USERS
-- Mot de passe "demo1234" pour les 3 comptes demo
-- Hash genere avec PHP password_hash('demo1234', PASSWORD_BCRYPT)
INSERT INTO `users` (`matricule`, `email`, `password_hash`, `name`, `avatar`, `role`, `filiere`, `niveau`, `managed_club_id`) VALUES
('MA', 'mika@u.fr',  '$2y$10$YgpHkb2ZJgU1ZqL9qXkA4.V.1bOQrZ4M7Lq3R6w9.7n4J8n2mVxqC', 'Mika ANDRIAMATOA',     'MA', 'student', 'L1 Info',  'L1',  NULL),
('SA', 'sarah@u.fr', '$2y$10$YgpHkb2ZJgU1ZqL9qXkA4.V.1bOQrZ4M7Lq3R6w9.7n4J8n2mVxqC', 'Sarah ANDRIANARIVO',  'SA', 'manager', 'L3 Info',  'L3',  2),
('HR', 'hery@u.fr',  '$2y$10$YgpHkb2ZJgU1ZqL9qXkA4.V.1bOQrZ4M7Lq3R6w9.7n4J8n2mVxqC', 'Hery RAKOTONIAINA',    'HR', 'admin',   'Admin',    '-',   NULL);

-- CLUBS
INSERT INTO `clubs` (`id`, `slug`, `name`, `category`, `color`, `short_desc`, `long_desc`, `tags`, `room`, `president`, `capacity`) VALUES
(1,  'club-001', 'Club Informatique',                  'tech',            '#ff4502', 'Promouvoir les competences numeriques et les projets etudiants.', 'Ateliers Python, IA, web. Hackathons mensuels.', JSON_ARRAY('Python','IA'),        'FabLab',  'Sarah ANDRIANARIVO', 87),
(2,  'club-002', 'Club Debat & Eloquence',             'debats',          '#dcb8ff', 'Affuter votre esprit critique et votre prise de parole.',         'Debats hebdomadaire, formations rhetorique.',     JSON_ARRAY('Eloquence','Argumentation'), 'Amphi B','Sarah ANDRIANARIVO', 54),
(3,  'club-003', 'Club Arts Martiaux',                 'sport',           '#c2ff01', 'Karate, judo, taekwondo. Entrainements reguliers.',              'Cours 3x/semaine, competitions regionales.',        JSON_ARRAY('Karate','Judo'),          'Dojo',    'Toky RASOANAIVO',   42),
(4,  'club-004', 'Club Environnement & Ecologie',       'environnement',   '#10ffa1', 'Sensibilisation au developpement durable.',                       'Nettoyages campus, conferences climat.',            JSON_ARRAY('Ecologie','Durabilite'),  'Salle C-204','Antsa RAZAFY',     68),
(5,  'club-005', 'Club Robotique',                     'tech',            '#ff4502', 'Conception et programmation de robots.',                          'Arduino, ROS, projets de groupe.',                 JSON_ARRAY('Robotique','Arduino'),    'FabLab',  'Naina ANDRIA',      36),
(6,  'club-006', 'Club Theatre & Impro',                'culture',         '#dcb8ff', 'Expression scenique et improvisation.',                           'Ateliers impro, soirees Freestyle.',               JSON_ARRAY('Theatre','Improvisation'), 'Theatre', 'Voahangy RALA',    29),
(7,  'club-007', 'Club Entrepreneuriat & Innovation',  'entrepreneuriat', '#0044ff', 'Accompagnement des projets entrepreneuriaux.',                     'Pitch nights, mentoring, business plans.',         JSON_ARRAY('Startup','Pitch'),       'Maison entrepreneur','Fanja RAKOTO', 73),
(8,  'club-008', 'Club Photo & Video',                  'culture',         '#dcb8ff', 'Techniques de prise de vue, post-production.',                     'Sorties photo, montage video, expositions.',        JSON_ARRAY('Photographie','Video'),   'Studio',  'Tiana RABE',        48),
(9,  'club-009', 'Club Musique & Chorale',              'culture',         '#dcb8ff', 'Pratique musicale collective.',                                   'Chorale, ateliers vocaux, concerts.',              JSON_ARRAY('Musique','Chorale'),     'Cour du chateau','Lova ANDRY',     35),
(10, 'club-010', 'Club Foot & Basketball',              'sport',           '#c2ff01', 'Entrainements et tournois.',                                      'Matchs hebdo, tournois inter-universitaires.',     JSON_ARRAY('Football','Basketball'),  'Terrain synthetique','Mamy RAJAO',92),
(11, 'club-011', 'Club Benevolat & Solidarite',         'benevolat',       '#0044ff', 'Actions solidaires et soutien scolaire.',                          'Collectes, tutorat, missions terrain.',            JSON_ARRAY('Solidarite','Benevolat'), 'Salle C-204','Mihary RANAIVO', 64),
(12, 'club-012', 'Club Langues & Cultures',             'langues',         '#ff4502', 'Ateliers de conversation et echanges culturels.',                 'Anglais, espagnol, japonais, chinois.',            JSON_ARRAY('Anglais','Espagnol'),    'Salle internationale','Niry RAZANA',81);

-- EVENTS
INSERT INTO `events` (`id`, `club_id`, `title`, `description`, `type`, `category`, `date`, `time`, `duration_min`, `room`, `capacity`, `registered`) VALUES
(1,  1, 'Hackathon IA & Education',          '48h pour concevoir des solutions IA pour l''education.', 'Hackathon',  'CI', '2026-07-28', '09:00:00', 720, 'FabLab',                  80,  56),
(2,  2, 'Atelier Python pour debutants',     'Decouverte de Python en 2h, exercices pratiques.',      'Atelier',    'CE', '2026-07-22', '18:00:00', 120, 'Salle C-204',             30,  24),
(3,  1, 'Conference : LLM en 2026',          'Panorama des modeles de langage et applications.',       'Conference', 'CI', '2026-08-12', '14:00:00', 90,  'Amphi A',                 150, 42),
(4,  3, 'Entrainement karate',               'Karate, kumite, katas ouverts a tous niveaux.',         'Entrainement','CE','2026-07-21', '19:00:00', 120, 'Dojo',                    25,  18),
(5,  2, 'Debat : IA et ethique',             'L''IA doit-elle etre encadree par la loi ?',             'Debat',      'CE', '2026-07-23', '14:00:00', 180, 'Amphi B',                 150, 87),
(6,  6, 'Soiree impro : Freestyle',          'Improvisation libre sur themes du public.',             'Spectacle',  'CT', '2026-07-25', '20:00:00', 150, 'Theatre',                 120, 62),
(7,  4, 'Journee nettoyage campus',          'Collecte de dechets, sensibilisation, repas convivial.', 'Action',    'CE', '2026-07-26', '08:00:00', 300, 'Entree principale',      100, 45),
(8,  9, 'Concert chorale ete',               'Repertoire pop-rock, jazz, classique.',                  'Concert',    'CM', '2026-07-27', '19:00:00', 120, 'Cour du chateau',         200, 0),
(9,  7, 'Pitch Night #5',                    '5 startups pitchent devant un jury de professionnels.',  'Networking', 'CE', '2026-07-29', '18:30:00', 180, 'Maison entrepreneur',     100, 78),
(10, 3, 'Competition inter-universitaire',   'Open de karate entre 6 ecoles.',                         'Competition','CE','2026-07-30', '10:00:00', 480, 'Dojo',                    50,  32),
(11, 10,'Tournoi de foot 3v3',                'Tournoi estival, 24 equipes.',                          'Tournoi',    'FB', '2026-08-02', '14:00:00', 360, 'Terrain synthetique',     64,  36),
(12, 12,'Soiree culture japonaise',          'Decouverte culture : gastronomie, calligraphie, jeux.',  'Culturel',   'LC', '2026-08-05', '18:00:00', 180, 'Salle internationale',    60,  28);

-- INSCRIPTIONS
INSERT INTO `inscriptions` (`user_id`, `club_id`, `year`, `role_in_club`) VALUES
(1, 2, 2026, 'member'),
(1, 5, 2026, 'member'),
(2, 2, 2026, 'president');

-- NOTIFICATIONS pour Mika (user 1)
INSERT INTO `notifications` (`user_id`, `kind`, `icon`, `title`, `message`, `link`, `is_read`) VALUES
(1, 'inscription', '✓', 'Inscription confirmee',   'Vous etes maintenant membre du Club Robotique.',         'club.html?id=5',           0),
(1, 'event',      '◷', 'Nouvel evenement',        'Le Club Informatique a publie un nouvel atelier.',        'events.html?id=2',         0),
(1, 'event',      '!', 'Rappel : Debat IA',       'L''evenement commence dans 2 jours (23 juillet a 14h).',  'events.html?id=5',         0),
(1, 'club',       'i', 'Nouveau club disponible', 'Le Club Langues & Cultures vient d''ouvrir.',            'club.html?id=12',          1),
(1, 'system',     '★', '+50 points d''engagement','Vous avez gagne 50 points pour votre participation.',    'profile.html',             1);

SET FOREIGN_KEY_CHECKS = 1;
