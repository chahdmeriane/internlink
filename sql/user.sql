CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(100)   NOT NULL,
    last_name  VARCHAR(100)   NOT NULL,
    email      VARCHAR(150)   NOT NULL UNIQUE,
    password   VARCHAR(255)   NOT NULL,
    role       ENUM('student','company','admin') NOT NULL,
    created_at DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
