<?php
/**
 * Страница авторизации
 */

if (isLoggedIn()) {
    redirect('/cabinet');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email)) {
        $errors[] = 'Введите email';
    }
    
    if (empty($password)) {
        $errors[] = 'Введите пароль';
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            setFlash('success', 'Добро пожаловать, ' . $user['name'] . '!');
            redirect('/cabinet');
        } else {
            $errors[] = 'Неверный email или пароль';
        }
    }
}

$title = 'Вход';
include 'templates/header.php';
?>

<div style="max-width: 500px; margin: 0 auto;">
    <div class="card">
        <h1 class="card-title" style="text-align: center; margin-bottom: 2rem;">Вход в аккаунт</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="flash flash-error">
                <ul style="list-style: none;">
                    <?php foreach ($errors as $error): ?>
                        <li>• <?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    value="<?= e($email) ?>" 
                    required
                    placeholder="your@email.com"
                >
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    required
                    placeholder="Введите пароль"
                >
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                Войти
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 2rem; color: var(--medium-gray);">
            Нет аккаунта? <a href="/register" style="color: var(--primary-orange); text-decoration: none; font-weight: 600;">Зарегистрироваться</a>
        </p>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(0, 0, 0, 0.02); border-radius: 8px;">
            <p style="font-weight: bold; margin-bottom: 1rem; color: var(--dark-gray);">Тестовые аккаунты:</p>
            <div style="font-size: 0.9rem; line-height: 1.8; color: var(--medium-gray);">
                <p><strong>Админ:</strong> admin@foxyd.ru / admin123</p>
                <p><strong>Преподаватель:</strong> instructor@foxyd.ru / instructor123</p>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
