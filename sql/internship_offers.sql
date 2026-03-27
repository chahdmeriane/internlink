CREATE TABLE IF NOT EXISTS internship_offers (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    company_id  INT UNSIGNED   NOT NULL,
    title       VARCHAR(200)   NOT NULL,
    field       VARCHAR(100)   NOT NULL,
    location    VARCHAR(150)   NOT NULL,
    duration    VARCHAR(50)    NOT NULL,
    skills      VARCHAR(255)            DEFAULT NULL,  -- comma-separated
    description TEXT                    DEFAULT NULL,
    status      ENUM('active','closed') NOT NULL DEFAULT 'active',
    created_at  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_company (company_id),
    KEY idx_status  (status),
    CONSTRAINT fk_offer_company FOREIGN KEY (company_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
