-- =====================================================================
-- Barangay Health Care System (San Ildefonso) - MySQL / MariaDB schema
-- Built from the Blade views: login, register, resident home/referrals/
-- profile, BHW dashboard/referrals/profile, event form, record-edit.
--
-- HOW TO USE (XAMPP):
--   1. Start MySQL in XAMPP, open phpMyAdmin (localhost/phpmyadmin).
--   2. To rebuild from scratch, drop the old barangay_health database first.
--   3. Import this file (Import tab), or paste it into the SQL tab.
--   4. In .env set DB_CONNECTION=mysql, DB_DATABASE=barangay_health,
--      SESSION_DRIVER=file, CACHE_STORE=file, QUEUE_CONNECTION=sync
--      (this file does not create Laravel's sessions/cache/jobs tables).
--   Do NOT also run `php artisan migrate` on this database: the users
--      table here replaces Laravel's default one.
-- Needs MySQL 8.0.16+ or MariaDB 10.2.5+ (CHECK constraints and
-- stored generated columns).
-- =====================================================================

CREATE DATABASE IF NOT EXISTS barangay_health
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barangay_health;

-- ---------------------------------------------------------------------
-- 1. Places
-- ---------------------------------------------------------------------
CREATE TABLE barangays (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100) NOT NULL,
  created_at  TIMESTAMP NULL DEFAULT NULL,
  updated_at  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_barangays_name (name)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. Accounts (sign-up step 1 + 2, login by phone or email)
--    One account = one person. status drives the "Pending sign-ups" card.
-- ---------------------------------------------------------------------
CREATE TABLE users (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name            VARCHAR(255) NOT NULL,
  phone           VARCHAR(20)  NOT NULL,
  email           VARCHAR(255) NULL,
  password        VARCHAR(255) NOT NULL,                 -- bcrypt hash only
  role            ENUM('resident','bhw','mother_leader') NOT NULL DEFAULT 'resident',
  status          ENUM('pending','approved','rejected')  NOT NULL DEFAULT 'pending',
  approved_by     BIGINT UNSIGNED NULL,
  approved_at     TIMESTAMP NULL DEFAULT NULL,
  remember_token  VARCHAR(100) NULL,
  created_at      TIMESTAMP NULL DEFAULT NULL,
  updated_at      TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_phone (phone),
  UNIQUE KEY uq_users_email (email),
  CONSTRAINT fk_users_approved_by
    FOREIGN KEY (approved_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- BHW profile screen: Worker ID, Barangay, Assigned purok.
CREATE TABLE bhw_profiles (
  user_id      BIGINT UNSIGNED NOT NULL,
  worker_no    VARCHAR(30) NULL,
  barangay_id  BIGINT UNSIGNED NOT NULL,
  purok        VARCHAR(50) NULL,
  created_at   TIMESTAMP NULL DEFAULT NULL,
  updated_at   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_bhw_worker_no (worker_no),
  KEY idx_bhw_barangay (barangay_id),
  CONSTRAINT fk_bhw_user
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_bhw_barangay
    FOREIGN KEY (barangay_id) REFERENCES barangays (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. Residents (the person the BHW keeps records on)
--    user_id is NULL for people who never create an account.
--    barangay_id = CURRENT barangay (history lives in resident_residences).
-- ---------------------------------------------------------------------
CREATE TABLE residents (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id      BIGINT UNSIGNED NULL,
  barangay_id  BIGINT UNSIGNED NOT NULL,
  resident_no  VARCHAR(30)  NULL,                        -- the [ID number] on the home card
  full_name    VARCHAR(255) NOT NULL,
  birthdate    DATE NULL,
  sex          ENUM('female','male') NULL,
  purok        VARCHAR(50)  NULL,                        -- still shown on resident home/profile
  phone        VARCHAR(20)  NULL,                        -- "Contact number" in Citizen profile
  email        VARCHAR(255) NULL,
  photo_path   VARCHAR(255) NULL,                        -- patient photo modal
  created_at   TIMESTAMP NULL DEFAULT NULL,
  updated_at   TIMESTAMP NULL DEFAULT NULL,
  deleted_at   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_residents_user (user_id),
  UNIQUE KEY uq_residents_no (resident_no),
  KEY idx_residents_barangay (barangay_id, deleted_at),
  KEY idx_residents_name (full_name),
  CONSTRAINT fk_residents_user
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT fk_residents_barangay
    FOREIGN KEY (barangay_id) REFERENCES barangays (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- "Change barangay" modal: where the resident lived, and since when.
-- The unique key allows only ONE open (current) row per resident.
CREATE TABLE resident_residences (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  resident_id   BIGINT UNSIGNED NOT NULL,
  barangay_id   BIGINT UNSIGNED NOT NULL,
  address       VARCHAR(255) NULL,
  moved_in_on   DATE NOT NULL,
  moved_out_on  DATE NULL,
  recorded_by   BIGINT UNSIGNED NULL,
  created_at    TIMESTAMP NULL DEFAULT NULL,
  is_current    TINYINT GENERATED ALWAYS AS (IF(moved_out_on IS NULL, 1, NULL)) STORED,
  PRIMARY KEY (id),
  UNIQUE KEY uq_one_current_residence (resident_id, is_current),
  KEY idx_residences_barangay (barangay_id),
  CONSTRAINT chk_residence_dates CHECK (moved_out_on IS NULL OR moved_out_on >= moved_in_on),
  CONSTRAINT fk_residences_resident
    FOREIGN KEY (resident_id) REFERENCES residents (id) ON DELETE RESTRICT,
  CONSTRAINT fk_residences_barangay
    FOREIGN KEY (barangay_id) REFERENCES barangays (id) ON DELETE RESTRICT,
  CONSTRAINT fk_residences_recorded_by
    FOREIGN KEY (recorded_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Resident "Referrals" screen: history of BHWs assigned to the resident
-- (Current = ended_on IS NULL). The Mother Leader site fills assigned_by.
CREATE TABLE bhw_assignments (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  resident_id  BIGINT UNSIGNED NOT NULL,
  bhw_user_id  BIGINT UNSIGNED NOT NULL,
  assigned_on  DATE NOT NULL,
  ended_on     DATE NULL,
  assigned_by  BIGINT UNSIGNED NULL,
  created_at   TIMESTAMP NULL DEFAULT NULL,
  updated_at   TIMESTAMP NULL DEFAULT NULL,
  is_current   TINYINT GENERATED ALWAYS AS (IF(ended_on IS NULL, 1, NULL)) STORED,
  PRIMARY KEY (id),
  UNIQUE KEY uq_one_current_bhw (resident_id, is_current),
  KEY idx_assign_bhw (bhw_user_id, ended_on),
  CONSTRAINT chk_assign_dates CHECK (ended_on IS NULL OR ended_on >= assigned_on),
  CONSTRAINT fk_assign_resident
    FOREIGN KEY (resident_id) REFERENCES residents (id) ON DELETE RESTRICT,
  CONSTRAINT fk_assign_bhw
    FOREIGN KEY (bhw_user_id) REFERENCES users (id) ON DELETE RESTRICT,
  CONSTRAINT fk_assign_by
    FOREIGN KEY (assigned_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. Events and announcements (event-form + resident home feed)
--    Date, time and venue are required only for type = 'event'
--    (same rule as the form validation).
-- ---------------------------------------------------------------------
CREATE TABLE events (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  barangay_id  BIGINT UNSIGNED NOT NULL,
  posted_by    BIGINT UNSIGNED NOT NULL,
  type         ENUM('event','announcement') NOT NULL,
  title        VARCHAR(100) NOT NULL,
  event_date   DATE NULL,
  event_time   TIME NULL,
  venue        VARCHAR(100) NULL,
  description  VARCHAR(1000) NULL,
  created_at   TIMESTAMP NULL DEFAULT NULL,
  updated_at   TIMESTAMP NULL DEFAULT NULL,
  deleted_at   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_events_barangay_date (barangay_id, event_date),
  CONSTRAINT chk_event_fields CHECK (
    type = 'announcement'
    OR (event_date IS NOT NULL AND event_time IS NOT NULL AND venue IS NOT NULL)
  ),
  CONSTRAINT fk_events_barangay
    FOREIGN KEY (barangay_id) REFERENCES barangays (id) ON DELETE RESTRICT,
  CONSTRAINT fk_events_posted_by
    FOREIGN KEY (posted_by) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. Edit record: Socioeconomic and social services
-- ---------------------------------------------------------------------
CREATE TABLE socioeconomic_profiles (
  resident_id           BIGINT UNSIGNED NOT NULL,
  employment_status     ENUM('employed','self_employed','unemployed','retired') NULL,
  income_range          ENUM('below_10k','10k_to_20k','above_20k') NULL,
  is_philhealth_member  TINYINT(1) NOT NULL DEFAULT 0,
  is_4ps_beneficiary    TINYINT(1) NOT NULL DEFAULT 0,
  updated_by            BIGINT UNSIGNED NULL,
  created_at            TIMESTAMP NULL DEFAULT NULL,
  updated_at            TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (resident_id),
  CONSTRAINT fk_socio_resident
    FOREIGN KEY (resident_id) REFERENCES residents (id) ON DELETE RESTRICT,
  CONSTRAINT fk_socio_updated_by
    FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE social_services (
  id    SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name  VARCHAR(60) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_social_services_name (name)
) ENGINE=InnoDB;

CREATE TABLE resident_social_services (
  resident_id        BIGINT UNSIGNED NOT NULL,
  social_service_id  SMALLINT UNSIGNED NOT NULL,
  created_at         TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (resident_id, social_service_id),
  CONSTRAINT fk_rss_resident
    FOREIGN KEY (resident_id) REFERENCES residents (id) ON DELETE CASCADE,
  CONSTRAINT fk_rss_service
    FOREIGN KEY (social_service_id) REFERENCES social_services (id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. Edit record: health readings. "Last recorded" on screen =
--    MAX(recorded_at) per resident; "Updated by" = recorded_by.
--    Ranges match the min/max on the form inputs. NULL passes a CHECK,
--    so the BHW can save only the readings they actually took.
-- ---------------------------------------------------------------------
CREATE TABLE vital_signs (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  resident_id       BIGINT UNSIGNED NOT NULL,
  recorded_by       BIGINT UNSIGNED NULL,
  recorded_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  bp_systolic       SMALLINT UNSIGNED NULL,              -- "120/80" is stored as 120 and 80
  bp_diastolic      SMALLINT UNSIGNED NULL,
  temperature_c     DECIMAL(4,1) NULL,
  spo2_percent      TINYINT UNSIGNED NULL,               -- oxygen saturation
  respiratory_rate  TINYINT UNSIGNED NULL,               -- breaths per minute
  pulse_bpm         SMALLINT UNSIGNED NULL,
  cbg_mgdl          SMALLINT UNSIGNED NULL,              -- capillary blood glucose
  created_at        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_vitals_resident_time (resident_id, recorded_at),
  CONSTRAINT chk_vitals_bp_pair CHECK ((bp_systolic IS NULL) = (bp_diastolic IS NULL)),
  CONSTRAINT chk_vitals_bp CHECK (bp_systolic BETWEEN 40 AND 300 AND bp_diastolic BETWEEN 20 AND 200
                                  AND bp_systolic > bp_diastolic),
  CONSTRAINT chk_vitals_temp  CHECK (temperature_c    BETWEEN 30 AND 45),
  CONSTRAINT chk_vitals_spo2  CHECK (spo2_percent     BETWEEN 0 AND 100),
  CONSTRAINT chk_vitals_resp  CHECK (respiratory_rate BETWEEN 4 AND 80),
  CONSTRAINT chk_vitals_pulse CHECK (pulse_bpm        BETWEEN 20 AND 250),
  CONSTRAINT chk_vitals_cbg   CHECK (cbg_mgdl         BETWEEN 10 AND 800),
  CONSTRAINT fk_vitals_resident
    FOREIGN KEY (resident_id) REFERENCES residents (id) ON DELETE RESTRICT,
  CONSTRAINT fk_vitals_recorded_by
    FOREIGN KEY (recorded_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- BMI is calculated by the database, never typed (same formula as the form).
CREATE TABLE nutritional_records (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  resident_id  BIGINT UNSIGNED NOT NULL,
  recorded_by  BIGINT UNSIGNED NULL,
  recorded_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  height_cm    DECIMAL(5,1) NULL,
  weight_kg    DECIMAL(5,1) NULL,
  bmi          DECIMAL(4,1) GENERATED ALWAYS AS (
                 CASE WHEN height_cm > 0 AND weight_kg > 0
                      THEN ROUND(weight_kg / POWER(height_cm / 100, 2), 1) END
               ) STORED,
  created_at   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_nutrition_resident_time (resident_id, recorded_at),
  CONSTRAINT chk_nutrition_height CHECK (height_cm BETWEEN 30 AND 250),
  CONSTRAINT chk_nutrition_weight CHECK (weight_kg BETWEEN 2 AND 400),
  CONSTRAINT fk_nutrition_resident
    FOREIGN KEY (resident_id) REFERENCES residents (id) ON DELETE RESTRICT,
  CONSTRAINT fk_nutrition_recorded_by
    FOREIGN KEY (recorded_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- "Medical status reports" + the "Add score" modal. 'label' is for
-- score_type = 'other' (the modal has no name field for it yet).
CREATE TABLE medical_scores (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  resident_id  BIGINT UNSIGNED NOT NULL,
  recorded_by  BIGINT UNSIGNED NULL,
  score_type   ENUM('blood_glucose','cholesterol','hemoglobin','uric_acid','other') NOT NULL,
  label        VARCHAR(80) NULL,
  value        DECIMAL(8,2) NOT NULL,
  unit         VARCHAR(10) NOT NULL,                     -- mg/dL, mmol/L, g/dL, g/L, µmol/L, %, U/L
  measured_as  ENUM('fasting','after_meal','random') NULL,
  recorded_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at   TIMESTAMP NULL DEFAULT NULL,
  deleted_at   TIMESTAMP NULL DEFAULT NULL,              -- the Remove button
  PRIMARY KEY (id),
  KEY idx_scores_resident_time (resident_id, recorded_at),
  CONSTRAINT chk_scores_value CHECK (value > 0),
  CONSTRAINT chk_scores_measured CHECK (measured_as IS NULL OR score_type = 'blood_glucose'),
  CONSTRAINT fk_scores_resident
    FOREIGN KEY (resident_id) REFERENCES residents (id) ON DELETE RESTRICT,
  CONSTRAINT fk_scores_recorded_by
    FOREIGN KEY (recorded_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. Medications ("Add medication" modal). is_active = "Currently taking".
--    Dose is typed by the BHW from the prescription; never pre-filled.
-- ---------------------------------------------------------------------
CREATE TABLE medications (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  resident_id  BIGINT UNSIGNED NOT NULL,
  added_by     BIGINT UNSIGNED NULL,
  name         VARCHAR(120) NOT NULL,
  dose         DECIMAL(8,2) NOT NULL,
  dose_unit    ENUM('mg','mcg','g','mL','tablet') NOT NULL,
  frequency    ENUM('once_daily','twice_daily','three_times_daily',
                    'every_4_to_6_hours','weekly','as_needed','other') NOT NULL,
  start_date   DATE NULL,
  is_active    TINYINT(1) NOT NULL DEFAULT 1,
  created_at   TIMESTAMP NULL DEFAULT NULL,
  updated_at   TIMESTAMP NULL DEFAULT NULL,
  deleted_at   TIMESTAMP NULL DEFAULT NULL,              -- the Remove link
  PRIMARY KEY (id),
  KEY idx_meds_resident_active (resident_id, is_active),
  CONSTRAINT chk_meds_dose CHECK (dose > 0),
  CONSTRAINT fk_meds_resident
    FOREIGN KEY (resident_id) REFERENCES residents (id) ON DELETE RESTRICT,
  CONSTRAINT fk_meds_added_by
    FOREIGN KEY (added_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. Photo evidence (several per resident, each with caption and date)
-- ---------------------------------------------------------------------
CREATE TABLE record_photos (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  resident_id  BIGINT UNSIGNED NOT NULL,
  uploaded_by  BIGINT UNSIGNED NULL,
  file_path    VARCHAR(255) NOT NULL,
  caption      VARCHAR(255) NULL,
  taken_on     DATE NULL,
  created_at   TIMESTAMP NULL DEFAULT NULL,
  deleted_at   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_photos_resident (resident_id),
  CONSTRAINT fk_photos_resident
    FOREIGN KEY (resident_id) REFERENCES residents (id) ON DELETE RESTRICT,
  CONSTRAINT fk_photos_uploaded_by
    FOREIGN KEY (uploaded_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. Starter data (lookup values only - no people).
--    Replace the barangay names with your real ones.
--    Create demo users with a Laravel seeder so passwords get hashed.
-- ---------------------------------------------------------------------
INSERT INTO barangays (name, created_at, updated_at) VALUES
  ('Barangay 1', NOW(), NOW()),
  ('Barangay 2', NOW(), NOW()),
  ('Barangay 3', NOW(), NOW());

INSERT INTO social_services (name) VALUES
  ('Food packs'), ('Cash aid'), ('Senior pension'), ('Scholarship');
