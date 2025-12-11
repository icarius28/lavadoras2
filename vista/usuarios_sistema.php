<?php
// Conectar a la base de datos

// Paginación
$limit = 10;  // Número de usuarios por página
$page = isset($_GET['page']) ? $_GET['page'] : 1;  // Página actual
$offset = ($page - 1) * $limit;

// Filtro por nombre o correo - SANITIZADO
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Obtener los usuarios filtrados
$sql = "SELECT * FROM usuarios WHERE rol_id in (1,2) AND (nombre LIKE '%$search%' OR correo LIKE '%$search%') LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Contar el total de usuarios para la paginación
$sql_count = "SELECT COUNT(*) as total FROM usuarios WHERE rol_id in (1,2) AND ( nombre LIKE '%$search%' OR correo LIKE '%$search%')";
$count_result = $conn->query($sql_count);
$total_users = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);


?>

<h1>Usuarios de la App</h1>
            
            <!-- Filtro de búsqueda -->
            <form action="home.php?m=us" method="GET" class="mb-3">
                <input type="hidden" name="m" value="us">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o correo" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>

            <!-- Tabla de usuarios -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5>Lista de Usuarios</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo Electrónico</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['nombre']; ?></td>
                                <td><?php echo $row['correo']; ?></td>
                                <td>
                                    <?php echo $row['status'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                </td>
                                <td>
                                    <!-- Botones para Editar y Bloquear -->
                                    <button class="btn btn-warning btn-sm" onclick="editarUsuario(<?php echo $row['id']; ?>)">Editar</button>
                                    <?php if ($row['status'] == 1) { ?>
                                        <button class="btn btn-danger btn-sm" onclick="cambiarStatus(<?php echo $row['id']; ?>, 0)">Bloquear</button>
                                    <?php } else { ?>
                                        <button class="btn btn-success btn-sm" onclick="cambiarStatus(<?php echo $row['id']; ?>, 1)">Activar</button>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <!-- Paginación -->
                    <nav>
                        <ul class="pagination">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?m=us&page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Anterior</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?m=us&page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php } ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?m=us&page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Modal de edición -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formEditarUsuario">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuario</h5>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="editar_id">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" id="editar_nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Correo</label>
                    <input type="email" name="correo" id="editar_correo" class="form-control" required>
                </div>
                <!-- Puedes añadir más campos si lo deseas -->
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </div>
    </form>
  </div>
</div>
<script>

function cambiarStatus(id, nuevoStatus) {
    $.post('../controllers/usuario_controller.php', {
        action: 'cambiar_status',
        id: id,
        status: nuevoStatus
    }, function(response) {
        location.reload();
    });
}

function editarUsuario(id) {
    $.get('../controllers/usuario_controller.php', { action: 'obtener_usuario', id: id }, function(data) {
        const usuario = JSON.parse(data);
        $('#editar_id').val(usuario.id);
        $('#editar_nombre').val(usuario.nombre);
        $('#editar_correo').val(usuario.correo);
        $('#modalEditar').modal('show');
    });
}

$('#formEditarUsuario').submit(function(e) {
    e.preventDefault();
    $.post('../controllers/usuario_controller.php', $(this).serialize() + '&action=editar_usuario', function(response) {
        $('#modalEditar').modal('hide');
        location.reload();
    });
});
</script>


