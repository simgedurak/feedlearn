<?php
session_start();
require_once 'config/constants.php';
require_once 'config/db.php';
require_once 'config/auth.php';

// Auto-redirect if logged in (unless previewing)
if (current_user() && empty($_GET['preview'])) {
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}
?>
<!-- LANDING UPDATED -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FeedLearn - Learn English Smarter</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/app.css?v=20260122">
    <script src="<?php echo BASE_URL; ?>/assets/js/app.js" defer></script>
</head>

<body class="landing-page">

    <!-- CUSTOM NAV FOR LANDING -->
    <header class="site-header landing-header">
        <div class="header-inner">
            <a href="#" class="brand-link">FeedLearn</a>
            <div style="display:flex; gap:16px;">
                <a href="<?php echo BASE_URL; ?>/auth/login.php" class="btn"
                    style="border:none; background:transparent;">Log
                    In</a>
                <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn-primary">Get Started</a>
            </div>
        </div>
    </header>

    <!-- 1. HERO SECTION -->
    <section class="hero-section">
        <div class="landing-container">
            <div class="hero-content">
                <h1 class="hero-title">Learn English Smarter<br>with <span>AI Support</span></h1>
                <p class="hero-subtitle">
                    Master grammar, vocabulary, and speaking with personalized feedback.
                    Track your progress and achieve your language goals faster.
                </p>
                <div class="hero-actions">
                    <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn-primary"
                        style="padding:16px 48px; font-size:1.1rem;">Let's Start Free</a>
                    <a href="#features" class="btn"
                        style="padding:16px 32px; font-size:1.1rem; background:#fff;">Explore Features</a>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. STATS (Pills/Cards) -->
    <section class="stats-section">
        <div class="landing-container">
            <div class="grid" style="grid-template-columns: repeat(4, 1fr); gap: 24px;">
                <div class="stat-card reveal">
                    <span class="stat-number" data-target="12430">0</span>
                    <span class="stat-label">Questions Answered</span>
                </div>
                <div class="stat-card reveal">
                    <span class="stat-number" data-target="150">0</span>
                    <span class="stat-label">Active Quizzes</span>
                </div>
                <div class="stat-card reveal">
                    <span class="stat-number" data-target="3200">0</span>
                    <span class="stat-label">Happy Learners</span>
                </div>
                <div class="stat-card reveal">
                    <span class="stat-number" data-target="98">0</span>
                    <span class="stat-label">% Exam Success</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. PLATFORM EXPERIENCE (Professional Mocks) -->
    <section id="features" class="section section-alt">
        <div class="landing-container">
            <div style="text-align:center; margin-bottom: 60px;">
                <h2>Experience the Platform</h2>
                <p class="muted">Powerful tools designed to accelerate your learning.</p>
            </div>

            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px;">
                <!-- 1. Quizzes -->
                <div class="preview-card reveal">
                    <div class="preview-icon">🧠</div>
                    <div class="preview-title">Interactive Quizzes</div>
                    <div class="preview-desc">Adaptive questions that test your grammar and vocabulary in real-time.
                    </div>
                    <div class="mock-ui">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span class="mock-pill"
                                style="background:var(--primary-soft); color:var(--primary); border:none;">Grammar</span>
                            <span style="font-size:0.75rem;">Q: 5/10</span>
                        </div>
                        <div class="mock-line" style="width:80%;"></div>
                        <div class="mock-line" style="width:50%;"></div>
                        <div style="margin-top:12px; display:flex; gap:8px;">
                            <div style="width:24px; height:24px; border-radius:50%; border:2px solid var(--secondary);">
                            </div>
                            <div class="mock-line" style="width:60%; margin-bottom:0;"></div>
                        </div>
                    </div>
                </div>

                <!-- 2. AI Tutor -->
                <div class="preview-card reveal" style="transform: translateY(-16px);"> <!-- Visual Lift -->
                    <div class="preview-icon">💬</div>
                    <div class="preview-title">AI Personal Tutor</div>
                    <div class="preview-desc">Get instant, personalized explanations for your mistakes anytime.</div>
                    <div class="mock-ui">
                        <div style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
                            <div style="width:24px; height:24px; background:var(--primary); border-radius:50%;"></div>
                            <div class="mock-line short" style="background:#fff; border:1px solid #e2e8f0;"></div>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <div style="width:24px; height:24px; background:#e2e8f0; border-radius:50%;"></div>
                            <div class="mock-line" style="width:70%;"></div>
                        </div>
                    </div>
                </div>

                <!-- 3. Analytics -->
                <div class="preview-card reveal">
                    <div class="preview-icon">📊</div>
                    <div class="preview-title">Detailed Analytics</div>
                    <div class="preview-desc">Visualize your growth with comprehensive charts and insights.</div>
                    <div class="mock-ui">
                        <div
                            style="display:flex; justify-content:space-between; align-items:end; height:50px; padding-bottom:4px; border-bottom:1px solid #e2e8f0;">
                            <div style="width:15%; height:40%; background:var(--primary-soft); border-radius:3px;">
                            </div>
                            <div style="width:15%; height:60%; background:var(--primary-soft); border-radius:3px;">
                            </div>
                            <div style="width:15%; height:30%; background:var(--primary-soft); border-radius:3px;">
                            </div>
                            <div style="width:15%; height:80%; background:var(--primary); border-radius:3px;"></div>
                            <div style="width:15%; height:50%; background:var(--primary-soft); border-radius:3px;">
                            </div>
                        </div>
                        <div style="margin-top:8px; font-weight:700; color:var(--text-main); font-size:0.8rem;">Weekly
                            Progress</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. HOW IT WORKS -->
    <section id="how-it-works" class="section">
        <div class="landing-container">
            <div style="text-align:center; margin-bottom:60px;">
                <h2>How It Works</h2>
                <p class="muted">Three simple steps to fluency.</p>
            </div>
            <div class="grid" style="grid-template-columns: repeat(3, 1fr); gap: 40px; text-align:center;">
                <div class="step-item reveal">
                    <div class="step-num">1</div>
                    <h3>Create Account</h3>
                    <p class="muted">Sign up in seconds and assess your level.</p>
                </div>
                <div class="step-item reveal">
                    <div class="step-num">2</div>
                    <h3>Practice Daily</h3>
                    <p class="muted">Complete short quizzes and chat with AI.</p>
                </div>
                <div class="step-item reveal">
                    <div class="step-num">3</div>
                    <h3>See Results</h3>
                    <p class="muted">Track improvement in your dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. TESTIMONIALS -->
    <section class="section section-alt">
        <div class="landing-container">
            <h2 style="text-align:center; margin-bottom:60px;">What Learners Say</h2>
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
                <div class="testimonial-card reveal">
                    <p class="muted">"I improved my grammar score by 20% in just two weeks. The explanations are so
                        clear!"</p>
                    <div class="user-profile">
                        <div class="avatar">AM</div>
                        <div>
                            <div style="font-weight:700; font-size:0.9rem;">Alex M.</div>
                            <div class="muted" style="font-size:0.8rem;">Intermediate</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card reveal">
                    <p class="muted">"The AI tutor helped me understand exactly why I was making mistakes with
                        prepositions."</p>
                    <div class="user-profile">
                        <div class="avatar" style="background:var(--secondary-soft); color:var(--secondary-dark);">SK
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:0.9rem;">Sarah K.</div>
                            <div class="muted" style="font-size:0.8rem;">Advanced</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card reveal">
                    <p class="muted">"I love the daily streak feature. It keeps me motivated to practice every single
                        morning."</p>
                    <div class="user-profile">
                        <div class="avatar" style="background:#fff7ed; color:#ea580c;">JD</div>
                        <div>
                            <div style="font-weight:700; font-size:0.9rem;">John D.</div>
                            <div class="muted" style="font-size:0.8rem;">Beginner</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. FINAL CTA -->
    <section
        style="background: linear-gradient(135deg, var(--secondary-dark) 0%, var(--primary) 100%); padding: 100px 0; text-align:center; color:#fff;">
        <div class="landing-container">
            <h2 style="color:#fff; font-size:clamp(2rem, 4vw, 3rem); margin-bottom:24px;">Ready to start learning?</h2>
            <p
                style="font-size:1.25rem; opacity:0.9; margin-bottom:48px; max-width:600px; margin-left:auto; margin-right:auto;">
                Join thousands of students mastering English today.
            </p>
            <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn"
                style="background:#fff; color:var(--primary); padding:18px 56px; font-weight:800; font-size:1.1rem; border-radius:99px; box-shadow:0 10px 25px rgba(0,0,0,0.1);">
                Get Started for Free
            </a>
        </div>
    </section>

    <footer style="text-align:center; padding:40px; border-top:1px solid var(--border-light); font-size:0.9rem;"
        class="muted">
        &copy; <?= date('Y') ?> FeedLearn. All rights reserved.
    </footer>

    <!-- JS ANIMATIONS -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Scroll Reveal
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        // Trigger count up if it's a stat number
                        if (entry.target.querySelector('.stat-number')) {
                            // find the number and start count
                            const num = entry.target.querySelector('.stat-number');
                            startCount(num);
                        }
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

            // 2. Count Up Logic
            function startCount(el) {
                if (el.dataset.counting) return;
                el.dataset.counting = "true";

                const target = +el.dataset.target; // Force number
                const duration = 2000; // ms
                const stepTime = 20;
                const steps = duration / stepTime;
                const increment = target / steps;

                let current = 0;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                        // Add % if it's the accuracy one
                        if (target === 92) el.innerText = Math.floor(current) + '%';
                        else el.innerText = Math.floor(current).toLocaleString();
                    } else {
                        if (target === 92) el.innerText = Math.floor(current) + '%';
                        else el.innerText = Math.floor(current).toLocaleString();
                    }
                }, stepTime);
            }
        });
    </script>
</body>

</html>