/* =========================================
   FEEDLEARN UI INTERACTIONS
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Settings Drawer & Mobile Menu Logic
    const toggleBtn = document.querySelector('.mobile-toggle');
    const drawer = document.querySelector('.settings-drawer');
    const overlay = document.querySelector('.drawer-overlay');
    const closeBtn = document.querySelector('.drawer-close');
    const darkModeToggle = document.getElementById('darkModeToggle');

    function openDrawer() {
        drawer.classList.add('active');
        overlay.classList.add('active');
    }

    function closeDrawer() {
        drawer.classList.remove('active');
        overlay.classList.remove('active');
    }

    if (toggleBtn) toggleBtn.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (overlay) overlay.addEventListener('click', closeDrawer);

    // 2. Unified Theme Manager (Lamp + Switch + Toast)
    const initTheme = () => {
        const lampBtn = document.getElementById('lampToggle');
        const darkSwitch = document.getElementById('darkModeToggle');
        const body = document.body;

        // Helper: Show Toast
        const showToast = (msg) => {
            const existing = document.querySelector('.theme-toast');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.className = 'theme-toast';
            toast.innerText = msg;
            document.body.appendChild(toast);

            // Trigger reflow for animation
            requestAnimationFrame(() => {
                toast.classList.add('visible');
            });

            setTimeout(() => {
                toast.classList.remove('visible');
                setTimeout(() => toast.remove(), 300);
            }, 2000);
        };

        // Core: Set Theme
        const setTheme = (isDark, notify = false) => {
            if (isDark) {
                body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
                if (darkSwitch) darkSwitch.checked = true;
                if (notify) showToast('Dark mode enabled');
            } else {
                body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
                if (darkSwitch) darkSwitch.checked = false;
                if (notify) showToast('Light mode enabled');
            }
        };

        // Init Load
        const saved = localStorage.getItem('theme');
        if (saved === 'dark') {
            setTheme(true, false);
        } else {
            setTheme(false, false);
        }

        // Listeners
        if (lampBtn) {
            lampBtn.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent focus jump or form submit
                const isDarkNow = body.classList.contains('dark-mode');
                setTheme(!isDarkNow, true);
            });
        }

        if (darkSwitch) {
            darkSwitch.addEventListener('change', (e) => {
                setTheme(e.target.checked, true);
            });
        }
    };
    initTheme();

    // 3. Goal Sync (Simple DOM scraping)
    // Try to find the goal text on the dashboard
    const dashGoalText = document.querySelector('.card .muted[style*="text-transform:uppercase"] + span'); // Rough selector for "1/3 Tasks"
    // Better selector based on structure: "Today's Goal" label parent -> sibling
    // Let's use specific text content search or just fallback since structure varies

    // We can try to grab the first card with "Today's Goal"
    const goalCard = Array.from(document.querySelectorAll('.card')).find(c => c.innerHTML.includes("Today's Goal"));

    if (goalCard) {
        // Extract "1 / 3 Tasks"
        const countEl = goalCard.querySelector('span[style*="font-weight:700"]');
        // This is a bit brittle, but valid for frontend-only

        if (countEl) {
            const countText = countEl.innerText;
            const drawerCount = document.getElementById('drawerGoalCount');
            if (drawerCount) drawerCount.innerText = countText;

            // Extract progress width
            const progressEl = goalCard.querySelector('div[style*="width:"]');
            if (progressEl) {
                const widthStyle = progressEl.style.width;
                const drawerFill = document.getElementById('drawerGoalFill');
                if (drawerFill) drawerFill.style.width = widthStyle;
            }

            // Extract description
            const descEl = goalCard.querySelector('p.muted');
            if (descEl) {
                const drawerText = document.getElementById('drawerGoalText');
                if (drawerText) drawerText.innerText = descEl.innerText;
            }
        }
    }

    // 2. Add Fade-in Animation to Cards (Staggered) -- REMOVED to prevent visibility issues
    /*
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        // card.style.opacity = '0'; // CAUSES INVISIBILITY if animation fails
        // card.style.animation = `fadeIn 0.5s ease-out forwards ${index * 0.1}s`;
    });
    */

    // 3. Table Row Hover Highlight (JS Fallback for complex tables)
    // Most handled by CSS, but we can add click ripples if we want later.

    // 4. Form Input Focus Glow (visual enhancement)
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('input-focused');
        });
        input.addEventListener('blur', () => {
            input.parentElement.classList.remove('input-focused');
        });
    });

    // 5. Toast / Alert dismiss
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // If we had a close button
        const close = alert.querySelector('.close');
        if (close) {
            close.addEventListener('click', () => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }
    });
});

/* =========================================
   CHATBOT HELPERS
   ========================================= */

// 1. Format Bot Message (Markdown + Badges)
window.formatBotMessage = function (text) {
    if (!text) return '';

    // Safety: Basic escaping
    let safeText = text.replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");

    // A. Specific Badge Replacements (Handling Markdown Bold if present or plain text)
    // Matches "**Quiz:**" or simply "Quiz:" (word boundary check)

    // Replace **Quiz:** -> Badge
    safeText = safeText.replace(/\*\*Quiz:\*\*/g, '<span class="chatbot-badge badge-quiz">Quiz</span>')
        .replace(/\*\*Speaking:\*\*/g, '<span class="chatbot-badge badge-speaking">Speaking</span>')
        .replace(/\*\*Writing:\*\*/g, '<span class="chatbot-badge badge-writing">Writing</span>');

    // Replace "Quiz:" (plain) -> Badge
    safeText = safeText.replace(/(^|\s)Quiz:/g, ' <span class="chatbot-badge badge-quiz">Quiz</span>')
        .replace(/(^|\s)Speaking:/g, ' <span class="chatbot-badge badge-speaking">Speaking</span>')
        .replace(/(^|\s)Writing:/g, ' <span class="chatbot-badge badge-writing">Writing</span>');

    // B. Generic Markdown Bold (**text**)
    safeText = safeText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

    // C. Newlines
    safeText = safeText.replace(/\n/g, '<br>');

    return safeText;
};

// 2. Append Quick Actions (Quiz, Speaking, Writing)
window.appendChatbotQuickActions = function (containerElement, text) {
    if (!text || !containerElement) return;

    const lower = text.toLowerCase();

    // Conditions to show actions:
    // 1. Mentions all three activities
    // 2. Or is the specific "three main things" prompt
    // 3. Or explicitly suggests "start"ing these

    const mentionsQuiz = lower.includes('quiz');
    const mentionsSpeaking = lower.includes('speaking');
    const mentionsWriting = lower.includes('writing');

    const isThreeMainThings = lower.includes('three main things') || (lower.includes('can do') && lower.includes('things'));
    const isAllThree = mentionsQuiz && mentionsSpeaking && mentionsWriting;

    if (isThreeMainThings || isAllThree) {
        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'quick-actions';

        // Note: URLs are relative to the chatbot page (/help/chatbot.php)
        // So ../activity/quiz.php works

        actionsDiv.innerHTML = `
           <a href="../activity/quiz.php" class="action-pill pill-quiz">
             <i>⚡</i> Quiz
           </a>
           <a href="../activity/speaking.php" class="action-pill pill-speaking">
             <i>🎤</i> Speaking
           </a>
           <a href="../activity/writing.php" class="action-pill pill-writing">
             <i>✍️</i> Writing
           </a>
       `;

        // Append with a small fade in
        actionsDiv.style.opacity = '0';
        actionsDiv.style.animation = 'fadeIn 0.5s forwards';

        containerElement.appendChild(actionsDiv);
    }

    // ALWAYS append simple feedback actions (Thumbs Up/Down) for every bot message
    const feedbackDiv = document.createElement('div');
    feedbackDiv.style.marginTop = '4px';
    feedbackDiv.style.display = 'flex';
    feedbackDiv.style.gap = '8px';
    feedbackDiv.innerHTML = `
        <button class="btn" style="padding:4px 8px; font-size:0.75rem; color:var(--text-muted); background:transparent; border:none;" onclick="this.style.color='var(--success)'">👍</button>
        <button class="btn" style="padding:4px 8px; font-size:0.75rem; color:var(--text-muted); background:transparent; border:none;" onclick="this.style.color='var(--danger)'">👎</button>
    `;
    containerElement.appendChild(feedbackDiv);
};

// Add simple keyframes for fade if not exists
const styleSheet = document.createElement("style");
styleSheet.innerText = `
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}
`;
document.head.appendChild(styleSheet);


