/**
 * internLink — Translation Widget
 * ─────────────────────────────────────────────────────────────────
 * Drop this <script src="translate-widget.js"></script> at the end
 * of <body> in every HTML page.  Works on localhost AND production.
 * No API key required — uses Google Translate Element (free).
 * ─────────────────────────────────────────────────────────────────
 */

(function () {
  /* ── 1. Inject Google Translate Element script ────────────────── */
  if (!document.getElementById('gt-script')) {
    const s = document.createElement('script');
    s.id = 'gt-script';
    s.src = 'https://translate.google.com/translate_a/element.js?cb=__ilTranslateInit';
    s.async = true;
    document.head.appendChild(s);
  }

  /* ── 2. Languages list ────────────────────────────────────────── */
  const LANGS = [
    { code: 'en', label: 'English',    flag: '🇬🇧' },
    { code: 'fr', label: 'Français',   flag: '🇫🇷' },
    { code: 'ar', label: 'العربية',    flag: '🇩🇿' },
    { code: 'es', label: 'Español',    flag: '🇪🇸' },
    { code: 'de', label: 'Deutsch',    flag: '🇩🇪' },
    { code: 'it', label: 'Italiano',   flag: '🇮🇹' },
    { code: 'pt', label: 'Português',  flag: '🇵🇹' },
    { code: 'zh-CN', label: '中文',    flag: '🇨🇳' },
    { code: 'ja', label: '日本語',     flag: '🇯🇵' },
    { code: 'ru', label: 'Русский',    flag: '🇷🇺' },
    { code: 'tr', label: 'Türkçe',     flag: '🇹🇷' },
    { code: 'nl', label: 'Nederlands', flag: '🇳🇱' },
  ];

  /* ── 3. Detect browser language for auto-pop ──────────────────── */
  function getBrowserLang() {
    const raw = (navigator.language || navigator.userLanguage || 'en').slice(0, 2).toLowerCase();
    const match = LANGS.find(l => l.code.startsWith(raw));
    return match ? match.code : null;
  }

  const STORAGE_KEY  = 'il-lang';
  const SHOWN_KEY    = 'il-lang-prompted';
  const savedLang    = localStorage.getItem(STORAGE_KEY);
  const browserLang  = getBrowserLang();
  const alreadyShown = sessionStorage.getItem(SHOWN_KEY);

  /* ── 4. Inject CSS ───────────────────────────────────────────── */
  const css = `
    /* hide ugly Google bar */
    .goog-te-banner-frame, #goog-gt-tt, .goog-tooltip { display:none!important; }
    body { top:0!important; }

    /* ── Floating button ── */
    #il-translate-fab {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 9999;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: var(--accent, #4f8ef7);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.35rem;
      box-shadow: 0 4px 20px rgba(79,142,247,0.45);
      transition: transform .2s, box-shadow .2s;
      animation: il-pop .4s cubic-bezier(.34,1.56,.64,1) both;
    }
    #il-translate-fab:hover {
      transform: scale(1.1);
      box-shadow: 0 6px 28px rgba(79,142,247,0.6);
    }
    @keyframes il-pop {
      from { transform: scale(0); opacity:0; }
      to   { transform: scale(1); opacity:1; }
    }

    /* ── Panel ── */
    #il-translate-panel {
      position: fixed;
      bottom: 84px;
      right: 24px;
      z-index: 9999;
      width: 280px;
      background: var(--surface, #111827);
      border: 1px solid var(--border, rgba(79,142,247,0.18));
      border-radius: 16px;
      box-shadow: 0 16px 48px rgba(0,0,0,0.5);
      overflow: hidden;
      transform-origin: bottom right;
      transition: transform .22s cubic-bezier(.34,1.56,.64,1), opacity .18s;
      transform: scale(0);
      opacity: 0;
      pointer-events: none;
    }
    #il-translate-panel.open {
      transform: scale(1);
      opacity: 1;
      pointer-events: all;
    }
    .il-panel-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 16px 10px;
      border-bottom: 1px solid var(--border, rgba(79,142,247,0.18));
    }
    .il-panel-head h4 {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: .88rem;
      color: var(--text, #f0f4ff);
      letter-spacing: -.01em;
    }
    .il-close {
      background: none;
      border: none;
      cursor: pointer;
      color: var(--muted, #8899bb);
      font-size: 1rem;
      line-height: 1;
      padding: 2px 4px;
      border-radius: 4px;
      transition: color .15s;
    }
    .il-close:hover { color: var(--text, #f0f4ff); }

    .il-lang-list {
      max-height: 280px;
      overflow-y: auto;
      padding: 8px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 4px;
    }
    .il-lang-list::-webkit-scrollbar { width: 4px; }
    .il-lang-list::-webkit-scrollbar-track { background: transparent; }
    .il-lang-list::-webkit-scrollbar-thumb { background: var(--border, rgba(79,142,247,0.18)); border-radius: 4px; }

    .il-lang-btn {
      display: flex;
      align-items: center;
      gap: 7px;
      padding: 8px 10px;
      border-radius: 8px;
      border: 1.5px solid transparent;
      background: transparent;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
      font-size: .8rem;
      font-weight: 500;
      color: var(--muted, #8899bb);
      text-align: left;
      transition: all .15s;
      width: 100%;
    }
    .il-lang-btn:hover {
      background: var(--surface2, #1a2236);
      color: var(--text, #f0f4ff);
    }
    .il-lang-btn.active {
      border-color: var(--accent, #4f8ef7);
      background: rgba(79,142,247,.1);
      color: var(--accent, #4f8ef7);
      font-weight: 700;
    }
    .il-lang-flag { font-size: 1rem; }

    /* ── Auto-suggest popup ── */
    #il-auto-suggest {
      position: fixed;
      bottom: 84px;
      right: 24px;
      z-index: 10000;
      background: var(--surface, #111827);
      border: 1px solid var(--accent, #4f8ef7);
      border-radius: 14px;
      padding: 16px 18px;
      width: 270px;
      box-shadow: 0 12px 40px rgba(79,142,247,0.3);
      transform-origin: bottom right;
      animation: il-pop .4s .6s cubic-bezier(.34,1.56,.64,1) both;
    }
    #il-auto-suggest p {
      font-size: .82rem;
      color: var(--muted, #8899bb);
      line-height: 1.5;
      margin-bottom: 12px;
    }
    #il-auto-suggest strong {
      color: var(--text, #f0f4ff);
    }
    .il-suggest-btns {
      display: flex;
      gap: 8px;
    }
    .il-suggest-yes {
      flex: 1;
      padding: 8px;
      background: var(--accent, #4f8ef7);
      border: none;
      border-radius: 8px;
      color: #fff;
      font-family: 'DM Sans', sans-serif;
      font-size: .8rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .15s;
    }
    .il-suggest-yes:hover { background: #3b7de8; }
    .il-suggest-no {
      padding: 8px 12px;
      background: transparent;
      border: 1.5px solid var(--border, rgba(79,142,247,0.18));
      border-radius: 8px;
      color: var(--muted, #8899bb);
      font-family: 'DM Sans', sans-serif;
      font-size: .8rem;
      cursor: pointer;
      transition: all .15s;
    }
    .il-suggest-no:hover { border-color: var(--text, #f0f4ff); color: var(--text, #f0f4ff); }
  `;

  const style = document.createElement('style');
  style.textContent = css;
  document.head.appendChild(style);

  /* ── 5. Hidden Google Translate element ───────────────────────── */
  const gtDiv = document.createElement('div');
  gtDiv.id = 'google_translate_element';
  gtDiv.style.cssText = 'position:absolute;width:0;height:0;overflow:hidden;opacity:0;pointer-events:none;';
  document.body.appendChild(gtDiv);

  /* ── 6. Google Translate init callback ────────────────────────── */
  window.__ilTranslateInit = function () {
    new google.translate.TranslateElement(
      { pageLanguage: 'en', autoDisplay: false },
      'google_translate_element'
    );
    // Restore previously saved language
    if (savedLang && savedLang !== 'en') {
      setTimeout(() => applyLang(savedLang), 800);
    }
  };

  /* ── 7. Apply language via Google Translate cookie ────────────── */
  function applyLang(code) {
    localStorage.setItem(STORAGE_KEY, code);

    if (code === 'en') {
      // Remove the translation cookie to go back to English
      document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
      document.cookie = 'googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=' + location.hostname + ';';
      location.reload();
      return;
    }

    const val = '/en/' + code;
    document.cookie = 'googtrans=' + val + '; path=/';
    document.cookie = 'googtrans=' + val + '; path=/; domain=' + location.hostname;

    // Use the hidden select element that Google Translate creates
    const tryChange = (attempts) => {
      const sel = document.querySelector('.goog-te-combo');
      if (sel) {
        sel.value = code;
        sel.dispatchEvent(new Event('change'));
        updateActiveBtn(code);
      } else if (attempts > 0) {
        setTimeout(() => tryChange(attempts - 1), 300);
      }
    };
    tryChange(10);
    updateActiveBtn(code);
  }

  /* ── 8. Build the FAB + Panel HTML ───────────────────────────── */
  const fab = document.createElement('button');
  fab.id = 'il-translate-fab';
  fab.title = 'Translate page';
  fab.innerHTML = '🌐';
  fab.setAttribute('aria-label', 'Language selector');

  const panel = document.createElement('div');
  panel.id = 'il-translate-panel';

  const currentLang = savedLang || 'en';
  const currentInfo = LANGS.find(l => l.code === currentLang) || LANGS[0];

  panel.innerHTML = `
    <div class="il-panel-head">
      <h4>🌐 Choose Language</h4>
      <button class="il-close" id="il-close-btn" aria-label="Close">✕</button>
    </div>
    <div class="il-lang-list">
      ${LANGS.map(l => `
        <button class="il-lang-btn ${l.code === currentLang ? 'active' : ''}"
                data-lang="${l.code}"
                onclick="window.__ilSetLang('${l.code}')">
          <span class="il-lang-flag">${l.flag}</span>
          ${l.label}
        </button>
      `).join('')}
    </div>
  `;

  document.body.appendChild(fab);
  document.body.appendChild(panel);

  /* ── 9. Panel open/close logic ───────────────────────────────── */
  fab.addEventListener('click', () => {
    closeAutoSuggest();
    panel.classList.toggle('open');
  });

  document.getElementById('il-close-btn').addEventListener('click', () => {
    panel.classList.remove('open');
  });

  document.addEventListener('click', (e) => {
    if (!panel.contains(e.target) && e.target !== fab) {
      panel.classList.remove('open');
    }
  });

  /* ── 10. Public setter (called from inline onclick) ─────────── */
  window.__ilSetLang = function (code) {
    applyLang(code);
    panel.classList.remove('open');
  };

  function updateActiveBtn(code) {
    document.querySelectorAll('.il-lang-btn').forEach(b => {
      b.classList.toggle('active', b.dataset.lang === code);
    });
    fab.innerHTML = LANGS.find(l => l.code === code)?.flag || '🌐';
  }

  /* ── 11. Auto-suggest popup (once per session) ───────────────── */
  function closeAutoSuggest() {
    const el = document.getElementById('il-auto-suggest');
    if (el) el.remove();
  }

  if (!alreadyShown && browserLang && browserLang !== 'en' && browserLang !== savedLang) {
    sessionStorage.setItem(SHOWN_KEY, '1');
    const suggest = LANGS.find(l => l.code === browserLang);
    if (suggest) {
      setTimeout(() => {
        const popup = document.createElement('div');
        popup.id = 'il-auto-suggest';
        popup.innerHTML = `
          <p>We noticed your browser is set to <strong>${suggest.flag} ${suggest.label}</strong>.<br>Would you like to translate this page?</p>
          <div class="il-suggest-btns">
            <button class="il-suggest-yes" onclick="window.__ilSetLang('${suggest.code}');document.getElementById('il-auto-suggest').remove()">
              Yes, translate
            </button>
            <button class="il-suggest-no" onclick="document.getElementById('il-auto-suggest').remove()">
              No thanks
            </button>
          </div>
        `;
        document.body.appendChild(popup);
      }, 1500);
    }
  }

  /* ── 12. Update FAB flag on load if already translated ───────── */
  if (savedLang && savedLang !== 'en') {
    updateActiveBtn(savedLang);
  }

})();
