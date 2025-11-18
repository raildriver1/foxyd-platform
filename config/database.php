<?php
/**
 * Модуль работы с базой данных Foxyd
 * Платформа для репетиторов по математике
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dbDir = dirname(DB_PATH);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0777, true);
            }
            
            $this->connection = new PDO('sqlite:' . DB_PATH);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $this->initTables();
            
        } catch (PDOException $e) {
            die("Ошибка подключения к БД: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    private function initTables() {
        // Таблица пользователей
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                name TEXT NOT NULL,
                phone TEXT,
                role TEXT DEFAULT 'student',
                avatar TEXT,
                verified INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Добавляем поле verified если его нет (для существующих БД)
        try {
            $this->connection->exec("ALTER TABLE users ADD COLUMN verified INTEGER DEFAULT 1");
        } catch (PDOException $e) {
            // Поле уже существует, игнорируем
        }
        
        // Таблица репетиторов (дополнительная информация)
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS tutors (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                description TEXT,
                experience INTEGER DEFAULT 0,
                price_per_hour INTEGER DEFAULT 0,
                subjects TEXT,
                rating REAL DEFAULT 5.0,
                photo TEXT,
                verified INTEGER DEFAULT 0,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        
        // Таблица бронирований занятий
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS bookings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL,
                tutor_id INTEGER NOT NULL,
                lesson_date DATETIME NOT NULL,
                duration INTEGER DEFAULT 60,
                status TEXT DEFAULT 'pending',
                price INTEGER NOT NULL,
                payment_status TEXT DEFAULT 'unpaid',
                payment_id TEXT,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES users(id),
                FOREIGN KEY (tutor_id) REFERENCES tutors(id)
            )
        ");
        
        // Таблица записей к инструкторам (appointments)
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS appointments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL,
                instructor_id INTEGER NOT NULL,
                appointment_date DATETIME NOT NULL,
                duration INTEGER DEFAULT 60,
                notes TEXT,
                status TEXT DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES users(id),
                FOREIGN KEY (instructor_id) REFERENCES users(id)
            )
        ");
        
        // Таблица курсов (для совместимости)
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS courses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                description TEXT,
                short_description TEXT,
                instructor_id INTEGER,
                cover_image TEXT,
                duration INTEGER DEFAULT 0,
                level TEXT DEFAULT 'beginner',
                category TEXT,
                price INTEGER DEFAULT 0,
                rating REAL DEFAULT 5.0,
                students_count INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (instructor_id) REFERENCES users(id)
            )
        ");
        
        // Таблица уроков
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS lessons (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                course_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                content TEXT,
                video_url TEXT,
                order_num INTEGER DEFAULT 0,
                duration INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (course_id) REFERENCES courses(id)
            )
        ");
        
        // Таблица прогресса студентов
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS progress (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                course_id INTEGER NOT NULL,
                lesson_id INTEGER NOT NULL,
                completed INTEGER DEFAULT 0,
                progress_percent INTEGER DEFAULT 0,
                completed_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (course_id) REFERENCES courses(id),
                FOREIGN KEY (lesson_id) REFERENCES lessons(id)
            )
        ");
        
        // Таблица уведомлений
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                message TEXT NOT NULL,
                type TEXT DEFAULT 'info',
                is_read INTEGER DEFAULT 0,
                link TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        
        $this->createDefaultData();
    }
    
    private function createDefaultData() {
        // Создаем админа
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, role) 
                VALUES ('admin@foxyd.ru', ?, 'Администратор', 'admin')
            ");
            $stmt->execute([$password]);
        }
        
        // Создаем тестовых репетиторов
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM tutors");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            // Репетитор 1
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES ('ivanov@foxyd.ru', ?, 'Иванов Иван Иванович', '+7 (999) 123-45-67', 'tutor')
            ");
            $stmt->execute([$password]);
            $userId = $this->connection->lastInsertId();
            
            $stmt = $this->connection->prepare("
                INSERT INTO tutors (user_id, description, experience, price_per_hour, subjects, rating, verified) 
                VALUES (?, 'Кандидат физико-математических наук. 15 лет опыта преподавания. Готовлю к ЕГЭ и ОГЭ по математике.', 15, 1500, 'Алгебра, Геометрия, Математический анализ', 4.9, 1)
            ");
            $stmt->execute([$userId]);
            
            // Репетитор 2
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES ('petrova@foxyd.ru', ?, 'Петрова Мария Сергеевна', '+7 (999) 234-56-78', 'tutor')
            ");
            $stmt->execute([$password]);
            $userId = $this->connection->lastInsertId();
            
            $stmt = $this->connection->prepare("
                INSERT INTO tutors (user_id, description, experience, price_per_hour, subjects, rating, verified) 
                VALUES (?, 'Магистр математики МГУ. Специализируюсь на подготовке к олимпиадам и вузовским экзаменам.', 8, 2000, 'Высшая математика, Теория вероятностей, Статистика', 5.0, 1)
            ");
            $stmt->execute([$userId]);
            
            // Репетитор 3
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES ('sidorov@foxyd.ru', ?, 'Сидоров Петр Александрович', '+7 (999) 345-67-89', 'tutor')
            ");
            $stmt->execute([$password]);
            $userId = $this->connection->lastInsertId();
            
            $stmt = $this->connection->prepare("
                INSERT INTO tutors (user_id, description, experience, price_per_hour, subjects, rating, verified) 
                VALUES (?, 'Учитель высшей категории. Работаю с учениками 5-11 классов. Понятно объясняю сложные темы.', 12, 1200, 'Алгебра, Геометрия, Подготовка к ЕГЭ', 4.8, 1)
            ");
            $stmt->execute([$userId]);
            
            // Репетитор 4
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES ('kozlova@foxyd.ru', ?, 'Козлова Анна Викторовна', '+7 (999) 456-78-90', 'tutor')
            ");
            $stmt->execute([$password]);
            $userId = $this->connection->lastInsertId();
            
            $stmt = $this->connection->prepare("
                INSERT INTO tutors (user_id, description, experience, price_per_hour, subjects, rating, verified) 
                VALUES (?, 'Преподаватель университета. Специализация - высшая математика и математический анализ.', 10, 1800, 'Математический анализ, Дифференциальные уравнения, Линейная алгебра', 4.7, 1)
            ");
            $stmt->execute([$userId]);
            
            // Репетитор 5
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES ('novikov@foxyd.ru', ?, 'Новиков Дмитрий Олегович', '+7 (999) 567-89-01', 'tutor')
            ");
            $stmt->execute([$password]);
            $userId = $this->connection->lastInsertId();
            
            $stmt = $this->connection->prepare("
                INSERT INTO tutors (user_id, description, experience, price_per_hour, subjects, rating, verified) 
                VALUES (?, 'Молодой преподаватель. Современные методики обучения. Работаю с детьми и взрослыми.', 5, 1000, 'Алгебра, Геометрия, Основы математики', 4.6, 1)
            ");
            $stmt->execute([$userId]);
        }
        
        // Создаем тестовых студентов
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM users WHERE role = 'student'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            // Студент 1
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES ('student1@foxyd.ru', ?, 'Кузнецов Алексей Дмитриевич', '+7 (999) 601-11-11', 'student')
            ");
            $stmt->execute([$password]);
            
            // Студент 2
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES ('student2@foxyd.ru', ?, 'Соколова Дарья Андреевна', '+7 (999) 602-22-22', 'student')
            ");
            $stmt->execute([$password]);
            
            // Студент 3
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES ('student3@foxyd.ru', ?, 'Попов Михаил Викторович', '+7 (999) 603-33-33', 'student')
            ");
            $stmt->execute([$password]);
        }
        
        // Создаем инструкторов и курсы
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM courses");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            // Инструктор 1 - Смирнова Елена
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES ('smirnova@foxyd.ru', ?, 'Смирнова Елена Александровна', '+7 (999) 111-22-33', 'instructor')
            ");
            $stmt->execute([$password]);
            $instructor1 = $this->connection->lastInsertId();
            
            // Инструктор 2 - Волков Сергей
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES ('volkov@foxyd.ru', ?, 'Волков Сергей Николаевич', '+7 (999) 222-33-44', 'instructor')
            ");
            $stmt->execute([$password]);
            $instructor2 = $this->connection->lastInsertId();
            
            // Инструктор 3 - Морозова Ольга
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES ('morozova@foxyd.ru', ?, 'Морозова Ольга Ивановна', '+7 (999) 333-44-55', 'instructor')
            ");
            $stmt->execute([$password]);
            $instructor3 = $this->connection->lastInsertId();
            
            // Инструктор 4 - Лебедев Андрей
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT INTO users (email, password, name, phone, role) 
                VALUES ('lebedev@foxyd.ru', ?, 'Лебедев Андрей Павлович', '+7 (999) 444-55-66', 'instructor')
            ");
            $stmt->execute([$password]);
            $instructor4 = $this->connection->lastInsertId();
            
            // Курс 1 - Алгебра для 7 класса
            $stmt = $this->connection->prepare("
                INSERT INTO courses (title, description, short_description, instructor_id, cover_image, duration, level, category, price, rating, students_count) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'Алгебра для 7 класса',
                'Полный курс алгебры для учащихся 7 класса. Изучим все основные темы: линейные уравнения, системы уравнений, функции и графики. Курс включает теорию, примеры решения задач и практические упражнения.',
                'Основы алгебры: уравнения, функции, графики',
                $instructor1,
                'algebra-7.jpg',
                720,
                'beginner',
                'Школьная математика',
                3500,
                4.8,
                124
            ]);
            $course1 = $this->connection->lastInsertId();
            
            // Уроки для курса 1
            $lessons1 = [
                ['Введение в алгебру', 'Что такое алгебра и зачем она нужна', 45],
                ['Числовые выражения', 'Работа с числовыми выражениями и их упрощение', 60],
                ['Линейные уравнения', 'Решение линейных уравнений с одной переменной', 75],
                ['Системы линейных уравнений', 'Методы решения систем уравнений', 90],
                ['Функции и их графики', 'Понятие функции, построение графиков', 80],
                ['Степени и их свойства', 'Свойства степеней, действия со степенями', 70],
                ['Многочлены', 'Действия с многочленами', 85],
                ['Формулы сокращенного умножения', 'Основные формулы и их применение', 75]
            ];
            
            foreach ($lessons1 as $i => $lesson) {
                $stmt = $this->connection->prepare("
                    INSERT INTO lessons (course_id, title, content, order_num, duration) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$course1, $lesson[0], $lesson[1], $i + 1, $lesson[2]]);
            }
            
            // Курс 2 - Геометрия для начинающих
            $stmt = $this->connection->prepare("
                INSERT INTO courses (title, description, short_description, instructor_id, cover_image, duration, level, category, price, rating, students_count) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'Геометрия для начинающих',
                'Введение в геометрию: основные понятия, фигуры, их свойства. Изучим треугольники, четырехугольники, окружности. Научимся решать задачи на построение и вычисление.',
                'Основы геометрии: фигуры, свойства, задачи',
                $instructor2,
                'geometry-basic.jpg',
                600,
                'beginner',
                'Геометрия',
                3000,
                4.9,
                98
            ]);
            $course2 = $this->connection->lastInsertId();
            
            // Уроки для курса 2
            $lessons2 = [
                ['Основные геометрические понятия', 'Точка, прямая, плоскость, отрезок', 50],
                ['Углы и их виды', 'Острые, прямые, тупые углы. Измерение углов', 55],
                ['Треугольники', 'Виды треугольников, их свойства', 70],
                ['Признаки равенства треугольников', 'Три признака равенства треугольников', 80],
                ['Четырехугольники', 'Параллелограмм, прямоугольник, ромб, квадрат', 75],
                ['Окружность', 'Радиус, диаметр, хорда, касательная', 65],
                ['Площади фигур', 'Формулы площадей основных фигур', 70]
            ];
            
            foreach ($lessons2 as $i => $lesson) {
                $stmt = $this->connection->prepare("
                    INSERT INTO lessons (course_id, title, content, order_num, duration) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$course2, $lesson[0], $lesson[1], $i + 1, $lesson[2]]);
            }
            
            // Курс 3 - Подготовка к ЕГЭ по математике
            $stmt = $this->connection->prepare("
                INSERT INTO courses (title, description, short_description, instructor_id, cover_image, duration, level, category, price, rating, students_count) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'Подготовка к ЕГЭ по математике (профильный уровень)',
                'Комплексная подготовка к ЕГЭ по математике профильного уровня. Разбор всех типов заданий, решение задач повышенной сложности, тренировочные тесты. Систематизация знаний и отработка навыков.',
                'Полная подготовка к ЕГЭ профильного уровня',
                $instructor1,
                'ege-math.jpg',
                1200,
                'advanced',
                'ЕГЭ/ОГЭ',
                8500,
                5.0,
                215
            ]);
            $course3 = $this->connection->lastInsertId();
            
            // Уроки для курса 3
            $lessons3 = [
                ['Структура ЕГЭ по математике', 'Разбор структуры экзамена, критерии оценивания', 60],
                ['Алгебраические уравнения', 'Решение уравнений всех типов', 90],
                ['Неравенства', 'Алгебраические и логарифмические неравенства', 85],
                ['Функции и графики', 'Исследование функций, построение графиков', 95],
                ['Производная и ее применение', 'Нахождение производной, применение к исследованию функций', 100],
                ['Интеграл', 'Первообразная и неопределенный интеграл', 90],
                ['Тригонометрия', 'Тригонометрические уравнения и неравенства', 95],
                ['Стереометрия', 'Решение задач по стереометрии', 110],
                ['Планиметрия', 'Сложные задачи по планиметрии', 100],
                ['Задачи с параметром', 'Методы решения задач с параметрами', 120],
                ['Теория вероятностей', 'Основы теории вероятностей для ЕГЭ', 80],
                ['Текстовые задачи', 'Задачи на проценты, движение, работу', 85]
            ];
            
            foreach ($lessons3 as $i => $lesson) {
                $stmt = $this->connection->prepare("
                    INSERT INTO lessons (course_id, title, content, order_num, duration) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$course3, $lesson[0], $lesson[1], $i + 1, $lesson[2]]);
            }
            
            // Курс 4 - Высшая математика для студентов
            $stmt = $this->connection->prepare("
                INSERT INTO courses (title, description, short_description, instructor_id, cover_image, duration, level, category, price, rating, students_count) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'Высшая математика: основы',
                'Курс высшей математики для студентов 1-2 курсов технических специальностей. Математический анализ, линейная алгебра, аналитическая геометрия, дифференциальные уравнения.',
                'Математический анализ и линейная алгебра',
                $instructor3,
                'higher-math.jpg',
                1500,
                'advanced',
                'Высшая математика',
                12000,
                4.7,
                87
            ]);
            $course4 = $this->connection->lastInsertId();
            
            // Уроки для курса 4
            $lessons4 = [
                ['Пределы и непрерывность', 'Понятие предела, вычисление пределов', 100],
                ['Производная функции', 'Определение производной, правила дифференцирования', 110],
                ['Исследование функций', 'Применение производной к исследованию функций', 120],
                ['Неопределенный интеграл', 'Первообразная, методы интегрирования', 115],
                ['Определенный интеграл', 'Формула Ньютона-Лейбница, приложения', 105],
                ['Матрицы и определители', 'Действия с матрицами, вычисление определителей', 90],
                ['Системы линейных уравнений', 'Методы решения систем', 95],
                ['Векторная алгебра', 'Операции с векторами', 85],
                ['Аналитическая геометрия', 'Уравнения прямых и плоскостей', 100],
                ['Дифференциальные уравнения', 'Уравнения первого порядка', 110]
            ];
            
            foreach ($lessons4 as $i => $lesson) {
                $stmt = $this->connection->prepare("
                    INSERT INTO lessons (course_id, title, content, order_num, duration) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$course4, $lesson[0], $lesson[1], $i + 1, $lesson[2]]);
            }
            
            // Курс 5 - Математика для младших школьников
            $stmt = $this->connection->prepare("
                INSERT INTO courses (title, description, short_description, instructor_id, cover_image, duration, level, category, price, rating, students_count) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'Математика 5-6 класс: увлекательный курс',
                'Интересный и понятный курс математики для учеников 5-6 классов. Дроби, проценты, пропорции, уравнения. Развиваем логическое мышление и математическую интуицию через интересные задачи.',
                'Математика средней школы: дроби, проценты, уравнения',
                $instructor4,
                'math-5-6.jpg',
                480,
                'beginner',
                'Школьная математика',
                2500,
                4.6,
                156
            ]);
            $course5 = $this->connection->lastInsertId();
            
            // Уроки для курса 5
            $lessons5 = [
                ['Натуральные числа', 'Повторение: действия с натуральными числами', 50],
                ['Обыкновенные дроби', 'Сложение и вычитание дробей', 65],
                ['Умножение и деление дробей', 'Действия с дробями', 70],
                ['Десятичные дроби', 'Операции с десятичными дробями', 60],
                ['Проценты', 'Понятие процента, задачи на проценты', 75],
                ['Пропорции', 'Прямая и обратная пропорциональность', 65],
                ['Простейшие уравнения', 'Решение уравнений', 55],
                ['Координатная плоскость', 'Работа с координатами', 60]
            ];
            
            foreach ($lessons5 as $i => $lesson) {
                $stmt = $this->connection->prepare("
                    INSERT INTO lessons (course_id, title, content, order_num, duration) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$course5, $lesson[0], $lesson[1], $i + 1, $lesson[2]]);
            }
            
            // Курс 6 - Олимпиадная математика
            $stmt = $this->connection->prepare("
                INSERT INTO courses (title, description, short_description, instructor_id, cover_image, duration, level, category, price, rating, students_count) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'Олимпиадная математика: от школьных до всероссийских олимпиад',
                'Курс для учеников, желающих участвовать в математических олимпиадах. Нестандартные задачи, логика, комбинаторика, теория чисел. Разбор олимпиадных задач разного уровня сложности.',
                'Подготовка к математическим олимпиадам',
                $instructor2,
                'olympiad-math.jpg',
                900,
                'advanced',
                'Олимпиадная математика',
                7500,
                4.9,
                62
            ]);
            $course6 = $this->connection->lastInsertId();
            
            // Уроки для курса 6
            $lessons6 = [
                ['Введение в олимпиадную математику', 'Особенности олимпиадных задач', 60],
                ['Логические задачи', 'Развитие логического мышления', 80],
                ['Принцип Дирихле', 'Применение принципа Дирихле', 90],
                ['Комбинаторика', 'Основы комбинаторики', 95],
                ['Теория чисел', 'Делимость, простые числа', 100],
                ['Геометрические задачи', 'Нестандартные геометрические задачи', 105],
                ['Инварианты и полуинварианты', 'Методы решения задач', 110],
                ['Раскраски и разбиения', 'Задачи на раскраски', 100]
            ];
            
            foreach ($lessons6 as $i => $lesson) {
                $stmt = $this->connection->prepare("
                    INSERT INTO lessons (course_id, title, content, order_num, duration) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$course6, $lesson[0], $lesson[1], $i + 1, $lesson[2]]);
            }
        }
        
        // Создаем тестовые записи в календаре
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM appointments");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            // Получаем ID студента и инструкторов
            $studentStmt = $this->connection->query("SELECT id FROM users WHERE role = 'student' OR role = 'admin' LIMIT 1");
            $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($student) {
                $instructorStmt = $this->connection->query("SELECT id FROM users WHERE role = 'instructor' ORDER BY id");
                $instructors = $instructorStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Создаем несколько тестовых встреч
                $appointments = [
                    [
                        'instructor_id' => $instructors[0]['id'] ?? $instructor1,
                        'date' => date('Y-m-d H:i:s', strtotime('+2 days 14:00')),
                        'duration' => 60,
                        'notes' => 'Консультация по подготовке к ЕГЭ',
                        'status' => 'confirmed'
                    ],
                    [
                        'instructor_id' => $instructors[1]['id'] ?? $instructor2,
                        'date' => date('Y-m-d H:i:s', strtotime('+5 days 16:00')),
                        'duration' => 90,
                        'notes' => 'Разбор сложных задач по геометрии',
                        'status' => 'pending'
                    ],
                    [
                        'instructor_id' => $instructors[2]['id'] ?? $instructor3,
                        'date' => date('Y-m-d H:i:s', strtotime('+7 days 11:00')),
                        'duration' => 60,
                        'notes' => 'Помощь с высшей математикой',
                        'status' => 'pending'
                    ]
                ];
                
                foreach ($appointments as $apt) {
                    $stmt = $this->connection->prepare("
                        INSERT INTO appointments (student_id, instructor_id, appointment_date, duration, notes, status) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $student['id'],
                        $apt['instructor_id'],
                        $apt['date'],
                        $apt['duration'],
                        $apt['notes'],
                        $apt['status']
                    ]);
                }
            }
        }
    }
}

$db = Database::getInstance();
$conn = $db->getConnection();
