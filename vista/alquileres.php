<?php

$limit = 10;  // Número de usuarios por página
$page = isset($_GET['page']) ? $_GET['page'] : 1;  // Página actual
$offset = ($page - 1) * $limit;

// Filtro por nombre o correo - SANITIZADO
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Obtener los usuarios filtrados
$sql = "SELECT alquileres.*, (select usuarios.nombre from usuarios where  alquileres.user_id = usuarios.id) as nombre_cliente FROM alquileres WHERE  tiempo_alquiler LIKE '%$search%' OR fecha_inicio LIKE '%$search%' OR fecha_fin LIKE '%$search%' LIMIT $limit OFFSET $offset";
if(isset($_SESSION['negocio']) && $_SESSION['negocio']){
    $negocio_id = (int) $_SESSION['negocio'];
    $sql = "SELECT alquileres.*, (select usuarios.nombre from usuarios where  alquileres.user_id = usuarios.id) as nombre_cliente FROM alquileres WHERE negocio_id = '$negocio_id' AND (tiempo_alquiler LIKE '%$search%' OR fecha_inicio LIKE '%$search%' OR fecha_fin LIKE '%$search%') LIMIT $limit OFFSET $offset";
}
$result = $conn->query($sql);

// Contar el total de usuarios para la paginación
$sql_count = "SELECT COUNT(*) as total FROM alquileres WHERE tiempo_alquiler  LIKE '%$search%' OR fecha_inicio LIKE '%$search%' OR fecha_fin LIKE '%$search%'";
if(isset($_SESSION['negocio']) && $_SESSION['negocio']){
    $negocio_id = (int) $_SESSION['negocio'];
    $sql_count = "SELECT COUNT(*) as total FROM alquileres WHERE negocio_id = '$negocio_id' AND (tiempo_alquiler  LIKE '%$search%' OR fecha_inicio LIKE '%$search%' OR fecha_fin LIKE '%$search%')";
}
$count_result = $conn->query($sql_count);
$total_users = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);



?>
<h1>Listado de Alquileres</h1>
<!-- Filtro de búsqueda -->
<form action="home.php?m=a" method="GET" class="mb-3">
                <input type="hidden" name="m" value="a">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por fecha" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>
            
            <!-- Tabla de alquileres -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5>Lista de Alquileres</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre del Cliente</th>
                                <th>Fecha de Alquiler</th>
                                <th>Fecha de Devolución</th>
                                <th>Valor servicio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['nombre_cliente']; ?></td>
                                <td><?= $row['start_time']?: 'N/I'; ?></td>
                                <td><?= $row['fecha_fin'] ?: 'N/I'; ?></td>
                                <td>$<?= $row['tiempo_alquiler'] * $row['valor_servicio']; ?></td>
                                <td>
                                    <?php
                                    // Mostrar el estado con color dependiendo del valor
                                    switch ($row['status_servicio']) {
                                        case 1:
                                            echo '<span class="badge bg-success">En proceso de entrega</span>';
                                            break;
                                        case 2:
                                            echo '<span class="badge bg-warning">Entregado</span>';
                                            break;
                                        case 3:
                                            echo '<span class="badge bg-secondary">proceso de devolucion</span>';
                                            break;
                                        case 4:
                                                echo '<span class="badge bg-info">Completado</span>';
                                            break;
                                        case 5:
                                            echo '<span class="badge bg-info">Cancelado</span>';
                                        break;
                                        default:
                                            echo '<span class="badge bg-danger">Cancelado</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td>
                                    <!-- Botones para ver detalles o editar 
                                    <a href="ver_alquiler.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Ver</a>
                                    <a href="editar_alquiler.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>-->
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <nav>
                        <ul class="pagination">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?m=a&page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Anterior</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?m=a&page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php } ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?m=a&page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>