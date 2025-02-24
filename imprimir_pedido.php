<?php
session_start();

if (empty($_SESSION['Usuario_Nombre']) ) { // si el usuario no esta logueado no lo deja entrar
  header('Location: cerrarsesion.php');
  exit;
}
 ($_SESSION['Descarga']);

//voy a necesitar la conexion: incluyo la funcion de Conexion.
require_once 'funciones/conexion.php';

//genero una variable para usar mi conexion desde donde me haga falta
//no envio parametros porque ya los tiene definidos por defecto
$MiConexion = ConexionBD();

//ahora voy a llamar el script con la funcion que genera mi listado
require_once 'funciones/select_general.php';

//voy a ir listando lo necesario para trabajar en este script: 

// Obtener los datos del pedido y sus detalles si se pasa el ID por GET
$DatosPedidoActual = Datos_Pedidos($MiConexion, $_GET['ID_PEDIDO']);
$DetallesPedido = Detalles_Pedido($MiConexion, $_GET['ID_PEDIDO']);

//Empiezo a guardar el contenido en una variable
ob_start();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pedido</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; }
        .header, .footer { text-align: center; margin: 20px 0; }
        .details { margin: 20px 0; }
        .details div { margin: 5px 0; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Comprobante de Pedido</h2>
            <p>Fecha: <span id="fecha"><?php echo $DatosPedidoActual['FECHA'] ?></span></p>
        </div>
        <div class="details">
            <h3>Datos del Cliente</h3>
            <div>Nombre: <span id="nombreCliente"><?php echo $DatosPedidoActual['CLIENTE'] ?>, <?php echo $DatosPedidoActual['CLIENTE_A'] ?></span></div>
            <div>Teléfono: <span id="telefonoCliente"><?php echo $DatosPedidoActual['TELEFONO'] ?></span></div>
        </div>

        <div class="details">
            <h3>Detalles del pedido</h3>
            <table border="1" width="100%" cellpadding="5">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Editorial</th>
                        <th>Precio Unitario</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($DetallesPedido as $detalle) { ?>
                        <tr>
                            <td><?php echo $detalle['LIBRO_T']; ?></td>
                            <td><?php echo $detalle['LIBRO_E']; ?></td>
                            <td>$<?php echo number_format($detalle['PRECIO'], 2); ?></td>
                            <td><?php echo $detalle['CANTIDAD']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="details">
            <h3>Precio</h3>
            <?php
                // Calcula el monto del descuento
                $monto_descuento = ($DatosPedidoActual['PRECIO_TOTAL'] * $DatosPedidoActual['DESCUENTO']) / 100;
                $saldo = ($DatosPedidoActual['PRECIO_TOTAL'] - $monto_descuento)-$DatosPedidoActual['SENIA']
            ?>
            <div>Precio Total: $<span id="precioTotal"><?php echo $DatosPedidoActual['PRECIO_TOTAL'] ?></span></div>
            <div>Descuento: %<span id="sena"><?php echo $DatosPedidoActual['DESCUENTO'] ?></span></div>
            <div>Seña: $<span id="sena"><?php echo $DatosPedidoActual['SENIA'] ?></span></div>
            <div>Saldo: $<span id="saldo"><?php echo $saldo ?></span></div>
        </div>
        <div class="footer">
            <p>Gracias por su compra</p>
        </div>
    </div>
</body>
</html>




<?php
//Termino de guardar el contenido en un variable 
$html=ob_get_clean();
//echo $html;

//creo la variable dompdf
require_once 'libreria/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
$dompdf = new Dompdf();

//activo las opciones para poder generar el pdf con imagenes
$options = $dompdf->getOptions();
$options->set(array('isRemoteEnable' => true));
$dompdf->setOptions($options);

//le paso el $html en el que guardamos toda la lista
$dompdf->loadHtml($html);

//seteo el papel en A4 vertical
$dompdf->setPaper('A4','portrait');

$dompdf->render();
//le indico el nombre del archivo y le doy true para que descargue
$dompdf->stream("archivo.pdf", array("Attachment" => true));
?>