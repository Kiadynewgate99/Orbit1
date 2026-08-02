-- =============================================================
-- ClubHub v1.0.0 - Schema MySQL
-- Compatible : MySQL 5.7+ / MariaDB 10.3+
-- Encodage   : utf8mb4
-- Charset    : full Unicode + emoji-safe
-- =============================================================

CREATE DATABASE IF NOT EXISTS `clubhub` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `clubhub`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `presences`;
DROP TABLE IF EXISTS `inscriptions`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `club_tags`;
DROP TABLE IF EXISTS `clubs`;
DROP TABLE IF EXISTS `users`;

-- =============================================================
-- 1. USERS
-- =============================================================
CREATE TABLE `users` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `matricule`       VARCHAR(20)  NOT NULL,
  `email`           VARCHAR(190) NOT NULL,
  `password_hash`   VARCHAR(255) DEFAULT NULL,        -- NULL si SSO only
  `name`            VARCHAR(120) NOT NULL,
  `avatar`          VARCHAR(4)   NOT NULL,            -- 2 lettres MA, SA, HR
  `role`            ENUM('student','manager','admin') NOT NULL DEFAULT 'student',
  `filiere`         VARCHAR(60)  DEFAULT NULL,
  `niveau`          VARCHAR(20)  DEFAULT NULL,
  `lang`            VARCHAR(5)   NOT NULL DEFAULT 'fr',
  `theme`           VARCHAR(20)  NOT NULL DEFAULT 'retro',
  `accessibility`   JSON         DEFAULT NULL,
  `managed_club_id` INT UNSIGNED DEFAULT NULL,
  `points`          INT          NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_at`   TIMESTAMP    NULL,
  `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_matricule` (`matricule`),
  UNIQUE KEY `uniq_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_managed_club` (`managed_club_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 2. CLUBS
-- =============================================================
CREATE TABLE `clubs` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`            VARCHAR(60)  NOT NULL,             -- club-001
  `name`            VARCHAR(120) NOT NULL,
  `category`        VARCHAR(40)  NOT NULL,             -- tech, culture, sport, ...
  `color`           VARCHAR(9)   NOT NULL DEFAULT '#FF4502',
  `short_desc`      VARCHAR(255) NOT NULL,
  `long_desc`       TEXT         DEFAULT NULL,
  `tags`            JSON         DEFAULT NULL,         -- ["Python", "IA"]
  `room`            VARCHAR(60)  DEFAULT NULL,
  `president`       VARCHAR(120) DEFAULT NULL,
  `status`          ENUM('active','incubation','archived') NOT NULL DEFAULT 'active',
  `capacity`        INT          NOT NULL DEFAULT 100,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by`      INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 3. EVENTS
-- =============================================================
CREATE TABLE `events` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `club_id`         INT UNSIGNED NOT NULL,
  `title`           VARCHAR(160) NOT NULL,
  `description`     TEXT         DEFAULT NULL,
  `type`            VARCHAR(30)  NOT NULL,             -- Atelier, Conference, Soiree...
  `category`        VARCHAR(30)  NOT NULL,             -- tag color (CI, CE, etc.)
  `date`            DATE         NOT NULL,
  `time`            TIME         NOT NULL,
  `duration_min`    INT UNSIGNED NOT NULL DEFAULT 120,
  `room`            VARCHAR(120) DEFAULT NULL,
  `capacity`        INT UNSIGNED NOT NULL DEFAULT 50,
  `registered`      INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by`      INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_club` (`club_id`),
  KEY `idx_date` (`date`),
  KEY `idx_type` (`type`),
  CONSTRAINT `fk_events_club` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 4. INSCRIPTIONS (lien user <-> club, sur l'annee)
-- =============================================================
CREATE TABLE `inscriptions` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED NOT NULL,
  `club_id`         INT UNSIGNED NOT NULL,
  `year`            YEAR         NOT NULL,
  `role_in_club`    ENUM('member','bureau','president') NOT NULL DEFAULT 'member',
  `status`          ENUM('pending','active','inactive','refused') NOT NULL DEFAULT 'active',
  `joined_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `left_at`         TIMESTAMP    NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_club_year` (`user_id`,`club_id`,`year`),
  KEY `idx_club_year` (`club_id`,`year`),
  CONSTRAINT `fk_insc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_insc_club` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 5. PRESENCES (user inscrit a un event + statut)
-- =============================================================
CREATE TABLE `presences` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED NOT NULL,
  `event_id`        INT UNSIGNED NOT NULL,
  `status`          ENUM('registered','present','absent','late') NOT NULL DEFAULT 'registered',
  `method`          ENUM('manual','qr','geo') NOT NULL DEFAULT 'manual',
  `checked_at`      TIMESTAMP    NULL,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_event` (`user_id`,`event_id`),
  KEY `idx_event_status` (`event_id`,`status`),
  CONSTRAINT `fk_pres_user`  FOREIGN KEY (`user_id`)  REFERENCES `users`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pres_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 6. NOTIFICATIONS
-- =============================================================
CREATE TABLE `notifications` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED NOT NULL,
  `kind`            VARCHAR(40)  NOT NULL,             -- inscription, event, club, system
  `icon`            VARCHAR(10)  DEFAULT 'i',
  `title`           VARCHAR(160) NOT NULL,
  `message`         VARCHAR(500) NOT NULL,
  `link`            VARCHAR(255) DEFAULT NULL,
  `is_read`         TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_unread` (`user_id`,`is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 7. AUDIT LOG
-- =============================================================
CREATE TABLE `audit_log` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED DEFAULT NULL,
  `action`          VARCHAR(60)  NOT NULL,             -- CREATE, UPDATE, DELETE, LOGIN...
  `target_type`     VARCHAR(40)  DEFAULT NULL,
  `target_id`       INT UNSIGNED DEFAULT NULL,
  `ip`              VARCHAR(45)  DEFAULT NULL,
  `user_agent`      VARCHAR(255) DEFAULT NULL,
  `metadata`        JSON         DEFAULT NULL,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_action` (`user_id`,`action`),
  KEY `idx_target` (`target_type`,`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- 8. SESSIONS  (optionnel si on veut bypasser PHP sessions)
-- =============================================================
CREATE TABLE `auth_tokens` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED NOT NULL,
  `token`           CHAR(64)     NOT NULL,
  `expires_at`      TIMESTAMP    NOT NULL,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token` (`token`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_token_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
