CREATE DATABASE IF NOT EXISTS jobfinder CHARACTER SET utf8mb4;
USE jobfinder;
CREATE TABLE IF NOT EXISTS users(
  id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(120) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL, role ENUM('seeker','employer','admin') DEFAULT 'seeker',
  skills TEXT, education TEXT, cv_path VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS jobs(
  id INT AUTO_INCREMENT PRIMARY KEY, employer_id INT NULL, title VARCHAR(150) NOT NULL, company VARCHAR(120) NOT NULL,
  type ENUM('Job','Internship') DEFAULT 'Job', category VARCHAR(80), location VARCHAR(100),
  salary_details VARCHAR(150), company_logo VARCHAR(255), source_url VARCHAR(500), skills VARCHAR(255),
  description TEXT, deadline DATE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS applications(
  id INT AUTO_INCREMENT PRIMARY KEY, job_id INT NOT NULL, user_id INT NOT NULL, cover TEXT,
  status ENUM('Pending','Shortlisted','Rejected','Hired') DEFAULT 'Pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(job_id,user_id),
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS mock_results(
  id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, topic VARCHAR(150), score INT, total INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE);
SET @job_schema = DATABASE();

SELECT COUNT(*) INTO @has_salary_details
FROM information_schema.columns
WHERE table_schema=@job_schema AND table_name='jobs' AND column_name='salary_details';
SET @sql = IF(@has_salary_details=0,
  'ALTER TABLE jobs ADD COLUMN salary_details VARCHAR(150) NULL AFTER location',
  'SELECT 1');
PREPARE job_schema_update FROM @sql;
EXECUTE job_schema_update;
DEALLOCATE PREPARE job_schema_update;

SELECT COUNT(*) INTO @has_company_logo
FROM information_schema.columns
WHERE table_schema=@job_schema AND table_name='jobs' AND column_name='company_logo';
SET @sql = IF(@has_company_logo=0,
  'ALTER TABLE jobs ADD COLUMN company_logo VARCHAR(255) NULL AFTER salary_details',
  'SELECT 1');
PREPARE job_schema_update FROM @sql;
EXECUTE job_schema_update;
DEALLOCATE PREPARE job_schema_update;

SELECT COUNT(*) INTO @has_source_url
FROM information_schema.columns
WHERE table_schema=@job_schema AND table_name='jobs' AND column_name='source_url';
SET @sql = IF(@has_source_url=0,
  'ALTER TABLE jobs ADD COLUMN source_url VARCHAR(500) NULL AFTER company_logo',
  'SELECT 1');
PREPARE job_schema_update FROM @sql;
EXECUTE job_schema_update;
DEALLOCATE PREPARE job_schema_update;

UPDATE jobs SET category='IT/Telecommunication' WHERE category='IT';
UPDATE jobs SET category='Administration' WHERE category='Admin';
UPDATE jobs SET category='Accounting/Finance' WHERE category='Accounting';
UPDATE jobs SET category='Marketing/Sales' WHERE category='Marketing';

INSERT INTO jobs(title,company,type,category,location,skills,description,deadline)
SELECT 'Junior PHP Developer','Dhaka Tech Ltd','Job','IT/Telecommunication','Dhaka','php mysql javascript bootstrap','Build and maintain web applications using PHP and MySQL.','2026-12-31'
WHERE NOT EXISTS (SELECT 1 FROM jobs WHERE title='Junior PHP Developer' AND company='Dhaka Tech Ltd');
INSERT INTO jobs(title,company,type,category,location,skills,description,deadline)
SELECT 'Web Development Intern','Mymensingh Soft','Internship','IT/Telecommunication','Mymensingh','html css javascript','3-month paid internship for fresh students.','2026-11-30'
WHERE NOT EXISTS (SELECT 1 FROM jobs WHERE title='Web Development Intern' AND company='Mymensingh Soft');
INSERT INTO jobs(title,company,type,category,location,skills,description,deadline)
SELECT 'Data Entry Officer','BD Services','Job','Administration','Dhaka','excel typing english','Enter and verify data. Fresh graduates welcome.','2026-11-15'
WHERE NOT EXISTS (SELECT 1 FROM jobs WHERE title='Data Entry Officer' AND company='BD Services');
INSERT INTO jobs(title,company,type,category,location,skills,description,deadline)
SELECT 'Marketing Intern','BrandBD','Internship','Marketing/Sales','Chattogram','communication social-media canva','Help run social media campaigns.','2026-12-10'
WHERE NOT EXISTS (SELECT 1 FROM jobs WHERE title='Marketing Intern' AND company='BrandBD');
