<?php require 'config.php'; $title='Home';
$jobs=q("SELECT * FROM jobs ORDER BY created_at DESC LIMIT 6"); require 'header.php'; ?>
<section class="hero mb-4" aria-labelledby="homeTitle">
<div class="hero-copy">
<span class="hero-eyebrow">Your next chapter starts here</span>
<h1 id="homeTitle" class="fw-bold">Find your next job or internship</h1>
<p class="lead">Search opportunities, apply online, and practice with AI multiple-choice and spoken-answer mock tests.</p>
<form action="jobs.php" class="hero-search"><input name="q" class="form-control form-control-lg" placeholder="Job title, skill or company" aria-label="Search jobs by title, skill or company">
<button class="btn btn-warning btn-lg">Search</button></form>
</div>
<svg class="workplace-scene" viewBox="0 0 640 390" role="img" aria-label="Animated illustration of a computer workstation with a job opportunity on screen">
<defs>
<linearGradient id="screenGradient" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#122A43"/><stop offset="1" stop-color="#10111C"/></linearGradient>
<linearGradient id="deskGradient" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#34445C"/><stop offset="1" stop-color="#1B2534"/></linearGradient>
<linearGradient id="cardGradient" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#263B56"/><stop offset="1" stop-color="#1A2639"/></linearGradient>
</defs>
<rect x="20" y="22" width="600" height="270" rx="22" fill="#101722" stroke="#35445A"/>
<path d="M38 220h564" stroke="#26384C" stroke-width="2"/>
<g fill="#263C52" opacity=".7">
<rect x="55" y="150" width="38" height="70" rx="4"/><rect x="98" y="119" width="45" height="101" rx="4"/>
<rect x="500" y="136" width="48" height="84" rx="4"/><rect x="554" y="165" width="34" height="55" rx="4"/>
</g>
<g fill="#38BDF8" opacity=".55">
<circle cx="68" cy="168" r="2"/><circle cx="82" cy="168" r="2"/><circle cx="111" cy="137" r="2"/><circle cx="128" cy="137" r="2"/>
<circle cx="512" cy="154" r="2"/><circle cx="532" cy="154" r="2"/><circle cx="565" cy="181" r="2"/>
</g>
<g>
<rect x="135" y="49" width="350" height="225" rx="13" fill="#27364B" stroke="#8293AA" stroke-width="2"/>
<rect x="147" y="61" width="326" height="196" rx="7" fill="url(#screenGradient)"/>
<rect x="147" y="61" width="326" height="24" rx="7" fill="#202D40"/>
<circle cx="161" cy="73" r="3" fill="#FB7185"/><circle cx="172" cy="73" r="3" fill="#FBBF24"/><circle cx="183" cy="73" r="3" fill="#34D399"/>
<rect x="163" y="99" width="111" height="8" rx="4" fill="#F8FAFC" opacity=".87"/>
<rect x="163" y="115" width="166" height="5" rx="2.5" fill="#94A3B8" opacity=".55"/>
<rect x="163" y="135" width="137" height="82" rx="7" fill="#17283C"/>
<path d="m174 197 22-20 19 9 20-28 17 11 30-21" fill="none" stroke="#38BDF8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="workplace-chart"/>
<path d="M174 204h113" stroke="#455A72" stroke-width="2"/>
<rect x="310" y="135" width="145" height="35" rx="7" fill="#17283C"/>
<rect x="321" y="145" width="67" height="5" rx="2.5" fill="#DCE8F5" opacity=".8"/>
<rect x="321" y="156" width="112" height="4" rx="2" fill="#71849B" opacity=".65"/>
<rect x="310" y="179" width="68" height="38" rx="7" fill="#17283C"/>
<rect x="387" y="179" width="68" height="38" rx="7" fill="#17283C"/>
<circle cx="330" cy="198" r="8" fill="#38BDF8" opacity=".85"/>
<path d="M402 203h36" stroke="#A78BFA" stroke-width="5" stroke-linecap="round"/>
<rect x="275" y="274" width="70" height="16" rx="5" fill="#65758B"/>
<path d="M290 289h40l17 14h-74z" fill="#8796AA"/>
</g>
<g class="workplace-float">
<rect x="390" y="95" width="185" height="82" rx="12" fill="url(#cardGradient)" stroke="#5D7390"/>
<circle cx="411" cy="118" r="10" fill="#00ABF0" opacity=".2"/>
<path d="M407 118h8m-4-4v8" stroke="#38BDF8" stroke-width="2" stroke-linecap="round"/>
<rect x="429" y="113" width="112" height="6" rx="3" fill="#F8FAFC" opacity=".9"/>
<rect x="407" y="137" width="137" height="5" rx="2.5" fill="#94A3B8" opacity=".6"/>
<rect x="407" y="151" width="82" height="5" rx="2.5" fill="#38BDF8" opacity=".85"/>
<circle cx="558" cy="157" r="5" fill="#34D399"/>
</g>
<path d="M39 303h562l-24 21H63z" fill="url(#deskGradient)" stroke="#52647A" stroke-width="2"/>
<path d="M83 324v40m474-40v40" stroke="#26364A" stroke-width="9" stroke-linecap="round"/>
<g>
<path d="M93 299c-1-37-25-34-18-63 19 9 28 21 25 40 8-28 29-30 37-45 7 31-3 43-28 60 28-17 43-9 53-22-1 21-15 33-39 37z" fill="#34D399" opacity=".9"/>
<path d="M102 299v-43m1 38 27-32m-30 23-17-24" stroke="#16795A" stroke-width="3" stroke-linecap="round"/>
<path d="M84 296h51l-5 20H89z" fill="#E87961"/>
</g>
<g>
<path d="M511 282h42l-4 27h-34z" fill="#E2E8F0"/>
<path d="M553 288h8a9 9 0 0 1 0 17h-9" fill="none" stroke="#E2E8F0" stroke-width="5"/>
<path d="M518 278c-4-9 6-10 2-18m14 18c-4-9 6-10 2-18" fill="none" stroke="#38BDF8" stroke-width="2" stroke-linecap="round" opacity=".8"/>
</g>
<g class="workplace-cursor">
<path d="m468 205 21 20-10 1 6 12-5 2-6-12-6 7z" fill="#F8FAFC" stroke="#101722" stroke-width="2"/>
</g>
<circle class="workplace-sparkle sparkle-one" cx="541" cy="65" r="4" fill="#38BDF8"/>
<circle class="workplace-sparkle sparkle-two" cx="104" cy="81" r="3" fill="#A78BFA"/>
</svg>
</section>
<section class="mb-4" aria-labelledby="popularCategories">
<h4 id="popularCategories" class="mb-3">Discover jobs by category</h4>
<div class="d-flex flex-wrap gap-2">
<?php foreach(job_categories() as $category): ?><a class="btn btn-outline-primary btn-sm" href="jobs.php?category=<?= rawurlencode($category) ?>"><?= e($category) ?></a><?php endforeach; ?>
</div>
</section>
<section class="latest-opportunities mb-4" aria-labelledby="latestOpportunities">
<h4 id="latestOpportunities" class="mb-3">Latest opportunities</h4><div class="row g-3">
<?php foreach($jobs as $i=>$j): ?><div class="col-md-4 opportunity-item" style="--opportunity-delay: <?= (int)($i * 120) ?>ms"><div class="card h-100 p-3">
<div class="d-flex align-items-center gap-2 mb-2"><?= company_logo($j) ?><span class="text-muted"><?= e($j['company']) ?></span></div>
<span class="badge bg-<?= $j['type']=='Job'?'primary':'success' ?> badge-type mb-2 align-self-start"><?= e($j['type']) ?></span>
<h5><?= e($j['title']) ?></h5><div class="text-muted"><?= e($j['location']) ?> &middot; <?= e($j['category']?:'Other') ?></div>
<div class="small mt-2"><strong>Salary:</strong> <?= e($j['salary_details']?:'Not disclosed') ?></div>
<a href="job.php?id=<?= $j['id'] ?>" class="btn btn-outline-primary btn-sm mt-3 align-self-start">View details</a></div></div><?php endforeach; ?></div>
</section>
<?php require 'footer.php';
