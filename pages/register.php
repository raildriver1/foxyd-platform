<?php
/**
 * Страница регистрации
 */

if (isLoggedIn()) {
    redirect('/cabinet');
}

$errors = [];
$name = '';
$email = '';
$phone = '';
$role = 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = $_POST['role'] ?? 'student';
    
    if (empty($name)) {
        $errors[] = 'Введите ваше имя';
    }
    
    if (empty($email) || !validateEmail($email)) {
        $errors[] = 'Введите корректный email';
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Пароль должен быть не менее 6 символов';
    }
    
    if ($password !== $password_confirm) {
        $errors[] = 'Пароли не совпадают';
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $errors[] = 'Пользователь с таким email уже существует';
        }
    }
    
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("
            INSERT INTO users (email, password, name, phone, role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$email, $passwordHash, $name, $phone, $role])) {
            $_SESSION['user_id'] = $conn->lastInsertId();
            setFlash('success', 'Регистрация прошла успешно!');
            redirect('/cabinet');
        } else {
            $errors[] = 'Ошибка при регистрации';
        }
    }
}

$title = 'Регистрация';
include 'templates/header.php';
?>

<div style="max-width: 500px; margin: 0 auto;">
    <div class="card">
        <h1 class="card-title" style="text-align: center; margin-bottom: 2rem;">Создать аккаунт</h1>
        
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
                <label for="name">Имя *</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-control" 
                    value="<?= e($name) ?>" 
                    required
                    placeholder="Ваше имя"
                >
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
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
                <label for="phone">Телефон</label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    class="form-control" 
                    value="<?= e($phone) ?>"
                    placeholder="+7 (999) 123-45-67"
                >
            </div>
            
            <div class="form-group">
                <label for="role">Я хочу</label>
                <select id="role" name="role" class="form-control">
                    <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Учиться (Студент)</option>
                    <option value="instructor" <?= $role === 'instructor' ? 'selected' : '' ?>>Преподавать (Преподаватель)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль *</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    required
                    placeholder="Минимум 6 символов"
                >
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Подтверждение пароля *</label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    class="form-control" 
                    required
                    placeholder="Повторите пароль"
                >
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                Зарегистрироваться
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 2rem; color: var(--medium-gray);">
            Уже есть аккаунт? <a href="/login" style="color: var(--primary-orange); text-decoration: none; font-weight: 600;">Войти</a>
        </p>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
