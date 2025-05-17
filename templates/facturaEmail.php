<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página de calificación</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f1f1f1;
        }

        .rating-container {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
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
            box-sizing: border-box;
            font-size: 16px;
            resize: vertical;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            text-align: left;
            width: 100%;
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

    </style>
</head>
<body>
    <form class="rating-container" action="facturaEmail.php" method="POST" enctype="multipart/form-data">
        <h1>Antes de completar la solicitud califica nuestro servicio</h1>

        <div class="rating-stars">
            <input type="radio" id="star1" name="rating" value="5" required>
            <label for="star1" class="estrella1">&#9733;</label>

            <input type="radio" id="star2" name="rating" value="4">
            <label for="star2" class="estrella2">&#9733;</label>

            <input type="radio" id="star3" name="rating" value="3">
            <label for="star3" class="estrella3">&#9733;</label>

            <input type="radio" id="star4" name="rating" value="2">
            <label for="star4" class="estrella4">&#9733;</label>

            <input type="radio" id="star5" name="rating" value="1">
            <label for="star5" class="estrella5">&#9733;</label>
        </div>

        <label for="opinion">Dejanos tu opinion:</label>
        <textarea name="opinion" id="opinion" placeholder="" required></textarea>

        <label for="correo">Correo electrónico:</label>
        <input type="email" name="correo" id="correo" required>

        <label for="file1">Archivo 1:</label>
        <input type="file" name="file1" id="file1" required>

        <label for="file2">Archivo 2:</label>
        <input type="file" name="file2" id="file2" required>

        <button type="submit">Enviar</button>
    </form>
</body>
</html>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $EmailEnvio = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $opinion = htmlspecialchars($_POST['opinion']);
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

    if (!filter_var($EmailEnvio, FILTER_VALIDATE_EMAIL)) {
        die("Correo electrónico inválido.");
    }

    if ($_FILES['file1']['error'] !== UPLOAD_ERR_OK || $_FILES['file2']['error'] !== UPLOAD_ERR_OK) {
        die("Error al subir los archivos.");
    }

    $file1 = $_FILES['file1']['tmp_name'];
    $file2 = $_FILES['file2']['tmp_name'];
    $filename1 = basename($_FILES['file1']['name']);
    $filename2 = basename($_FILES['file2']['name']);

    $remitente = "sharikgonzalezb@gmail.com";
    $destinatario = "szurrego@ucundinamarca.edu.co";
    $asunto = "Solicitud factura " . $filename1;

    $message = "Nueva solicitud de recibo.\n";
    $message .= "Enviar factura a la dirección de correo: $EmailEnvio\n";
    $message .= "Calificación: $rating estrellas\n";
    $message .= "Opinión: $opinion\n";

    $fileContent1 = file_get_contents($file1);
    $fileContent2 = file_get_contents($file2);

    $boundary = md5(time());
    $headers = "From: Transmillas.com <$remitente>\r\n";
    $headers .= "Reply-To: $remitente\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= $message . "\r\n\r\n";

    // Adjuntar archivo 1
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: application/octet-stream; name=\"$filename1\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n";
    $body .= "Content-Disposition: attachment; filename=\"$filename1\"\r\n\r\n";
    $body .= chunk_split(base64_encode($fileContent1)) . "\r\n";

    // Adjuntar archivo 2
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: application/octet-stream; name=\"$filename2\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n";
    $body .= "Content-Disposition: attachment; filename=\"$filename2\"\r\n\r\n";
    $body .= chunk_split(base64_encode($fileContent2)) . "\r\n";

    $body .= "--$boundary--";

    if (mail($destinatario, $asunto, $body, $headers)) {
        header("Location: https://transmillas.com/index.php?enviado=ok");
        exit();
    } else {
        echo "Error al enviar el correo. Intenta más tarde.";
    }
}
?>
