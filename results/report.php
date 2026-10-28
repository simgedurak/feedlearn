<?php
session_start();
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$user = current_user();
if (!$user) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$range = isset($_GET['range']) ? (int) $_GET['range'] : 30; // Default 30 days
$stats = db_get_report_stats($user['id'], $range === 0 ? null : $range);
$recentEvals = db_get_recent_evaluations_for_user($user['id'], 10);

require_once __DIR__ . '/../templates/header.php';
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="section">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 24px;">
        <div>
            <h2>Detailed Progress Report 📊</h2>
            <p class="muted" style="margin-bottom:0;">An overview of your learning journey.</p>
        </div>

        <!-- Time Filter -->
        <div
            style="background:#fff; border:1px solid var(--border-light); border-radius:99px; padding:4px; display:flex;">
            <a href="?range=7" class="btn"
                style="padding:6px 16px; font-size:0.85rem; border:none; background: <?= $range == 7 ? 'var(--secondary-soft)' : 'transparent' ?>; color: <?= $range == 7 ? 'var(--secondary-dark)' : 'var(--text-muted)' ?>;">7
                Days</a>
            <a href="?range=30" class="btn"
                style="padding:6px 16px; font-size:0.85rem; border:none; background: <?= $range == 30 ? 'var(--secondary-soft)' : 'transparent' ?>; color: <?= $range == 30 ? 'var(--secondary-dark)' : 'var(--text-muted)' ?>;">30
                Days</a>
            <a href="?range=90" class="btn"
                style="padding:6px 16px; font-size:0.85rem; border:none; background: <?= $range == 90 ? 'var(--secondary-soft)' : 'transparent' ?>; color: <?= $range == 90 ? 'var(--secondary-dark)' : 'var(--text-muted)' ?>;">90
                Days</a>
            <a href="?range=0" class="btn"
                style="padding:6px 16px; font-size:0.85rem; border:none; background: <?= $range == 0 ? 'var(--secondary-soft)' : 'transparent' ?>; color: <?= $range == 0 ? 'var(--secondary-dark)' : 'var(--text-muted)' ?>;">All
                Time</a>
        </div>
    </div>

    <!-- A) KPI Cards -->
    <div class="grid" style="margin-bottom: 32px;">
        <div class="card" style="text-align:center; padding: 24px;">
            <div class="muted"
                style="font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Accuracy
            </div>
            <div style="font-size: 2.2rem; font-weight:800; color: var(--secondary-dark);">
                <?= $stats['avg_accuracy'] ?>%
            </div>
        </div>

        <div class="card" style="text-align:center; padding: 24px;">
            <div class="muted"
                style="font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Completed
            </div>
            <div style="font-size: 2.2rem; font-weight:800; color: var(--text-main);">
                <?= $stats['total_quizzes'] ?>
            </div>
        </div>

        <div class="card" style="text-align:center; padding: 24px;">
            <div class="muted"
                style="font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Streak</div>
            <div style="font-size: 2.2rem; font-weight:800; color: #f97316;">
                <?= $stats['study_streak'] ?>
            </div>
            <div style="font-size:0.75rem; color:var(--text-muted);">days</div>
        </div>

        <div class="card" style="text-align:center; padding: 24px;">
            <div class="muted"
                style="font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Est. Time
            </div>
            <div style="font-size: 2.2rem; font-weight:800; color: #6366f1;">
                <?= count($recentEvals) * 5 ?>m
            </div>
        </div>
    </div>

    <!-- B) Charts Row -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 32px;">

        <!-- Score History -->
        <div class="card">
            <h3 class="card-title">Performance Trend</h3>
            <div style="height:300px; position:relative;">
                <canvas id="historyChart"></canvas>
            </div>
        </div>

        <!-- Activity Breakdown -->
        <div class="card">
            <h3 class="card-title">Mix</h3>
            <div style="height:200px; position:relative; display:flex; justify-content:center;">
                <canvas id="breakdownChart"></canvas>
            </div>

            <div style="margin-top:20px;">
                <h4 style="font-size:0.9rem; margin-bottom:8px;">Top Weakness</h4>
                <?php if (!empty($stats['weak_topics'])):
                    $topWeak = key($stats['weak_topics']); ?>
                    <div
                        style="padding:10px; background:#fee2e2; border-radius:8px; color:#991b1b; font-weight:600; text-transform:capitalize;">
                        <?= str_replace('_', ' ', $topWeak) ?>
                    </div>
                <?php else: ?>
                    <div class="muted">No data yet</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- C) Detailed Weakness Analysis -->
    <div class="card" style="margin-bottom: 32px;">
        <h3 class="card-title">Problem Areas & Recommendations</h3>
        <div class="grid" style="grid-template-columns: 1fr 1fr;">

            <!-- Weak Topics List -->
            <div>
                <?php if (empty($stats['weak_topics'])): ?>
                    <p class="muted">Great job! No specific weak topics detected in this period.</p>
                <?php else: ?>
                    <ul class="list">
                        <?php foreach ($stats['weak_topics'] as $topic => $count): ?>
                            <li style="display:flex; justify-content:space-between; align-items:center;">
                                <span
                                    style="font-weight:600; text-transform:capitalize;"><?= htmlspecialchars(str_replace('_', ' ', $topic)) ?></span>
                                <div
                                    style="width:100px; height:8px; background:var(--bg-body); border-radius:99px; margin-left:auto; margin-right:12px;">
                                    <div
                                        style="width:<?= min(100, $count * 20) ?>%; height:100%; background:var(--danger); border-radius:99px;">
                                    </div>
                                </div>
                                <span style="font-size:0.8rem; color:var(--text-muted);"><?= $count ?> errors</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Recommendation Action -->
            <div
                style="display:flex; flex-direction:column; justify-content:center; padding:24px; background:var(--secondary-soft); border-radius:var(--radius-md);">
                <h4 style="color:var(--secondary-dark);">🚀 Personalized Plan</h4>
                <p style="font-size:0.9rem; color:var(--text-main);">To boost your score, we recommend focusing on your
                    specific gaps.</p>
                <div style="margin-top:auto;">
                    <a href="<?= BASE_URL ?>/activity/quiz.php" class="btn btn-primary" style="width:100%;">
                        Start Practice Session
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- D) Recent Sessions Table -->
    <div class="card">
        <h3 class="card-title">Recent History</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Activity</th>
                        <th>Score</th>
                        <th>Level</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentEvals)): ?>
                        <tr>
                            <td colspan="5" class="text-center muted">No records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentEvals as $eval): ?>
                            <tr>
                                <td><?= date('M d', strtotime($eval['created_at'])) ?></td>
                                <td style="text-transform:capitalize; font-weight:600;">
                                    <?= htmlspecialchars($eval['activity_type']) ?>
                                </td>
                                <td>
                                    <span
                                        style="font-weight:700; color: <?= $eval['score'] >= 80 ? 'var(--success)' : ($eval['score'] >= 50 ? 'var(--warning)' : 'var(--danger)') ?>;">
                                        <?= round($eval['score']) ?>%
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($eval['cefr_level']) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/results/feedback.php?id=<?= $eval['id'] ?>" class="btn"
                                        style="padding:6px 16px; font-size:0.8rem;">Review</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Prepare Data
    const historyData = <?= json_encode($stats['score_history']) ?>;
    const activityData = <?= json_encode($stats['activity_breakdown']) ?>;

    // 1. History Chart
    const ctxHistory = document.getElementById('historyChart').getContext('2d');
    new Chart(ctxHistory, {
        type: 'line',
        data: {
            labels: historyData.map(d => d.date),
            datasets: [{
                label: 'Accuracy',
                data: historyData.map(d => d.score),
                borderColor: '#2dd4bf',
                backgroundColor: (context) => {
                    const ctx = context.chart.ctx;
                    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(45, 212, 191, 0.4)');
                    gradient.addColorStop(1, 'rgba(45, 212, 191, 0)');
                    return gradient;
                },
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#2dd4bf',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, max: 100, grid: { borderDash: [4, 4] } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Breakdown Chart
    const ctxBreak = document.getElementById('breakdownChart').getContext('2d');
    const actKeys = Object.keys(activityData);
    const actVals = Object.values(activityData);

    new Chart(ctxBreak, {
        type: 'doughnut',
        data: {
            labels: actKeys.map(k => k.charAt(0).toUpperCase() + k.slice(1)),
            datasets: [{
                data: actVals,
                backgroundColor: ['#f43f5e', '#2dd4bf', '#f97316', '#6366f1'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false } // Hide default legend, too cluttered
            }
        }
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>