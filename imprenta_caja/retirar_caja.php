<?php
ob_start();
session_start();

if (empty($_SESSION['Usuario_Nombre'])) {
    header('Location: ../core/cerrarsesion.php');
    exit;
}

require('../shared/encabezado.inc.php');
require('../shared/barraLateral.inc.php');
require_once '../funciones/conexion.php';
require_once '../funciones/imprenta.php';

$MiConexion = ConexionBD();

// Obtener los proveedores desde la base de datos
$Proveedores = Listar_Proveedores($MiConexion);

if (!empty($_POST['BotonRegistrar'])) {
    // Validar y limpiar los datos del formulario
    Validar_Venta();

    // Asignar el mensaje de validación a una variable local
    $Mensaje = $_SESSION['Mensaje'];
    $Estilo = 'danger'; // Estilo para mensajes de error

    // Si no hay errores, proceder con la inserción
    if (empty($Mensaje)) {
        if (empty($_POST['idCaja'])) {
            echo "<script>
                alert('Error: No hay caja seleccionada. Por favor, seleccione una caja antes de registrar el retiro.');
                window.location.href = '../core/index.php';
            </script>";
            exit;
        }

        // Llamar al método InsertarVenta
        if (InsertarVenta($MiConexion)) {
            $_SESSION['Mensaje'] = 'Retiro registrado correctamente.';
            $_SESSION['Estilo'] = 'success';

            // Redirigir para evitar reenvío del formulario
            header("Location: planilla_caja.php");
            exit;
        } else {
            $_SESSION['Mensaje'] = 'Error al registrar el retiro.';
            $_SESSION['Estilo'] = 'danger';
        }
    }
}

$MiConexion->close();
?>

<main id="main" class="main">

    <div class="pagetitle">
      <h1>Retiros de Caja</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Menu</a></li>
          <li class="breadcrumb-item">Caja</li>
          <li class="breadcrumb-item active">Retirar de Caja</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="card">
        <div class="card-body">

          <!-- Sección de Métodos de Retiro -->
        <form method="post">
            <?php if (!empty($Mensaje)) { ?>
                <div class="alert alert-<?php echo $Estilo; ?> alert-dismissable">
                <?php echo $Mensaje; ?>
                </div>
                <?php unset($_SESSION['Mensaje'], $_SESSION['Estilo']); // Limpiar el mensaje después de mostrarlo ?>
            <?php } ?>

            <!-- Campo oculto para idCaja -->
            <input type="hidden" name="idCaja" value="<?php echo isset($_SESSION['Id_Caja']) ? $_SESSION['Id_Caja'] : ''; ?>">

            <div class="text-center mb-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 card-title">Seleccione el Método de Retiro</h6>
            </div>
            <div class="d-flex flex-wrap justify-content-center">
                <button type="button" class="btn btn-secondary mx-2 my-2 metodo-pago" data-id="1">Efectivo</button>
                <button type="button" class="btn btn-secondary mx-2 my-2 metodo-pago" data-id="2">Banco</button>
                <button type="button" class="btn btn-secondary mx-2 my-2 metodo-pago" data-id="3">Caja de Seguridad</button>
                <input type="hidden" name="idTipoPago" id="idTipoPago">
            </div>

            <div class="container">
                <!-- Sección de Tipos de Retiro -->
                <div class="text-center mb-4">
                    <h6 class="mb-0 card-title">Seleccione el Tipo de Retiro</h6>
                </div>
                <div class="row justify-content-center mb-4">
                    <!-- Botones de Proveedor, Sueldos y Etc. -->
                    <div class="col-auto">
                        <button type="button" class="btn btn-secondary tipo-servicio" data-id="1">Proveedores</button>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-secondary tipo-servicio" data-id="2">Sueldos</button>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-secondary tipo-servicio" data-id="3">Etc.</button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="idDetalle" id="idDetalle">

            <!-- Campo para ingresar el valor de dinero -->
            <div class="row justify-content-center mb-4">
                <div class="col-md-6 text-center">
                    <label for="valorDinero" class="form-label">Ingrese el Valor de Dinero</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control text-center" id="Monto" name="Monto" placeholder="0" min="0" step="1">
                    </div>
                </div>
            </div>

            <!-- Campo para observaciones -->
            <div class="row justify-content-center mb-4">
                <div class="col-md-6 text-center">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="Observaciones" rows="3" placeholder="Ingrese comentarios u observaciones"></textarea>
                </div>
            </div>

            <!-- Botones de registrar o reset -->
            <div class="row justify-content-center">
                <input type='hidden' name="idTipoOperacion" id="idTipoOperacion" />
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary" value="Registrar" name="BotonRegistrar">Registrar Retiro</button>
                </div>
                <div class="col-auto">
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </form><!-- End Horizontal Form -->
        </div>
      </div>

    </section>

</main><!-- End #main -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const metodoPagoButtons = document.querySelectorAll('.metodo-pago');
        const tipoServicioButtons = document.querySelectorAll('.tipo-servicio');
        const idTipoOperacionInput = document.getElementById('idTipoOperacion');

        // Manejar métodos de pago
        metodoPagoButtons.forEach(button => {
            button.addEventListener('click', () => {
                metodoPagoButtons.forEach(btn => btn.classList.remove('btn-primary'));
                button.classList.add('btn-primary');
                document.getElementById('idTipoPago').value = button.getAttribute('data-id');
                idTipoOperacionInput.value = button.getAttribute('data-id') === '1' ? '2' : '3';
            });
        });

        // Manejar tipos de servicio
        tipoServicioButtons.forEach(button => {
            button.addEventListener('click', () => {
                tipoServicioButtons.forEach(btn => btn.classList.remove('btn-primary'));
                button.classList.add('btn-primary');
                document.getElementById('idDetalle').value = button.getAttribute('data-id');
            });
        });
    });
</script>

<?php
require('../shared/footer.inc.php');
ob_end_flush();
?>