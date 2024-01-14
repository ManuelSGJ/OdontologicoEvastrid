<?php
include_once '../Modules/functions/sessions.php';

if (!controllSession()) {
    $rootViews = dirname($_SERVER['PHP_SELF']);
    header('Location: http://localhost' . $rootViews . '/login.php');
}
?>

<?php

//* proceso 1: consultar fecha actual y ultima consulta
$numeroEvolucion = $_GET['numeroEvolucion'];

include '../Modules/functions/funcionesSql.php';

//* proceso 2: obteniendo evolucion
$evolucion = obtenerRegistro('evoluciones_h_c', '*', 'codigo = ?', [$numeroEvolucion])[0];

if ($evolucion == false) {
    $rootViews = dirname($_SERVER['PHP_SELF']);
    header('Location: http://localhost' . $rootViews . '/login.php');
}

//* procesop 2: consultando dientes (tabla de dientes oIntegrado) de la consulta o ultima evolucion
$odontogramaConsulta = obtenerRegistro('odontogramas', '*', 'odontogramas.codigo  = ?', [$evolucion['codigo_odontograma_FK']])[0];
$dientesOdontogramaConsulta = makeConsult(
    'o_integrado',
    [
        'o_integrado.codigo',
        'o_integrado.codigo_dientes_FK',
        'o_integrado.codigo_convenciones_FK',
        'o_integrado.codigo_odontogramas_FK',
        'dientes.numero_diente',
        'dientes.cuadrante',
        'dientes.cuadrante_fila',
        'convenciones.convencion',
        'convenciones.figura',
        'convenciones.color',

    ],
    'o_integrado.codigo_odontogramas_FK = ?',
    [$odontogramaConsulta['codigo']],
    [
        ' INNER JOIN dientes ON o_integrado.codigo_dientes_FK = dientes.codigo',
        ' LEFT JOIN convenciones ON o_integrado.codigo_convenciones_FK = convenciones.codigo',
        ' LEFT JOIN convencion_seccion ON o_integrado.codigo = convencion_seccion.codigo_OI_FK',
        ' LEFT JOIN convenciones_oc ON convencion_seccion.codigo_convenciones_oc_FK = convenciones_oc.codigo',
        ' LEFT JOIN seccion ON convencion_seccion.codigo_seccion_FK = seccion.codigo',
    ],
    [
        'convenciones_oc.convencion' => 'convencion_oc',
        'convenciones_oc.color' => 'color_oc',
        'seccion.nombreSeccion' => 'seccion_oc'
    ],
    [
        'o_integrado.codigo',
        'o_integrado.codigo_dientes_FK',
        'o_integrado.codigo_convenciones_FK',
        'o_integrado.codigo_odontogramas_FK',
        'dientes.numero_diente',
        'dientes.cuadrante',
        'dientes.cuadrante_fila',
        'convenciones.convencion',
        'convenciones.figura',
        'convenciones.color'
    ]
);


//* proceso 3:  dar formato a dientes para odonograma
$dientesOdontograma = array(
    'cuadrante1' => array(
        'fila1' => [],
        'fila2' => []
    ),

    'cuadrante2' => array(
        'fila1' => [],
        'fila2' => []
    ),

    'cuadrante3' => array(
        'fila1' => [],
        'fila2' => []
    ),

    'cuadrante4' => array(
        'fila1' => [],
        'fila2' => []
    )
);

foreach ($dientesOdontogramaConsulta as $diente) {
    if ($diente['cuadrante_fila'] === 1) {
        array_push($dientesOdontograma['cuadrante' . $diente['cuadrante']]['fila1'], $diente);
    }

    if ($diente['cuadrante_fila'] === 2) {
        array_push($dientesOdontograma['cuadrante' . $diente['cuadrante']]['fila2'], $diente);
    }
}

?>

<?php include '../Modules/templates/head.php'; ?>

<link rel="stylesheet" href="../css/ondotogramaManuel.css">


<div class="cont-pacientes">

    <div class="pacientes">

        <div class="logo-admin">
            <img src="../Img/Logo2.jpg" alt="">
        </div>

        <div class="titulo-historia">
            <h1>HISTORIA CLINICA ODONTOLÓGICA</h1>
            <h3>Evolución De Historia Clinica <br> Odontológica</h3>
        </div>

        <div class="salida">
            <a id="btnGoBack">
                <p>Regresar</p>
                <i class="fa-solid fa-person-walking-arrow-right"></i>
            </a>
        </div>

        <form id="formEvolucion">
            <div class="datos-paciente general-2">
                <div class="articulacion evolucion">

                    <div class="g-evolucion">
                        <button class="nueva evolucion g-odontograma" title="Gestionar nueva evolución" id="gestionarOdontograma">
                            <i class="fa-solid fa-teeth"></i>
                            <h3>Ver odontograma</h3>
                        </button>
                    </div>

                    <div class="fechas-paciente general-1 evolucion evl">

                        <div class="fecha-n evolucion evl">
                            <input id="evolucionFecha" type="date" value="<?php echo $evolucion['fecha_evolucion']; ?>" disabled>
                            <label>Fecha*</label>
                        </div>

                        <div class="años evolucion actv">
                            <input id="evolucionActividad" type="text" value="<?php echo $evolucion['actividad']; ?>" disabled>
                            <label>Actividad</label>
                        </div>

                        <div class="años evolucion evl">
                            <input id="evolucionCodigoCups" type="text" value="<?php echo $evolucion['codigo_cups']; ?>" disabled>
                            <label>Codigo CUPS</label>
                        </div>

                        <div class="años evolucion evl">
                            <input id="evolucionCopago" type="number" value="<?php echo $evolucion['copago']; ?>" disabled>
                            <label>Valor Copago</label>
                        </div>

                        <input type="hidden" id="numeroConsulta" value="<?php echo $ultimaConsulta; ?>">
                    </div>

                    <h1>Descripción Del Procedimiento</h1>

                    <div class="consulta evolucion">
                        <div class="cuadro-texto evolucion">
                            <textarea id="evolucionDescripcion" cols="30" rows="10" placeholder="Redactar la informacion en el cuadro de texto." disabled><?php echo $evolucion['descripcion_procedimiento']; ?></textarea>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

</div>



<div class="overlayOdontograma" id="overlay">
    <div class="odontogramaModal" id="odontogramaModal">
        <div class="encabezadoModal">
            <h1>Odontograma</h1>
            <button id="btnCloseModal">
                x
            </button>
        </div>

        <div class="odontogramaM">
            <?php foreach ($dientesOdontograma as $cuadranteNombre => $seccionesCuadrante) { ?>
                <?php /*//* variables de control para cuadrantes, filas y horientaciones*/ ?>
                <?php $seccionSuperior = $seccionesCuadrante['fila1']; ?>
                <?php $seccionInferior = $seccionesCuadrante['fila2']; ?>
                <?php $horientacionInvertida = ($cuadranteNombre == 'cuadrante1' or $cuadranteNombre == 'cuadrante3') ? 'inv' : ''; ?>

                <div class="cuadrante">
                    <div class="seccionSuperior <?php echo $horientacionInvertida; ?>">
                        <?php foreach ($seccionSuperior as $dienteSeccionSuperior) { ?>

                            <?php
                            //* Obteniendo informacion del diente en odontograma

                            //* variables para generales
                            $procesoDiente = $dienteSeccionSuperior['convencion'] != null ? 'general' : '';
                            $convencionDiente = $procesoDiente == 'general' ? $dienteSeccionSuperior['convencion'] : '';
                            $urlConvencionDienteImg = $procesoDiente == 'general' ? '../Img/convenciones/' . $dienteSeccionSuperior['figura'] : '';
                            $activeImg = $procesoDiente == 'general' ? 'active' : '';

                            //* variables para seccion
                            $procesoSeccion = $dienteSeccionSuperior['convencion_oc'] != null ? 'seccion' : '';
                            $convencionSeccion = $procesoSeccion == 'seccion' ? explode(',', $dienteSeccionSuperior['convencion_oc']) : '';
                            $colorSeccion = $procesoSeccion == 'seccion' ? explode(',', $dienteSeccionSuperior['color_oc']) : '';
                            $secciones = $procesoSeccion == 'seccion' ? explode(',', $dienteSeccionSuperior['seccion_oc']) : '';


                            //* llenando valores span
                            $spans = [
                                'top' => [
                                    'Rojo' => '',
                                    'Azul' => '',
                                    'Verde' => ''
                                ],
                                'left' => [
                                    'Rojo' => '',
                                    'Azul' => '',
                                    'Verde' => ''
                                ],
                                'center' => [
                                    'Rojo' => '',
                                    'Azul' => '',
                                    'Verde' => ''
                                ],
                                'right' => [
                                    'Rojo' => '',
                                    'Azul' => '',
                                    'Verde' => ''
                                ],
                                'bot' => [
                                    'Rojo' => '',
                                    'Azul' => '',
                                    'Verde' => ''
                                ]
                            ];

                            if ($secciones != '') {
                                for ($i = 0; $i < count($secciones); $i++) {
                                    $seccion = $secciones[$i];
                                    $color = $colorSeccion[$i];

                                    $spans[$seccion][$color] = 'active';
                                }
                            }
                            ?>

                            <div class="diente" dienteNumero="<?php echo $dienteSeccionSuperior['numero_diente']; ?>" id="diente-<?php echo $dienteSeccionSuperior['numero_diente']; ?>" procesoDiente="<?php echo $procesoDiente; ?>" convencionDiente="<?php echo $convencionDiente; ?>">
                                <button class="sectionDiente top v">
                                    <span class="<?php echo $spans['top']['Rojo']; ?>"></span>
                                    <span class="<?php echo $spans['top']['Azul']; ?>"></span>
                                    <span class="<?php echo $spans['top']['Verde']; ?>"></span>
                                </button>
                                <button class="sectionDiente left h">
                                    <span class="<?php echo $spans['left']['Rojo']; ?>"></span>
                                    <span class="<?php echo $spans['left']['Azul']; ?>"></span>
                                    <span class="<?php echo $spans['left']['Verde']; ?>"></span>
                                </button>
                                <button class="sectionDiente center v">
                                    <span class="<?php echo $spans['center']['Rojo']; ?>"></span>
                                    <span class="<?php echo $spans['center']['Azul']; ?>"></span>
                                    <span class="<?php echo $spans['center']['Verde']; ?>"></span>
                                </button>
                                <button class="sectionDiente right h">
                                    <span class="<?php echo $spans['right']['Rojo']; ?>"></span>
                                    <span class="<?php echo $spans['right']['Azul']; ?>"></span>
                                    <span class="<?php echo $spans['right']['Verde']; ?>"></span>
                                </button>
                                <button class="sectionDiente bot v">
                                    <span class="<?php echo $spans['bot']['Rojo']; ?>"></span>
                                    <span class="<?php echo $spans['bot']['Azul']; ?>"></span>
                                    <span class="<?php echo $spans['bot']['Verde']; ?>"></span>
                                </button>
                                <button class="general" title="Diente General">
                                    <i class="fa-solid fa-tooth"></i>
                                    <p><?php echo $dienteSeccionSuperior['numero_diente']; ?></p>
                                </button>
                                <div class="imgOperacionGeneral <?php echo $activeImg; ?>">
                                    <img src="<?php echo $urlConvencionDienteImg; ?>" alt="">
                                </div>
                            </div>

                        <?php } ?>
                    </div>

                    <div class="seccionInferior <?php echo $horientacionInvertida; ?>">
                        <?php foreach ($seccionInferior as $dienteSeccionInferior) { ?>

                            <?php
                            //* Obteniendo informacion del diente en odontograma

                            //* variables para generales
                            $procesoDiente = $dienteSeccionInferior['convencion'] != null ? 'general' : '';
                            $convencionDiente = $procesoDiente == 'general' ? $dienteSeccionInferior['convencion'] : '';
                            $urlConvencionDienteImg = $procesoDiente == 'general' ? '../Img/convenciones/' . $dienteSeccionInferior['figura'] : '';
                            $activeImg = $procesoDiente == 'general' ? 'active' : '';

                            //* variables para seccion
                            $procesoSeccion = $dienteSeccionInferior['convencion_oc'] != null ? 'seccion' : '';
                            $convencionSeccion = $procesoSeccion == 'seccion' ? explode(',', $dienteSeccionInferior['convencion_oc']) : '';
                            $colorSeccion = $procesoSeccion == 'seccion' ? explode(',', $dienteSeccionInferior['color_oc']) : '';
                            $secciones = $procesoSeccion == 'seccion' ? explode(',', $dienteSeccionInferior['seccion_oc']) : '';


                            //* llenando valores span
                            $spans = [
                                'top' => [
                                    'Rojo' => '',
                                    'Azul' => '',
                                    'Verde' => ''
                                ],
                                'left' => [
                                    'Rojo' => '',
                                    'Azul' => '',
                                    'Verde' => ''
                                ],
                                'center' => [
                                    'Rojo' => '',
                                    'Azul' => '',
                                    'Verde' => ''
                                ],
                                'right' => [
                                    'Rojo' => '',
                                    'Azul' => '',
                                    'Verde' => ''
                                ],
                                'bot' => [
                                    'Rojo' => '',
                                    'Azul' => '',
                                    'Verde' => ''
                                ]
                            ];

                            if ($secciones != '') {
                                for ($i = 0; $i < count($secciones); $i++) {
                                    $seccion = $secciones[$i];
                                    $color = $colorSeccion[$i];

                                    $spans[$seccion][$color] = 'active';
                                }
                            }
                            ?>

                            <div class="diente" dienteNumero="<?php echo $dienteSeccionInferior['numero_diente']; ?>" id="diente-<?php echo $dienteSeccionInferior['numero_diente']; ?>" procesoDiente="<?php echo $procesoDiente; ?>" convencionDiente="<?php echo $convencionDiente; ?>">
                                <button class="sectionDiente top v">
                                    <span class="<?php echo $spans['top']['Rojo']; ?>"></span>
                                    <span class="<?php echo $spans['top']['Azul']; ?>"></span>
                                    <span class="<?php echo $spans['top']['Verde']; ?>"></span>
                                </button>
                                <button class="sectionDiente left h">
                                    <span class="<?php echo $spans['left']['Rojo']; ?>"></span>
                                    <span class="<?php echo $spans['left']['Azul']; ?>"></span>
                                    <span class="<?php echo $spans['left']['Verde']; ?>"></span>
                                </button>
                                <button class="sectionDiente center v">
                                    <span class="<?php echo $spans['center']['Rojo']; ?>"></span>
                                    <span class="<?php echo $spans['center']['Azul']; ?>"></span>
                                    <span class="<?php echo $spans['center']['Verde']; ?>"></span>
                                </button>
                                <button class="sectionDiente right h">
                                    <span class="<?php echo $spans['right']['Rojo']; ?>"></span>
                                    <span class="<?php echo $spans['right']['Azul']; ?>"></span>
                                    <span class="<?php echo $spans['right']['Verde']; ?>"></span>
                                </button>
                                <button class="sectionDiente bot v">
                                    <span class="<?php echo $spans['bot']['Rojo']; ?>"></span>
                                    <span class="<?php echo $spans['bot']['Azul']; ?>"></span>
                                    <span class="<?php echo $spans['bot']['Verde']; ?>"></span>
                                </button>
                                <button class="general" title="Diente General">
                                    <i class="fa-solid fa-tooth"></i>
                                    <p><?php echo $dienteSeccionInferior['numero_diente']; ?></p>
                                </button>
                                <div class="imgOperacionGeneral <?php echo $activeImg; ?>">
                                    <img src="<?php echo $urlConvencionDienteImg; ?>" alt="">
                                </div>
                            </div>

                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>


<script src="../JS/evoluciones/modalOdontograma.js"></script>

<script>

    const btnGoBack = document.querySelector('#btnGoBack');

    btnGoBack.addEventListener('click', (event) => {
        event.preventDefault();
        window.history.back();
    })
</script>


<?php include '../Modules/templates/footer.php'; ?>