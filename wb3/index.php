<?php
header('Content-Type: index/html; charset=UTF-8');

function sanitize($data) {
  $data = trim($data);    // удаляет пробелы в начале и конце строки
  $data = stripslashes($data);    //удаляет экранирующие слеши (\) из строки
  $data = htmlspecialchars($data);    //преобразует специальные HTML-символы в их HTML-сущности
  return $data;
}

// Функция валидации
function validate_form($data) {
  $errors = [];

  // Валидация ФИО
  $fio = sanitize($data['fio']);
  if (!preg_match("/^[a-zA-Zа-яА-Я\s]+$/u", $fio)) {
      $errors['fio'] = "ФИО должно содержать только буквы и пробелы.";
  }
  if (strlen($fio) > 150) {
      $errors['fio'] = "ФИО не должно превышать 150 символов.";
  }

  // Валидация телефона
  $phone = sanitize($data['phone']);
  if (!preg_match("/^[0-9\+\-\(\)\s]+$/", $phone)) {
      $errors['phone'] = "Некорректный формат телефона.";
  }

  // Валидация email
  $email = sanitize($data['email']);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = "Некорректный формат email.";
  }

  // Валидация даты
  $dob = sanitize($data['dob']);
  if (empty($dob)) {
      $errors['dob'] = "Дата рождения обязательна для заполнения.";
  }

  // Валидация пола
  $gender = sanitize($data['gender']);
  if (!in_array($gender, ['male', 'female'])) {
      $errors['gender'] = "Некорректное значение пола.";
  }

  // Валидация ЯП
  $languages = $data['languages'];
  $allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
  foreach ($languages as $language) {
      if (!in_array($language, $allowed_languages)) {
          $errors['languages'] = "Недопустимый язык программирования.";
          break; 
      }
  }

  // Валидация биографии
  $bio = sanitize($data['bio']);
  if (empty($bio)) {
      $errors['bio'] = "Биография обязательна для заполнения.";
  }

  // Валидация чекбокса
  if (!isset($data['agreement'])) {
      $errors['agreement'] = "Необходимо согласиться с условиями.";
  }

  return $errors;
}

$user = 'u68691'; 
$password = '9388506'; 
$pdo = new PDO('mysql:host=localhost;dbname=u68691', $user, $password,
  [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); 

// Подготовленный запрос. Не именованные метки.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $errors = validate_form($_POST);
  if (empty($errors)) {
      try {
          $fio = sanitize($_POST['fio']);
          $phone = sanitize($_POST['phone']);
          $email = sanitize($_POST['email']);
          $dob = sanitize($_POST['dob']);
          $gender = sanitize($_POST['gender']);
          $bio = sanitize($_POST['bio']);

          // Вставка данных в таблицу users
          $stmt = $pdo->prepare("INSERT INTO users (fio, phone, email, dob, gender, bio) VALUES (?, ?, ?, ?, ?, ?)");
          $stmt->execute([$fio, $phone, $email, $dob, $gender, $bio]);

          $user_id = $pdo->lastInsertId(); //получаем id текущего пользователя

          // Вставка данных в таблицу users_languages
          $languages = $_POST['languages'];
          foreach ($languages as $language) {
            $stmt_lang = $pdo->prepare("SELECT lang_id FROM langs WHERE lang_name = ?");
            $stmt_lang->execute([$language]);
            $lang_result = $stmt_lang->fetch(PDO::FETCH_ASSOC);
            $lang_id = $lang_result['lang_id'];
            $stmt_user_lang = $pdo->prepare("INSERT INTO users_languages (user_id, lang_id) VALUES (?, ?)");
            $stmt_user_lang->execute([$user_id, $lang_id]);
          }

          echo "<p style='color:green;'>Данные успешно сохранены!</p>";

      } catch (PDOException $e) {
          echo "<p style='color:red;'>Ошибка сохранения данных: " . $e->getMessage() . "</p>";
      }

  } else {
      echo "<div class='error'>";
      foreach ($errors as $key => $value) {
          echo "<p>$value</p>";
      }
      echo "</div>";
  }
}
else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['save'])) {
      print('Спасибо, результаты сохранены.');
    }
}
?>