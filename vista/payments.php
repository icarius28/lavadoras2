<?php
$limit = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where = "1=1";
if (isset($_SESSION['negocio']) && $_SESSION['negocio']) {
    $where = "negocio_id = '{$_SESSION['negocio']}'";
}

$sql = "SELECT * FROM pagos WHERE $where AND referencia LIKE '%$search%' LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$sql_count = "SELECT COUNT(*) as total FROM pagos WHERE $where AND referencia LIKE '%$search%'";
$total_result = $conn->query($sql_count);
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// listado de usuarios tipo cliente
$usuarios  = "SELECT * FROM usuarios where rol_id = 4";
$list_usuarios = $conn->query($usuarios);


?>
<h1>Listado de Pagos</h1>
<form action="home.php?m=pagos" method="GET" class="mb-3">
    <input type="hidden" name="m" value="pagos">
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Buscar por referencia" value="<?= htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary">Buscar</button>
    </div>
</form>

<a href="#" class="btn btn-primary mb-3" onclick="$('#modalCrearPago').modal('show')">Registrar Pago</a>

<div class="card shadow mb-4">
    <div class="card-header"><h5>Lista de Pagos</h5></div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Referencia</th>
                    <th>Método</th>
                    <th>Valor</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= htmlspecialchars($row['referencia']); ?></td>
                    <td><?= $row['metodo_pago']; ?></td>
                    <td>$<?= number_format($row['valor'], 2); ?></td>
                    <td><?= $row['fecha']; ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editarPago(<?= $row['id']; ?>)">Editar</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <nav>
            <ul class="pagination">
                <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?m=pagos&page=<?= $page - 1; ?>&search=<?= htmlspecialchars($search); ?>">Anterior</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?m=pagos&page=<?= $i; ?>&search=<?= htmlspecialchars($search); ?>"><?= $i; ?></a>
                </li>
                <?php } ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?m=pagos&page=<?= $page + 1; ?>&search=<?= htmlspecialchars($search); ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<!-- Modal Crear Pago -->
<div class="modal fade" id="modalCrearPago" tabindex="-1">
  <div class="modal-dialog">
    <form id="formCrearPago">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Registrar Nuevo Pago</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php if (isset($_SESSION['negocio'])) { ?>
            <input type="hidden" name="negocio_id" value="<?= $_SESSION['negocio']; ?>">
          <?php } ?>
          <div class="mb-3">
            <label>Referencia</label>
            <input type="text" name="referencia" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Valor</label>
            <input type="number" name="valor" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Cliete</label>
            <select name="usuario_id" class="form-select" required>
              <option value="" disabled selected>Seleccione un cliente</option>
              <?php foreach ($list_usuarios as $usuario): ?>
                <option value="<?= $usuario['id'] ?>"><?= $usuario['nombre'] . ' ' . $usuario['apellido'] ?></option>
              <?php endforeach; ?>
            </select>
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
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Editar Pago -->
<div class="modal fade" id="modalEditarPago" tabindex="-1">
  <div class="modal-dialog">
    <form id="formEditarPago">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Pago</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          <div class="mb-3">
            <label>Referencia</label>
            <input type="text" name="referencia" id="edit_referencia" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Valor</label>
            <input type="number" name="valor" id="edit_valor" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Método de Pago</label>
            <select name="metodo_pago" id="edit_metodo_pago" class="form-select" required>
              <option value="Nequi">Nequi</option>
              <option value="Daviplata">Daviplata</option>
              <option value="Bancolombia">Bancolombia</option>
              <option value="Efectivo">Efectivo</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">Actualizar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
function editarPago(id) {
    showLoading('Cargando pago...');
    $.get('../controllers/pago_controller.php', { action: 'obtener_pago', id: id }, function(data) {
        Swal.close();
        let pago;
        try {
            pago = JSON.parse(data);
        } catch (e) {
            console.error("Error parsing JSON:", data);
            Swal.fire("Error", "No se pudieron cargar los datos", "error");
            return;
        }
        
        $('#edit_id').val(pago.id);
        $('#edit_referencia').val(pago.referencia);
        $('#edit_valor').val(pago.valor); // Assuming controller returns 'valor'
        $('#edit_metodo_pago').val(pago.metodo_pago);
        $('#modalEditarPago').modal('show');
    }).fail(function() {
        Swal.close();
        Swal.fire("Error", "Error de conexión", "error");
    });
}

$('#formCrearPago').submit(function(e) {
    e.preventDefault();
    const referencia = $('#formCrearPago input[name="referencia"]').val();
    const valor = $('#formCrearPago input[name="valor"]').val();
    const usuario = $('#formCrearPago select[name="usuario_id"]').val();
    const metodo = $('#formCrearPago select[name="metodo_pago"]').val();

    if (!validateNotEmpty(referencia)) { showErrorAlert('Referencia obligatoria'); return; }
    if (!validateNotEmpty(valor)) { showErrorAlert('Valor obligatorio'); return; }
    if (!validateNumeric(valor)) { showErrorAlert('El valor debe ser numérico'); return; }
    if (!validateNotEmpty(usuario)) { showErrorAlert('Seleccione un cliente'); return; }
    if (!validateNotEmpty(metodo)) { showErrorAlert('Seleccione un método de pago'); return; }

    showLoading();
    $.post('../controllers/pago_controller.php', $(this).serialize() + '&action=crear_pago', function(response) {
        Swal.fire({
            icon: 'success',
            title: 'Pago creado',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            $('#modalCrearPago').modal('hide');
            location.reload();
        });
    });
});

$('#formEditarPago').submit(function(e) {
    e.preventDefault();
    const referencia = $('#edit_referencia').val();
    const valor = $('#edit_valor').val();
    const metodo = $('#edit_metodo_pago').val();

    if (!validateNotEmpty(referencia)) { showErrorAlert('Referencia obligatoria'); return; }
    if (!validateNotEmpty(valor)) { showErrorAlert('Valor obligatorio'); return; }
    if (!validateNumeric(valor)) { showErrorAlert('El valor debe ser numérico'); return; }
    if (!validateNotEmpty(metodo)) { showErrorAlert('Seleccione un método de pago'); return; }

    showLoading();
    $.post('../controllers/pago_controller.php', $(this).serialize() + '&action=editar_pago', function(response) {
        Swal.fire({
            icon: 'success',
            title: 'Pago actualizado',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            $('#modalEditarPago').modal('hide');
            location.reload();
        });
    });
});
</script>
