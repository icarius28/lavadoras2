<?php

// Obtener los totales de la base de datos
// Total de alquileres
$sql_alquileres = "SELECT COUNT(*) as total_alquileres FROM alquileres";
$result_alquileres = $conn->query($sql_alquileres);
$row_alquileres = $result_alquileres->fetch_assoc();
$total_alquileres = $row_alquileres['total_alquileres'];

// Total de usuarios
$sql_usuarios = "SELECT COUNT(*) as total_usuarios FROM usuarios";
$result_usuarios = $conn->query($sql_usuarios);
$row_usuarios = $result_usuarios->fetch_assoc();
$total_usuarios = $row_usuarios['total_usuarios'];

// Total de negocios
$sql_negocios = "SELECT COUNT(*) as total_negocios FROM negocios";
$result_negocios = $conn->query($sql_negocios);
$row_negocios = $result_negocios->fetch_assoc();
$total_negocios = $row_negocios['total_negocios'];

$sql_alquileres_por_mes = "
    SELECT 
        MONTH(fecha_inicio) AS mes, 
        COUNT(*) AS cantidad 
    FROM alquileres 
    GROUP BY mes
    ORDER BY mes
";

$result_alquileres_por_mes = $conn->query($sql_alquileres_por_mes);

$datos_alquileres = array_fill(1, 12, 0); // Inicializa de enero (1) a diciembre (12) con 0

while ($row = $result_alquileres_por_mes->fetch_assoc()) {
    $mes = (int)$row['mes'];
    $datos_alquileres[$mes] = (int)$row['cantidad'];
}


if (isset($_SESSION['negocio']) && $_SESSION['negocio']) {
    $negocio_id = (int) $_SESSION['negocio'];
    
    $sql_alquileres = "SELECT COUNT(*) as total_alquileres FROM alquileres WHERE negocio_id = '$negocio_id'";
    $result_alquileres = $conn->query($sql_alquileres);
    $total_alquileres = $result_alquileres->fetch_assoc()['total_alquileres'];

    $sql_lavadoras_disponibles = "SELECT COUNT(*) as lavadoras FROM lavadoras WHERE negocio_id = '$negocio_id' and status = 'disponible'";
    $result_lavadoras_disponibles = $conn->query($sql_lavadoras_disponibles);
    $lavadoras_disponibles = $result_lavadoras_disponibles->fetch_assoc()['lavadoras'];

    $sql_lavadoras = "SELECT COUNT(*) as lavadoras FROM lavadoras WHERE negocio_id = '$negocio_id'";
    $result_lavadoras = $conn->query($sql_lavadoras);
    $total_lavadoras = $result_lavadoras->fetch_assoc()['lavadoras'];

    $sql_ult_alq = "SELECT 
    alquileres.*, 
    usuarios.nombre AS cliente_nombre
    FROM alquileres
    LEFT JOIN usuarios ON alquileres.user_id = usuarios.id
    WHERE negocio_id = '$negocio_id'
    ORDER BY alquileres.id DESC 
    LIMIT 10";
    $list_ult_alquileres = $conn->query($sql_ult_alq);

    $sql_alquileres_por_mes = "
    SELECT 
        MONTH(fecha_inicio) AS mes, 
        COUNT(*) AS cantidad 
    FROM alquileres 
    WHERE negocio_id = '$negocio_id'
    GROUP BY mes
    ORDER BY mes
";

        $result_alquileres_por_mes = $conn->query($sql_alquileres_por_mes);

        $datos_alquileres = array_fill(1, 12, 0); // Inicializa de enero (1) a diciembre (12) con 0

        while ($row = $result_alquileres_por_mes->fetch_assoc()) {
            $mes = (int)$row['mes'];
            $datos_alquileres[$mes] = (int)$row['cantidad'];
        }

}
$datos_json = json_encode(array_values($datos_alquileres));

// Cerrar la conexión
$conn->close();

?>

<h1>Bienvenido al Dashboard</h1>
            <div class="row">
                <!-- Tarjeta con datos estadísticos -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header">
                            <h5>Total Alquileres</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text" id="total-alquileres"><?= $total_alquileres ?></p> <!-- Total dinámico -->
                        </div>
                    </div>
                </div>
<?php if(isset($_SESSION['negocio']) && $_SESSION['negocio']){ ?>
   
    <div class="col-md-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header">
                            <h5>Lavadoras</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text" id="total-usuarios"><?= $lavadoras ?></p> <!-- Total dinámico -->
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header">
                            <h5>Lavadoras disponibles</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text" id="total-negocios"><?= $total_lavadoras_disponibles ?></p> <!-- Total dinámico -->
                        </div>
                    </div>
                </div>
            </div>
            <?php 

            }else{
            ?>

<div class="col-md-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header">
                            <h5>Total Usuarios</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text" id="total-usuarios"><?= $total_usuarios ?></p> <!-- Total dinámico -->
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header">
                            <h5>Total Negocios</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text" id="total-negocios"><?= $total_negocios ?></p> <!-- Total dinámico -->
                        </div>
                    </div>
                </div>
            </div>

            <?php } ?>

            <!-- Gráfico de estadísticas -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <h5>Gráfico de Alquileres</h5>
                        </div>
                        <div class="card-body">
                            <div id="alquileres-chart"></div>
                        </div>
                    </div>
                </div>
            </div>

            </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Usar Highcharts para mostrar gráficos

        // Aquí colocarás los datos dinámicos desde la base de datos

    Highcharts.chart('alquileres-chart', {
        chart: {
            type: 'column'
        },
        title: {
            text: 'Alquileres por Mes'
        },
        xAxis: {
            categories: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
        },
        yAxis: {
            title: {
                text: 'Número de Alquileres'
            }
        },
        series: [{
            name: 'Alquileres',
            data: <?= $datos_json ?>
        }]
    });
</script>
    </script>