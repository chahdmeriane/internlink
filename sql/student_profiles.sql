CREATE TABLE IF NOT EXISTS student_profiles (
    id             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    user_id        INT UNSIGNED   NOT NULL UNIQUE,
    university     VARCHAR(200)            DEFAULT NULL,
    field_of_study VARCHAR(100)            DEFAULT NULL,
    year           VARCHAR(30)             DEFAULT NULL,
    city           VARCHAR(100)            DEFAULT NULL,
    country        VARCHAR(100)            DEFAULT NULL,
    skills         TEXT                    DEFAULT NULL,  -- comma-separated
    bio            TEXT                    DEFAULT NULL,
    created_at     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_sp_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
