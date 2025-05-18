<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['fase'])) {
        // Primera fase: Recibir archivos y correo
        $EmailEnvio = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($EmailEnvio, FILTER_VALIDATE_EMAIL)) {
            die("Correo electrónico inválido.");
        }

        if ($_FILES['file1']['error'] !== UPLOAD_ERR_OK || $_FILES['file2']['error'] !== UPLOAD_ERR_OK) {
            die("Error al subir los archivos.");
        }

        // Guardar archivos temporalmente
        $_SESSION['correo'] = $EmailEnvio;
        $_SESSION['file1'] = base64_encode(file_get_contents($_FILES['file1']['tmp_name']));
        $_SESSION['filename1'] = basename($_FILES['file1']['name']);
        $_SESSION['file2'] = base64_encode(file_get_contents($_FILES['file2']['tmp_name']));
        $_SESSION['filename2'] = basename($_FILES['file2']['name']);
        $_SESSION['fase'] = 'calificar';

    } else if ($_SESSION['fase'] == 'calificar') {
        // Segunda fase: recibir opinión y calificación y enviar correo
        $opinion = htmlspecialchars($_POST['opinion']);
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

        $EmailEnvio = $_SESSION['correo'];
        $contenido1 = base64_decode($_SESSION['file1']);
        $contenido2 = base64_decode($_SESSION['file2']);
        $filename1 = $_SESSION['filename1'];
        $filename2 = $_SESSION['filename2'];

        $remitente = "paginaweb@transmillas.com";
        $destinatario = "ventastransmillas@gmail.com";
        $asunto = "Solicitud factura con calificación";

        $mensaje = "Nueva solicitud de factura.\n";
        $mensaje .= "Correo: $EmailEnvio\n";
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

        // Adjuntar archivos
        $cuerpo .= "--$boundary\r\n";
        $cuerpo .= "Content-Type: application/octet-stream; name=\"$filename1\"\r\n";
        $cuerpo .= "Content-Transfer-Encoding: base64\r\n";
        $cuerpo .= "Content-Disposition: attachment; filename=\"$filename1\"\r\n\r\n";
        $cuerpo .= chunk_split(base64_encode($contenido1)) . "\r\n";

        $cuerpo .= "--$boundary\r\n";
        $cuerpo .= "Content-Type: application/octet-stream; name=\"$filename2\"\r\n";
        $cuerpo .= "Content-Transfer-Encoding: base64\r\n";
        $cuerpo .= "Content-Disposition: attachment; filename=\"$filename2\"\r\n\r\n";
        $cuerpo .= chunk_split(base64_encode($contenido2)) . "\r\n";

        $cuerpo .= "--$boundary--";

        session_destroy();

        if (mail($destinatario, $asunto, $cuerpo, $headers)) {
            header("Location: https://transmillas.com/?enviado=ok#factura");
            exit();
        } else {
            echo "<p style='color:red; text-align:center;'>Error al enviar el correo. Intenta más tarde.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calificación y envío de archivos</title>
    <style>
    body {
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #f1f1f1, #e0eafc);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .rating-container {
        background: white;
        padding: 40px 30px;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        width: 500px;
        display: flex;
        flex-direction: column;
        animation: fadeIn 1s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    h1 {
        font-size: 24px;
        margin-bottom: 25px;
        text-align: center;
        color: #333;
    }

    .rating-stars {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        margin-bottom: 20px;
    }

    .rating-stars input {
        display: none;
    }

    .rating-stars label {
        font-size: 50px;
        color: #ccc;
        cursor: pointer;
        transition: transform 0.2s, color 0.3s;
    }

    .rating-stars input:checked ~ label,
    .rating-stars label:hover,
    .rating-stars label:hover ~ label {
        color: #ffc107;
        transform: scale(1.1);
    }

    textarea, input[type="email"], input[type="file"] {
        width: 100%;
        margin-top: 12px;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    textarea:focus, input:focus {
        border-color: #007bff;
        outline: none;
    }

    label {
        margin-top: 18px;
        font-weight: bold;
        color: #555;
    }

    button {
        margin-top: 25px;
        padding: 14px;
        width: 100%;
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 17px;
        cursor: pointer;
        transition: background 0.3s, transform 0.2s;
    }

    button:hover {
        background: linear-gradient(135deg, #0056b3, #004494);
        transform: translateY(-2px);
    }
</style>

</head>
<body>
    <form class="rating-container" action="" method="POST" enctype="multipart/form-data">
        <?php if (!isset($_SESSION['fase'])): ?>
            <h1>Por favor sube tus documentos</h1>

            <label for="correo">Correo electrónico:</label>
            <input type="email" name="correo" id="correo" required>

            <label for="file1">Archivo 1:</label>
            <input type="file" name="file1" id="file1" required>

            <label for="file2">Archivo 2:</label>
            <input type="file" name="file2" id="file2" required>

            <button type="submit">Siguiente</button>

        <?php else: ?>
            <h1>Califica nuestro servicio</h1>

            <div class="rating-stars">
                <input type="radio" id="star1" name="rating" value="5" required>
                <label for="star1">&#9733;</label>
                <input type="radio" id="star2" name="rating" value="4">
                <label for="star2">&#9733;</label>
                <input type="radio" id="star3" name="rating" value="3">
                <label for="star3">&#9733;</label>
                <input type="radio" id="star4" name="rating" value="2">
                <label for="star4">&#9733;</label>
                <input type="radio" id="star5" name="rating" value="1">
                <label for="star5">&#9733;</label>
            </div>

            <label for="opinion">Déjanos tu opinión:</label>
            <textarea name="opinion" id="opinion" required></textarea>

            <button type="submit">Finalizar</button>
        <?php endif; ?>
    </form>
</body>
</html>
