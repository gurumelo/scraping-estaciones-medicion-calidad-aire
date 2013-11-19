<?php
header('Content-Type: text/html; charset=utf-8');

# Se define la zona horaria que corresponda
date_default_timezone_set('Europe/Madrid');

# Se define las locales para que saque mes, tres letras, en castellano
setlocale(LC_ALL, 'es_ES');

# Importamos librería para parsear
require 'simple_html_dom.php';

# En el día actual proveen los datos del día anterior
$epocaunix = strtotime('-1 day');
$ano = date('y', $epocaunix);
$mes = date('m', $epocaunix);
$dia = date('d', $epocaunix);
$mesletras = strtolower(strftime('%b', $epocaunix));
	
echo $ano.$mes.$dia.$mesletras;

$url = 'http://juntadeandalucia.es/medioambiente/atmosfera/informes_siva/'. $mesletras.$ano .'/nhu'. $ano.$mes.$dia .'.htm';	
	
echo $url;
	
$html = file_get_html($url);

# El script se ejecuta a las 2 de la mañana, si en esa hora no hay se hacen intentos(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23)
if (!is_object($html)) {
# Esperamos 1 hora para hacer la siguiente comprobación
	sleep(3600);
	$i = 0;
	while($i < 20) {
		$html = file_get_html($url);
		if (is_object($html)) {
			break;
                }
		else {
			echo "no en línea". date("h:i:s");
			sleep(3600);
		}
		$i++;
	}
}


function parsearTablas($estacion, $posicion) {
	global $ano;
	global $mes;
	global $dia;
	global $conexion;
	global $html;
	$tabla = $html->find('table', $posicion)->find('tr');
	echo $estacion;
	$ultima = count($tabla) - 1;
	echo "La última es: ". $ultima .".......";
	$o = 0;
	foreach($tabla as $row) {
		$tds = $row->find('td');
		if ( $o > 0 && $o < $ultima ) {
	                $tds = $row->find('td');
			$hora = $tds[0]->innertext;
			$estado = $tds[1]->innertext;
			echo $o;
                        $hora = mysqli_real_escape_string($conexion, $hora);
                        $estado = mysqli_real_escape_string($conexion, $estado);
                        $sentencia = "INSERT INTO cuantitativos(estacion,hora,estado,fecha) VALUES('". $estacion ."','". $hora ."','". $estado ."', '". $ano ."-". $mes ."-". $dia ."')";
                        mysqli_query($conexion,$sentencia);
		}
		$o++;
	}
}



$conexion = mysqli_connect('localhost','root','******************','gaseame');

parsearTablas('LOS ROSALES',4);
parsearTablas('MARISMAS DEL TITAN',6);
parsearTablas('POZO DULCE',8);
parsearTablas('LA RABIDA',14);
parsearTablas('CAMPUS DEL CARMEN',26);

mysqli_close($conexion);

?>
