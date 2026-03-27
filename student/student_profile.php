<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Profile — internLink</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="shared.css"/>
  <style>
    /* ── Profile specific ── */
    .profile-hero {
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent2) 100%);
      border-radius: var(--radius); padding: 32px; color: #fff;
      display: flex; align-items: center; gap: 28px; margin-bottom: 28px;
      position: relative; overflow: hidden;
    }
    .profile-hero::before {
      content: ''; position: absolute; inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .profile-avatar-wrap { position: relative; flex-shrink: 0; }
    .profile-avatar {
      width: 100px; height: 100px; border-radius: 50%;
      background: rgba(255,255,255,.25); border: 3px solid rgba(255,255,255,.5);
      display: flex; align-items: center; justify-content: center;
      font-size: 2.4rem; font-weight: 800; font-family: 'Syne', sans-serif;
      position: relative; z-index: 1;
    }
    .avatar-edit-btn {
      position: absolute; bottom: 0; right: 0;
      width: 28px; height: 28px; border-radius: 50%;
      background: #fff; border: none; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      font-size: .75rem; z-index: 2; box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }
    .profile-info h2 { font-size: 1.6rem; font-weight: 800; margin-bottom: 4px; }
    .profile-info p  { opacity: .85; font-size: .9rem; margin-bottom: 12px; }
    .profile-meta { display: flex; gap: 20px; flex-wrap: wrap; }
    .profile-meta span { font-size: .82rem; opacity: .8; display: flex; align-items: center; gap: 5px; }
    .profile-complete {
      margin-left: auto; text-align: center; flex-shrink: 0; position: relative; z-index: 1;
    }
    .profile-complete-label { font-size: .78rem; opacity: .8; margin-bottom: 8px; }
    .complete-ring { position: relative; width: 80px; height: 80px; }
    .complete-ring svg { transform: rotate(-90deg); }
    .complete-pct { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-family: 'Syne', sans-serif; font-size: 1.1rem; font-weight: 800; color: #fff; }

    /* Section tabs */
    .tabs { display: flex; gap: 4px; border-bottom: 2px solid var(--border); margin-bottom: 24px; }
    .tab-btn { padding: 10px 18px; border: none; background: transparent; font-family: 'DM Sans', sans-serif; font-size: .875rem; font-weight: 600; color: var(--muted); cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all .18s; }
    .tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); }
    .tab-btn:hover:not(.active) { color: var(--text); }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }

    /* Skills section */
    .skills-input-wrap { display: flex; gap: 10px; margin-bottom: 14px; }
    .skills-input-wrap input { flex: 1; }

    /* CV upload */
    .cv-dropzone {
      border: 2px dashed var(--border); border-radius: var(--radius);
      padding: 32px; text-align: center; cursor: pointer;
      transition: all .2s; background: var(--surface2);
    }
    .cv-dropzone:hover, .cv-dropzone.dragover { border-color: var(--accent); background: var(--accent-l); }
    .cv-dropzone .drop-icon { font-size: 2rem; margin-bottom: 10px; opacity: .5; }
    .cv-dropzone p { font-size: .875rem; color: var(--muted); }
    .cv-dropzone strong { color: var(--accent); }
    .cv-file-info { display: flex; align-items: center; gap: 12px; padding: 14px 16px; background: var(--accent-l); border: 1px solid #bfdbfe; border-radius: var(--radius); margin-top: 12px; }
    .cv-file-icon { font-size: 1.6rem; }
    .cv-file-name { font-weight: 600; font-size: .875rem; }
    .cv-file-size { font-size: .75rem; color: var(--muted); }

    /* Stats row */
    .stats-mini { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 28px; }
    .stat-mini { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; text-align: center; box-shadow: var(--shadow); }
    .stat-mini-val { font-family: 'Syne', sans-serif; font-size: 1.6rem; font-weight: 800; color: var(--accent); }
    .stat-mini-lbl { font-size: .75rem; color: var(--muted); margin-top: 2px; }

    @media (max-width:768px) { .profile-hero{flex-direction:column;text-align:center} .profile-meta{justify-content:center} .profile-complete{margin-left:0} .stats-mini{grid-template-columns:1fr 1fr} }
  </style>
</head>
<body>

<!-- Top Nav -->
<nav class="top-nav">
  <a class="logo" href="student_dashboard.php">intern<span>Link</span></a>
  <div class="spacer"></div>
  <div class="nav-bell" title="Notifications">🔔<span class="nav-bell-dot"></span></div>
  <div class="nav-avatar" id="navAvatar">A</div>
</nav>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="sidebar-section">
    <div class="sidebar-section-label">Menu</div>
    <a class="sidebar-link" href="student_dashboard.php"><span class="link-icon">🏠</span>Dashboard</a>
    <a class="sidebar-link active" href="student_profile.php"><span class="link-icon">👤</span>My Profile</a>
    <a class="sidebar-link" href="internships.php"><span class="link-icon">🔍</span>Browse Internships</a>
    <a class="sidebar-link" href="applications.php"><span class="link-icon">📋</span>My Applications<span class="link-badge" id="appBadge">0</span></a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-section-label">Account</div>
    <a class="sidebar-link" href="logout.php"><span class="link-icon">🚪</span>Log Out</a>
  </div>
</aside>

<!-- Main -->
<main class="main">

  <!-- Alerts -->
  <div class="alert alert-success" id="alertSuccess"><span>✓</span><span id="alertSuccessMsg"></span></div>
  <div class="alert alert-error"   id="alertError"><span>✗</span><span id="alertErrorMsg"></span></div>

  <!-- Profile Hero -->
  <div class="profile-hero">
    <div class="profile-avatar-wrap">
      <div class="profile-avatar" id="heroAvatar">A</div>
      <button class="avatar-edit-btn" title="Change photo">✏️</button>
    </div>
    <div class="profile-info">
      <h2 id="heroName">Loading…</h2>
      <p id="heroEmail">—</p>
      <div class="profile-meta">
        <span id="heroUniversity">🏛 —</span>
        <span id="heroField">📚 —</span>
        <span id="heroWilaya">📍 —</span>
        <span id="heroYear">📅 —</span>
      </div>
    </div>
    <div class="profile-complete">
      <div class="profile-complete-label">Profile</div>
      <div class="complete-ring">
        <svg width="80" height="80" viewBox="0 0 80 80">
          <circle cx="40" cy="40" r="34" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="6"/>
          <circle cx="40" cy="40" r="34" fill="none" stroke="#fff" stroke-width="6"
            stroke-linecap="round" stroke-dasharray="213.6" stroke-dashoffset="213.6" id="completeCircle"/>
        </svg>
        <div class="complete-pct" id="completePct">0%</div>
      </div>
    </div>
  </div>

  <!-- Stats Mini -->
  <div class="stats-mini">
    <div class="stat-mini"><div class="stat-mini-val" id="statsApps">0</div><div class="stat-mini-lbl">Applications</div></div>
    <div class="stat-mini"><div class="stat-mini-val" id="statsAccepted">0</div><div class="stat-mini-lbl">Accepted</div></div>
    <div class="stat-mini"><div class="stat-mini-val" id="statsSkills">0</div><div class="stat-mini-lbl">Skills Listed</div></div>
    <div class="stat-mini"><div class="stat-mini-val" id="statsMatch">—</div><div class="stat-mini-lbl">Avg Match %</div></div>
  </div>

  <!-- Tabs -->
  <div class="tabs">
    <button class="tab-btn active" onclick="switchTab('info',this)">Personal Info</button>
    <button class="tab-btn" onclick="switchTab('skills',this)">Skills & Bio</button>
    <button class="tab-btn" onclick="switchTab('cv',this)">CV & Documents</button>
    <button class="tab-btn" onclick="switchTab('security',this)">Security</button>
  </div>

  <!-- Tab: Personal Info -->
  <div class="tab-panel active" id="tab-info">
    <div class="card">
      <form id="infoForm">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">First Name <span>*</span></label>
            <input class="form-input" type="text" name="firstName" id="firstName" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Last Name <span>*</span></label>
            <input class="form-input" type="text" name="lastName" id="lastName" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input class="form-input" type="email" name="email" id="emailField" readonly style="opacity:.6;cursor:not-allowed"/>
            <p class="form-hint">Email cannot be changed. Contact support if needed.</p>
          </div>
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input class="form-input" type="tel" name="phone" id="phone" placeholder="+213 6xx xxx xxx"/>
          </div>
          <div class="form-group">
            <label class="form-label">University <span>*</span></label>
            <input class="form-input" type="text" name="university" id="university" placeholder="e.g. USTHB, ESI…"/>
          </div>
          <div class="form-group">
            <label class="form-label">Wilaya</label>
            <select class="form-select" name="wilaya" id="wilaya">
              <option value="">Select wilaya…</option>
              <option>Alger</option><option>Oran</option><option>Constantine</option><option>Sétif</option>
              <option>Annaba</option><option>Batna</option><option>Blida</option><option>Béjaïa</option>
              <option>Tlemcen</option><option>Tizi Ouzou</option><option>Biskra</option><option>Other</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Field of Study <span>*</span></label>
            <select class="form-select" name="field" id="fieldStudy">
              <option value="">Select field…</option>
              <option>Computer Science</option><option>Software Engineering</option><option>Data Science</option>
              <option>Electronics & Telecommunications</option><option>Civil Engineering</option>
              <option>Business Administration</option><option>Finance & Accounting</option>
              <option>Marketing</option><option>Medicine</option><option>Architecture</option><option>Other</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Academic Year <span>*</span></label>
            <select class="form-select" name="year" id="yearField">
              <option value="">Select year…</option>
              <option>1st year</option><option>2nd year</option><option>3rd year</option>
              <option>Master 1</option><option>Master 2</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">LinkedIn Profile</label>
          <input class="form-input" type="url" name="linkedin" id="linkedin" placeholder="https://linkedin.com/in/yourprofile"/>
        </div>
        <div class="form-group">
          <label class="form-label">GitHub / Portfolio</label>
          <input class="form-input" type="url" name="github" id="github" placeholder="https://github.com/yourhandle"/>
        </div>
        <button type="submit" class="btn btn-primary btn-lg" id="saveInfoBtn">Save Changes</button>
      </form>
    </div>
  </div>

  <!-- Tab: Skills & Bio -->
  <div class="tab-panel" id="tab-skills">
    <div class="card" style="margin-bottom:20px">
      <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px">Skills</h3>
      <div class="skills-input-wrap">
        <input class="form-input" type="text" id="skillInput" placeholder="Type a skill and press Enter…" onkeydown="addSkillOnEnter(event)"/>
        <button class="btn btn-secondary" onclick="addSkill()">Add</button>
      </div>
      <div class="tag-list" id="skillsList"></div>
      <p class="form-hint" style="margin-top:10px">Add skills that match the internships you want. These power the matching algorithm.</p>
    </div>
    <div class="card">
      <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px">Bio / Motivation</h3>
      <div class="form-group">
        <textarea class="form-textarea" id="bioText" rows="5" placeholder="Tell companies a bit about yourself — your background, goals, and what type of internship you're looking for…" maxlength="600"></textarea>
        <p class="form-hint"><span id="bioCount">0</span>/600 characters</p>
      </div>
      <button class="btn btn-primary" onclick="saveSkills()">Save Skills & Bio</button>
    </div>
  </div>

  <!-- Tab: CV & Documents -->
  <div class="tab-panel" id="tab-cv">
    <div class="card" style="margin-bottom:20px">
      <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px">Upload CV (PDF)</h3>
      <div class="cv-dropzone" id="cvDropzone" onclick="document.getElementById('cvFile').click()" ondragover="handleDrag(event)" ondrop="handleDrop(event)">
        <div class="drop-icon">📄</div>
        <p><strong>Click to browse</strong> or drag & drop your CV here</p>
        <p style="margin-top:6px;font-size:.78rem">PDF only · Max 5 MB</p>
      </div>
      <input type="file" id="cvFile" accept=".pdf" style="display:none" onchange="handleFileSelect(event)"/>
      <div id="cvFileInfo" style="display:none" class="cv-file-info">
        <span class="cv-file-icon">📄</span>
        <div>
          <div class="cv-file-name" id="cvFileName">—</div>
          <div class="cv-file-size" id="cvFileSize">—</div>
        </div>
        <button class="btn btn-sm btn-danger" style="margin-left:auto" onclick="removeCV()">Remove</button>
      </div>
      <button class="btn btn-primary" style="margin-top:16px" id="uploadCvBtn" onclick="uploadCV()">Upload CV</button>
    </div>
    <div class="card">
      <h3 style="font-size:1rem;font-weight:700;margin-bottom:8px">Current CV</h3>
      <div id="currentCvSection">
        <p class="form-hint">No CV uploaded yet.</p>
      </div>
    </div>
  </div>

  <!-- Tab: Security -->
  <div class="tab-panel" id="tab-security">
    <div class="card">
      <h3 style="font-size:1rem;font-weight:700;margin-bottom:20px">Change Password</h3>
      <form id="passwordForm" onsubmit="changePassword(event)">
        <div class="form-group">
          <label class="form-label">Current Password <span>*</span></label>
          <input class="form-input" type="password" name="currentPw" id="currentPw" required/>
        </div>
        <div class="form-group">
          <label class="form-label">New Password <span>*</span></label>
          <input class="form-input" type="password" name="newPw" id="newPw" required minlength="8"/>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password <span>*</span></label>
          <input class="form-input" type="password" name="confirmPw" id="confirmPw" required/>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
      </form>
    </div>
  </div>

</main>

<script>
  // ── State ──────────────────────────────────────────────────────────────────
  let skills = [];
  let profileData = {};

  // ── Init ───────────────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', () => {
    loadProfile();
    document.getElementById('bioText').addEventListener('input', updateBioCount);
  });

  // ── Load profile from server ───────────────────────────────────────────────
  function loadProfile() {
    fetch('get_profile.php')
      .then(r => r.json())
      .then(data => {
        if (!data.success) { window.location.href = 'login.html'; return; }
        profileData = data.profile;
        populateForm(profileData);
        updateHero(profileData);
        updateCompletion(profileData);
        updateStats(data.stats || {});
        skills = profileData.skills ? profileData.skills.split(',').map(s=>s.trim()).filter(Boolean) : [];
        renderSkills();
        document.getElementById('bioText').value = profileData.bio || '';
        updateBioCount();
        loadCurrentCV(data.cv);
      })
      .catch(() => showAlert('error', 'Failed to load profile. Please refresh.'));
  }

  function populateForm(p) {
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
    set('firstName', p.first_name);
    set('lastName',  p.last_name);
    set('emailField', p.email);
    set('phone',     p.phone);
    set('university',p.university);
    set('wilaya',    p.wilaya);
    set('fieldStudy',p.field_of_study);
    set('yearField', p.academic_year);
    set('linkedin',  p.linkedin);
    set('github',    p.github);
    document.getElementById('navAvatar').textContent = (p.first_name || '?')[0].toUpperCase();
  }

  function updateHero(p) {
    document.getElementById('heroName').textContent = (p.first_name || '') + ' ' + (p.last_name || '');
    document.getElementById('heroEmail').textContent = p.email || '—';
    document.getElementById('heroAvatar').textContent = (p.first_name || '?')[0].toUpperCase();
    document.getElementById('heroUniversity').textContent = '🏛 ' + (p.university || '—');
    document.getElementById('heroField').textContent = '📚 ' + (p.field_of_study || '—');
    document.getElementById('heroWilaya').textContent = '📍 ' + (p.wilaya || '—');
    document.getElementById('heroYear').textContent = '📅 ' + (p.academic_year || '—');
  }

  function updateStats(s) {
    document.getElementById('statsApps').textContent     = s.applications || 0;
    document.getElementById('statsAccepted').textContent = s.accepted || 0;
    document.getElementById('statsSkills').textContent   = skills.length;
    document.getElementById('statsMatch').textContent    = s.avg_match ? s.avg_match + '%' : '—';
    document.getElementById('appBadge').textContent      = s.applications || 0;
  }

  // Profile completion percentage
  function updateCompletion(p) {
    const fields = ['first_name','last_name','email','university','field_of_study','academic_year','wilaya','bio','skills','cv_path'];
    const filled = fields.filter(f => p[f] && p[f] !== '').length;
    const pct    = Math.round((filled / fields.length) * 100);
    const circumference = 213.6;
    const offset = circumference - (pct / 100) * circumference;
    document.getElementById('completeCircle').style.strokeDashoffset = offset;
    document.getElementById('completePct').textContent = pct + '%';
  }

  // ── Tabs ───────────────────────────────────────────────────────────────────
  function switchTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
  }

  // ── Save personal info ─────────────────────────────────────────────────────
  document.getElementById('infoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('saveInfoBtn');
    btn.textContent = 'Saving…'; btn.disabled = true;
    const formData = Object.fromEntries(new FormData(this));
    fetch('update_profile.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) { showAlert('success', 'Profile updated successfully!'); loadProfile(); }
      else showAlert('error', data.message || 'Update failed.');
    })
    .catch(() => showAlert('error', 'Server error. Try again.'))
    .finally(() => { btn.textContent = 'Save Changes'; btn.disabled = false; });
  });

  // ── Skills ─────────────────────────────────────────────────────────────────
  function addSkillOnEnter(e) { if (e.key === 'Enter') { e.preventDefault(); addSkill(); } }
  function addSkill() {
    const input = document.getElementById('skillInput');
    const val   = input.value.trim();
    if (!val) return;
    const words = val.split(',').map(s => s.trim()).filter(Boolean);
    words.forEach(w => { if (!skills.includes(w) && skills.length < 30) skills.push(w); });
    input.value = '';
    renderSkills();
    document.getElementById('statsSkills').textContent = skills.length;
  }
  function removeSkill(skill) { skills = skills.filter(s => s !== skill); renderSkills(); document.getElementById('statsSkills').textContent = skills.length; }
  function renderSkills() {
    const list = document.getElementById('skillsList');
    list.innerHTML = skills.map(s => `<span class="tag">${s}<span class="tag-remove" onclick="removeSkill('${s.replace(/'/g,"\\'")}')">✕</span></span>`).join('');
  }
  function updateBioCount() { document.getElementById('bioCount').textContent = document.getElementById('bioText').value.length; }
  function saveSkills() {
    fetch('update_profile.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ skills: skills.join(', '), bio: document.getElementById('bioText').value })
    })
    .then(r => r.json())
    .then(d => { if (d.success) showAlert('success', 'Skills & bio saved!'); else showAlert('error', d.message); });
  }

  // ── CV upload ──────────────────────────────────────────────────────────────
  let cvFile = null;
  function handleDrag(e) { e.preventDefault(); document.getElementById('cvDropzone').classList.add('dragover'); }
  function handleDrop(e) {
    e.preventDefault();
    document.getElementById('cvDropzone').classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file) setCV(file);
  }
  function handleFileSelect(e) { if (e.target.files[0]) setCV(e.target.files[0]); }
  function setCV(file) {
    if (file.type !== 'application/pdf') { showAlert('error','Please upload a PDF file.'); return; }
    if (file.size > 5 * 1024 * 1024) { showAlert('error','File must be under 5 MB.'); return; }
    cvFile = file;
    document.getElementById('cvFileName').textContent = file.name;
    document.getElementById('cvFileSize').textContent = (file.size/1024).toFixed(0) + ' KB';
    document.getElementById('cvFileInfo').style.display = 'flex';
  }
  function removeCV() { cvFile = null; document.getElementById('cvFileInfo').style.display = 'none'; document.getElementById('cvFile').value = ''; }
  function uploadCV() {
    if (!cvFile) { showAlert('error','Please select a PDF file first.'); return; }
    const fd = new FormData();
    fd.append('cv', cvFile);
    const btn = document.getElementById('uploadCvBtn');
    btn.textContent = 'Uploading…'; btn.disabled = true;
    fetch('upload_cv.php', { method:'POST', body: fd })
      .then(r => r.json())
      .then(d => { if (d.success) { showAlert('success','CV uploaded!'); loadProfile(); removeCV(); } else showAlert('error', d.message); })
      .catch(() => showAlert('error','Upload failed.'))
      .finally(() => { btn.textContent = 'Upload CV'; btn.disabled = false; });
  }
  function loadCurrentCV(cv) {
    const sec = document.getElementById('currentCvSection');
    if (cv) {
      sec.innerHTML = `<div class="cv-file-info"><span class="cv-file-icon">📄</span><div><div class="cv-file-name">${cv.filename}</div><div class="cv-file-size">Uploaded ${cv.uploaded_at}</div></div><a href="uploads/cv/${cv.filename}" target="_blank" class="btn btn-sm btn-secondary" style="margin-left:auto">View</a></div>`;
    } else {
      sec.innerHTML = '<p class="form-hint">No CV uploaded yet.</p>';
    }
  }

  // ── Change password ────────────────────────────────────────────────────────
  function changePassword(e) {
    e.preventDefault();
    const newPw = document.getElementById('newPw').value;
    const conf  = document.getElementById('confirmPw').value;
    if (newPw !== conf) { showAlert('error','Passwords do not match.'); return; }
    fetch('change_password.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ currentPassword: document.getElementById('currentPw').value, newPassword: newPw })
    })
    .then(r => r.json())
    .then(d => { if (d.success) { showAlert('success','Password updated!'); document.getElementById('passwordForm').reset(); } else showAlert('error', d.message); })
    .catch(() => showAlert('error','Server error.'));
  }

  // ── Alerts ─────────────────────────────────────────────────────────────────
  function showAlert(type, msg) {
    const id = type === 'success' ? 'alertSuccess' : 'alertError';
    const mid = type === 'success' ? 'alertSuccessMsg' : 'alertErrorMsg';
    document.getElementById(mid).textContent = msg;
    const el = document.getElementById(id);
    el.classList.add('show');
    window.scrollTo({top:0,behavior:'smooth'});
    setTimeout(() => el.classList.remove('show'), 4000);
  }
</script>
</body>
</html>
