<?php
// Conexión a base de datos aquí

// Obtener negocios para el select
$negocios = $conn->query("SELECT id, nombre FROM negocios WHERE status = 1");
?>

<h3>Generar Reporte de Alquileres</h3>
<form action="generar_reporte_pdf.php" method="GET" target="_blank">
    <div class="row mb-3">
        <div class="col-md-4">
            <label>Desde:</label>
            <input type="date" name="fecha_inicio" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label>Hasta:</label>
            <input type="date" name="fecha_fin" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label>Negocio:</label>
            <select name="negocio_id" class="form-control">
                <option value="">-- Todos --</option>
                <?php while ($row = $negocios->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nombre'] ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-success">Generar PDF</button>
</form>
