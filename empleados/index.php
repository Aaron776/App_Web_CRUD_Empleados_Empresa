<?php
include("../conexion/conexion.php");
$txtID = (isset($_POST['text_id'])) ? $_POST['text_id'] : ""; // aqui valiudamos si estamos enviando datos del formulario o no
$txtNombre = (isset($_POST['text_nombre'])) ? $_POST['text_nombre'] : "";
$txtApellidoPaterno = (isset($_POST['text_apellido_paterno'])) ? $_POST['text_apellido_paterno'] : "";
$txtApellidoMaterno = (isset($_POST['text_apellido_materno'])) ? $_POST['text_apellido_materno'] : "";
$txtEmail = (isset($_POST['text_email'])) ? $_POST['text_email'] : "";
$txtFoto = (isset($_FILES['text_foto']["name"])) ? $_FILES['text_foto']["name"] : "";

$accion = (isset($_POST['accion'])) ? $_POST['accion'] : ""; // aqui vemos si presionamos el boton con el name accion

$accionAgregar="";
$accionModificar=$accionEliminar=$accionCancelar="disabled";
$mostrarModal=false;

switch ($accion) {
    case 'btn_guardar':
        $sentencia_sql = $pdo->prepare("INSERT INTO empleados (nombre, apellidoP, apellidoM, correo, foto) VALUES (:txtNombre,:txtApellidoPaterno,:txtApellidoMaterno,:txtEmail,:txtFoto)");
        $sentencia_sql->bindParam(':txtNombre', $txtNombre);
        $sentencia_sql->bindParam(':txtApellidoPaterno', $txtApellidoPaterno);
        $sentencia_sql->bindParam(':txtApellidoMaterno', $txtApellidoMaterno);
        $sentencia_sql->bindParam(':txtEmail', $txtEmail);

        $fecha = new DateTime();
        $nombreArchivo = ($txtFoto != "") ? $fecha->getTimestamp() . "_" . $_FILES["text_foto"]["name"] : "imagen.jpg"; // aqui validamos si se envia una foto o no
        $tmpFoto = $_FILES["text_foto"]["tmp_name"]; // aqui guardamos la foto temporalmente
        if ($tmpFoto != "") { // aqui verificamos si se envia una foto
            move_uploaded_file($tmpFoto, "../imagenes/" . $nombreArchivo); // aqui movemos la foto del servidor a la carpeta imagenes
        }
        $sentencia_sql->bindParam(':txtFoto', $nombreArchivo);
        $sentencia_sql->execute();

        echo "<div class='alert alert-success' role='alert' style='text-align:center;'><strong>Registro Guardado Correctamente</strong></div>";
        header('Location:index.php'); // aqui redireccionamos a la pagina principal
        break;
    case 'btn_actualizar':
        $sentencia_sql = $pdo->prepare("UPDATE empleados SET nombre=:txtNombre, apellidoP=:txtApellidoPaterno, apellidoM=:txtApellidoMaterno, correo=:txtEmail WHERE id=:txtID");
        $sentencia_sql->bindParam(':txtNombre', $txtNombre);
        $sentencia_sql->bindParam(':txtApellidoPaterno', $txtApellidoPaterno);
        $sentencia_sql->bindParam(':txtApellidoMaterno', $txtApellidoMaterno);
        $sentencia_sql->bindParam(':txtEmail', $txtEmail);
        $sentencia_sql->bindParam(':txtID', $txtID);
        $sentencia_sql->execute();

        $fecha = new DateTime();
        $nombreArchivo = ($txtFoto != "") ? $fecha->getTimestamp() . "_" . $_FILES["text_foto"]["name"] : "imagen.jpg"; // aqui validamos si se envia una foto o no
        $tmpFoto = $_FILES["text_foto"]["tmp_name"]; // aqui guardamos la foto temporalmente
        if ($tmpFoto != "") { // aqui verificamos si se envia una foto
            move_uploaded_file($tmpFoto, "../imagenes/" . $nombreArchivo); // aqui movemos la foto del servidor
            $sentencia_sql = $pdo->prepare("SELECT foto FROM empleados WHERE id=:txtID"); // aqui seleccionamos la foto del registro que queremos eliminar
            $sentencia_sql->bindParam(':txtID', $txtID);
            $sentencia_sql->execute();
            $foto_anterior = $sentencia_sql->fetch(PDO::FETCH_LAZY); // aqui guardamos la foto anterior del registro que queremos eliminar
            if (isset($foto_anterior["foto"])) {
                if (file_exists("../imagenes/" . $foto_anterior["foto"])) { // aqui verificamos si la foto existe en el servidor
                    if($foto_anterior['foto']!="imagen.jpg") // aqui verificamos si la foto es la default
                    unlink("../imagenes/" . $foto_anterior["foto"]); // aqui eliminamos la foto del servidor
                }
            }

            $sentencia_sql = $pdo->prepare("UPDATE empleados SET  foto=:txtFoto WHERE id=:txtID"); // aqui actualizamos la foto del registro
            $sentencia_sql->bindParam(':txtFoto', $nombreArchivo);
            $sentencia_sql->bindParam(':txtID', $txtID);
            $sentencia_sql->execute();
        }

        echo "<div class='alert alert-success' role='alert' style='text-align:center;'><strong>Registro Actualizado Correctamente</strong></div>";
        header('Location:index.php'); // aqui redireccionamos a la pagina principal
        break;
    case 'btn_eliminar':
        $sentencia_sql = $pdo->prepare("SELECT foto FROM empleados WHERE id=:txtID"); // aqui seleccionamos la foto del registro que queremos eliminar
        $sentencia_sql->bindParam(':txtID', $txtID);
        $sentencia_sql->execute();
        $foto_anterior = $sentencia_sql->fetch(PDO::FETCH_LAZY); // aqui guardamos la foto anterior del registro que queremos eliminar
        if (isset($foto_anterior["foto"]) && (($foto_anterior['foto']!="imagen.jpg"))){ // aqui verificamos si la foto existe y si es la default
            if (file_exists("../imagenes/" . $foto_anterior["foto"])) { // aqui verificamos si la foto existe en el servidor
                unlink("../imagenes/" . $foto_anterior["foto"]); // aqui eliminamos la foto del servidor
            }
        }

        $sentencia_sql = $pdo->prepare("DELETE FROM empleados WHERE id=:txtID");
        $sentencia_sql->bindParam(':txtID', $txtID);
        $sentencia_sql->execute();

        echo "<div class='alert alert-danger' role='alert' style='text-align:center;'><strong>Registro Eliminado Correctamente</strong></div>";
        header('Location:index.php'); // aqui redireccionamos a la pagina principal
        break;
    case 'btn_cancelar':
        header('Location:index.php'); // aqui redireccionamos a la pagina principal
        break;
    case 'Seleccionar':
        $accionAgregar="disabled";
        $accionModificar=$accionEliminar=$accionCancelar="";
        $mostrarModal=true;

        $sentencia_sql = $pdo->prepare("SELECT * FROM empleados WHERE id=:txtID"); 
        $sentencia_sql->bindParam(':txtID', $txtID);
        $sentencia_sql->execute();
        $empleado = $sentencia_sql->fetch(PDO::FETCH_LAZY);
        $txtNombre = $empleado["nombre"];
        $txtApellidoPaterno = $empleado["apellidoP"];
        $txtApellidoMaterno = $empleado["apellidoM"];
        $txtEmail = $empleado["correo"];
        $txtFoto = $empleado["foto"];
}


$seleccion = $pdo->prepare("SELECT * FROM empleados"); // aqui etamos mostrando los registros de la tabla empleados
$seleccion->execute();
$lista_empleados = $seleccion->fetchAll(PDO::FETCH_ASSOC);
?>


<!doctype html>
<html lang="en">
<head>
    <title>CRUD PHP MYSQL</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Empleados</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="mb-3">
                                    <input type="hidden" name="text_id" required value="<?php echo $txtID; ?>" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Nombre(s):</label>
                                    <input type="text" name="text_nombre" required  value="<?php echo $txtNombre; ?>" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Apellido Paterno:</label>
                                    <input type="text" name="text_apellido_paterno" required value="<?php echo $txtApellidoPaterno; ?>" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Apellido Materno:</label>
                                    <input type="text" name="text_apellido_materno" required value="<?php echo $txtApellidoMaterno; ?>" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Email:</label>
                                    <input type="email" name="text_email" required value="<?php echo $txtEmail; ?>" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
                                </div>
                                <div class="mb-3">
                                    <label for="" class="form-label">Foto:</label>
                                    <?php if($txtFoto!=""){ // aqui verificamos si la foto existe o no ?> 
                                        <br>
                                         <img src="../imagenes/<?php echo $txtFoto; //aqui mostramos la foto modo preview ?>" alt="" width="100px" class="img-thumbnail rounded mx-auto d-block">   
                                        <br>
                                        <br>
                                    <?php } ?>
                                    <input type="file" accept="image/*"  name="text_foto" value="<?php echo $txtFoto; ?>" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="mb-3">
                                <button type="submit" <?php echo $accionAgregar?> value="btn_guardar" name="accion" class="btn btn-success">Guardar</button>
                                <button type="submit" <?php echo $accionModificar?> value="btn_actualizar" name="accion" class="btn btn-warning">Modificar</button>
                                <button type="submit" <?php echo $accionEliminar?> onclick="return confirmarEliminar('¿Desea eliminar el registro?')" value="btn_eliminar" name="accion" class="btn btn-danger">Eliminar</button>
                                <button type="submit" <?php echo $accionCancelar?> value="btn_cancelar" name="accion" class="btn btn-primary">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Agregar Registro
            </button>
        </form>
    </div>
    <br><br>
    <div class="row">
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">FOTO</th>
                        <th scope="col">NOMBRE COMPLETO</th>
                        <th scope="col">EMAIL</th>
                        <th scope="col">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista_empleados as $index) { ?>
                        <tr class="">
                            <td><img class="img-thumbnail" src="../imagenes/<?php echo $index['foto'] ?>" width="100px" height="100px"></td>
                            <td><?php echo $index['nombre'] ?> <?php echo $index['apellidoP'] ?> <?php echo $index['apellidoM'] ?></td>
                            <td><?php echo $index['correo'] ?></td>
                            <td>
                                <form action="" method="post">
                                    <input type="hidden" name="text_id" value="<?php echo $index['id'] ?>">
                                    <input type="submit" value="Seleccionar" name="accion" class="btn btn-info">
                                    <button type="submit" onclick="return confirmarEliminar('¿Desea eliminar el registro?')" value="btn_eliminar" name="accion" class="btn btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js" integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
    </script>
    <?php if($mostrarModal){?>
        <script>
            $('#exampleModal').modal('show');
        </script>
    <?php }?>

    <script>
        function confirmarEliminar(mensaje){
            return (confirm(mensaje))?true:false;
        }
    </script>
</body>

</html>