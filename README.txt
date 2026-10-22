JOB & INTERNSHIP FINDER  (HTML/CSS/JS/Bootstrap + PHP + MySQL + Gemini API)

SETUP (XAMPP)
1. Unzip the folder "jobfinder" into  C:\xampp\htdocs\
2. Start Apache and MySQL in XAMPP.
3. Open http://localhost/phpmyadmin -> Import -> choose database.sql -> Go. The script creates missing tables, upgrades job-detail columns, and avoids duplicating the sample jobs when rerun.
4. Open config.php and paste your Gemini API key into GEMINI_API_KEY
   (get a free key at https://aistudio.google.com/apikey). Change DB_PASS if your MySQL has a password.
5. Visit http://localhost/jobfinder/setup.php ONCE to create the admin account, then delete setup.php.
6. Open http://localhost/jobfinder/

SETUP (DOCKER)
1. From this folder, run: docker compose up --build
2. Open http://localhost:8080/
3. The database is initialized from database.sql on first startup. To upgrade an existing database, run `docker compose exec -T db sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < database.sql`. User CVs remain in uploads/ and company logos are saved in images/.
4. Set GEMINI_API_KEY in config.php if you want to use the AI features.
5. To stop the app, run: docker compose down

Docker uses development-only database credentials by default. Set DB_USER, DB_PASS, DB_NAME, and MYSQL_ROOT_PASSWORD in a .env file before deployment. The database is stored in a Docker volume and is not removed by docker compose down.

ACCOUNTS
Admin:    admin@jobfinder.com / admin123
Employer/Seeker: use the Register page.

FEATURES
- Register/login (job seeker, employer, admin) with hashed passwords
- Profile, skills, CV upload
- Job/internship posting, search and filter (keyword, type, category, location, salary); browse popular job categories from the home page
- Online application + status tracking (Pending/Shortlisted/Rejected/Hired)
- Job postings include required location, optional salary details, and an optional JPG/PNG/WebP company logo saved in images/
- Skill-based job recommendation (recommend.php)
- AI Mock Test: Gemini-generated multiple-choice or spoken-answer questions, read-aloud voice interviewer with a 60-second answer timer per question, AI scoring and saved history; copy/paste activity alerts with an on-submit signal summary (mocktest.php)
- Employer dashboard: applicants, CV, mock test average score
- Admin panel: users, jobs, analytics

NOTES
- Chrome/Edge recommended for the voice features. Spoken tests read each question aloud, then start a 60-second answer countdown; spoken answers can be recorded with browser speech recognition or typed.
- The mock test counts browser copy/paste events, plays a short beep when supported, and shows counts after submission. These signals can have ordinary causes and do not reliably identify AI use or prove cheating.
- Never share your API key publicly (do not upload config.php to GitHub with the key inside).
