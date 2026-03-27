-- ═══════════════════════════════════════════════════════════════════════════
-- internLink — Full Database Schema  (v2 — global edition)
-- Run once:  mysql -u root internlink < schema.sql
-- ═══════════════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ── 1. users ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name     VARCHAR(100) NOT NULL,
  last_name      VARCHAR(100) NOT NULL,
  email          VARCHAR(255) NOT NULL UNIQUE,
  password       VARCHAR(255) NOT NULL,
  role           ENUM('student','company','admin') NOT NULL DEFAULT 'student',
  two_fa_code    VARCHAR(6)   DEFAULT NULL,
  two_fa_expires DATETIME     DEFAULT NULL,
  created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 2. student_profiles ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS student_profiles (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id        INT UNSIGNED NOT NULL UNIQUE,
  phone          VARCHAR(30)  DEFAULT NULL,
  university     VARCHAR(200) DEFAULT NULL,
  field_of_study VARCHAR(150) DEFAULT NULL,
  academic_year  VARCHAR(50)  DEFAULT NULL,
  country        VARCHAR(100) DEFAULT NULL,   -- e.g. "France", "Algeria", "United States"
  wilaya         VARCHAR(100) DEFAULT NULL,   -- city / region field (global use)
  bio            TEXT         DEFAULT NULL,
  skills         TEXT         DEFAULT NULL,   -- comma-separated: "Python,React,SQL"
  linkedin       VARCHAR(300) DEFAULT NULL,
  github         VARCHAR(300) DEFAULT NULL,
  cv_path        VARCHAR(300) DEFAULT NULL,   -- relative: uploads/cv/cv_student_1.pdf
  updated_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. company_profiles ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS company_profiles (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED NOT NULL UNIQUE,
  company_name VARCHAR(200) DEFAULT NULL,
  sector       VARCHAR(150) DEFAULT NULL,
  country      VARCHAR(100) DEFAULT NULL,
  wilaya       VARCHAR(100) DEFAULT NULL,   -- city
  description  TEXT         DEFAULT NULL,
  website      VARCHAR(300) DEFAULT NULL,
  is_verified  TINYINT(1)   NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. internships ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS internships (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id       INT UNSIGNED NOT NULL,
  title            VARCHAR(250) NOT NULL,
  description      TEXT         DEFAULT NULL,
  required_skills  TEXT         DEFAULT NULL,   -- comma-separated
  domain           VARCHAR(150) DEFAULT NULL,
  country          VARCHAR(100) DEFAULT NULL,
  wilaya           VARCHAR(100) DEFAULT NULL,   -- city
  duration_months  INT UNSIGNED DEFAULT 1,
  deadline         DATE         DEFAULT NULL,
  is_paid          TINYINT(1)   NOT NULL DEFAULT 0,
  work_type        ENUM('onsite','remote','hybrid') DEFAULT 'onsite',
  salary           DECIMAL(10,2) DEFAULT NULL,
  is_active        TINYINT(1)   NOT NULL DEFAULT 1,
  created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 5. applications ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS applications (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id     INT UNSIGNED NOT NULL,
  internship_id  INT UNSIGNED NOT NULL,
  cover_letter   TEXT         DEFAULT NULL,
  match_percent  INT UNSIGNED DEFAULT 0,
  status         ENUM('pending','viewed','accepted','rejected') NOT NULL DEFAULT 'pending',
  applied_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  viewed_at      DATETIME     DEFAULT NULL,
  decided_at     DATETIME     DEFAULT NULL,
  feedback       TEXT         DEFAULT NULL,
  UNIQUE KEY unique_application (student_id, internship_id),
  FOREIGN KEY (student_id)    REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 6. saved_internships ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS saved_internships (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id     INT UNSIGNED NOT NULL,
  internship_id  INT UNSIGNED NOT NULL,
  saved_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_save (student_id, internship_id),
  FOREIGN KEY (student_id)    REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (internship_id) REFERENCES internships(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════════════════════════════
-- MIGRATIONS — run these if you already have the old schema installed:
-- ═══════════════════════════════════════════════════════════════════════════
-- ALTER TABLE users            ADD COLUMN IF NOT EXISTS two_fa_code VARCHAR(6) DEFAULT NULL;
-- ALTER TABLE users            ADD COLUMN IF NOT EXISTS two_fa_expires DATETIME DEFAULT NULL;
-- ALTER TABLE student_profiles ADD COLUMN IF NOT EXISTS country VARCHAR(100) DEFAULT NULL;
-- ALTER TABLE company_profiles ADD COLUMN IF NOT EXISTS country VARCHAR(100) DEFAULT NULL;
-- ALTER TABLE internships      ADD COLUMN IF NOT EXISTS country  VARCHAR(100) DEFAULT NULL;
-- ALTER TABLE internships      ADD COLUMN IF NOT EXISTS is_paid  TINYINT(1)   NOT NULL DEFAULT 0;
-- ALTER TABLE internships      ADD COLUMN IF NOT EXISTS work_type ENUM('onsite','remote','hybrid') DEFAULT 'onsite';
-- ALTER TABLE internships      ADD COLUMN IF NOT EXISTS salary   DECIMAL(10,2) DEFAULT NULL;
