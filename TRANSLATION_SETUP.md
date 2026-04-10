# 🌐 internLink — Translation Widget Setup

## What it does
- Floating 🌐 globe button (bottom-right corner) on every page
- Auto-detects the visitor's browser language and shows a one-time popup ("Translate to French?")
- 12 languages: English, French, Arabic, Spanish, German, Italian, Portuguese, Chinese, Japanese, Russian, Turkish, Dutch
- Remembers the chosen language across pages (localStorage)
- Works on **localhost** and **after deployment** — no API key needed

---

## Installation (1 line per page)

Add this single line just before `</body>` in **every HTML file**:

```html
<script src="translate-widget.js"></script>
```

### Example — index.html
```html
  ...
  <script src="translate-widget.js"></script>
</body>
</html>
```

> If your HTML files are in a subfolder (e.g. `/html/`), adjust the path:
> ```html
> <script src="../translate-widget.js"></script>
> ```
> or keep the widget in the same folder as your HTML files and use `src="translate-widget.js"`.

---

## Files to update

Add the script tag to ALL of these:

| File | Suggested src path |
|---|---|
| index.html | `translate-widget.js` |
| login.html | `translate-widget.js` |
| register.html | `translate-widget.js` |
| reset_password.html | `translate-widget.js` |
| browse.html | `translate-widget.js` |
| student_dashboard.html | `translate-widget.js` |
| student_profile.html | `translate-widget.js` |
| internships_dash.html | `translate-widget.js` |
| applications.html | `translate-widget.js` |
| notifications.html | `translate-widget.js` |
| Company_dashboard.html | `translate-widget.js` |
| Company_profile.html | `translate-widget.js` |
| Company_offers.html | `translate-widget.js` |
| Company_applications.html | `translate-widget.js` |
| admin_dashboard.html | `translate-widget.js` |
| admin_users.html | `translate-widget.js` |
| admin_internships.html | `translate-widget.js` |
| admin_applications.html | `translate-widget.js` |
| admin_companies.html | `translate-widget.js` |

---

## How the auto-popup works

1. On first visit (once per browser session), the widget reads `navigator.language`
2. If the browser is set to a non-English language (e.g. French/Arabic), a popup appears asking the user if they want to translate
3. The choice is remembered for the whole session — the popup won't appear again
4. If the user clicks "Yes", the page translates immediately
5. The language preference is saved to `localStorage` and applied automatically on every subsequent page

---

## Adding a new language

In `translate-widget.js`, find the `LANGS` array and add an entry:

```js
{ code: 'ko', label: '한국어', flag: '🇰🇷' },
```

Use any valid [Google Translate language code](https://cloud.google.com/translate/docs/languages).

---

## Notes

- Uses the **free** Google Translate Element — no API key, no billing
- Works on `localhost` (the Google script loads from CDN, translation happens client-side)
- The "Go back to English" button resets the cookie and reloads the page cleanly
- The ugly Google translate toolbar at the top is hidden via CSS automatically
