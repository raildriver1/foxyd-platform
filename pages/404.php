<?php
$title = 'Страница не найдена';
include 'templates/header.php';
?>

<div style="text-align: center; padding: 5rem 0;">
    <div style="font-size: 8rem; font-weight: 800; color: var(--primary-orange); margin-bottom: 1rem;">404</div>
    <h1 style="font-size: 3rem; margin-bottom: 1rem;">Страница не найдена</h1>
    <p style="font-size: 1.2rem; color: var(--medium-gray); margin-bottom: 2rem;">
        К сожалению, запрошенная страница не существует
    </p>
    <a href="/" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
        Вернуться на главную
    </a>
</div>

<?php include 'templates/footer.php'; ?>
