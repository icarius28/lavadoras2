<?php

$lavadoras = [];
$km = '';
$telefono = '';
$id_negocio = $_SESSION['negocio'] ?? null;


if ($id_negocio) {
    // Obtener precios por tipo de lavadora y servicio
    $sql = "SELECT * FROM precios_lavado WHERE id_negocio = '$id_negocio'";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $lavadoras[$row['tipo_lavadora']][$row['tipo_servicio']] = $row['precio'];
    }

    // Obtener datos adicionales
    $sql_config = "SELECT * FROM config WHERE id_negocio = '$id_negocio'";
    $result_config = $conn->query($sql_config);
    if ($row = $result_config->fetch_assoc()) {
        $km = $row['km'] ?? '';
        $telefono = $row['telefono'] ?? '';
    }
}

$tipos_lavadora = [
    'Manual doble tina sin bomba',
    'Manual doble tina con bomba',
    'Automática de 18 libras',
    'Automática de 24 libras'
];
$tipos_servicio = ['normal', '24horas', 'nocturno'];
?>

<!-- Formulario -->
<div id="contenedorFormulario" class="mt-4">
  <form id="formPrecioLavadoras">
    <?php foreach ($tipos_lavadora as $lavadora): ?>
      <div class="mb-3 border p-3 rounded">
        <h5><?= $lavadora ?></h5>
        <?php foreach ($tipos_servicio as $servicio): 
          $valor = $lavadoras[$lavadora][$servicio] ?? '';
        ?>
          <div class="mb-2">
            <label class="form-label"><?= ucfirst($servicio) ?></label>
            <input 
              type="number" step="0.01" class="form-control"
              name='precios[<?= $lavadora ?>][<?= $servicio ?>]'
              value='<?= htmlspecialchars($valor) ?>' required>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-success">Guardar</button>
  </form>
</div>

<script>
$('#formPrecioLavadoras').submit(function(e) {
    e.preventDefault();
    showLoading();
    // Validar campos vacíos o inconsistentes si es necesario (aquí ya tienen 'required' y son numéricos)
    
    $.post('../controllers/precio_controller.php', $(this).serialize() + '&action=guardar_precios_lavadoras', function(response) {
        Swal.fire({
            icon: 'success',
            title: 'Guardado correctamente',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            location.reload();
        });
    }).fail(function() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar los precios',
        });
    });
});
</script>
