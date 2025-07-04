<?php
require_once __DIR__ . '/helpers/database.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/validation.php';

$auth = Auth::getInstance();
$validation = Validation::getInstance();

// Handle logout
if (isset($_GET['logout'])) {
    $auth->logout();
    // Redirect to login page after logout
    header('Location: login.php?logout_success=1');
    exit;
}

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $data = $validation->sanitize($_POST);
    $email = $data['email'] ?? '';
    
    // Validation rules
    $rules = [
        'email' => ['required', 'email'],
        'password' => ['required', 'min:6']
    ];

    // Validate input
    if ($validation->validate($data, $rules)) {
        // Attempt login
     //   echo "<pre>Debug at line " . __LINE__ . " (one-622): \n";
        // echo $auth->login($data['email'], $data['password']);
        // print_r($data);
        // die();

        if ($auth->login($data['email'], $data['password'])) {
            // Successful login
            header('Location: index.php');
            exit;
        } else {
            // Failed login
            $errors['login'] = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
            error_log("Login failed for email: " . $data['email']);
        }
    } else {
        // Validation errors
        $errors = $validation->getErrors();
        error_log("Validation errors: " . json_encode($errors));
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة المواقع الجغرافية</title>
    
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Material Design Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Toastify -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="text-center mb-4">تسجيل الدخول</h1>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php 
                                if (isset($errors['login'])) {
                                    echo $errors['login'];
                                } else {
                                    echo 'يرجى تصحيح الأخطاء التالية:';
                                    echo '<ul class="mb-0 mt-2">';
                                    foreach ($errors as $field => $fieldErrors) {
                                        foreach ($fieldErrors as $error) {
                                            echo '<li>' . $error . '</li>';
                                        }
                                    }
                                    echo '</ul>';
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="login.php" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="mdi mdi-email"></i>
                                    </span>
                                    <input type="email" 
                                           class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                           id="email" 
                                           name="email" 
                                           value="<?php echo htmlspecialchars($email); ?>"
                                           required
                                           autocomplete="email">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $errors['email'][0]; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">كلمة المرور</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="mdi mdi-lock"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                                           id="password" 
                                           name="password" 
                                           required
                                           autocomplete="current-password">
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $errors['password'][0]; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="mdi mdi-login me-2"></i>
                                تسجيل الدخول
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastify -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <?php if (isset($_GET['logout_success'])): ?>
    <script>
        Toastify({
            text: "تم تسجيل الخروج بنجاح",
            duration: 3000,
            gravity: "top",
            position: "center",
            className: "bg-success"
        }).showToast();
    </script>
    <?php endif; ?>
</body>
</html>