<?php
// Obtener el valor seleccionado enviado desde JavaScript
// $opcion = $_POST['opcion'];

// // Realizar la conexión a la base de datos y ejecutar la consulta
// // Suponiendo que tienes una tabla "opciones" con los campos "id" y "nombre"
// $conexion = new mysqli("localhost", "usuario", "contraseña", "basedatos");
// $query = "SELECT id, nombre FROM opciones WHERE opcion = ?";
// $stmt = $conexion->prepare($query);
// $stmt->bind_param("s", $opcion);
// $stmt->execute();
// $resultado = $stmt->get_result();

// // Crear un array con los resultados de la consulta
// $data = array();
// while ($fila = $resultado->fetch_assoc()) {
//   $data[] = $fila;
// }

// // Devolver los datos en formato JSON
// echo json_encode($data);

// // Cerrar la conexión y liberar recursos
// $stmt->close();
// $conexion->close();











require("login_autentica.php"); 
include("declara.php");
$miRol=$nivel_acceso;
$misede=$_SESSION['usu_idsede'];
$idUserA=$_SESSION['usuario_id'];

$id_sedes=$_GET['opcion'];

/* $sql6="SELECT usu_nombre, usu_mail FROM usuarios WHERE idusuarios='$idUserA' ";
                    $DB->Execute($sql6); 
                    $rw5=mysqli_fetch_row($DB->Consulta_ID);
 $nomuser=$rw5[0]; */

echo$sql1 = "SELECT `idusuarios`,concat_ws(' / ',usu_nombre,zon_nombre) as nombre FROM  seguimiento_user inner join zonatrabajo on seg_idzona=idzonatrabajo  inner join  `usuarios` on idusuarios=seg_idusuario  WHERE `roles_idroles` in (2,3,5) and seg_fechaalcohol='$fechaactual' and (usu_estado=1 or usu_filtro=1) and usu_idsede=$id_sedes";
 $DB1->Execute($sql1); 
//  $cantMensajes=$DB1->recogedato(0);

$data = array();
//  $DB->Execute($sql); $va=0; 
	while($data=mysqli_fetch_row($DB1->Consulta_ID))
	{

        $data[] = $rw1[1];

    }

// Devolver los datos en formato JSON
echo json_encode($data);

?>