<?php
header('Content-Type: text/html; charset=utf-8');

# Se define la zona horaria que corresponda
date_default_timezone_set('Europe/Madrid');

# Se define las locales para que saque mes, tres letras, en castellano
setlocale(LC_ALL, 'es_ES');

# Importamos librería para parsear
require 'simple_html_dom.php';


# Función que trae los datos
function traedatos() {
	# En el día actual proveen los datos del día anterior
	$epocaunix = strtotime('-1 day');
	$ano = date('y', $epocaunix);
	$mes = date('m', $epocaunix);
	$dia = date('d', $epocaunix);
	$mesletras = strtolower(strftime('%b', $epocaunix));
	
	echo $ano.$mes.$dia.$mesletras;

	$url = 'http://juntadeandalucia.es/medioambiente/atmosfera/informes_siva/'. $mesletras.$ano .'/hu'. $ano.$mes.$dia .'.htm';	
	
	echo $url;
	
	$html = file_get_html($url);

	# El script se ejecuta a las 3 de la mañana, si en esa hora no hay se hacen 20 intentos(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23)
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

	$tabla = $html->find('table', 4)->find('tr');
	$conexion = mysqli_connect('localhost','root','***************','gaseame');
	$o = 0;
	foreach ($tabla as $row) {
		if ($o == 3 || $o == 5 || $o == 6 || $o == 7 || $o == 13) {
			$tds = $row->find('td');
			$ths = $row->find('th');
			echo $tds[1]->innertext;
			echo $ths[0]->innertext;
			$estacion = $tds[1]->innertext;
			$cualidad = $ths[0]->innertext;
			$estacion = mysqli_real_escape_string($conexion, $estacion);
			$cualidad = mysqli_real_escape_string($conexion, $cualidad);
			$sentencia = "INSERT INTO cualitativos(estacion,estado,fecha) VALUES('". $estacion ."','". $cualidad."', '". $ano ."-". $mes ."-". $dia ."')";
			mysqli_query($conexion,$sentencia);
		}
		$o++;
	}
	mysqli_close($conexion);




}

traedatos();


?>
