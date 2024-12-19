<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="Style.css">
    <style>
        .formContainer {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .containerFormulario {
            margin-bottom: 15px;
        }
    </style>
</head>
<?php
//Declaracion de array con valores de formulario y array de booleanos para su comprobacion
$formulario = '
<body>
    <div id="pagina">
        <h1>PEDIR SOLICITUD</h1>
        <div class="formContainer">';

$camposValidos = [
            'matricula' => true,
            'dato' => true,
            'fechaValida' => true,
];

$valorCampos = [
    'matricula' => '',
    'dato' => '',
    'fechaInicio' => '',
    'fechaFinal' => '',
];

//Si acciona el formulario...
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Asignacion de valores
    $btnEnviado = $_POST['enviado'] ?? '';
    $tipoVehiculo = $_POST['vehiculo'] ?? '';
    $valorCampos['matricula'] = $_POST['matricula'] ?? '';
    $valorCampos['dato'] = $_POST['dato'] ?? '';
    $valorCampos['fechaInicio'] = $_POST['fechaInicio'] ?? '';
    $valorCampos['fechaFinal'] = $_POST['fechaFinal'] ?? '';

    //Eliminar espacios en blanco de los datos introducidos
    $valorCampos['matricula'] = str_replace(' ', '', $valorCampos['matricula']);
    $valorCampos['dato'] = str_replace(' ', '', $valorCampos['dato']);

    //Asignacion de booleanos
    $camposValidos['matricula'] = !empty($_POST['matricula']);
    $camposValidos['dato'] = !empty($_POST['dato']);
    $camposValidos["fechaValida"] = ($valorCampos["fechaInicio"] && $valorCampos["fechaFinal"] &&
    compruebaFecha( $valorCampos['fechaInicio'],  $valorCampos['fechaFinal']));

    if(empty($tipoVehiculo)){
        echo 'No se seleccionó un tipo de vehículo.';
    }
    //Si das click en el submit de validar tipo se despliega un formulario para introducir datos en diferentes ficheros
    if ($btnEnviado === 'Validar tipo' && !empty($tipoVehiculo)) {
            echo muestraOpciones($tipoVehiculo, $formulario, $camposValidos, $valorCampos, $formularioEnviado);
    }
    //Si das click en el submit de enviar los datos introducidos
    if ($btnEnviado === 'Enviar' && !empty($tipoVehiculo)) {
        $formularioEnviado = true;
        //Si los campos estan vacios vuelve a mostrar el formulario con mensajes de error
        if( !compruebaCampos($camposValidos, $tipoVehiculo)){
            echo muestraOpciones($tipoVehiculo, $formulario, $camposValidos, $valorCampos, $formularioEnviado);
        }else{
            //Dependiendo de si existe la matricula la escribe en el fichero seleccionado
            escribeMatricula($tipoVehiculo, $valorCampos);
        }
    }
}
//Comprueba si los campos son validos mediante un conteo por diferente numero de datos entre residentes.txt y el resto
function compruebaCampos($camposValidos, $tipoVehiculo)
{
    $contadorValido = 0;
    foreach ($camposValidos as $campo) {
        if ($campo) {
            $contadorValido++;
        }
        if($tipoVehiculo != 'residentesYHoteles' && $contadorValido === 2){
            return true;
        }
        if($tipoVehiculo === 'residentesYHoteles' && $contadorValido === 3){
            return true;
        }
    }
    return false;
}

//Hace un despliegue de formulario dependiendo de la opcion seleccionada de vehiculo. Escribe el formulario con funciones
//predefinidas de generar texto y generar date
function muestraOpciones($tipoVehiculo, $formulario, $camposValidos, $valorCampos, $formularioEnviado)
{
    $contenedorMatricula = '<h3>Matrícula</h3>
    <div class="containerFormulario">
        <input type="text" name="matricula" value="' . $valorCampos['matricula'] . '" style="height: 3em;">
        ' . (!$camposValidos['matricula'] && $formularioEnviado ? '<span style="color: red;">Debe introducir una matrícula</span>' : '') . '
    </div>';
    $mensajeError = '<span style="color: red;">Debe introducir un valor</span></div>';
    $formulario .= '<form action="AñadirMatricula.php" method="post">
                    <input type="hidden" name="vehiculo" value="' . $tipoVehiculo . '">';
    switch ($tipoVehiculo) {
        case 'vehiculosEMT':
            $formulario .= '<h1>EMT</h1>'.generaTexto($camposValidos, $valorCampos, $formularioEnviado, $mensajeError, $contenedorMatricula, 'de calle');
            break;
        case 'taxis':
            $formulario .= '<h1>Taxi</h1>'.generaTexto($camposValidos, $valorCampos, $formularioEnviado, $mensajeError, $contenedorMatricula, '');
            break;
        case 'servicios':
            $formulario .= '<h1>Servicio</h1>'. generaTexto($camposValidos, $valorCampos, $formularioEnviado, $mensajeError, $contenedorMatricula, 'de tipo de servicio');
            break;
        case 'logistica':
            $formulario .= '<h1>Logística</h1>'. generaTexto($camposValidos, $valorCampos, $formularioEnviado, $mensajeError, $contenedorMatricula, 'de empresa');
            break;
        case 'residentesYHoteles':
            $formulario .= '<h1>Residentes</h1>'.generaDate($camposValidos, $valorCampos, $formularioEnviado, $mensajeError, $contenedorMatricula, 'de calle');
            break;
        default:
            $formulario .= '<p style="color: red;">Selección inválida</p>';
            break;
    }
    $formulario .='
        <div class="containerFormulario">
            <input type="submit" name="enviado" value="Enviar" style = "width:49%; height:3em;" >
            <input type="reset" value="Borrar" style = "width:49%; height:3em;">
        </div>
        </form>'. generaBoton();
    return $formulario;
}

//Busca la matricula introducida en el formulario en el fichero seleccionado, si existe da un aviso de que ya ha solicitado un permiso
function buscaMatricula($tipoVehiculo, $valorCampos){
    $nombreArchivo = 'ficherosTxt/'.$tipoVehiculo.'.txt';
    $archivo = fopen($nombreArchivo, 'r');
    if ($archivo) {
        while (!feof($archivo)) {
            $linea = fgets($archivo);
            $linea = trim($linea);
            $array = explode(" ", $linea);
            $matriculaArchivo = $array[0];
            if ($matriculaArchivo === $valorCampos['matricula']) {
                muestraDatos($valorCampos,true);
                return true;
            }
        }
        fclose($archivo);
    } else {
        echo "No se pudo abrir el archivo: $nombreArchivo";
        die();
    }
    return false;
}

//Escribe la matricula si no la ha encontrado en el fichero y da un mensaje con los datos escritos correctamente
function escribeMatricula($tipoVehiculo, $valorCampos){
    if(!buscaMatricula($tipoVehiculo, $valorCampos)){
        $nombreArchivo = 'ficherosTxt/'.$tipoVehiculo.'.txt';
        $archivo = fopen($nombreArchivo, 'a');
        if ($archivo) {
            //Si el archivo es de residentes formatea le fecha introducida y escribe los datos en el fichero
            if($tipoVehiculo === 'residentesYHoteles'){
                $fechaInicio = formatearFecha($valorCampos['fechaInicio']);
                $fechaFinal = formatearFecha($valorCampos['fechaFinal']);
                $contenido = $valorCampos['matricula'].' '.$valorCampos['dato'].' '.$fechaInicio.' '.$fechaFinal."\n";
            }else{
                $contenido = $valorCampos['matricula'].' '.$valorCampos['dato']."\n";
            }
            muestraDatos($valorCampos, false);
            fwrite($archivo, $contenido);
            fclose($archivo);
        } else {
            echo "No se pudo abrir el archivo: $nombreArchivo";
            die();
        }
    }
}

//Funciones predefinidas para crear inputs de tipo texto, date y button si se ha introducido un valor lo escribe en el repintado
function generaTexto($camposValidos, $valorCampos, $formularioEnviado, $mensajeError, $contenedorMatricula, $dato)
{
    return $contenedorMatricula .'
                <div class="containerFormulario">
                    <label for="dato">Ingrese nombre '.$dato.'</label>
                    <input type="text" id="dato" name="dato" value="' . htmlspecialchars($valorCampos['dato']) .'" >
                    ' . (!$camposValidos['dato'] && $formularioEnviado ? $mensajeError : ' </div>');
}

function generaDate($camposValidos, $valorCampos, $formularioEnviado, $mensajeError, $contenedorMatricula, $dato){
    return generaTexto($camposValidos, $valorCampos, $formularioEnviado, $mensajeError, $contenedorMatricula, $dato).'
            <div class="containerFormulario">
                    <label for="fechaInicio">Fecha de inicio</label>
                    <input type="date" id="fechaInicio" name="fechaInicio" value="' . htmlspecialchars($valorCampos['fechaInicio']) . '"></div>
            <div class="containerFormulario">
                    <label for="fechaFinal">Fecha final</label>
                    <input type="date" id="fechaFinal" name="fechaFinal" value="' . htmlspecialchars($valorCampos['fechaFinal']) . '">
                    ' . (!$camposValidos['fechaValida'] && $formularioEnviado ? $mensajeError : '</div>');
}


function generaBoton(){
    return '<div class="containerFormulario">
                <a href="Formulario.html" class="boton">Regresar</a>
            </div>';
}

//Funciones adicionales para la fecha y mostrar datos
function compruebaFecha($fechaInicio, $fechaFinal){
    $fechaInicioConversion = strtotime($fechaInicio);
    $fechaFinalConversion = strtotime($fechaFinal);
    return $fechaInicioConversion < $fechaFinalConversion;
}

function formatearFecha($fecha){
    $fechaObjeto = new DateTime($fecha);
    return $fechaObjeto -> format('Y/m/d');
}

function muestraDatos($valorCampos, $registrado){
    echo '<div class = "formContainer">
            <div class = "containerFormulario">
                <p>Matricula: '.$valorCampos['matricula'].'</p><br>
                <p>Dato: '.$valorCampos['dato'].'</p><br>
                <p>Fecha Inicio: '.$valorCampos['fechaInicio'].'</p><br>
                <p>Fecha Final: '.$valorCampos['fechaFinal'].'</p><br>
            </div>
            <div class = "containerFormulario">
            '.($registrado ? '<h2 style= "color:red;">MATRCIULA YA SOLICITADA</h2></div>' : '<h2 style= "color:green;">MATRCIULA REGISTRADA</h2></div>').'
        </div>

       ';
}
