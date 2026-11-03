<?php
require_once 'config.php';
require_role(['student']);
verify_csrf();
$u = current_user();
$error = '';

// Add the plan-details field automatically for existing installations.
$columns = db()->query("SHOW COLUMNS FROM career_profiles LIKE 'plan_details'")->fetch();
if (!$columns) {
    db()->exec("ALTER TABLE career_profiles ADD COLUMN plan_details LONGTEXT NULL AFTER strengths");
}
$postShsColumn = db()->query("SHOW COLUMNS FROM career_profiles LIKE 'post_shs_plan'")->fetch();
if (!$postShsColumn) {
    db()->exec("ALTER TABLE career_profiles ADD COLUMN post_shs_plan LONGTEXT NULL AFTER plan_details");
}

$questions = [
    'Which school activities do you enjoy most?' => [
        'Science and experiments'=>'Science & Technology', 'Helping classmates'=>'Health & Human Services',
        'Drawing, writing, or performing'=>'Arts & Communication', 'Leading a group or planning events'=>'Business & Leadership',
        'Building or fixing things'=>'Engineering & Skilled Trades', 'Working with computers'=>'Information Technology'
    ],
    'Which task sounds most interesting?' => [
        'Solving number or logic problems'=>'Science & Technology', 'Listening and helping someone'=>'Health & Human Services',
        'Creating a design or story'=>'Arts & Communication', 'Organizing a project or budget'=>'Business & Leadership',
        'Making or repairing equipment'=>'Engineering & Skilled Trades', 'Developing a website or app'=>'Information Technology'
    ],
    'What kind of work environment appeals to you?' => [
        'Laboratory or research setting'=>'Science & Technology', 'School, clinic, or community'=>'Health & Human Services',
        'Studio, media, or creative space'=>'Arts & Communication', 'Office or enterprise'=>'Business & Leadership',
        'Workshop or construction site'=>'Engineering & Skilled Trades', 'Technology company'=>'Information Technology'
    ]
];
$careerMap = [
 'Science & Technology'=>['Science, mathematics, laboratory work, research, agriculture, and environmental studies.','Explore STEM subjects, science fairs, research activities, and practical experiments.'],
 'Health & Human Services'=>['Education, psychology, social work, nursing, public health, and community service.','Build communication, empathy, teamwork, and responsible helping skills.'],
 'Arts & Communication'=>['Communication, multimedia arts, design, writing, journalism, and performing arts.','Create a portfolio and join school publication or creative activities.'],
 'Business & Leadership'=>['Business management, accounting, entrepreneurship, marketing, and public administration.','Practice budgeting, project planning, leadership, and presentation skills.'],
 'Engineering & Skilled Trades'=>['Engineering, architecture, construction, technical-vocational programs, and applied technology.','Try practical projects and strengthen mathematics and problem-solving skills.'],
 'Information Technology'=>['Information systems, computer science, software development, networking, and cybersecurity.','Practice digital literacy, coding fundamentals, and responsible technology use.']
];
$emptyPlan = ['career'=>'','course'=>'','subjects'=>'','skills'=>'','activities'=>'','next_step'=>'','timeline'=>'','support'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'post_shs_plan') {
    $postShsQuestions = [
        'path' => 'What do you plan to do after graduating from Senior High School?',
        'course' => 'What college course or degree program are you interested in taking?',
        'school' => 'What university or college would you like to attend?',
        'career' => 'What career or profession do you want to pursue in the future?',
        'reason' => 'Why did you choose this course or career path?',
        'skills' => 'What skills, talents, or interests do you have that can help you achieve your career goals?',
        'challenges' => 'What challenges do you think you might encounter while pursuing your chosen course or career?',
        'preparation' => 'How do you plan to prepare yourself financially, academically, and personally for your future?',
        'future' => 'Where do you see yourself five to ten years from now?'
    ];
    $newAnswers = [];
    foreach ($postShsQuestions as $key => $question) $newAnswers[$key] = trim($_POST['post_shs'][$key] ?? '');
    foreach (['course','school','career','reason','skills'] as $requiredKey) {
        if ($newAnswers[$requiredKey] === '') { $error = 'Please complete required questions 2–6 before saving your My Career Plan.'; break; }
    }
    if (!$error) {
        $existing = db()->prepare('SELECT id FROM career_profiles WHERE student_id=?');
        $existing->execute([$u['id']]);
        $json = json_encode($newAnswers, JSON_UNESCAPED_UNICODE);
        if ($existing->fetchColumn()) {
            db()->prepare('UPDATE career_profiles SET post_shs_plan=?, updated_at=CURRENT_TIMESTAMP WHERE student_id=?')->execute([$json,$u['id']]);
        } else {
            $blankLegacy = json_encode(['career'=>'','course'=>'','subjects'=>'','skills'=>'','activities'=>'','next_step'=>'','timeline'=>'','support'=>'']);
            db()->prepare('INSERT INTO career_profiles(student_id,interest_area,answers,goal,strengths,plan_details,post_shs_plan) VALUES(?,?,?,?,?,?,?)')->execute([$u['id'],'Not yet explored','[]','','',$blankLegacy,$json]);
        }
        flash('success', 'Your My Career Plan has been saved. You can return anytime to review or update your answers.');
        redirect('career.php#my-career-plan');
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = []; $scores = [];
    foreach ($questions as $i=>$q) {
        $a = $_POST['q'.$i] ?? '';
        if (!array_key_exists($a, $q)) { $error = 'Please answer all three career-interest questions.'; break; }
        $answers[] = $a; $area = $q[$a]; $scores[$area] = ($scores[$area] ?? 0) + 1;
    }
    if (!$error) {
        arsort($scores); $area = array_key_first($scores);
        $goal = trim($_POST['goal'] ?? ''); $strength = trim($_POST['strength'] ?? '');
        $plan = [];
        foreach ($emptyPlan as $key=>$default) { $plan[$key] = trim($_POST['plan'][$key] ?? ''); }
        $planJson = json_encode($plan, JSON_UNESCAPED_UNICODE);
        $sql = 'INSERT INTO career_profiles(student_id,interest_area,answers,goal,strengths,plan_details) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE interest_area=VALUES(interest_area),answers=VALUES(answers),goal=VALUES(goal),strengths=VALUES(strengths),plan_details=VALUES(plan_details),updated_at=CURRENT_TIMESTAMP';
        $stmt = db()->prepare($sql); $stmt->execute([$u['id'],$area,json_encode($answers),$goal,$strength,$planJson]);
        flash('success', 'Your career plan has been saved. You can return anytime to review or update it.');
        redirect('career.php');
    }
}
$stmt = db()->prepare('SELECT * FROM career_profiles WHERE student_id=?');
$stmt->execute([$u['id']]); $profile = $stmt->fetch();
$savedAnswers = $profile ? (json_decode($profile['answers'] ?? '[]', true) ?: []) : [];
$postShsAnswers = $profile && !empty($profile['post_shs_plan']) ? (json_decode($profile['post_shs_plan'], true) ?: []) : [];
$postShsQuestions = [
 'path'=>'What do you plan to do after graduating from Senior High School?',
 'course'=>'What college course or degree program are you interested in taking?',
 'school'=>'What university or college would you like to attend?',
 'career'=>'What career or profession do you want to pursue in the future?',
 'reason'=>'Why did you choose this course or career path?',
 'skills'=>'What skills, talents, or interests do you have that can help you achieve your career goals?',
 'challenges'=>'What challenges do you think you might encounter while pursuing your chosen course or career?',
 'preparation'=>'How do you plan to prepare yourself financially, academically, and personally for your future?',
 'future'=>'Where do you see yourself five to ten years from now?'
];
$plan = $emptyPlan;
if ($profile && !empty($profile['plan_details'])) { $decoded = json_decode($profile['plan_details'], true); if (is_array($decoded)) $plan = array_merge($plan, $decoded); }
$pageTitle = 'My Career Plan'; include 'header.php';
?>
<style>
.career-hero{background:linear-gradient(120deg,#092f9e,#1764dc);color:#fff;border-radius:22px;padding:30px;box-shadow:0 12px 30px #123b8720}.career-hero p{color:#e3ebff;max-width:760px}.career-step{border:1px solid #e4eaf5;border-radius:17px;padding:22px;background:#fff;margin-bottom:18px}.career-step-number{display:inline-grid;place-items:center;width:34px;height:34px;border-radius:50%;background:#e9efff;color:#1648b8;font-weight:800;margin-right:9px}.career-field{background:#f8faff;border:1px solid #e3eaf7;border-radius:12px;padding:15px;height:100%}.career-field label{font-weight:700;margin-bottom:7px}.career-field .form-control{border-color:#dce4f2}.plan-label{font-size:.82rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.04em}.plan-value{white-space:pre-wrap;color:#26354b}.plan-progress{height:9px;background:#e8edf7;border-radius:20px;overflow:hidden}.plan-progress span{display:block;height:100%;background:#2361d5;border-radius:20px}
</style>
<section class="career-hero mb-4"><span class="small fw-bold text-uppercase" style="letter-spacing:.12em">Student Development • Career Exploration</span><h1 class="fw-bold mt-2 mb-2">My Career Plan</h1><p class="mb-3">Discover what interests you, set a direction, and create small steps toward your future. Your plan can change as you learn more about yourself and the opportunities available.</p><div class="d-flex flex-wrap gap-2"><span class="badge rounded-pill text-bg-light text-primary px-3 py-2">Explore</span><span class="badge rounded-pill text-bg-light text-primary px-3 py-2">Set Goals</span><span class="badge rounded-pill text-bg-light text-primary px-3 py-2">Take Action</span></div></section>
<section id="my-career-plan" class="career-step">
 <div class="mb-3"><span class="career-step-number">★</span><span class="h5 fw-bold">My Career Plan</span><p class="text-secondary mt-2 mb-0">What do you plan to do after graduating from Senior High School? Questions 2–6 are required; questions 1 and 7–9 are optional.</p></div>
 <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="form_action" value="post_shs_plan">
 <?php $postShsIndex=0; foreach($postShsQuestions as $key=>$question): $postShsIndex++; ?>
 <div class="mb-3"><label class="form-label fw-semibold" for="postShs_<?= e($key) ?>"><?= $postShsIndex ?>. <?= e($question) ?><?php if(in_array($key,['course','school','career','reason','skills'],true)): ?> <span class="text-danger">*</span><span class="small text-secondary">(Required)</span><?php else: ?> <span class="small text-secondary">(Optional)</span><?php endif; ?></label>
 <?php if($key==='path'): ?><select class="form-select" id="postShs_<?= e($key) ?>" name="post_shs[<?= e($key) ?>]"><option value="">Select an option (optional)</option><?php foreach(['Pursue a college degree','Take technical-vocational training','Start a business','Find employment','Still deciding','Other'] as $choice): ?><option value="<?= e($choice) ?>" <?= (($postShsAnswers[$key]??'')===$choice)?'selected':'' ?>><?= e($choice) ?></option><?php endforeach; ?></select>
 <?php else: ?><textarea class="form-control" id="postShs_<?= e($key) ?>" name="post_shs[<?= e($key) ?>]" rows="2" maxlength="2000" placeholder="Write your answer here..." <?= in_array($key,['course','school','career','reason','skills'],true)?'required':'' ?>><?= e($postShsAnswers[$key]??'') ?></textarea><?php endif; ?></div>
 <?php endforeach; ?>
 <div class="d-flex flex-wrap gap-2 align-items-center mt-4"><button type="submit" class="btn btn-primary btn-lg px-4">Save My Career Plan</button><span class="small text-secondary">Your saved answers remain available and can be updated anytime.</span></div></form>
</section>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<?php if($profile): ?>
<section class="career-step mb-4"><div class="d-flex justify-content-between flex-wrap gap-2 align-items-center"><div><span class="badge text-bg-success mb-2">Saved career profile</span><h2 class="h4 fw-bold mb-1">Your current direction: <?= e($profile['interest_area']) ?></h2><p class="text-secondary mb-0"><?= e($careerMap[$profile['interest_area']][0] ?? 'Explore related learning opportunities.') ?></p></div><a href="#edit-plan" class="btn btn-primary">Update my plan</a></div><hr><p class="mb-1 fw-semibold">Suggested ways to explore</p><p class="text-secondary mb-0"><?= e($careerMap[$profile['interest_area']][1] ?? 'Discuss your options with a guidance counselor.') ?></p></section>
<section class="career-step"><h2 class="h4 fw-bold mb-3">My Career Plan Summary</h2><div class="row g-3">
<?php $summary = ['Career or job I want to explore'=>'career','Course or training I want to learn about'=>'course','Subjects I want to focus on'=>'subjects','Skills I want to develop'=>'skills','Activities or experiences I can try'=>'activities','My next small step'=>'next_step','Target timeline'=>'timeline','People who can support me'=>'support']; foreach($summary as $label=>$key): ?><div class="col-md-6"><div class="career-field"><div class="plan-label"><?= e($label) ?></div><div class="plan-value mt-2"><?= nl2br(e($plan[$key] ?: 'Not filled in yet')) ?></div></div></div><?php endforeach; ?>
<div class="col-md-6"><div class="career-field"><div class="plan-label">My personal goal</div><div class="plan-value mt-2"><?= nl2br(e($profile['goal'] ?: 'Not added yet')) ?></div></div></div><div class="col-md-6"><div class="career-field"><div class="plan-label">Strengths I want to develop</div><div class="plan-value mt-2"><?= nl2br(e($profile['strengths'] ?: 'Not added yet')) ?></div></div></div></div><p class="small text-secondary mt-3 mb-0">Last updated: <?= e($profile['updated_at']) ?></p></section>
<?php endif; ?>
<div id="edit-plan" class="career-step"><div class="mb-4"><p class="text-secondary mt-2 mb-0">Choose one answer for each question. This is a reflection activity, not a formal psychological test or a final career recommendation.</p></div>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<?php foreach($questions as $i=>$q): ?><fieldset class="mb-4"><legend class="fs-6 fw-bold"><?= e(($i+1).'. '.array_keys($questions)[$i]) ?></legend><div class="row g-2"><?php foreach($q as $option=>$area): ?><div class="col-md-6"><label class="choice h-100"><input type="radio" name="q<?= $i ?>" value="<?= e($option) ?>" required <?= (($savedAnswers[$i]??'')===$option)?'checked':'' ?>> <span><?= e($option) ?></span></label></div><?php endforeach; ?></div></fieldset><?php endforeach; ?>
<hr class="my-4"><div class="mb-4"><span class="career-step-number">2</span><span class="h5 fw-bold">Set your personal goals</span><p class="text-secondary mt-2">Think about what you want to achieve in school or in your future career.</p><label class="form-label fw-semibold">My personal goal</label><textarea class="form-control" name="goal" rows="2" maxlength="1000" placeholder="Example: I want to learn more about careers in technology and improve my problem-solving skills."><?= e($profile['goal']??'') ?></textarea><label class="form-label fw-semibold mt-3">Strengths or skills I want to improve</label><textarea class="form-control" name="strength" rows="2" maxlength="1000" placeholder="Example: Communication, teamwork, creativity, computer skills, or time management."><?= e($profile['strengths']??'') ?></textarea></div>
<hr class="my-4"><div class="mb-3"><span class="career-step-number">3</span><span class="h5 fw-bold">Build your action plan</span><p class="text-secondary mt-2">Fill in what you already know. You may leave fields blank and update them later.</p></div>
<div class="row g-3">
<div class="col-md-6"><div class="career-field"><label for="pCareer">Career or job I want to explore</label><input id="pCareer" class="form-control" name="plan[career]" maxlength="180" value="<?= e($plan['career']) ?>" placeholder="Example: Civil engineer, teacher, chef"></div></div>
<div class="col-md-6"><div class="career-field"><label for="pCourse">Course, strand, or training I want to learn about</label><input id="pCourse" class="form-control" name="plan[course]" maxlength="180" value="<?= e($plan['course']) ?>" placeholder="Example: BS Information Technology"></div></div>
<div class="col-md-6"><div class="career-field"><label for="pSubjects">Subjects I want to focus on</label><textarea id="pSubjects" class="form-control" name="plan[subjects]" rows="2" maxlength="1000" placeholder="List subjects that may help you prepare."><?= e($plan['subjects']) ?></textarea></div></div>
<div class="col-md-6"><div class="career-field"><label for="pSkills">Skills I want to develop</label><textarea id="pSkills" class="form-control" name="plan[skills]" rows="2" maxlength="1000" placeholder="Example: Public speaking, research, digital literacy."><?= e($plan['skills']) ?></textarea></div></div>
<div class="col-md-6"><div class="career-field"><label for="pActivities">Activities or experiences I can try</label><textarea id="pActivities" class="form-control" name="plan[activities]" rows="2" maxlength="1000" placeholder="Example: Join a school club, attend a career talk, make a sample project."><?= e($plan['activities']) ?></textarea></div></div>
<div class="col-md-6"><div class="career-field"><label for="pNext">My next small step</label><textarea id="pNext" class="form-control" name="plan[next_step]" rows="2" maxlength="1000" placeholder="Example: Research three colleges offering my course."><?= e($plan['next_step']) ?></textarea></div></div>
<div class="col-md-6"><div class="career-field"><label for="pTimeline">When do I plan to do it?</label><input id="pTimeline" class="form-control" name="plan[timeline]" maxlength="120" value="<?= e($plan['timeline']) ?>" placeholder="Example: This month / Before enrollment"></div></div>
<div class="col-md-6"><div class="career-field"><label for="pSupport">Who can support or guide me?</label><input id="pSupport" class="form-control" name="plan[support]" maxlength="180" value="<?= e($plan['support']) ?>" placeholder="Example: Parent/guardian, teacher, guidance counselor"></div></div>
</div><div class="d-flex flex-wrap gap-2 align-items-center mt-4"><button type="submit" class="btn btn-primary btn-lg px-4">Save My Career Plan</button><span class="small text-secondary">You can edit your answers and action plan anytime.</span></div></form></div>
<section class="career-step"><h2 class="h5 fw-bold">4. Review and keep improving</h2><p class="text-secondary">A useful career plan is flexible. Review it when you discover a new interest, learn about a course, join an activity, or receive advice from someone you trust.</p><div class="row g-3"><div class="col-md-4"><div class="career-field"><strong>Explore</strong><p class="small text-secondary mb-0 mt-2">Read about careers, courses, and possible work settings.</p></div></div><div class="col-md-4"><div class="career-field"><strong>Prepare</strong><p class="small text-secondary mb-0 mt-2">Build skills through school subjects, projects, and activities.</p></div></div><div class="col-md-4"><div class="career-field"><strong>Ask for guidance</strong><p class="small text-secondary mb-0 mt-2">Talk with your family, teachers, or the Guidance Office.</p></div></div></div><p class="small text-secondary mt-3 mb-0">Career suggestions are starting points only. Admission requirements, course offerings, and professional licenses may change. Confirm details with the relevant school or official institution before making decisions.</p></section>
<?php include 'footer.php'; ?>
