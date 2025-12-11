<?php
// ---- Conexión a la base de datos ----
// Asegúrate de tener $conn = new mysqli(...) antes de esto.
// require 'db_connect.php'; // <- tu conexión aquí

// ---- Paginación ----
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// ---- Filtro - SANITIZADO ----
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// ---- Query usuarios (si sesión negocio está, filtrar por ese negocio) ----
if (isset($_SESSION['negocio']) && $_SESSION['negocio']) {
    $negocio_sesion = (int) $_SESSION['negocio'];  // Cast a entero
    $sql = "SELECT * FROM usuarios WHERE rol_id IN (3,4) AND conductor_negocio = '$negocio_sesion' AND (nombre LIKE '%$search%' OR correo LIKE '%$search%') LIMIT $limit OFFSET $offset";
    $sql_count = "SELECT COUNT(*) as total FROM usuarios WHERE rol_id IN (3,4) AND conductor_negocio = '$negocio_sesion' AND (nombre LIKE '%$search%' OR correo LIKE '%$search%')";
} else {
    $sql = "SELECT * FROM usuarios WHERE rol_id IN (3,4) AND (nombre LIKE '%$search%' OR correo LIKE '%$search%') LIMIT $limit OFFSET $offset";
    $sql_count = "SELECT COUNT(*) as total FROM usuarios WHERE rol_id IN (3,4) AND (nombre LIKE '%$search%' OR correo LIKE '%$search%')";
}

$result = $conn->query($sql);
$count_result = $conn->query($sql_count);
$total_users = ($count_result && $count_result->num_rows) ? (int)$count_result->fetch_assoc()['total'] : 0;
$total_pages = ($total_users > 0) ? ceil($total_users / $limit) : 1;

// ---- Si es negocio, obtener sus proveedores; si no (admin), obtener todos los negocios y todos los proveedores ----
$list_proveedores = null;
$list_negocios = null;
$proveedores_all = [];

if (isset($_SESSION['negocio']) && $_SESSION['negocio']) {
    $negocio_id = (int)$_SESSION['negocio'];
    $stmt = $conn->prepare("SELECT * FROM proveedores WHERE estado = 'activo' AND negocio_id = ?");
    $stmt->bind_param("i", $negocio_id);
    $stmt->execute();
    $list_proveedores = $stmt->get_result();
    // Obtener usuario_id del negocio para tomar_recaudo (como tenías)
    $stmt2 = $conn->prepare("SELECT usuario_id FROM negocios WHERE id = ? LIMIT 1");
    $stmt2->bind_param("i", $negocio_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $usuario_id = ($res2 && $res2->num_rows) ? $res2->fetch_assoc()['usuario_id'] : null;
} else {
    // admin: cargar todos los negocios activos
    $q2 = "SELECT id, nombre FROM negocios WHERE status = 1";
    $list_negocios = $conn->query($q2);

    // cargar todos los proveedores activos (para filtrar por JS)
    $q3 = "SELECT * FROM proveedores WHERE estado = 'activo'";
    $resProv = $conn->query($q3);
    if ($resProv) {
        while ($r = $resProv->fetch_assoc()) {
            $proveedores_all[] = $r;
        }
    }
}
?>
<!---- HTML ---->
<h1>Usuarios de la App</h1>

<!-- Buscador -->
<form action="home.php?m=up" method="GET" class="mb-3">
    <input type="hidden" name="m" value="up">
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o correo" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary">Buscar</button>
    </div>
</form>

<!-- Botón Crear Usuario (visible para admin y negocio) -->
<a href="crear_usuario.php" class="btn btn-primary mb-3">Crear Nuevo usuario</a>

<!-- Tabla -->
<div class="card shadow mb-4">
    <div class="card-header"><h5>Lista de Usuarios</h5></div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr><th>ID</th><th>Nombre</th><th>Correo Electrónico</th><th>Monedero</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id'];?></td>
                            <td><?php echo htmlspecialchars($row['nombre']);?></td>
                            <td><?php echo htmlspecialchars($row['correo']);?></td>
                            <td>$<?php echo number_format($row['monedero'],0,',','.');?></td>
                            <td><?php echo $row['status'] == 1 ? 'Activo' : 'Inactivo';?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editarUsuario(<?php echo $row['id']; ?>)">Editar</button>
                                <?php if ($row['status'] == 1): ?>
                                    <button class="btn btn-danger btn-sm" onclick="cambiarStatus(<?php echo $row['id']; ?>, 0)">Bloquear</button>
                                <?php else: ?>
                                    <button class="btn btn-success btn-sm" onclick="cambiarStatus(<?php echo $row['id']; ?>, 1)">Activar</button>
                                <?php endif; ?>
                                <button class="btn btn-success btn-sm" onclick="$('#modalCrearPago').modal('show'); recargar(<?php echo $row['id']; ?>);">Recargar</button>

                                <?php if(isset($_SESSION['negocio']) && $row['monedero'] > 0 && $row['rol_id'] == 3): ?>
                                    <button class="btn btn-success btn-sm" onclick="tomar_recaudo(<?php echo $row['id']; ?>, <?php echo $row['monedero']; ?>, <?php echo isset($usuario_id) ? $usuario_id : 'null'; ?>);">Tomar Recaudo</button>
                                <?php endif; ?>
                                    <!-- 🔹 Nuevo botón -->
                                 <button class="btn btn-secondary btn-sm" onclick="resetPassword(<?php echo $row['id']; ?>, '<?php echo $row['correo']; ?>')">Resetear Contraseña</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No hay usuarios</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <nav>
            <ul class="pagination">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Anterior</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formEditarUsuario">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Editar Usuario</h5><button type="button" class="close" data-bs-dismiss="modal">&times;</button></div>
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
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Guardar cambios</button></div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Crear Usuario (visible para admin y negocio) -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="formCrearNegocio" class="w-100">
      <div class="modal-content shadow rounded-4 border-0">
        <div class="modal-header bg-primary text-white rounded-top-4">
          <h5 class="modal-title" id="modalCrearLabel">Crear Nuevo Usuario</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <h6 class="mb-4 text-secondary fw-bold border-bottom pb-2">Datos del Usuario</h6>

          <?php if (isset($_SESSION['negocio']) && $_SESSION['negocio']): ?>
            <!-- Usuario logueado como negocio: fijo el negocio -->
            <input type="hidden" name="id" id="id" value="<?php echo (int)$_SESSION['negocio']; ?>">
            <div class="col-md-6 mb-3">
              <label for="editar_proveedor" class="form-label">Proveedor</label>
              <select class="form-select" name="proveedor" id="editar_proveedor" required>
                <?php if ($list_proveedores && $list_proveedores->num_rows): ?>
                  <?php while ($p = $list_proveedores->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                  <?php endwhile; ?>
                <?php else: ?>
                    <option value="">Sin proveedores</option>
                <?php endif; ?>
              </select>
            </div>
          <?php else: ?>
            <!-- Administrador: elegir negocio -->
            <div class="row g-3">
              <div class="col-md-6 mb-3">
                <label for="negocio_select" class="form-label">Negocio</label>
                <select class="form-select" name="id" id="negocio_select" required>
                  <option value="">Seleccione un negocio</option>
                  <?php if ($list_negocios && $list_negocios->num_rows): ?>
                    <?php while ($n = $list_negocios->fetch_assoc()): ?>
                      <option value="<?php echo (int)$n['id']; ?>"><?php echo htmlspecialchars($n['nombre']); ?></option>
                    <?php endwhile; ?>
                  <?php endif; ?>
                </select>
              </div>

              <div class="col-md-6 mb-3">
                <label for="editar_proveedor" class="form-label">Proveedor</label>
                <select class="form-select" name="proveedor" id="editar_proveedor" required>
                  <option value="">Seleccione un negocio primero</option>
                </select>
              </div>
            </div>
          <?php endif; ?>

          <div class="row g-3 mt-2">
            <div class="col-md-6">
              <label for="editar_usuario_nombre" class="form-label">Nombre</label>
              <input type="text" class="form-control" name="usuario_nombre" id="editar_usuario_nombre" required>
            </div>
            <div class="col-md-6">
              <label for="editar_usuario_apellido" class="form-label">Apellido</label>
              <input type="text" class="form-control" name="usuario_apellido" id="editar_usuario_apellido" required>
            </div>
            <div class="col-md-6">
              <label for="editar_usuario_telefono" class="form-label">Teléfono</label>
              <input type="text" class="form-control" name="usuario_telefono" id="editar_usuario_telefono" required>
            </div>
            <div class="col-md-6">
              <label for="editar_usuario_correo" class="form-label">Correo</label>
              <input type="email" class="form-control" name="usuario_correo" id="editar_usuario_correo" required>
            </div>
            <div class="col-12">
              <label for="editar_usuario_usuario" class="form-label">Usuario</label>
              <input type="text" class="form-control" name="usuario_usuario" id="editar_usuario_usuario" required>
            </div>
          </div>

        </div>

        <div class="modal-footer bg-light rounded-bottom-4">
          <button type="submit" class="btn btn-success w-100 py-2 fw-bold">Crear Usuario</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Crear Pago (igual que antes) -->
<div class="modal fade" id="modalCrearPago" tabindex="-1">
  <div class="modal-dialog">
    <form id="formCrearPago">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Registrar Nuevo Pago</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <?php if (isset($_SESSION['negocio'])): ?>
            <input type="hidden" name="negocio_id" value="<?= (int)$_SESSION['negocio']; ?>">
          <?php endif; ?>
          <div class="mb-3">
            <label>Referencia</label>
            <input type="hidden" name="usuario_id" id="id_us">
            <input type="text" name="referencia" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Valor</label>
            <input type="number" name="valor" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Método de Pago</label>
            <select name="metodo_pago" class="form-select" required>
              <option value="Nequi">Nequi</option>
              <option value="Daviplata">Daviplata</option>
              <option value="Bancolombia">Bancolombia</option>
              <option value="Efectivo">Efectivo</option>
            </select>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary" type="submit">Guardar</button></div>
      </div>
    </form>
  </div>
</div>

<!-- Cargar array JS de proveedores (solo para admin) -->
<script>
<?php if (!isset($_SESSION['negocio']) || !$_SESSION['negocio']): ?>
    const proveedoresAll = <?php echo json_encode($proveedores_all, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
<?php else: ?>
    const proveedoresAll = [];
<?php endif; ?>
</script>

<script>
function recargar(id) { $("#id_us").val(id); }

function resetPassword(id, correo) {
    if (!confirm('¿Seguro que deseas resetear la contraseña de este usuario?')) return;

    $.post('../controllers/controller_reset_password.php', 
        { id: id, correo: correo, action: 'reset_password' }, 
        function(response) {
            try {
                const data = JSON.parse(response);
                alert(data.message);
            } catch(e) {
                console.error(response);
                alert('Error al procesar la solicitud.');
            }
        }
    );
}


function cambiarStatus(id, nuevoStatus) {
    $.post('../controllers/usuario_controller.php', { action: 'cambiar_status', id: id, status: nuevoStatus }, function(response){ location.reload(); });
}
function tomar_recaudo(id, monedero, negocio) {
    $.post('../controllers/usuario_controller.php', { action: 'tomar_recaudo', id: id, monedero: monedero, negocio: negocio }, function(response){ location.reload(); });
}

function editarUsuario(id) {
    $.get('../controllers/usuario_controller.php', { action: 'obtener_usuario', id: id }, function(data) {
        try {
            const usuario = JSON.parse(data);
            $('#editar_id').val(usuario.id);
            $('#editar_nombre').val(usuario.nombre);
            $('#editar_correo').val(usuario.correo);
            $('#modalEditar').modal('show');
        } catch(e) {
            alert('Error al obtener usuario');
            console.error(e, data);
        }
    });
}

// Enviar formularios
$('#formCrearPago').submit(function(e){
    e.preventDefault();
    $.post('../controllers/pago_controller.php', $(this).serialize() + '&action=crear_pago', function(response){
        $('#modalCrearPago').modal('hide');
        location.reload();
    });
});

$('#formEditarUsuario').submit(function(e){
    e.preventDefault();
    $.post('../controllers/usuario_controller.php', $(this).serialize() + '&action=editar_usuario', function(response){
        $('#modalEditar').modal('hide');
        location.reload();
    });
});

// Abrir modal Crear Usuario desde el enlace
$('a[href="crear_usuario.php"]').click(function(e){
    e.preventDefault();
    $('#modalCrear').modal('show');
});

// Enviar crear usuario
$('#formCrearNegocio').submit(function(e){
    e.preventDefault();
    // Validación: si admin, asegurarse que negocio y proveedor estén seleccionados
    var negocioVal = $('#negocio_select').length ? $('#negocio_select').val() : $('#id').val();
    var proveedorVal = $('#editar_proveedor').val();
    if (!negocioVal) {
        alert('Seleccione un negocio');
        return;
    }
    if (!proveedorVal) {
        alert('Seleccione un proveedor');
        return;
    }

    $.post('../controllers/usuario_controller.php', $(this).serialize() + '&action=crear_usuario_app', function(response){
        $('#modalCrear').modal('hide');
        location.reload();
    });
});

// Si es admin: cuando cambia el select negocio, filtrar proveedores
$('#negocio_select').on('change', function(){
    var negocioId = $(this).val();
    var $prov = $('#editar_proveedor');
    $prov.empty();
    $prov.append($('<option>').val('').text('Cargando...'));
    if (!negocioId) {
        $prov.empty().append($('<option>').val('').text('Seleccione un negocio primero'));
        return;
    }
    // Filtrar proveedoresAll por negocio_id
    var filtered = proveedoresAll.filter(function(p){ return String(p.negocio_id) === String(negocioId); });
    $prov.empty();
    if (filtered.length) {
        filtered.forEach(function(p){
            $prov.append($('<option>').val(p.id).text(p.nombre));
        });
    } else {
        $prov.append($('<option>').val('').text('Sin proveedores para este negocio'));
    }
});
</script>
