<?php
// achievements.php
require_once __DIR__ . '/auth/_guard.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/services/gamification.php';
require_login();

$user = current_user();
$achievements = achievements_get($user['id']);
$cur = $achievements['current_badge'];
$next = $achievements['next_badge'];

require_once __DIR__ . '/templates/header.php';
?>

<div class="section">
    <div class="card" style="text-align:center; padding: 40px 24px;">
        <div style="font-size: 4rem; margin-bottom: 16px; animation: popIn 0.5s;">
            <?= $cur['icon'] ?>
        </div>
        <h2 style="margin-bottom: 8px; color: var(--primary);">
            <?= htmlspecialchars($cur['name']) ?>
        </h2>
        <p class="muted" style="font-size: 1.1rem;">
            Total XP: <strong>
                <?= $achievements['xp'] ?>
            </strong>
        </p>

        <?php if ($next): ?>
            <div style="max-width: 400px; margin: 24px auto;">
                <div
                    style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:8px; font-weight:600;">
                    <span>Progress to
                        <?= htmlspecialchars($next['name']) ?>
                    </span>
                    <span style="color:var(--primary);">
                        <?= $achievements['xp_to_next'] ?> XP left
                    </span>
                </div>
                <div
                    style="height: 12px; background: var(--bg-body); border-radius: 99px; overflow: hidden; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
                    <div
                        style="width: <?= $achievements['progress_percent'] ?>%; height: 100%; background: linear-gradient(90deg, var(--primary), var(--secondary)); border-radius: 99px; transition: width 1s ease;">
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p style="color: var(--success); font-weight: bold; margin-top: 24px;">
                🎉 You have reached the maximum rank!
            </p>
        <?php endif; ?>
    </div>
</div>

<div class="section">
    <h3>All Badges</h3>
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <?php foreach ($achievements['all_badges'] as $badge): ?>
            <div class="card badge-card <?= $badge['unlocked'] ? 'unlocked' : 'locked' ?>"
                style="text-align: center; padding: 32px 16px; position: relative; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 220px;">

                <?php if (!$badge['unlocked']): ?>
                    <div style="position: absolute; top: 12px; right: 12px; font-size: 1.2rem; opacity: 0.5;">🔒</div>
                <?php endif; ?>

                <div class="badge-icon"
                    style="font-size: 3.5rem; margin-bottom: 16px; filter: <?= $badge['unlocked'] ? 'none' : 'grayscale(1) opacity(0.4)' ?>; transition: transform 0.3s;">
                    <?= $badge['icon'] ?>
                </div>

                <h4
                    style="font-size: 1.1rem; margin-bottom: 8px; color: <?= $badge['unlocked'] ? 'var(--text-main)' : 'var(--text-muted)' ?>;">
                    <?= htmlspecialchars($badge['name']) ?>
                </h4>

                <p class="muted" style="font-size: 0.9rem;">
                    <?= $badge['unlocked'] ? 'Unlocked!' : 'Unlock at ' . $badge['min'] . ' XP' ?>
                </p>

                <?php if ($badge['current']): ?>
                    <span class="badge"
                        style="margin-top: 12px; background: var(--primary-soft); color: var(--primary);">Current Rank</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .badge-card.locked {
        background: var(--bg-body);
        border-style: dashed;
    }

    .badge-card.unlocked:hover .badge-icon {
        transform: scale(1.1) rotate(5deg);
    }
</style>

<?php require_once __DIR__ . '/templates/footer.php'; ?>