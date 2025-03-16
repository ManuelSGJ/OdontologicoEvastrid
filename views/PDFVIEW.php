<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Historia Clínica Odontológica</title>
    <style>
        .seccion{
            text-align: center;
        }

        .diente{
            text-align: center;
            line-height: 20px;
            color: red;
            border-top-color: red;
            border-top-width: 1px;
            border-left-color: red;
            border-left-width: 1px;
            border-left-style: solid;
            border-right-color: red;
            border-right-width: 1px;
            border-right-style: solid;
            border-bottom-color: red;
            border-bottom-width: 1px;
        }
    </style>
</head>

<body style="font-family: Arial, sans-serif; font-size: 12px; line-height: 1.5;">
    <!-- Encabezado -->
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0; font-size: 18px; color: #2c3e50;">Historia Clínica Odontológica</h2>
        <p style="margin: 0; font-size: 14px; color: #34495e;"><strong>Paciente:</strong> <?= $paciente['nombres'] . ' ' . $paciente['apellidoUno'] . ' ' . $paciente['apellidoDos'] ?></p>
    </div>
    <hr style="border: 1px solid #34495e;">

    <!-- Datos del paciente -->
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14px; color: #2c3e50;">Datos del Paciente</h3>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li><strong>Tipo de documento:</strong> <?= $paciente['clase_de_documento'] ?></li>
            <li><strong>Número de documento:</strong> <?= $paciente['numero_documento'] ?></li>
            <li><strong>Fecha de nacimiento:</strong> <?= $paciente['fecha_nacimiento'] ?></li>
            <li><strong>Fecha de inicio del tratamiento:</strong> <?= $paciente['fecha_inicio_tratamiento'] ?></li>
            <li><strong>Sexo:</strong> <?= ucfirst($paciente['sexo']) ?></li>
        </ul>
    </div>

    <!-- Residencia -->
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 14px; color: #2c3e50;">Residencia</h3>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li><strong>Departamento:</strong> <?= $paciente['departamento'] ?></li>
            <li><strong>Municipio:</strong> <?= $paciente['municipio'] ?></li>
            <li><strong>Dirección:</strong> <?= $paciente['direccion_residencia'] ?></li>
            <li><strong>Teléfono:</strong> <?= $paciente['telefono'] ?></li>
        </ul>
    </div>

    <!-- Antecedentes Familiares -->
    <div>
        <h3 style="font-size: 14px; color: #2c3e50;">Antecedentes Familiares</h3>
        <ul style="list-style-type: disc; margin-left: 20px;">
            <?php foreach ($antecedentesFamiliares as $antecedente => $tiene): ?>
                <?php if ($tiene): ?>
                    <li><?= $antecedente ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
        <p><strong>Otros:</strong> <?= $paciente['otrosAntecedentesFamiliares'] ?></p>
    </div>

    <!-- Odontograma -->
    <div class="odontogramaM">
        <h3 style="font-size: 14px; color: #2c3e50;">Odontograma</h3>
        <div class="seccion">
            <?php foreach ($dientesOdontograma['seccionSuperior']['fila1'] as $diente) { ?>
                <span class="diente" style="border: 1px solid black;" width="20px">
                    <?php echo $diente['numero_diente']; ?>
                </span>
            <?php } ?>
        </div>
        <div class="seccion">
            <?php foreach ($dientesOdontograma['seccionSuperior']['fila2'] as $diente) { ?>
                <span class="diente" style="border: 1px solid black;">
                    <?php echo $diente['numero_diente']; ?>
                </span>
            <?php } ?>
        </div>
        <div class="seccion">
            <?php foreach ($dientesOdontograma['seccionInferior']['fila1'] as $diente) { ?>
                <span class="diente" style="border: 1px solid black;">
                    <?php echo $diente['numero_diente']; ?>
                </span>
            <?php } ?>
        </div>
        <div class="seccion">
            <?php foreach ($dientesOdontograma['seccionInferior']['fila2'] as $diente) { ?>
                <span class="diente" style="border: 1px solid black;">
                    <?php echo $diente['numero_diente']; ?>
                </span>
            <?php } ?>
        </div>
    </div>
</body>

</html>
