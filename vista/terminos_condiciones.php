<?php
// Conexión a base de datos (ajusta con tus datos)


// Consulta
$sql = "SELECT * FROM terminos_condiciones LIMIT 1";
$result = $conn->query($sql);

$terminos = "";
$terminos_uso = "";
$terminos_delivery = "";
$terminos_uso_delivery = "";

if ($result && $row = $result->fetch_assoc()) {
    $terminos = $row['terminos'] ?? "";
    $terminos_uso = $row['terminos_uso'] ?? "";
    $terminos_delivery = $row['terminos_delivery'] ?? "";
    $terminos_uso_delivery = $row['terminos_uso_delivery'] ?? "";
}
?>

<div id="contenedorFormulario" class="mt-4">
  <form id="formTerminos">
    <div class="mb-3">
      <label for="terminos" class="form-label">Términos y Condiciones</label>
      <textarea class="form-control" id="terminos" name="terminos" rows="6" required><?= htmlspecialchars($terminos) ?></textarea>
    </div>

    <div class="mb-3">
      <label for="terminos_uso" class="form-label">Términos de Uso</label>
      <textarea class="form-control" id="terminos_uso" name="terminos_uso" rows="6" required><?= htmlspecialchars($terminos_uso) ?></textarea>
    </div>

    <div class="mb-3">
      <label for="terminos_delivery" class="form-label">Términos de Delivery</label>
      <textarea class="form-control" id="terminos_delivery" name="terminos_delivery" rows="6" required><?= htmlspecialchars($terminos_delivery) ?></textarea>
    </div>

    <div class="mb-3">
      <label for="terminos_uso_delivery" class="form-label">Términos de Uso de Delivery</label>
      <textarea class="form-control" id="terminos_uso_delivery" name="terminos_uso_delivery" rows="6" required><?= htmlspecialchars($terminos_uso_delivery) ?></textarea>
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
  </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#formTerminos').submit(function(e) {
  e.preventDefault();
  $.post('../controllers/terminos_controller.php', $(this).serialize() + '&action=guardar_config', function(response) {
    alert("Términos actualizados correctamente");
    location.reload();
  }).fail(function() {
    alert("Error al guardar los términos.");
  });
});
</script>
