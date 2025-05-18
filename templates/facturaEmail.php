<?php
session_start();

$error = "";

// Paso 1: Recibir archivos + correo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === '1') {
    if (!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
        $error = "Correo electrónico inválido.";
    } elseif ($_FILES['file1']['error'] !== UPLOAD_ERR_OK || $_FILES['file2']['error'] !== UPLOAD_ERR_OK) {
        $error = "Error al subir los archivos.";
    } else {
        $_SESSION['correo'] = $_POST['correo'];

        $tmpDir = sys_get_temp_dir() . '/uploads/';
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);

        $file1Path = $tmpDir . uniqid('file1_') . '_' . basename($_FILES['file1']['name']);
        $file2Path = $tmpDir . uniqid('file2_') . '_' . basename($_FILES['file2']['name']);

        move_uploaded_file($_FILES['file1']['tmp_name'], $file1Path);
        move_uploaded_file($_FILES['file2']['tmp_name'], $file2Path);

        $_SESSION['file1'] = $file1Path;
        $_SESSION['file1name'] = basename($_FILES['file1']['name']);
        $_SESSION['file2'] = $file2Path;
        $_SESSION['file2name'] = basename($_FILES['file2']['name']);

        $_SESSION['step'] = 2;
    }
}

// Paso 2: Recibir calificación y opinión y enviar correo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === '2') {
    if (empty($_SESSION['correo']) || empty($_SESSION['file1']) || empty($_SESSION['file2'])) {
        $error = "No hay datos suficientes. Por favor, sube los archivos primero.";
        unset($_SESSION['step']);
    } else {
        $rating = intval($_POST['rating'] ?? 0);
        $opinion = trim($_POST['opinion'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $error = "Selecciona una calificación válida.";
        } elseif (empty($opinion)) {
            $error = "Escribe tu opinión.";
        } else {
            $correo = $_SESSION['correo'];
            $file1Path = $_SESSION['file1'];
            $file2Path = $_SESSION['file2'];
            $file1Name = $_SESSION['file1name'];
            $file2Name = $_SESSION['file2name'];

            $contenido1 = file_get_contents($file1Path);
            $contenido2 = file_get_contents($file2Path);

            $remitente = "paginaweb@transmillas.com";
            $destinatario = "sharikgonzalezb@gmail.com";
            $asunto = "Solicitud factura con calificación";

            $mensaje = "Nueva solicitud de factura.\n";
            $mensaje .= "Correo: $correo\n";
            $mensaje .= "Calificación: $rating estrellas\n";
            $mensaje .= "Opinión: $opinion\n";

            $boundary = md5(time());

            $headers = "From: Transmillas.com <$remitente>\r\n";
            $headers .= "Reply-To: $remitente\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

            $cuerpo = "--$boundary\r\n";
            $cuerpo .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $cuerpo .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $cuerpo .= $mensaje . "\r\n\r\n";

            // Adjuntar archivo 1
            $cuerpo .= "--$boundary\r\n";
            $cuerpo .= "Content-Type: application/octet-stream; name=\"$file1Name\"\r\n";
            $cuerpo .= "Content-Transfer-Encoding: base64\r\n";
            $cuerpo .= "Content-Disposition: attachment; filename=\"$file1Name\"\r\n\r\n";
            $cuerpo .= chunk_split(base64_encode($contenido1)) . "\r\n";

            // Adjuntar archivo 2
            $cuerpo .= "--$boundary\r\n";
            $cuerpo .= "Content-Type: application/octet-stream; name=\"$file2Name\"\r\n";
            $cuerpo .= "Content-Transfer-Encoding: base64\r\n";
            $cuerpo .= "Content-Disposition: attachment; filename=\"$file2Name\"\r\n\r\n";
            $cuerpo .= chunk_split(base64_encode($contenido2)) . "\r\n";

            $cuerpo .= "--$boundary--";

            if (mail($destinatario, $asunto, $cuerpo, $headers)) {
                // Borrar archivos y limpiar sesión
                unlink($file1Path);
                unlink($file2Path);
                session_destroy();

                header("Location: https://transmillas.com/?enviado=ok#factura");
                exit();
            } else {
                $error = "Error al enviar el correo. Intenta más tarde.";
            }
        }
    }
}

$step = $_SESSION['step'] ?? 1;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de factura con calificación</title>
    <style>
        body {
            margin: 0; padding: 0; background-color: #f1f1f1;
            font-family: Arial, sans-serif;
            display: flex; justify-content: center; align-items: center; height: 100vh;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 500px;
            display: flex;
            flex-direction: column;
        }
        h1 {
            font-size: 22px;
            margin-bottom: 20px;
            text-align: center;
        }
        .rating-stars {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            margin-bottom: 15px;
        }
        .rating-stars input {
            display: none;
        }
        .rating-stars label {
            font-size: 40px;
            color: #ccc;
            cursor: pointer;
            transition: color 0.3s;
        }
        .rating-stars input:checked ~ label,
        .rating-stars label:hover,
        .rating-stars label:hover ~ label {
            color: #ffcc00;
        }
        textarea, input[type="email"], input[type="file"] {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }
        label {
            margin-top: 15px;
            font-weight: bold;
        }
        button {
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container">
<?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($step === 1): ?>
    <h1>Sube tus archivos y escribe tu correo</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="step" value="1">

        <label for="correo">Correo electrónico:</label>
        <input type="email" name="correo" id="correo" required>

        <label for="file1">Archivo 1:</label>
        <input type="file" name="file1" id="file1" required>

        <label for="file2">Archivo 2:</label>
        <input type="file" name="file2" id="file2" required>

        <button type="submit">Enviar</button>
    </form>

<?php elseif ($step === 2): ?>
    <h1>Antes de completar la solicitud califica nuestro servicio</h1>
    <form action="" method="POST">
        <input type="hidden" name="step" value="2">

        <div class="rating-stars">
            <input type="radio" id="star5" name="rating" value="5" required><label for="star5">&#9733;</label>
            <input type="radio" id="star4" name="rating" value="4"><label for="star4">&#9733;</label>
            <input type="radio" id="star3" name="rating" value="3"><label for="star3">&#9733;</label>
            <input type="radio" id="star2" name="rating" value="2"><label for="star2">&#9733;</label>
            <input type="radio" id="star1" name="rating" value="1"><label for="star1">&#9733;</label>
        </div>

        <label for="opinion">Tu opinión:</label>
        <textarea name="opinion" id="opinion" rows="5" required></textarea>

        <button type="submit">Enviar valoración y solicitud</button>
    </form>
<?php endif; ?>

</div>

</body>
</html>
