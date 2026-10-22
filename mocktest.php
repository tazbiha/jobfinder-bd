<?php
require 'config.php';
need('seeker');
$u=user();
$err='';
$result=null;

$act=$_POST['action']??'';
if($act==='generate'){
  $topic=trim($_POST['topic']??''); $countChoice=$_POST['count']??'5'; $n=$countChoice==='all'?15:max(3,min(15,(int)$countChoice)); $lvl=$_POST['level']??'Intermediate';
  $mode=($_POST['mode']??'mcq')==='spoken'?'spoken':'mcq';
  if($mode==='spoken'){
    [$qs,$err]=gemini("Create $n descriptive spoken-answer questions for a job mock test on: \"$topic\". Difficulty: $lvl.
Return ONLY a JSON array. Each item: {\"question\":string,\"answer_guide\":string with key points expected in a strong answer,\"explanation\":short learning note}.");
    if(!$err) $qs=array_values(array_filter($qs,fn($z)=>isset($z['question'],$z['answer_guide'])));
  }else{
    [$qs,$err]=gemini("Create $n multiple-choice questions for a job mock test on: \"$topic\". Difficulty: $lvl.
Return ONLY a JSON array. Each item: {\"question\":string,\"options\":[4 strings],\"answer\":integer index 0-3 of correct option,\"explanation\":short string}.");
    if(!$err) $qs=array_values(array_filter($qs,fn($z)=>isset($z['question'],$z['options'],$z['answer']) && count($z['options'])==4));
  }
  if(!$err){
    if($qs) $_SESSION['quiz']=['topic'=>$topic,'mode'=>$mode,'qs'=>$qs,'ends_at'=>time()+count($qs)*60];
    else $err='AI returned no valid questions. Try again.';
  }
}
if($act==='submit' && !empty($_SESSION['quiz'])){
  $qz=$_SESSION['quiz']; $score=0; $rev=[];
  $copyCount=max(0,min(999,(int)($_POST['integrity_copy_count']??0)));
  $pasteCount=max(0,min(999,(int)($_POST['integrity_paste_count']??0)));
  if(($qz['mode']??'mcq')==='spoken'){
    $answers=[];
    foreach($qz['qs'] as $i=>$z){
      $answers[]=['question'=>$z['question'],'answer_guide'=>$z['answer_guide'],'answer'=>trim((string)($_POST["a$i"]??''))];
    }
    [$grades,$err]=gemini("Grade each spoken mock-test answer from 0 to 100 against its answer guide. Empty answers score 0. Be fair and concise.\n".json_encode($answers,JSON_UNESCAPED_UNICODE)."\nReturn ONLY a JSON array with one item per answer: {\"score\":integer 0-100,\"feedback\":string,\"model_answer\":string}.");
    if(!$err && (!is_array($grades) || count($grades)!==count($answers))) $err='AI returned incomplete scoring. Please submit again.';
    if(!$err){
      foreach($grades as $i=>$grade){
        $gradeScore=max(0,min(100,(int)($grade['score']??0)));
        $score+=$gradeScore;
        $rev[]=['z'=>$qz['qs'][$i],'answer'=>$answers[$i]['answer'],'score'=>$gradeScore,'feedback'=>$grade['feedback']??'','model_answer'=>$grade['model_answer']??''];
      }
      $score=(int)round($score/count($qz['qs']));
      x("INSERT INTO mock_results(user_id,topic,score,total) VALUES(?,?,?,100)",[$u['id'],$qz['topic'],$score]);
      $result=['score'=>$score,'total'=>100,'rev'=>$rev,'topic'=>$qz['topic'],'mode'=>'spoken','copy_count'=>$copyCount,'paste_count'=>$pasteCount];
      unset($_SESSION['quiz']);
    }else $_SESSION['quiz']['ends_at']=time()+60;
  }else{
    foreach($qz['qs'] as $i=>$z){ $pick=isset($_POST["q$i"])?(int)$_POST["q$i"]:-1; $ok=$pick===(int)$z['answer']; if($ok)$score++; $rev[]=['z'=>$z,'pick'=>$pick,'ok'=>$ok]; }
    x("INSERT INTO mock_results(user_id,topic,score,total) VALUES(?,?,?,?)",[$u['id'],$qz['topic'],$score,count($qz['qs'])]);
    $result=['score'=>$score,'total'=>count($qz['qs']),'rev'=>$rev,'topic'=>$qz['topic'],'mode'=>'mcq','copy_count'=>$copyCount,'paste_count'=>$pasteCount]; unset($_SESSION['quiz']);
  }
}
if(!empty($_SESSION['quiz']) && empty($_SESSION['quiz']['ends_at'])) $_SESSION['quiz']['ends_at']=time()+count($_SESSION['quiz']['qs'])*60;
$showQuizTimer=!empty($_SESSION['quiz']) && ($_SESSION['quiz']['mode']??'mcq')!=='spoken';
$quizEndsAt=$showQuizTimer?(int)$_SESSION['quiz']['ends_at']:0;
$quizSecondsLeft=$showQuizTimer?max(0,$quizEndsAt-time()):0;
$title='AI Mock Test'; require 'header.php'; ?>
<h4>AI Mock Test</h4>
<?php if($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
<?php if($result): $pct=round($result['score']/$result['total']*100); ?>
<div class="card p-4 mb-3 text-center"><h5><?= e($result['topic']) ?></h5><div class="display-4 fw-bold text-primary"><?= $result['score'] ?>/<?= $result['total'] ?></div>
<p><?= $pct>=80?'Excellent work!':($pct>=50?'Good, keep practicing.':'Needs more practice - review the explanations below.') ?> (<?= $pct ?>%)</p>
<div class="alert alert-secondary text-start mb-3"><strong>Test activity signals</strong><br>Copy events: <?= (int)$result['copy_count'] ?> &middot; Paste events: <?= (int)$result['paste_count'] ?><br><small>These browser signals can have ordinary causes and do not prove AI use or cheating.</small></div>
<a href="mocktest.php" class="btn btn-primary mx-auto">Take another test</a></div>
<?php foreach($result['rev'] as $i=>$r): $z=$r['z']; ?><div class="card p-3 mb-2 border-start border-4 border-<?= (($r['score']??($r['ok']?100:0))>=50)?'success':'danger' ?>">
<strong><?= $i+1 ?>. <?= e($z['question']) ?></strong>
<?php if(($result['mode']??'mcq')==='spoken'): ?>
<div class="mt-2"><strong>Your answer:</strong> <?= e($r['answer']?:'No answer provided') ?></div>
<div class="mt-2"><strong>AI score:</strong> <?= (int)$r['score'] ?>/100</div>
<div><?= e($r['feedback']) ?></div>
<small class="text-muted">Suggested answer: <?= e($r['model_answer']) ?></small>
<?php else: ?>
<div>Your answer: <?= $r['pick']>=0?e($z['options'][$r['pick']]):'<em>skipped</em>' ?></div>
<div class="text-success">Correct: <?= e($z['options'][(int)$z['answer']]) ?></div><small class="text-muted"><?= e($z['explanation']??'') ?></small>
<?php endif; ?></div><?php endforeach; ?>
<?php elseif(!empty($_SESSION['quiz'])): $qz=$_SESSION['quiz']; ?>
<form id="quizForm" method="post" data-mode="<?= e($qz['mode']??'mcq') ?>"><input type="hidden" name="action" value="submit">
<input type="hidden" id="integrityCopyCount" name="integrity_copy_count" value="0">
<input type="hidden" id="integrityPasteCount" name="integrity_paste_count" value="0">
<div class="row g-3 align-items-start">
<div class="col-lg-8">
<p class="text-muted">Topic: <?= e($qz['topic']) ?></p>
<p class="small text-muted" id="integrityNotice" role="status" aria-live="polite">Copy and paste events are counted during this test; a short beep may play. These signals do not determine whether AI was used.</p>
<?php if(($qz['mode']??'mcq')==='spoken'): ?>
<div id="spokenInterviewer" class="card p-3 mb-3" data-answer-seconds="60">
<div class="d-flex flex-wrap justify-content-between gap-2">
<strong id="spokenProgress" aria-live="polite">Question 1 of <?= count($qz['qs']) ?></strong>
<strong>Time left: <span id="spokenTimer">60</span>s</strong>
</div>
<p id="spokenStatus" class="small text-muted mt-2 mb-2" role="status" aria-live="polite">Start the interview when you are ready. The AI interviewer will read each question aloud.</p>
<div class="d-flex flex-wrap gap-2">
<button type="button" id="startSpokenInterview" class="btn btn-primary btn-sm">Start voice interview</button>
<button type="button" id="repeatSpokenQuestion" class="btn btn-outline-primary btn-sm" disabled>Repeat question</button>
<button type="button" id="nextSpokenQuestion" class="btn btn-outline-secondary btn-sm" disabled>Next question</button>
</div>
</div>
<?php endif; ?>
<?php foreach($qz['qs'] as $i=>$z): ?><div class="card p-3 mb-3<?= (($qz['mode']??'mcq')==='spoken'?' spoken-question':'') ?>"<?= (($qz['mode']??'mcq')==='spoken'?' data-question="'.e($z['question']).'"'.($i>0?' hidden':''):'') ?>><strong><?= $i+1 ?>. <?= e($z['question']) ?></strong>
<?php if(($qz['mode']??'mcq')==='spoken'): ?>
<label class="form-label mt-3" for="a<?= $i ?>">Your answer</label>
<textarea class="form-control mb-2" id="a<?= $i ?>" name="a<?= $i ?>" rows="3" placeholder="Speak or type your answer"></textarea>
<div class="d-flex align-items-center gap-2"><button type="button" class="btn btn-outline-primary btn-sm voice-answer" data-target="a<?= $i ?>" data-status="voiceStatus<?= $i ?>">Speak answer</button><span id="voiceStatus<?= $i ?>" class="small text-muted" role="status" aria-live="polite"></span></div>
<?php else: ?>
<?php foreach($z['options'] as $k=>$o): ?><label class="opt p-2 rounded"><input type="radio" name="q<?= $i ?>" value="<?= $k ?>"> <?= e($o) ?></label><?php endforeach; ?>
<?php endif; ?></div><?php endforeach; ?>
<button class="btn btn-primary">Submit &amp; see score</button>
</div>
<aside class="col-lg-4"><div class="mocktest-camera-panel sticky-top">
<video id="mocktestCamera" class="mocktest-camera" autoplay muted playsinline aria-label="Your camera preview"></video>
<p id="mocktestCameraStatus" class="small text-muted mb-0" role="status" aria-live="polite">Starting camera...</p>
</div></aside>
</div></form>
<script>
const quizForm=document.getElementById('quizForm');
const quizTimer=document.getElementById('quizTimer');
const cameraVideo=document.getElementById('mocktestCamera');
const cameraStatus=document.getElementById('mocktestCameraStatus');
let cameraStream=null;
let quizClock=null;
let timeoutSubmitted=false;
let activeRecognition=null;
let activeVoiceButton=null;
let integrityCopyCount=0;
let integrityPasteCount=0;
let integrityAudioContext=null;
const integrityNotice=document.getElementById('integrityNotice');
const playIntegrityBeep=()=>{
 const AudioContextClass=window.AudioContext||window.webkitAudioContext;
 if(!AudioContextClass){
  integrityNotice.textContent='Copy/paste events are counted, but this browser does not support the alert sound.';
  return;
 }
 try{
  if(!integrityAudioContext) integrityAudioContext=new AudioContextClass();
  const playTone=()=>{
   const oscillator=integrityAudioContext.createOscillator();
   const gain=integrityAudioContext.createGain();
   oscillator.frequency.value=880;
   gain.gain.value=0.08;
   oscillator.connect(gain);
   gain.connect(integrityAudioContext.destination);
   oscillator.start();
   oscillator.stop(integrityAudioContext.currentTime+0.12);
  };
  if(integrityAudioContext.state==='suspended'){
   integrityAudioContext.resume().then(playTone).catch(()=>{
    integrityNotice.textContent='Copy/paste events are counted, but the browser blocked the alert sound.';
   });
  }else playTone();
 }catch(error){
  integrityNotice.textContent='Copy/paste events are counted, but the alert sound could not be played.';
 }
};
document.addEventListener('copy',()=>{
 integrityCopyCount++;
 playIntegrityBeep();
},true);
document.addEventListener('paste',()=>{
 integrityPasteCount++;
 playIntegrityBeep();
},true);
quizForm.addEventListener('submit',()=>{
 document.getElementById('integrityCopyCount').value=String(integrityCopyCount);
 document.getElementById('integrityPasteCount').value=String(integrityPasteCount);
});

const updateQuizTimer=()=>{
 if(!quizTimer)return;
 const remaining=Math.max(0,Number(quizTimer.dataset.endsAt)*1000-Date.now());
 const seconds=Math.ceil(remaining/1000);
 quizTimer.textContent=String(Math.floor(seconds/60)).padStart(2,'0')+':'+String(seconds%60).padStart(2,'0');
 if(seconds===0&&!timeoutSubmitted){
  timeoutSubmitted=true;
  if(quizClock) clearInterval(quizClock);
  quizForm.requestSubmit();
 }
};
if(quizTimer){
 updateQuizTimer();
 if(!timeoutSubmitted) quizClock=setInterval(updateQuizTimer,250);
}

const startCamera=async()=>{
 if(!navigator.mediaDevices?.getUserMedia){
  cameraStatus.textContent='Camera access is not supported in this browser.';
  return;
 }
 try{
  cameraStream=await navigator.mediaDevices.getUserMedia({video:{facingMode:'user'},audio:false});
  cameraVideo.srcObject=cameraStream;
  cameraStatus.textContent='Camera is on for this mock test.';
 }catch(error){
  cameraStatus.textContent='Camera access was denied. Allow camera permission and reload the test.';
 }
};
startCamera();

if(quizForm.dataset.mode==='spoken'){
 const SpeechRecognition=window.SpeechRecognition||window.webkitSpeechRecognition;
 const spokenInterviewer=document.getElementById('spokenInterviewer');
 const spokenQuestions=[...document.querySelectorAll('.spoken-question')];
 const spokenTimer=document.getElementById('spokenTimer');
 const spokenStatus=document.getElementById('spokenStatus');
 const spokenProgress=document.getElementById('spokenProgress');
 const startSpokenInterview=document.getElementById('startSpokenInterview');
 const repeatSpokenQuestion=document.getElementById('repeatSpokenQuestion');
 const nextSpokenQuestion=document.getElementById('nextSpokenQuestion');
 const answerSeconds=Number(spokenInterviewer.dataset.answerSeconds);
 let currentSpokenQuestion=0;
 let answerClock=null;
 let answerDeadline=0;
 let speechRequest=0;

 const cancelQuestionSpeech=()=>{
  speechRequest++;
  if('speechSynthesis' in window) window.speechSynthesis.cancel();
 };
 const startAnswerClock=()=>{
  if(answerClock)return;
  answerDeadline=Date.now()+answerSeconds*1000;
  const updateAnswerClock=()=>{
   const seconds=Math.max(0,Math.ceil((answerDeadline-Date.now())/1000));
   spokenTimer.textContent=String(seconds);
   if(seconds===0){
    clearInterval(answerClock);
    answerClock=null;
    if(currentSpokenQuestion<spokenQuestions.length-1) showSpokenQuestion(currentSpokenQuestion+1);
    else quizForm.requestSubmit();
   }
  };
  updateAnswerClock();
  answerClock=setInterval(updateAnswerClock,250);
 };
 const readSpokenQuestion=(startTimerAfterReading)=>{
  const question=spokenQuestions[currentSpokenQuestion].dataset.question;
  if(!('speechSynthesis' in window)||!('SpeechSynthesisUtterance' in window)){
   spokenStatus.textContent='Read-aloud is unavailable in this browser. Your answer time has started.';
   if(startTimerAfterReading) startAnswerClock();
   return;
  }
  cancelQuestionSpeech();
  const request=speechRequest;
  const utterance=new SpeechSynthesisUtterance(question);
  utterance.lang='en-US';
  utterance.onend=()=>{
   if(request!==speechRequest)return;
   if(startTimerAfterReading){
    spokenStatus.textContent='Your answer time has started. Speak or type your response.';
    startAnswerClock();
   }else spokenStatus.textContent='Question repeated. Your answer countdown is still running.';
  };
  utterance.onerror=()=>{
   if(request!==speechRequest)return;
   spokenStatus.textContent='The question could not be read aloud. Your answer time has started.';
   if(startTimerAfterReading) startAnswerClock();
  };
  spokenStatus.textContent=startTimerAfterReading?'AI interviewer is asking the question...':'Repeating the question...';
  window.speechSynthesis.speak(utterance);
 };
 const showSpokenQuestion=index=>{
  if(answerClock){clearInterval(answerClock);answerClock=null}
  cancelQuestionSpeech();
  if(activeRecognition){activeRecognition.stop();activeRecognition=null;activeVoiceButton=null}
  currentSpokenQuestion=index;
  spokenQuestions.forEach((question,i)=>{question.hidden=i!==index});
  spokenProgress.textContent=`Question ${index+1} of ${spokenQuestions.length}`;
  spokenTimer.textContent=String(answerSeconds);
  nextSpokenQuestion.textContent=index===spokenQuestions.length-1?'Submit answers':'Next question';
  readSpokenQuestion(true);
 };
 startSpokenInterview.addEventListener('click',()=>{
  startSpokenInterview.hidden=true;
  repeatSpokenQuestion.disabled=false;
  nextSpokenQuestion.disabled=false;
  showSpokenQuestion(0);
 });
 repeatSpokenQuestion.addEventListener('click',()=>readSpokenQuestion(!answerClock));
 nextSpokenQuestion.addEventListener('click',()=>{
  if(currentSpokenQuestion<spokenQuestions.length-1) showSpokenQuestion(currentSpokenQuestion+1);
  else quizForm.requestSubmit();
 });
 quizForm.addEventListener('submit',()=>{
  timeoutSubmitted=true;
  if(answerClock) clearInterval(answerClock);
  cancelQuestionSpeech();
  if(activeRecognition) activeRecognition.stop();
 });

 document.querySelectorAll('.voice-answer').forEach(button=>{
  button.addEventListener('click',()=>{
   const status=document.getElementById(button.dataset.status);
   if(!SpeechRecognition){status.textContent='Speech recognition is unavailable. You can type your answer.';return}
   if(activeRecognition){
    const sameButton=activeVoiceButton===button;
    activeRecognition.stop();
    activeRecognition=null;
    activeVoiceButton=null;
    if(sameButton)return;
   }
   const recognition=new SpeechRecognition();
   const answerField=document.getElementById(button.dataset.target);
   activeRecognition=recognition;
   activeVoiceButton=button;
   recognition.lang='en-US';
   recognition.continuous=true;
   recognition.interimResults=true;
   button.textContent='Stop speaking';
   status.textContent='Listening...';
   recognition.onresult=event=>{
    let finalized='';
    let interim='';
    for(let index=event.resultIndex;index<event.results.length;index++){
     const phrase=event.results[index][0].transcript;
     if(event.results[index].isFinal) finalized+=phrase+' ';
     else interim+=phrase;
    }
    if(finalized) answerField.value=[answerField.value.trim(),finalized.trim()].filter(Boolean).join(' ');
    status.textContent=interim?'Hearing: '+interim:'Answer captured.';
   };
   recognition.onerror=()=>{status.textContent='Speech recognition failed. You can type your answer.'};
   recognition.onend=()=>{
    button.textContent='Speak answer';
    if(activeVoiceButton===button){activeRecognition=null;activeVoiceButton=null}
    if(!status.textContent.startsWith('Speech recognition failed')) status.textContent=answerField.value?'Answer captured.':'Stopped listening.';
   };
   try{recognition.start()}catch(error){
    activeRecognition=null;activeVoiceButton=null;button.textContent='Speak answer';
    status.textContent='Could not start speech recognition. You can type your answer.';
   }
  });
 });
}

window.addEventListener('pagehide',()=>{
 if(cameraStream) cameraStream.getTracks().forEach(track=>track.stop());
 if(activeRecognition) activeRecognition.stop();
},{once:true});
</script>
<?php else: ?>
<div class="card p-4"><form method="post"><input type="hidden" name="action" value="generate">
<label class="form-label">Topic / job role</label><input name="topic" class="form-control mb-2" placeholder="e.g. PHP & MySQL, Aptitude, English grammar, Marketing" required>
<div class="row"><div class="col-md-4"><label class="form-label">Answer mode</label><select name="mode" class="form-select mb-2"><option value="mcq">Multiple choice</option><option value="spoken">Spoken answer</option></select></div>
<div class="col-md-4"><label class="form-label">Questions</label><select name="count" class="form-select mb-2"><option value="5" selected>5</option><option value="10">10</option><option value="15">15</option><option value="all">All (15)</option></select></div>
<div class="col-md-4"><label class="form-label">Level</label><select name="level" class="form-select mb-2"><option>Beginner</option><option selected>Intermediate</option><option>Advanced</option></select></div></div>
<button class="btn btn-primary mt-2" onclick="this.innerText='Generating with Gemini...'">Generate test</button></form></div>
<?php endif; require 'footer.php';
