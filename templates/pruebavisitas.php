<?php
// Ruta al archivo XML de almacenamiento
$xmlFile = 'contador.xml';

// Verificar si el archivo XML existe y leer el contador actual
if (file_exists($xmlFile)) {
    $xml = simplexml_load_file($xmlFile);
    $contador = intval($xml->contador);
} else {
    // Si el archivo no existe, establecer un contador inicial de 0
    $contador = 0;
    $xml = new SimpleXMLElement('<contador></contador>');
}

// Incrementar el contador
$contador++;

// Actualizar el valor del contador en el objeto SimpleXMLElement
$xml->contador = $contador;

// Guardar el objeto SimpleXMLElement en el archivo XML
$xml->asXML($xmlFile);

// Mostrar el contador
echo "Número de visitantes: " . $contador;
?>