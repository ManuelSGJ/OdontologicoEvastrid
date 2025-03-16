<?php

// Incluye los datos necesarios
include '../Modules/functions/bdconection.php';
include_once '../Modules/functions/consultasGenerales.php';
include_once '../Modules/functions/funcionesSql.php';

//* datos del paciente
$cedulaPaciente = $_GET['cedulaPaciente'];
$paciente = makeConsult(
    'pacientes',
    [
        'pacientes.nombres',
        'pacientes.apellidoUno',
        'pacientes.apellidoDos',
        'pacientes.fecha_nacimiento',
        'pacientes.fecha_inicio_tratamiento',
        'tipos_documentos.clase_de_documento',
        'pacientes.numero_documento',
        'pacientes.sexo',
        'pacientes.telefono',
        'residencias.direccion_residencia',
        'municipios.municipio',
        'departamentos.departamento',
        'departamentos.codigo as codigo_departamento',
        'pacientes.otrosAntecedentesFamiliares',
    ],
    "pacientes.numero_documento = ?",
    [$cedulaPaciente],
    [
        'INNER JOIN residencias ON residencias.codigo = pacientes.codigo_residencia_FK',
        'INNER JOIN tipos_documentos ON tipos_documentos.codigo = pacientes.codigo_tipo_documento_FK',
        'LEFT JOIN responsables ON responsables.numero_documento_paciente_FK = pacientes.numero_documento',
        'INNER JOIN municipios ON municipios.codigo = residencias.codigo_municipio_FK',
        'INNER JOIN departamentos ON departamentos.codigo = municipios.codigo_departamento_FK',
    ]
)[0];

$antecedentesFamiliares = [
    'Hipertensión Arterial' => true,
    'Diabetes tipo 2' => true,
    'Cáncer' => false,
    'Asma' => false,
    'Enfermedad Pulmonar' => false,
    'ACV' => false
];

//* datos del odontograma
$pacienteTrabajar = $_GET['cedulaPaciente'];

//*consultando convenciones Generales
$sql = "SELECT * FROM  convenciones";
$stmt = $connect->prepare($sql);
$stmt->execute();
$convencionesDesordenadas = $stmt->get_result();
$convencionesOrdenadas = $convencionesDesordenadas->fetch_all(MYSQLI_ASSOC);

//*consultando convenciones Obturado y Cariado OC
$sqlOC = "SELECT * FROM convenciones_oc";
$stmtOC = $connect->prepare($sqlOC);
$stmtOC->execute();
$convencionesSeccionDesordenadas = $stmtOC->get_result();
$convencionesSeccionOrdenadas = $convencionesSeccionDesordenadas->fetch_all(MYSQLI_ASSOC);

//*consultando codigos CIES
$sqlCIES = "SELECT * FROM codigos_cies";
$stmtCIES = $connect->prepare($sqlCIES);
$stmtCIES->execute();
$codigosCIES = $stmtCIES->get_result()->fetch_all(MYSQLI_ASSOC);


//* comprobando si el paciente ya tiene una consulta
$hasConsulta = makeConsult('consultas', ['*'], 'numero_documento_paciente_FK = ?', [$pacienteTrabajar]);

if (is_bool($hasConsulta[0]) && $hasConsulta[0] === false) {
    //*consultando dientes (tabla de dientes)
    $sqlDientes = "SELECT * FROM dientes";
    $stmtDientes = $connect->prepare($sqlDientes);
    $stmtDientes->execute();
    $dientesBD = $stmtDientes->get_result()->fetch_all(MYSQLI_ASSOC);

    $emptyOdontograma = true;
} else {
    $lastConsulta = end($hasConsulta);
    $lastOdonograma = makeConsult('odontogramas', ['*'], 'codigoConsultaFK  = ?', [$lastConsulta['codigo']]);
    $lastOdonograma = end($lastOdonograma);

    $dientesBD = makeConsult(
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
        [$lastOdonograma['codigo']],
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

    $emptyOdontograma = false;
}

//* proceso 5:  dar formato a dientes para odonograma
$dientesOdontograma = array(
    'seccionSuperior' => [
        'fila1' => [],
        'fila2' => []
    ],

    'seccionInferior' => [
        'fila1' => [],
        'fila2' => []
    ],
);

foreach ($dientesBD as $diente) {
    if ($diente['cuadrante'] === 2) {

        if ($diente['cuadrante_fila'] === 1) {
            array_push($dientesOdontograma['seccionSuperior']['fila1'], $diente);
        }

        if ($diente['cuadrante_fila'] === 2) {
            array_push($dientesOdontograma['seccionSuperior']['fila2'], $diente);
        }
    }

    if ($diente['cuadrante'] === 4) {

        if ($diente['cuadrante_fila'] === 1) {
            array_push($dientesOdontograma['seccionInferior']['fila1'], $diente);
        }

        if ($diente['cuadrante_fila'] === 2) {
            array_push($dientesOdontograma['seccionInferior']['fila2'], $diente);
        }
    }
}

$dientesSuperires = [
    'fila1' => [],
    'fila2' => []
];

$dientesInferiores = [
    'fila1' => [],
    'fila2' => []
];

foreach ($dientesBD as $diente) {
    if ($diente['cuadrante'] === 1) {

        if ($diente['cuadrante_fila'] === 1) {
            array_push($dientesSuperires['fila1'], $diente);
        }

        if ($diente['cuadrante_fila'] === 2) {
            array_push($dientesSuperires['fila2'], $diente);
        }
    }

    if ($diente['cuadrante'] === 3) {

        if ($diente['cuadrante_fila'] === 1) {
            array_push($dientesInferiores['fila1'], $diente);
        }

        if ($diente['cuadrante_fila'] === 2) {
            array_push($dientesInferiores['fila2'], $diente);
        }
    }
}

foreach ($dientesOdontograma['seccionSuperior'] as $filaName => $filaValue) {
    $dientesSuperioresReverse = array_reverse($dientesSuperires[$filaName]);
    $dientesOdontograma['seccionSuperior'][$filaName] = array_merge($dientesSuperioresReverse, $filaValue);
}

foreach ($dientesOdontograma['seccionInferior'] as $filaName => $filaValue) {
    $dientesInferioressReverse = array_reverse($dientesInferiores[$filaName]);
    $dientesOdontograma['seccionInferior'][$filaName]  = array_merge($dientesInferioressReverse, $filaValue);
}

ob_start();
include 'PDFVIEW.php';
$html = ob_get_clean();


require_once('../lib/tcpdf/tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();
$pdf->writeHTML($html);

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('example_001.pdf', 'I');
