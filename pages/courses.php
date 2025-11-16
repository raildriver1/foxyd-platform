<?php
/**
 * Каталог всех курсов
 */

$courses = getAllCourses();

$title = 'Все курсы';
include 'templates/header.php';
?>

<div>
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem;">Каталог курсов</h1>
        <p style="font-size: 1.2rem; color: var(--medium-gray);">Выберите курс и начните обучение уже сегодня</p>
    </div>
    
    <div class="grid">
        <?php foreach ($courses as $course): ?>
            <div class="card" style="height: 100%; display: flex; flex-direction: column;">
                <div style="padding: 1.5rem; background: transparent; margin: -2.5rem -2.5rem 1.5rem;">
                    <div class="badge badge-orange" style="margin-bottom: 1rem;"><?= e($course['category']) ?></div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.8rem;"><?= e($course['title']) ?></h3>
                    <p style="color: var(--medium-gray); line-height: 1.6;"><?= e($course['short_description']) ?></p>
                </div>
                
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                        <div style="font-size: 0.9rem; color: var(--medium-gray);">
                            ⏱ <?= formatDuration($course['duration']) ?>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--medium-gray);">
                            ⭐ <?= number_format($course['rating'], 1) ?>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--medium-gray);">
                            👥 <?= $course['students_count'] ?>
                        </div>
                    </div>
                    
                    <div style="margin-top: auto;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <span style="font-size: 2rem; font-weight: 800; color: var(--primary-orange);">
                                <?= formatPrice($course['price']) ?>
                            </span>
                        </div>
                        
                        <a href="/course?id=<?= $course['id'] ?>" class="btn btn-primary" style="width: 100%; padding: 0.9rem;">
                            Подробнее
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
