<?php
session_start();

if (empty($_SESSION['Usuario_Nombre']) ) { // si el usuario no esta logueado no lo deja entrar
  header('Location: cerrarsesion.php');
  exit;
}

require ('encabezado.inc.php'); //Aca uso el encabezado que esta seccionados en otro archivo

require ('barraLateral.inc.php'); //Aca uso el encabezaso que esta seccionados en otro archivo

//voy a necesitar la conexion: incluyo la funcion de Conexion.
require_once 'funciones/conexion.php';

//genero una variable para usar mi conexion desde donde me haga falta
//no envio parametros porque ya los tiene definidos por defecto
$MiConexion = ConexionBD();

//ahora voy a llamar el script con la funcion que genera mi listado
require_once 'funciones/select_general.php';


//voy a ir listando lo necesario para trabajar en este script: 
$ListadoLibros = Listar_Libros($MiConexion);
$CantidadLibros = count($ListadoLibros);

  //estoy en condiciones de poder buscar segun el parametro
  
    if (!empty($_POST['BotonBuscar'])) {

        $parametro = $_POST['parametro'];
        $criterio = $_POST['gridRadios'];
        $ListadoLibros=Listar_Libros_Parametro($MiConexion,$criterio,$parametro);
        $CantidadLibros = count($ListadoLibros);


}


?>



<main id="main" class="main">

<div class="pagetitle">
  <h1>Listado Libros</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Menu</a></li>
      <li class="breadcrumb-item">Libros</li>
      <li class="breadcrumb-item active">Listado Libros</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section">
    
    <div class="card">
        <div class="card-body">
          <h5 class="card-title">Listado Libros</h5>
          <?php if (!empty($_SESSION['Mensaje'])) { ?>
            <div class="alert alert-<?php echo $_SESSION['Estilo']; ?> alert-dismissable">
              <?php echo $_SESSION['Mensaje'] ?>
            </div>
          <?php } ?>

          <Form method="POST">
          <div class="row mb-4">
            <label for="inputEmail3" class="col-sm-1 col-form-label">Buscar</label>
              <div class="col-sm-3">
                <input type="text" class="form-control" name="parametro" id="parametro">
                </div>

                <style> .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; line-height: 1.5; border-radius: 0.2rem; } </style>

              <div class="col-sm-3 mt-2">
                <button type="submit" class="btn btn-success btn-xs d-inline-block" value="buscar" name="BotonBuscar">Buscar</button>
                <button type="submit" class="btn btn-danger btn-xs d-inline-block" value="limpiar" name="BotonLimpiar">Limpiar</button>
                <button type="submit" class="btn btn-primary btn-xs d-inline-block" value="descargar" name="Descargar">Descargar</button>
              </div>
              <div class="col-sm-5 mt-2">
                    <div class="form-check form-check-inline small-text">
                      <input class="form-check-input" type="radio" name="gridRadios" id="gridRadios1" value="Titulo" checked>
                      <label class="form-check-label" for="gridRadios1">
                        Titulo
                      </label>
                    </div>
                    <div class="form-check form-check-inline small-text">
                      <input class="form-check-input" type="radio" name="gridRadios" id="gridRadios2" value="Codigo">
                      <label class="form-check-label" for="gridRadios2">
                        Codigo
                      </label>
                    </div>
                    <div class="form-check form-check-inline small-text">
                      <input class="form-check-input" type="radio" name="gridRadios" id="gridRadios3" value="Editorial">
                      <label class="form-check-label" for="gridRadios3">
                        Editorial
                    </div>
                    
                  </div>
              
          </div>
          </form>
          <!-- Table with stripped rows -->
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th scope="col">ID</th>
                  <th scope="col">Código</th>
                  <th scope="col">Titulo</th>
                  <th scope="col">Editorial</th>
                  <th scope="col">Mayorista</th>
                  <th scope="col">Precio</th>
                  <th scope="col">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php for ($i=0; $i<$CantidadLibros; $i++) { ?>
                  <tr>
                    <td class="small"><?php echo $ListadoLibros[$i]['ID_LIBRO']; ?></td>
                    <td class="small"><?php echo $ListadoLibros[$i]['CODIGO']; ?></td>
                    <td class="small"><?php echo $ListadoLibros[$i]['TITULO']; ?></td>
                    <td class="small"><?php echo $ListadoLibros[$i]['EDITORIAL']; ?></td>
                    <td class="small"><?php echo $ListadoLibros[$i]['MAYORISTA']; ?></td>
                    <td class="small"><?php echo number_format($ListadoLibros[$i]['PRECIO'], 2); ?></td>
                    <td>
                      <!-- valido para que solo se puedan eliminar los personales -->
                      <?php if($ListadoLibros[$i]['MAYORISTA'] == 'Personal') { ?>
                        <a href="eliminar_libros.php?ID_LIBRO=<?php echo $ListadoLibros[$i]['ID_LIBRO']; ?>" 
                          title="Eliminar" 
                          onclick="return confirm('Confirma eliminar este libro?');">
                          <i class="bi bi-trash-fill text-danger fs-5"></i>
                        </a>
                      <?php } ?>

                      <a href="modificar_libros.php?ID_LIBRO=<?php echo $ListadoLibros[$i]['ID_LIBRO']; ?>" 
                          title="Modificar">
                        <i class="bi bi-pencil-fill text-warning fs-5"></i>
                      </a>

                      <a href="#" 
                        title="Agregar a pedido" 
                        onclick="agregarAPedido(<?php echo $ListadoLibros[$i]['ID_LIBRO']; ?>)">
                        <i class="bi bi-bag-plus-fill text-primary fs-5"></i>
                      </a>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
          <!-- End Table with stripped rows -->
          
        </div>
    </div>
 
</section>

</main><!-- End #main -->

<?php
  $_SESSION['Mensaje']='';
  require ('footer.inc.php'); //Aca uso el FOOTER que esta seccionados en otro archivo
?>


</body>

</html>