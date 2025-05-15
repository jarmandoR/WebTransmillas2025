<?php
// include class

// require("login_autentica.php");
// include("cabezote3.php"); 
$nombre="nombre";
$cedula="cedula";
$valor="80000";
$deudas="deudas";
$fechaini="fechaini";
$fechafin="fechafin";
// $asc="ASC";


$fechaini = strtotime($fechaini);
$fechafin = strtotime($fechafin);

$fechainidia=date("d",$fechaini);
$fechainimes=date("m",$fechaini);
$fechainiaño=date("Y",$fechaini);      


$fechafindia=date("d",$fechafin);
$fechafinmes=date("m",$fechafin);
$fechafinaño=date("Y",$fechafin);   
//             $fechadelregidia = date("d",$fechadelregi);

// // error_reporting(0);
require('fpdf/fpdf.php');

// create document
// $pdf = new FPDF();
// $pdf->AddPage();

// // add text


// $pdf->Image('assets/encabezadocert.png', null, null, 180);
// $pdf->SetFont('Times', '', 12);
// $pdf->Cell(0, 10, 'BERMUDAS S.A.S.', 0, 1);
// $pdf->Cell(0, 10, 'Nit 901.169.262-8', 0, 1);



// $pdf->MultiCell(0, 7, utf8_decode('CERTIFICA'), 0, 1);


// $pdf->Ln();
// $pdf->Ln();

// $formatofin=$formato;

// if ($formatofin==1){
// 	    $pdf->MultiCell(0, 7, utf8_decode('Que '.$nombre.', identificada con la cédula de ciudadanía N.º '.$cedula.' de Bogotá, laboró en nuestra empresa desde el '.date("d",$fechadelregi).' de '.date("m",$fechadelregi).' del '.date("Y",$fechadelregi).' con un CONTRATO A TERMINO INDEFINIDO, desempeñando el cargo de '.$cargo.'.'), 0, 1);
// }else if($formatofin==2){

// $pdf->MultiCell(0, 7, utf8_decode('Que '.$nombre.', identificada con la cédula de ciudadanía N.º '.$cedula.' de Bogotá, laboró en nuestra empresa desde el '.date("d",$fechadelregi).' de '.date("m",$fechadelregi).' del '.date("Y",$fechadelregi).'  y hasta el __ de ____ de __; con un CONTRATO A TERMINO INDEFINIDO, desempeñando el cargo de '.$cargo.'.'), 0, 1);

// }
// $pdf->Ln();

// $pdf->MultiCell(0, 7, utf8_decode('La presente se expide en la ciudad de Bogotá D.C, a los  ( '.date("d",$fechacertificado).') días del mes '.date("m",$fechacertificado).' del año  ('.date("Y",$fechacertificado).'), con destino al (la) INTERESADO(A).'), 0, 1);
// $pdf->Ln();

// $pdf->Cell(0, 10, 'Cordialmente,', 0, 1);
// $pdf->Ln();
// $pdf->Ln();


// $pdf->Cell(0, 10, '______________________________ ', 0, 1);
// $pdf->Cell(0, 10, 'MARINA CASTIBLANCO', 0, 1);
// $pdf->Cell(0, 10, 'GERENTE ADMINISTRATIVA', 0, 1);
// $pdf->Cell(0, 10, 'BERMUDAS S.A.S.', 0, 1);

// // output file
// $pdf->Output('', 'basic.pdf');


class PDF extends FPDF
{
// Cabecera de página


function addBackground($file, $x = 0, $y = 0, $w = null, $h = null) {
    $this->Image($file, $x, $y, $w, $h);
}
function Header()
{

          // Definir borde negro alrededor del encabezado
          $this->SetLineWidth(0.5); // Establece el ancho de línea
          $this->SetDrawColor(0, 0, 0); // Establece el color del borde (negro)
          $this->Rect(10, 10, 190, 60, 'D'); // Dibuja un rectángulo con borde

    $this->SetFont('Times','B',15);
   
    $this->Cell(80);
   
    $this->Ln(20);

    $this->SetFont('Arial', 'B', 25); // Establece la fuente, el estilo (negrita) y el tamaño (12 puntos)
    $this->SetY($this->GetY() -15); // Mueve hacia abajo
        $this->Cell(150, 10, 'DESPRENDIBLE DE NOMINA', 0, 1, 'C'); // Agrega un título centrado

        // $posX = $this->GetPageWidth() - 50; // Ajusta el valor según sea necesario
        
        // // Agrega la imagen al lado derecho del encabezado
        // $this->Image('images/logoDesprendible.jpg', $posX, $this->GetY(), 50); // Cambia 'ruta/a/tu/imagen.png' a la ruta de tu imagen y ajusta el tamaño si es necesario


        $imageWidth = 35; // Ancho de la imagen
        $textWidth = $this->GetStringWidth('DESPRENDIBLE DE NOMINA'); // Ancho del texto
        $availableWidth = $this->GetPageWidth() - $textWidth - $imageWidth; // Ancho disponible para la imagen
        $posX = $this->GetPageWidth() - $availableWidth; // Posición x para la imagen
        // Calcula la posición y para la imagen (subir un poco la imagen)
        $posY = $this->GetY() - 13; // Ajusta el valor según sea necesario

        // Agrega la imagen al lado derecho del encabezado
        $this->Image('images/logoDesprendible.jpg', $posX, $posY, $imageWidth); // Cambia 'ruta/a/tu/imagen.png' a la ruta de tu imagen y ajusta el tamaño si es necesario
        // Agrega la imagen al lado derecho del encabezado
        // $this->Image('images/logoDesprendible.jpg', $posX, $this->GetY(), $imageWidth); // Cambia 'ruta/a/tu/imagen.png' a la ruta de tu imagen y ajusta el tamaño si es necesario
        $this->SetFont('Arial', '', 12);

        $this->SetY($this->GetY() +1); // Mueve hacia abajo
        $this->Cell(100, 10, 'CEDULA                                     No.BOG 50', 0, 1, 'C');
        $this->SetY($this->GetY() -2); // Mueve hacia abajo
        $this->Cell(90, 10, 'NOMBRE                                              ', 0, 1, 'C');
        $this->SetY($this->GetY() -2); // Mueve hacia abajo
        $this->Cell(84, 10, 'Cargo                                               ', 0, 1, 'C');
        $this->SetY($this->GetY() -2); // Mueve hacia abajo
        $this->Cell(98, 10, 'Periodo del:                              Al:               ', 0, 1, 'C');
        $this->SetY($this->GetY() -2); // Mueve hacia abajo
        $this->Cell(140, 10, 'No DAVIVIENDA                                                             BOGOTA', 0, 1, 'C');
}






// Pie de página
function Footer()
{
    // Posición: a 1,5 cm del final
    $this->SetY(-15);
    // Times italic 8
    $this->SetFont('Times','I',8);
    // $this->Image('assets/piedepaginaberm.jpg',0,260,0);
    // Número de página
    // $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}




// Creación del objeto de la clase heredada
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);
// for($i=1;$i<=40;$i++)
//     $pdf->Cell(0,10,'Imprimiendo línea número '.$i,0,1);


// Dibuja el primer rectángulo
$pdf->SetLineWidth(0.5); // Establece el ancho de línea
$pdf->SetDrawColor(0, 0, 0); // Establece el color del borde (negro en este caso)
$pdf->Rect(10, 72, 95, 15); // Dibuja un rectángulo con relleno


// Dibuja el segundo rectángulo
$pdf->SetLineWidth(0.5); // Establece el ancho de línea
$pdf->SetDrawColor(0, 0, 0); // Establece el color del borde (negro en este caso)
$pdf->Rect(105, 72, 95,15); // Dibuja un rectángulo sin relleno


$pdf->Ln(20);
$pdf->SetY($pdf->GetY() -14); // Mueve hacia abajo
$pdf->Cell(190, 10, 'DEVENGADOS                                                                      DEDUCCIONES', 0, 1, 'C');



          // Definir borde negro alrededor del encabezado
          $pdf->SetLineWidth(0.5); // Establece el ancho de línea
          $pdf->SetDrawColor(0, 0, 0); // Establece el color del borde (negro)
          $pdf->Rect(10, 10, 190, 100, 'D'); // Dibuja un rectángulo con borde





// Elimina la palabra "coma" y lo que le sigue


$pdf->Ln(10);

$formatofin=$formato;

// if ($formatofin==1){
// 	    $pdf->MultiCell(0, 6, utf8_decode('Que '.$nombre.', identificada con la cédula de ciudadanía N.º '.$cedula.' de Bogotá, laboró en nuestra empresa desde el '.date("d",$fechadelregi).' de '.date("m",$fechadelregi).' del '.date("Y",$fechadelregi).' con un CONTRATO A TERMINO INDEFINIDO, desempeñando el cargo de '.$cargo.'.'), 0,'J');
// }else if($formatofin==2){

$pdf->MultiCell(0, 6, utf8_decode('Concepto 1 : Prestacion de servicios desde  el '.$fechainidia.' al '.$fechafindia.' del '.$fechainimes.' del '.$fechainiaño.''), 0,'J');


// }
$pdf->Ln(10);

$pdf->Cell(0, 6, 'Deudas', 0, 1);
$pdf->Cell(0, 6, '$'.$deudas.'', 0, 1);


$totalOtrosAPagar=$valor-$deudas;

$totalOtrosAPagar_formateado = number_format($totalOtrosAPagar, 0, ',', '.');

$pdf->Cell(0, 6, 'Total, a pagar,', 0, 1);
$pdf->Cell(0, 6, ''.$totalOtrosAPagar_formateado.'', 0, 1);


$pdf->Cell(0, 6, 'Atentamente,', 0, 1);
$pdf->Ln(10);
$pdf->SetFont('Times', 'I', 12);
// $pdf->Cell(40, 6, 'Marina Castiblanco', 'B', 1);

$pdf->Cell(0, 6, '______________________________ ', 0, 1);
$pdf->Cell(0, 6, ''.$nombre.'', 0, 1);

$pdf->Cell(0, 6, ''.$cedula.'', 0, 1);


$pdf->Ln(10);
// $pdf->MultiCell(0, 6, 'Puede certificarla veracidad de este certificado en los correos: castiblanco@bermudas.com.co y/o recursosh@bermudas.com.co 
// ', 0,'C');

$pdf->Output();
?>
