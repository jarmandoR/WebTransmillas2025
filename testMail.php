<?php
// Mostrar errores (opcional para pruebas)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Datos del correo
$to = "sharikgonzalezb@gmail.com"; // Tu correo destino
$subject = "Prueba de correo desde Hostinguer";
$message = "Este es un mensaje de prueba enviado desde la función mail() en Hostinguer.";
$headers = "From: paginaweb@transmillas.com";

// Envío del correo
if (mail($to, $subject, $message, $headers)) {
    echo "✅ Correo enviado correctamente.";
} else {
    echo "❌ Error al enviar el correo.";
}
?>
