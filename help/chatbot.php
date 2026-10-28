<?php
require_once __DIR__ . '/../auth/_guard.php';
require_once __DIR__ . '/../config/constants.php';
require_login();
require_once __DIR__ . '/../templates/header.php';
?>

<section class="section" id="chatbot-page">
    <div class="card chat-card">

        <!-- Header -->
        <div class="chat-header">
            <span style="font-size:1.8rem;">🤖</span>
            <div>
                <h2 style="margin:0; font-size:1.5rem;">Help Chatbot</h2>
                <p class="muted" style="margin:0; font-size:0.9rem;">Ask me anything about English or FeedLearn!</p>
            </div>
        </div>

        <!-- Chat Area -->
        <div id="chat-history" class="chat-history">
            <div class="message bot">
                <div class="bubble">Hello! I'm your AI Tutor. How can I help you improve your English today? 👋</div>
            </div>
        </div>

        <!-- Scroll Button (Hidden by default) -->
        <button id="scroll-btn" class="scroll-btn" type="button">
            ↓
        </button>

        <!-- Input Area -->
        <form id="chat-form" class="chat-controls">
            <input type="text" id="chat-input" placeholder="Type your question..." required autocomplete="off">
            <button type="submit" class="btn btn-primary send-btn">
                <span>Send</span>
                <i>➤</i>
            </button>
        </form>
    </div>

    <div style="text-align: center; margin-top: 10px;">
        <a href="<?= BASE_URL ?>/dashboard.php" class="btn">Back to Dashboard</a>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('chat-form');
        const input = document.getElementById('chat-input');
        const history = document.getElementById('chat-history');
        const scrollBtn = document.getElementById('scroll-btn');

        // Scroll Logic: Soft Autoscroll
        function scrollToBottomIfNearEnd() {
            const el = document.getElementById('chat-history');
            if (!el) return;

            const nearBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 80;
            if (nearBottom) {
                el.scrollTop = el.scrollHeight;
            }
        }

        // Force scroll for button click
        function forceScrollToBottom() {
            history.scrollTop = history.scrollHeight;
        }

        // Toggle Scroll Button
        history.addEventListener('scroll', () => {
            const threshold = 150;
            const isScrolledUp = history.scrollHeight - history.scrollTop - history.clientHeight > threshold;
            if (isScrolledUp) {
                scrollBtn.classList.add('visible');
            } else {
                scrollBtn.classList.remove('visible');
            }
        });

        scrollBtn.addEventListener('click', () => {
            forceScrollToBottom();
        });

        function appendMessage(text, isUser) {
            const msgDiv = document.createElement('div');
            msgDiv.className = 'message ' + (isUser ? 'user' : 'bot');

            let contentHtml = text;
            if (!isUser) {
                if (window.formatBotMessage) {
                    contentHtml = window.formatBotMessage(text);
                }
            } else {
                contentHtml = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
            }

            msgDiv.innerHTML = `<div class="bubble">${contentHtml}</div>`;
            history.appendChild(msgDiv);

            // Soft Scroll
            scrollToBottomIfNearEnd();

            if (!isUser && window.appendChatbotQuickActions) {
                window.appendChatbotQuickActions(msgDiv, text);
                // Re-check scroll after actions
                setTimeout(() => scrollToBottomIfNearEnd(), 50);
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const msg = input.value.trim();
            if (!msg) return;

            // User Message
            appendMessage(msg, true);
            input.value = '';
            input.disabled = true;

            // Loading
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'message bot typing';
            loadingDiv.innerHTML = `
                <div class="bubble">
                    <div class="typing-indicator">
                        <span></span><span></span><span></span>
                    </div>
                </div>`;
            history.appendChild(loadingDiv);
            scrollToBottomIfNearEnd(); // Soft scroll for loading

            try {
                const res = await fetch('<?= BASE_URL ?>/services/api_chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: msg })
                });

                history.removeChild(loadingDiv); // Remove loading

                const data = await res.json();

                if (data.reply) {
                    appendMessage(data.reply, false);
                } else if (data.error) {
                    appendMessage("Error: " + data.error, false);
                }
            } catch (err) {
                if (history.contains(loadingDiv)) history.removeChild(loadingDiv);
                appendMessage("Connection error. Please try again.", false);
            }

            input.disabled = false;
            input.focus();
        });
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>