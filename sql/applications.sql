CREATE TABLE IF NOT EXISTS applications (
    id         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED   NOT NULL,
    offer_id   INT UNSIGNED   NOT NULL,
    status     ENUM('waiting','accepted','rejected') NOT NULL DEFAULT 'waiting',
    applied_at DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_student_offer (student_id, offer_id),  -- one app per student per offer
    KEY idx_offer   (offer_id),
    KEY idx_student (student_id),
    KEY idx_status  (status),
    CONSTRAINT fk_app_student FOREIGN KEY (student_id) REFERENCES users (id)                ON DELETE CASCADE,
    CONSTRAINT fk_app_offer   FOREIGN KEY (offer_id)   REFERENCES internship_offers (id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
