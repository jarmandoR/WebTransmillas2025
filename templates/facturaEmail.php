<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Solicitud de factura</title>
  <style>
    body {
      background-color: #f1f1f1;
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }

    form {
      background-color: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      width: 500px;
    }

    h1 {
      font-size: 22px;
      margin-bottom: 20px;
      text-align: center;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    input[type="email"], input[type="file"], textarea {
      width: 100%;
      padding: 10px;
      margin-top: 8px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
    }

    .rating-stars {
      display: flex;
      flex-direction: row-reverse;
      justify-content: center;
      margin: 15px 0;
    }

    .rating-stars input {
      display: none;
    }

    .rating-stars label {
      font-size: 35px;
      color: #ccc;
      cursor: pointer;
      transition: color 0.3s;
    }

    .rating-stars input:checked ~ label,
    .rating-stars label:hover,
    .rating-stars label:hover ~ label {
      color: #ffcc00;
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

  <form method="POST" enctype="multipart/form-data">
    <div id="paso1">
      <h1>Solicitud de Factura</h1>

      <label for="correo">Correo electrónico:</label>
      <input type="email" name="correo" id="correo" required>

      <label for="file1">Archivo 1:</label>
      <input type="file" name="file1" id="file1" required>

      <label for="file2">Archivo 2:</label>
      <input type="file" name="file2" id="file2" required>

      <button type="button" onclick="mostrarCalificacion()">Siguiente</button>
    </div>

    <div id="paso2" style="display:none;">
      <h1>Califica nuestro servicio</h1>

      <div class="rating-stars">
        <input type="radio" id="star5" name="rating" value="5" required><label for="star5">&#9733;</label>
        <input type="radio" id="star4" name="rating" value="4"><label for="star4">&#9733;</label>
        <input type="radio" id="star3" name="rating" value="3"><label for="star3">&#9733;</label>
        <input type="radio" id="star2" name="rating" value="2"><label for="star2">&#9733;</label>
        <input type="radio" id="star1" name="rating" value="1"><label for="star1">&#9733;</label>
      </div>

      <label for="opinion">Opinión:</label>
      <textarea name="opinion" id="opinion" required></textarea>

      <button type="submit">Finalizar y Enviar</button>
    </div>
  </form>

  <script>
    function mostrarCalificacion() {
      const correo = document.getElementById("correo").value.trim();
      const file1 = document.getElementById("file1").files.length;
      const file2 = document.getElementById("file2").files.length;

      if (!correo || file1 === 0 || file2 === 0) {
        alert("Por favor completa todos los campos y selecciona ambos archivos.");
        return;
      }

      document.getElementById("paso1").style.display = "none";
      document.getElementById("paso2").style.display = "block";
    }
  </script>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
  $opinion = htmlspecialchars($_POST['opinion']);
  $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

  if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    die("Correo inválido.");
  }

  if ($_FILES['file1']['error'] !== UPLOAD_ERR_OK || $_FILES['file2']['error'] !== UPLOAD_ERR_OK) {
    die("Error en los archivos adjuntos.");
  }

  $archivo1 = $_FILES['file1']['tmp_name'];
  $archivo2 = $_FILES['file2']['tmp_name'];
  $nombre1 = basename($_FILES['file1']['name']);
  $nombre2 = basename($_FILES['file2']['name']);

  $contenido1 = file_get_contents($archivo1);
  $contenido2 = file_get_contents($archivo2);

  $destino = "sharikgonzalezb@gmail.com";
  $remitente = "paginaweb@transmillas.com";
  $asunto = "Solicitud de factura con calificación";

  $mensaje = "Correo: $correo\n";
  $mensaje .= "Calificación: $rating estrellas\n";
  $mensaje .= "Opinión: $opinion\n";

  $boundary = md5(uniqid());

  $headers = "From: Transmillas <$remitente>\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

  $cuerpo = "--$boundary\r\n";
  $cuerpo .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
  $cuerpo .= $mensaje . "\r\n";

  // Adjuntar archivo 1
  $cuerpo .= "--$boundary\r\n";
  $cuerpo .= "Content-Type: application/octet-stream; name=\"$nombre1\"\r\n";
  $cuerpo .= "Content-Transfer-Encoding: base64\r\n";
  $cuerpo .= "Content-Disposition: attachment; filename=\"$nombre1\"\r\n\r\n";
  $cuerpo .= chunk_split(base64_encode($contenido1)) . "\r\n";

  // Adjuntar archivo 2
  $cuerpo .= "--$boundary\r\n";
  $cuerpo .= "Content-Type: application/octet-stream; name=\"$nombre2\"\r\n";
  $cuerpo .= "Content-Transfer-Encoding: base64\r\n";
  $cuerpo .= "Content-Disposition: attachment; filename=\"$nombre2\"\r\n\r\n";
  $cuerpo .= chunk_split(base64_encode($contenido2)) . "\r\n";

  $cuerpo .= "--$boundary--";

  if (mail($destino, $asunto, $cuerpo, $headers)) {
    header("Location: https://transmillas.com/?enviado=ok#factura");
    exit();
  } else {
    echo "<p style='text-align:center;color:red;'>Error al enviar el correo.</p>";
  }
}
?>
</body>
</html>
