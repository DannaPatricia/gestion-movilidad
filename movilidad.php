<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>
</body>
</html>
<div class="formContainer">
    <div class="containerFormulario">
<?php

$opcionVehiculo = "";
// Declaracion de agunas variables a usar como al array de archivos y sus rutas y los elementos a recoger del formulario
$fechaInicio = "";
$fechaFinal = "";
$archivos = [
    'vehiculosEMT' => 'ficherosTxt/vehiculosEMT.txt',
    'taxis' => 'ficherosTxt/taxis.txt',
    'logistica' => 'ficherosTxt/logistica.txt',
    'residentesYHoteles' => 'ficherosTxt/residentesYHoteles.txt',
    'servicios' => 'ficherosTxt/servicios.txt'
];

// Accion del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    leeVehiculos($archivos);
}

// Funcion principal que lee el archivo de "vehiculos"
function leeVehiculos($archivos) {
    echo '<h1 style="color: #512188;">INFRACTORES</h1>';
    $vehiculos = fopen('ficherosTxt/vehiculos.txt', 'r');
    // Funcion de declarar los punteros, devuelve el array de punteros y sus archivos(nombres)
    $archivosAbiertos = abrirPuntero($archivos);
    if ($vehiculos) {
        while (($linea = fgets($vehiculos)) !== false) {
            $linea = trim($linea);
            $array = explode(" ", $linea);
            $matricula = $array[0];
            $hora = $array[4];
            $fecha = $array[3];
            $noElectrico = ($array[5] != 'electrico');
            // Condicion si no es electrico y la matricula es infractora
            if ($noElectrico && !verificarInfractor($matricula, $fecha, $archivosAbiertos, $hora)) {
                echo "$linea<br><br>";
            }
        }
        echo generaBoton();
        fclose($vehiculos);
    } else {
        echo "No se pudo abrir el archivo de vehículos.";
        die();
    }
    // Cerrar los punteros
    foreach ($archivosAbiertos as $archivoAbierto) {
        fclose($archivoAbierto);
    }
}

function abrirPuntero($archivos) {
    $archivosAbiertos = [];
    foreach ($archivos as $archivo => $ruta) {
        $archivoAbierto = fopen($ruta, 'r');
        if ($archivoAbierto) {
            // Por cada archivo abierto genero una clave que sera el nombre el archivo, el elemento es el archivo abierto
            $archivosAbiertos[$archivo] = $archivoAbierto;
        } else {
            echo "No se pudo abrir el archivo: $ruta <br>";
            die();
        }
    }
    return $archivosAbiertos;
}

function verificarInfractor($matriculaBuscada, $fecha, $archivosAbiertos, $hora) {
    // Recorrer el array de punteros y por cada linea verificar la matricula en cada uno de los archivos
    foreach ($archivosAbiertos as $archivo => $archivoAbierto) {
        // Funcion para mover el puntero al inicio y evitar abirlo y cerrarlo multiples veces
        rewind($archivoAbierto);
        // Condicion para llmar a funcion de matriculas sin multiples restricciones
        // Uso del in_array para ver si el nombre del archivo es uno de los declarados
        if (in_array($archivo, ['vehiculosEMT', 'taxis', 'servicios']) && buscaMatricula($matriculaBuscada, $archivoAbierto)) {
                return true;
        }
        // Condicion para llamar a funcion con multiples restricciones
        if (in_array($archivo, ['residentesYHoteles', 'logistica']) && buscaMatrEspecifica($matriculaBuscada, $fecha, $archivoAbierto, $archivo, $hora)) {
                return true;
        }
    }
    return false;
}

// Busca matricula sin numerosas restricciones, vehiculosEMT, taxis y servicios
function buscaMatricula($matriculaBuscada, $archivoAbierto) {
    while (($linea = fgets($archivoAbierto)) !== false) {
        $linea = trim($linea);
        $array = explode(" ", $linea);
        $matricula = $array[0];
        if ($matricula === $matriculaBuscada) {
            return true;
        }
    }
    return false;
}

// Busca matricula con mas de una restriccion como residentes y logistica
function buscaMatrEspecifica($matriculaBuscada, $fecha, $archivoAbierto, $archivo, $hora) {
    while (($linea = fgets($archivoAbierto)) !== false) {
        $linea = trim($linea);
        $array = explode(" ", $linea);
        $matriculaArchivo = $array[0];
        $fecha1 = $array[2];
        $fecha2 = $array[3];
        // Si encuentra la matricula y cumple con las funciones fecha y hora NO pinto
        if ($matriculaArchivo === $matriculaBuscada &&
            (($archivo === 'residentesYHoteles' && fechaEnRango($fecha1, $fecha2, $fecha)) ||
            ($archivo === 'logistica' && esHoraValida($hora)))) {
            return true;
        }
    }
    return false;
}

// Devuelve true o false en funcion de que a fecha este o no en el rango solicitado
function fechaEnRango($fecha1, $fecha2, $fecha) {
    $fecha1Conversion = strtotime($fecha1);
    $fecha2Conversion = strtotime($fecha2);
    $fechaConversion = strtotime($fecha);
    return $fechaConversion >= $fecha1Conversion && $fechaConversion <= $fecha2Conversion;
}

// Devuelve true o false en funcion de que la hora se encuentre o no en el rango fijo de 6:00 a 11:00
function esHoraValida($hora) {
    $horaConversion = strtotime($hora);
    $horaInicio = strtotime("6:00");
    $horaFinal = strtotime("11:00");
    return $horaConversion >= $horaInicio && $horaConversion <= $horaFinal;
}

function generaBoton()
{
    return '<div class="containerFormulario">
                <a href="Formulario.html" class="boton" style = "width:90%; margin: 1em 0;">Regresar</a>
            </div>';
}
?>
</div>
</div>
</body>
</html>
