<?php
include 'template/header.php';
include 'model/conexion.php';

$mensaje = ''; // Inicializar la variable mensaje

// Definir la cantidad de resultados a mostrar
$cantidadResultados = 2;

// Procesar la búsqueda
if (isset($_GET['buscar'])) {
    $buscarPalabra = $_GET['buscar'];

    // Consulta para buscar la palabra exacta
    $queryExacta = "SELECT * FROM diccionario_ashanika WHERE palabra = :buscarPalabra OR significado = :buscarPalabra ORDER BY id_ashanika LIMIT 1";
    $stmtExacta = $bd->prepare($queryExacta);
    $stmtExacta->bindValue(':buscarPalabra', $buscarPalabra, PDO::PARAM_STR);
    $stmtExacta->execute();

    // Consulta para buscar coincidencias parciales
    $queryParciales = "SELECT * FROM diccionario_ashanika WHERE palabra LIKE :buscarPalabra OR significado LIKE :buscarPalabra ORDER BY id_ashanika LIMIT :cantidadResultados";
    $stmtParciales = $bd->prepare($queryParciales);
    $stmtParciales->bindValue(':buscarPalabra', '%' . $buscarPalabra . '%', PDO::PARAM_STR);
    $stmtParciales->bindValue(':cantidadResultados', $cantidadResultados, PDO::PARAM_INT);
    $stmtParciales->execute();

    // Combinar resultados de ambas consultas
    $resultadosExactos = $stmtExacta->fetchAll(PDO::FETCH_ASSOC);
    $resultadosParciales = $stmtParciales->fetchAll(PDO::FETCH_ASSOC);
    $resultados = array_merge($resultadosExactos, $resultadosParciales);

    // Guardar la búsqueda en la base de datos
    saveSearch($bd, $buscarPalabra);

    // Verificar si se encontraron resultados
    if (empty($resultados)) {
        $mensaje = 'No se encontraron resultados. Comunícate con el administrador.';
    }
} else {
    // Si no se realiza una búsqueda, mostrar los primeros resultados
    $resultados = $bd->query("SELECT * FROM diccionario_ashanika ORDER BY id_ashanika LIMIT $cantidadResultados")->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <title>diccionario ashanika</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link rel="stylesheet" href="estilos.css"> <!-- Ajusta el nombre del archivo según donde hayas guardado tu hoja de estilos -->
</head>

<body>
    

    <section class="container">
        <?php echo $mensaje; ?>
        <!-- Formulario de búsqueda -->
        <form method="GET" action="index.php" class="mb-3">
            <div class="form-group">
                <br>
                <br>
                <label for="buscarPalabra">Buscar por Español o ashanika:</label>
                <br>
                <br>

                <input type="text" name="buscar" class="form-control" id="buscarPalabra" placeholder="Ingrese palabra en ashanika o español">
            </div>
            <br>
            <button type="submit" class="btn btn-secondary btn-block">Buscar</button>
            <br>
        </form>
        <br>

        <div class="table-responsive">
            <table class="table">
                <thead class="bg-primary">
                    <tr>
                        <th>Español</th>
                        <th>ashanika</th>
                    </tr>
                </thead>
                <tbody>
                    <?php displayResults($resultados); ?>
                </tbody>
            </table>
        </div>
    </section>
</body>

</html>
<?php include 'template/footer.php'; ?>

<?php
function saveSearch($bd, $consulta)
{
    $guardarBusqueda = $bd->prepare("INSERT INTO registros_busquedas (consulta, fecha) VALUES (:consulta, NOW())");
    $guardarBusqueda->bindValue(':consulta', $consulta, PDO::PARAM_STR);
    $guardarBusqueda->execute();
}

function displayResults($resultados)
{
    foreach ($resultados as $perFila) {
        echo '<tr>
                <td>' . $perFila['palabra'] . '</td>
                <td>' . $perFila['significado'] . '</td>
             </tr>';
    }
}
?>
