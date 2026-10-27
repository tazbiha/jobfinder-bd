```sql
USE defaultdb;

-- ========================================
-- 1. USERS TABLE
-- ========================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('seeker', 'employer', 'admin') DEFAULT 'seeker',
    skills TEXT,
    education TEXT,
    cv_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================
-- 2. JOBS TABLE
-- ========================================

CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NULL,
    title VARCHAR(150) NOT NULL,
    company VARCHAR(120) NOT NULL,
    type ENUM('Job', 'Internship') DEFAULT 'Job',
    category VARCHAR(80),
    location VARCHAR(100),
    salary_details VARCHAR(150),
    company_logo VARCHAR(255),
    source_url VARCHAR(500),
    skills VARCHAR(255),
    description TEXT,
    deadline DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (employer_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================
-- 3. APPLICATIONS TABLE
-- ========================================

CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    cover TEXT,
    status ENUM(
        'Pending',
        'Shortlisted',
        'Rejected',
        'Hired'
    ) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_job_user (job_id, user_id),

    FOREIGN KEY (job_id)
        REFERENCES jobs(id)
        ON DELETE CASCADE,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================
-- 4. MOCK TEST RESULTS TABLE
-- ========================================

CREATE TABLE IF NOT EXISTS mock_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    topic VARCHAR(150),
    score INT,
    total INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================
-- 5. SCHEMA MIGRATIONS
-- Add missing jobs columns if necessary
-- ========================================

SET @job_schema = DATABASE();

-- salary_details
SELECT COUNT(*) INTO @has_salary_details
FROM information_schema.columns
WHERE table_schema = @job_schema
  AND table_name = 'jobs'
  AND column_name = 'salary_details';

SET @sql = IF(
    @has_salary_details = 0,
    'ALTER TABLE jobs ADD COLUMN salary_details VARCHAR(150) NULL AFTER location',
    'SELECT 1'
);

PREPARE job_schema_update FROM @sql
