<?php
/**
 * Отдельная страница курса с уроками
 */

$courseId = $_GET['id'] ?? null;
if (!$courseId) {
    redirect('/courses');
}

$course = getCourseById($courseId);
if (!$course) {
    redirect('/courses');
}

$lessons = getCourseLessons($courseId);
$progress = isLoggedIn() ? getCourseProgress(getCurrentUserId(), $courseId) : 0;

// Обработка отметки урока как завершенного
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn() && isset($_POST['complete_lesson'])) {
    $lessonId = (int)$_POST['lesson_id'];
    markLessonCompleted(getCurrentUserId(), $courseId, $lessonId);
    setFlash('success', 'Урок отмечен как пройденный!');
    redirect('/course?id=' . $courseId);
}

$title = $course['title'];
include 'templates/header.php';
?>

<div class="grid-2">
    <div>
        <div class="card">
            <div style="padding: 2rem; background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange)); color: white; border-radius: 12px; margin: -2.5rem -2.5rem 2rem;">
                <div class="badge" style="background: rgba(255, 255, 255, 0.3); color: white; margin-bottom: 1rem;"><?= e($course['category']) ?></div>
                <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem;"><?= e($course['title']) ?></h1>
                <p style="font-size: 1.1rem; opacity: 0.95; line-height: 1.8;"><?= e($course['description']) ?></p>
            </div>
            
            <div style="display: flex; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <div style="color: var(--medium-gray); font-size: 0.9rem;">Длительность</div>
                    <div style="font-weight: 700; font-size: 1.2rem;"><?= formatDuration($course['duration']) ?></div>
                </div>
                <div>
                    <div style="color: var(--medium-gray); font-size: 0.9rem;">Уровень</div>
                    <div style="font-weight: 700; font-size: 1.2rem;"><?= ucfirst($course['level']) ?></div>
                </div>
                <div>
                    <div style="color: var(--medium-gray); font-size: 0.9rem;">Рейтинг</div>
                    <div style="font-weight: 700; font-size: 1.2rem;">⭐ <?= number_format($course['rating'], 1) ?></div>
                </div>
            </div>
            
            <?php if (isLoggedIn()): ?>
                <div class="progress-container">
                    <div class="progress-header">
                        <span>Ваш прогресс</span>
                        <span style="font-weight: 700; color: var(--primary-orange);"><?= $progress ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.8rem;">О преподавателе</h2>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: 700;">
                    <?= substr($course['instructor_name'], 0, 1) ?>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 1.2rem;"><?= e($course['instructor_name']) ?></div>
                    <div style="color: var(--medium-gray);">Опытный преподаватель</div>
                </div>
            </div>
        </div>
    </div>
    
    <div>
        <div class="card" style="position: sticky; top: 100px;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.8rem;">Программа курса</h2>
            
            <?php if (empty($lessons)): ?>
                <p style="text-align: center; padding: 2rem; color: var(--medium-gray);">
                    Уроки скоро появятся
                </p>
            <?php else: ?>
                <div>
                    <?php foreach ($lessons as $lesson): ?>
                        <?php $completed = isLoggedIn() && isLessonCompleted(getCurrentUserId(), $lesson['id']); ?>
                        <div style="padding: 1.5rem; border: 2px solid <?= $completed ? 'var(--primary-orange)' : 'var(--border-color)' ?>; border-radius: 12px; margin-bottom: 1rem; transition: all 0.3s;" onmouseover="this.style.borderColor='var(--primary-orange)'" onmouseout="this.style.borderColor='<?= $completed ? 'var(--primary-orange)' : 'var(--border-color)' ?>'">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <h3 style="font-weight: 700; font-size: 1.1rem; flex: 1;">
                                    <?php if ($completed): ?>
                                        <span style="color: var(--primary-orange);">✓</span>
                                    <?php endif; ?>
                                    <?= e($lesson['title']) ?>
                                </h3>
                                <span class="badge badge-gray"><?= formatDuration($lesson['duration']) ?></span>
                            </div>
                            <p style="color: var(--medium-gray); font-size: 0.95rem; margin-bottom: 1rem;"><?= e($lesson['content']) ?></p>
                            
                            <?php if (isLoggedIn() && !$completed): ?>
                                <form method="POST" style="margin-top: 1rem;">
                                    <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                                    <button type="submit" name="complete_lesson" class="btn btn-secondary" style="width: 100%;">
                                        Отметить пройденным
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!isLoggedIn()): ?>
                <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(255, 107, 53, 0.1); border-radius: 8px; text-align: center;">
                    <p style="margin-bottom: 1rem; color: var(--dark-gray);">Войдите, чтобы начать обучение</p>
                    <a href="/register" class="btn btn-primary">
                        Зарегистрироваться
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
