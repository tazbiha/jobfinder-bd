<?php require 'config.php'; need('employer'); $u=user(); $error=''; $form=$_POST;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $titleText=trim($_POST['title']??'');
  $company=trim($_POST['company']??'');
  $location=trim($_POST['location']??'');
  $salary=trim($_POST['salary_details']??'');
  $sourceUrl=trim($_POST['source_url']??'');
  $logoPath=null;
  if($titleText==='' || $company==='' || $location==='' || trim($_POST['description']??'')==='' || empty($_POST['deadline'])){
    $error='Please provide a job title, company, location, description, and deadline.';
  } elseif(($salaryLength=preg_match_all('/./us',$salary))===false || $salaryLength>150){
    $error='Salary details must be 150 characters or fewer.';
  } elseif($sourceUrl!=='' && (!filter_var($sourceUrl,FILTER_VALIDATE_URL) || !in_array(strtolower(parse_url($sourceUrl,PHP_URL_SCHEME)??''),['http','https'],true) || strlen($sourceUrl)>500)){
    $error='Please enter a valid HTTP or HTTPS source link under 500 characters.';
  } elseif(!empty($_FILES['company_logo']['name'])){
    $logo=$_FILES['company_logo'];
    if($logo['error']!==UPLOAD_ERR_OK || $logo['size']>2*1024*1024){
      $error='Company logo upload failed or exceeds the 2MB limit.';
    } else {
      $finfo=finfo_open(FILEINFO_MIME_TYPE);
      $mime=$finfo?finfo_file($finfo,$logo['tmp_name']):false;
      if($finfo) finfo_close($finfo);
      $extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
      if(!$mime || !isset($extensions[$mime])){
        $error='Company logo must be a JPG, PNG, or WebP image under 2MB.';
      } else {
        $logoPath='images/company_'.$u['id'].'_'.bin2hex(random_bytes(12)).'.'.$extensions[$mime];
        if(!move_uploaded_file($logo['tmp_name'],$logoPath)){
          $logoPath=null;
          $error='Could not save the company logo. Please try again.';
        }
      }
    }
  }
  if(!$error){
    x("INSERT INTO jobs(employer_id,title,company,type,category,location,salary_details,company_logo,source_url,skills,description,deadline) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)",
      [$u['id'],$titleText,$company,($_POST['type']??'')==='Internship'?'Internship':'Job',trim($_POST['category']??''),$location,$salary?:null,$logoPath,$sourceUrl?:null,trim($_POST['skills']??''),trim($_POST['description']??''),$_POST['deadline']]);
    flash('Posted successfully.'); header('Location: employer.php'); exit;
  }
}
$title='Post a Job'; require 'header.php'; ?>
<div class="card p-4"><h4>Post a job / internship</h4><?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post" enctype="multipart/form-data" class="row g-2">
<div class="col-md-6"><input class="form-control" name="title" value="<?= e($form['title']??'') ?>" placeholder="Job title" required></div>
<div class="col-md-6"><input class="form-control" name="company" value="<?= e($form['company']??$u['name']) ?>" placeholder="Company" required></div>
<div class="col-md-3"><select class="form-select" name="type"><option <?= ($form['type']??'Job')==='Job'?'selected':'' ?>>Job</option><option <?= ($form['type']??'')==='Internship'?'selected':'' ?>>Internship</option></select></div>
<div class="col-md-3"><select class="form-select" name="category"><option value="">Select category</option><?php foreach(job_categories() as $category): ?><option value="<?= e($category) ?>" <?= ($form['category']??'')===$category?'selected':'' ?>><?= e($category) ?></option><?php endforeach; ?><?php foreach(['IT','Admin','Accounting','Marketing'] as $category): ?><option value="<?= e($category) ?>" <?= ($form['category']??'')===$category?'selected':'' ?>><?= e($category) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><input class="form-control" name="location" value="<?= e($form['location']??'') ?>" placeholder="Job location" required></div>
<div class="col-md-3"><input class="form-control" type="date" name="deadline" value="<?= e($form['deadline']??'') ?>" required></div>
<div class="col-md-6"><input class="form-control" name="salary_details" value="<?= e($form['salary_details']??'') ?>" maxlength="150" placeholder="Salary (e.g. BDT 30,000-50,000/month or Negotiable)"></div>
<div class="col-md-6"><input class="form-control" type="url" name="source_url" value="<?= e($form['source_url']??'') ?>" maxlength="500" placeholder="Original job posting URL (optional)"></div>
<div class="col-md-6"><label class="form-label" for="companyLogo">Company logo</label><input id="companyLogo" class="form-control" type="file" name="company_logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-label="Upload company logo"><div class="form-text">Upload a logo from your device. It will be saved in the site’s images folder (JPG, PNG, or WebP; max 2MB).</div></div>
<div class="col-12"><input class="form-control" name="skills" value="<?= e($form['skills']??'') ?>" placeholder="Required skills, comma separated (php, mysql, ...)"></div>
<div class="col-12"><textarea class="form-control" name="description" rows="5" placeholder="Description, requirements" required><?= e($form['description']??'') ?></textarea></div>
<div class="col-12"><button class="btn btn-primary">Publish</button></div></form></div>
<?php require 'footer.php';
