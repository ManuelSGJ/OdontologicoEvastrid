<?php
include_once '../Modules/functions/sessions.php';
include_once '../Modules/functions/consultasGenerales.php';

if (!controllSession()) {
    $rootViews = dirname($_SERVER['PHP_SELF']);
    header('Location: http://localhost' . $rootViews . '/login.php');
}

$pacientes = consultaTablaCondicion('pacientes', " WHERE estado = 'activo' ");
?>
<?php include '../Modules/templates/head.php'; ?>

<div class="cont-pacientes">
    <div class="pacientes">
        <div class="logo-admin patient">
            <img src="../Img/Logo2.jpg" alt="">
        </div>

        <div class="titulo-historia misPacientes">
            <h3>Mis Pacientes <i class="fa-solid fa-hand-holding-heart"></i></h3>

        </div>
        <div class="salida">
            <a href="inicio.php">
                <p>Ir a Inicio</p>
                <i class="fa-solid fa-person-walking-arrow-right"></i>
            </a>
        </div>
        <table id="misPacientes">

            <thead>
                <tr>
                    <th>Cedula</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($pacientes as $paciente) { ?>
                    <tr>
                        <td><?php echo $paciente['numero_documento']; ?></td>
                        <td><?php echo $paciente['nombres']; ?></td>
                        <td><?php echo $paciente['apellidoUno'] . " " . $paciente['apellidoDos']; ?></td>
                        <td class="ultimoTD">
                            <a href="edicionPaciente.php?cedulaPaciente=<?php echo $paciente['numero_documento']; ?>">
                                <button class="linea1" title="Editar Paciente">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </a>

                            <a href="consulta.php?cedulaPaciente=<?php echo $paciente['numero_documento']; ?>">
                                <button class="linea2" title="Nueva Consulta">
                                    <i class="fa-solid fa-file-medical"></i>
                                </button>
                            </a>

                            <a href="ultimaConsulta.php?cedulaPaciente=<?php echo $paciente['numero_documento']; ?>">
                                <button title="Última Consulta">
                                    <i class="fa-solid fa-tooth"></i>
                                </button>
                            </a>

                            <a href="historialConsultas.php?cedulaPaciente=<?php echo $paciente['numero_documento']; ?>">
                                <button title="Ver Historial de consultas">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </a>

                            <a href="historiaClinica.php?cedulaPaciente=<?php echo $paciente['numero_documento']; ?>">
                                <button title="Ver Historia clinica Odontolgica">
                                    <i class="fa-solid fa-book-medical"></i>
                                </button>
                            </a>

                            <a href="generateHCO.php?cedulaPaciente=<?php echo $paciente['numero_documento']; ?>" target="_blank">
                                <button title="Generar Historia Clinica Odontologica">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </button>
                            </a>

                            <a href="" class="btnEliminarPaciente" value=<?php echo $paciente['numero_documento']; ?>>
                                <button title="Eliminar Paciente">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>

    let btnEliminarPaciente = document.querySelectorAll('.btnEliminarPaciente');

    btnEliminarPaciente.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            eliminarPaciente(btn.getAttribute('value'));
        })
    })

    //* script para eliminacion de pacientes 
    function eliminarPaciente(idPaciente) {
        Swal.fire({
            title: '¿Estas seguro de eliminar este paciente?',
            text: "Esta accion no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',

            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {

                let url = '../Modules/models/pacientes/eliminar.php';

                let data = new FormData();
                data.append('cedulaPaciente', idPaciente);

                //* peticion ajax
                const xhr = new XMLHttpRequest();

                xhr.open('POST', url, true);

                xhr.onload = function () {
                    if (this.status === 200) {
                        console.log(xhr.responseText);
                        let respuesta = JSON.parse(xhr.responseText);

                        if (respuesta.proceso == 'correcto') {
                            Swal.fire({
                                title: 'Eliminado',
                                text: 'El paciente ha sido eliminado',
                                icon: 'success',
                                timer: 2000,
                            }).then((result) => {
                                window.location.reload();
                            })
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'El paciente no ha sido eliminado',
                                icon: 'error',
                                timer: 2000,
                            })
                        }
                    }
                }

                xhr.send(data);
            }
        })
    }
</script>

<?php include '../Modules/templates/footer.php'; ?>