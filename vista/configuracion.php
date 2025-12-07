<?php

$sql = "SELECT * FROM config_general";
if(isset($_SESSION['negocio']) && $_SESSION['negocio']){
    $where = " id_negocio = '{$_SESSION['negocio']}'  ";
    $sql = "SELECT * FROM config WHERE $where";
}
$result = $conn->query($sql);
if(isset($_SESSION['negocio']) && $_SESSION['negocio']){
  $tarifa = "";
  $telefono = "";

  // Obtener la fila una sola vez
  $row = $result->fetch_assoc();

  if ($row) {
    if (isset($row['tarifa'])) {
      $tarifa = $row['tarifa'];
    }
    if (isset($row['telefono']) && $row['telefono'] != null) {
      $telefono = $row['telefono'];
    }
  }
}else{
  $row = $result->fetch_assoc();
  if ($row) {
    if (isset($row['km'])) {
      $km = $row['km'];
    }
    if (isset($row['global_tarifa'])) {
      $tarifa = $row['global_tarifa'];
    }
    
    if (isset($row['valor_minimo'])) {
      $valor_minimo = $row['valor_minimo'];
    }
    if (isset($row['porcentaje'])) {
      $porcentaje = $row['porcentaje'];
    }

    // Nuevos campos
    if (isset($row['max_intentos_cancelacion'])) {
        $max_intentos_cancelacion = $row['max_intentos_cancelacion'];
    }
    if (isset($row['multa_cliente'])) {
        $multa_cliente = $row['multa_cliente'];
    }
    if (isset($row['multa_domiciliario'])) {
        $multa_domiciliario = $row['multa_domiciliario'];
    }

    // Métodos de pago
    if (isset($row['payu_habilitado'])) {
        $payu_habilitado = $row['payu_habilitado'];
    }
    if (isset($row['payu_cuenta'])) {
        $payu_cuenta = $row['payu_cuenta'];
    }
    
    if (isset($row['payu_checkout_url'])) {
        $payu_checkout_url = $row['payu_checkout_url'];
    }
    if (isset($row['payu_merchant_id'])) {
        $payu_merchant_id = $row['payu_merchant_id'];
    }
    if (isset($row['payu_account_id'])) {
        $payu_account_id = $row['payu_account_id'];
    }
    if (isset($row['payu_response_url'])) {
        $payu_response_url = $row['payu_response_url'];
    }
    if (isset($row['payu_confirmation_url'])) {
        $payu_confirmation_url = $row['payu_confirmation_url'];
    }
    if (isset($row['email_pay'])) {
        $email_pay = $row['email_pay'];
    }

    if (isset($row['bancolombia_habilitado'])) {
        $bancolombia_habilitado = $row['bancolombia_habilitado'];
    }
    if (isset($row['bancolombia_cuenta'])) {
        $bancolombia_cuenta = $row['bancolombia_cuenta'];
    }

    if (isset($row['nequi_habilitado'])) {
        $nequi_habilitado = $row['nequi_habilitado'];
    }
    if (isset($row['nequi_cuenta'])) {
        $nequi_cuenta = $row['nequi_cuenta'];
    }

    if (isset($row['daviplata_habilitado'])) {
        $daviplata_habilitado = $row['daviplata_habilitado'];
    }
    if (isset($row['daviplata_cuenta'])) {
        $daviplata_cuenta = $row['daviplata_cuenta'];
    }

    // Canales de comunicación
    if (isset($row['whatsapp_contacto'])) {
        $whatsapp_contacto = $row['whatsapp_contacto'];
    }
    if (isset($row['correo_contacto'])) {
        $correo_contacto = $row['correo_contacto'];
    }
    
  }
}
?>

<!-- Contenedor del formulario -->
<div id="contenedorFormulario" class="mt-4" >
  <form id="formCrearNegocio">
  <?php
          if(isset($_SESSION['negocio']) && $_SESSION['negocio']){
            
                ?>
           <input type="hidden" name="id" id="id" value="<?php echo $_SESSION['negocio']; ?>" class="form-control" required>
        <div class="mb-3">
          <label for="tarifa" class="form-label">Tarifa Hora</label>
          <input type="number" step="0.01" class="form-control" id="tarifa" name="tarifa" value="<?= $tarifa; ?>" required>
        </div>
        <div class="mb-3">
          <label for="telefono" class="form-label">Teléfono</label>
          <input type="text"  class="form-control" id="telefono" name="telefono" value="<?= $telefono; ?>" required>
        </div>
    <?php }else{ ?>
    
      <div class="mb-3">
    <label for="logo_negocio" class="form-label">Banner</label>
    <input type="file" class="form-control" id="logo_negocio" name="logo_negocio" accept="image/png, image/jpeg, image/jpg, image/gif">
  </div>
    
     <div class="mb-3">
          <label for="km" class="form-label">Rango de ubicación KM</label>
          <input type="number" step="0.01" class="form-control" id="km" name="km" value="<?= $km  ; ?>" required>
        </div>

      <div class="mb-3">
          <label for="km" class="form-label">Rango de ubicación KM</label>
          <input type="number" step="0.01" class="form-control" id="km" name="km" value="<?= $km  ; ?>" required>
        </div>
        <div class="mb-3">
          <label for="precio" class="form-label">Tarifa Hora</label>
          <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="<?= $tarifa; ?>" required>
        </div>
        <div class="mb-3">
          <label for="porcentaje" class="form-label">Porcentaje de cobro (%)</label>
          <input type="number" step="0.01" class="form-control" id="porcentaje" name="porcentaje" value="<?= $porcentaje; ?>" required>
        </div>
        <div class="mb-3">
          <label for="min_servicio" class="form-label">Valor mínimo para servicio (Pesos $)</label>
          <input type="number" step="0.01" class="form-control" id="min_servicio" name="min_servicio" value="<?= $valor_minimo; ?>" required>
        </div>

        <!-- Intentos de cancelación -->
        <div class="mb-3">
          <label for="max_intentos_cancelacion" class="form-label">Máximo de intentos de cancelación</label>
          <input type="number" class="form-control" id="max_intentos_cancelacion" name="max_intentos_cancelacion" value="<?= $max_intentos_cancelacion ?? ''; ?>" required>
        </div>

        <!-- Multas por defecto -->
        <div class="mb-3">
          <label for="multa_cliente" class="form-label">Multa por defecto al cliente (Pesos $)</label>
          <input type="number" step="0.01" class="form-control" id="multa_cliente" name="multa_cliente" value="<?= $multa_cliente ?? ''; ?>" required>
        </div>

        <div class="mb-3">
          <label for="multa_domiciliario" class="form-label">Multa por defecto al domiciliario (Pesos $)</label>
          <input type="number" step="0.01" class="form-control" id="multa_domiciliario" name="multa_domiciliario" value="<?= $multa_domiciliario ?? ''; ?>" required>
        </div>

        <!-- Métodos de pago -->
        <hr>
        <h5>Métodos de Pago</h5>

        <!-- PayU -->
        <div class="mb-3">
          <div class="form-check form-switch">
            <input 
              class="form-check-input" 
              type="checkbox" 
              id="payu_habilitado" 
              name="payu_habilitado" 
              <?= isset($payu_habilitado) && $payu_habilitado ? 'checked' : ''; ?>
            >
            <label class="form-check-label" for="payu_habilitado">Habilitar PayU</label>
          </div>

          <div id="payu_config" style="margin-top: 10px; <?= (isset($payu_habilitado) && $payu_habilitado) ? '' : 'display:none;'; ?>">

            <input 
              type="text" 
              class="form-control mb-2" 
              id="payu_checkout_url" 
              name="payu_checkout_url" 
              placeholder="URL de Checkout PayU" 
              value="<?= htmlspecialchars($payu_checkout_url ?? '') ?>"
            >

            <input 
              type="text" 
              class="form-control mb-2" 
              id="payu_merchant_id" 
              name="payu_merchant_id" 
              placeholder="Merchant ID PayU" 
              value="<?= htmlspecialchars($payu_merchant_id ?? '') ?>"
            >

            <input 
              type="text" 
              class="form-control mb-2" 
              id="payu_account_id" 
              name="payu_account_id" 
              placeholder="Account ID PayU" 
              value="<?= htmlspecialchars($payu_account_id ?? '') ?>"
            >

            <input 
              type="text" 
              class="form-control mb-2" 
              id="payu_response_url" 
              name="payu_response_url" 
              placeholder="URL de Respuesta PayU" 
              value="<?= htmlspecialchars($payu_response_url ?? '') ?>"
            >

            <input 
              type="text" 
              class="form-control mb-2" 
              id="payu_confirmation_url" 
              name="payu_confirmation_url" 
              placeholder="URL de Confirmación PayU" 
              value="<?= htmlspecialchars($payu_confirmation_url ?? '') ?>"
            >

            <input 
              type="email" 
              class="form-control mb-2" 
              id="email_pay" 
              name="email_pay" 
              placeholder="Correo electrónico PayU" 
              value="<?= htmlspecialchars($email_pay ?? '') ?>"
              required
            >

          </div>
        </div>

        <script>
          document.getElementById('payu_habilitado').addEventListener('change', function() {
            const payuConfig = document.getElementById('payu_config');
            if(this.checked) {
              payuConfig.style.display = 'block';
            } else {
              payuConfig.style.display = 'none';
            }
          });
        </script>

        <!-- Cuenta de ahorros Bancolombia -->
        <div class="mb-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="bancolombia_habilitado" name="bancolombia_habilitado" <?= isset($bancolombia_habilitado) && $bancolombia_habilitado ? 'checked' : ''; ?>>
            <label class="form-check-label" for="bancolombia_habilitado">Habilitar Cuenta de Ahorros Bancolombia</label>
          </div>
          <input type="text" class="form-control mt-2" id="bancolombia_cuenta" name="bancolombia_cuenta" placeholder="Número de cuenta Bancolombia" value="<?= $bancolombia_cuenta ?? ''; ?>">
        </div>

        <!-- Nequi -->
        <div class="mb-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="nequi_habilitado" name="nequi_habilitado" <?= isset($nequi_habilitado) && $nequi_habilitado ? 'checked' : ''; ?>>
            <label class="form-check-label" for="nequi_habilitado">Habilitar Nequi</label>
          </div>
          <input type="text" class="form-control mt-2" id="nequi_cuenta" name="nequi_cuenta" placeholder="Número de cuenta Nequi" value="<?= $nequi_cuenta ?? ''; ?>">
        </div>

        <!-- Daviplata -->
        <div class="mb-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="daviplata_habilitado" name="daviplata_habilitado" <?= isset($daviplata_habilitado) && $daviplata_habilitado ? 'checked' : ''; ?>>
            <label class="form-check-label" for="daviplata_habilitado">Habilitar Daviplata</label>
          </div>
          <input type="text" class="form-control mt-2" id="daviplata_cuenta" name="daviplata_cuenta" placeholder="Número de cuenta Daviplata" value="<?= $daviplata_cuenta ?? ''; ?>">
        </div>

        <!-- Canales de comunicación -->
        <hr>
        <h5>Canales de Comunicación</h5>

        <div class="mb-3">
          <label for="whatsapp_contacto" class="form-label">WhatsApp</label>
          <input type="text" class="form-control" id="whatsapp_contacto" name="whatsapp_contacto" placeholder="Número de WhatsApp" value="<?= $whatsapp_contacto ?? ''; ?>">
        </div>

        <div class="mb-3">
          <label for="correo_contacto" class="form-label">Correo electrónico</label>
          <input type="email" class="form-control" id="correo_contacto" name="correo_contacto" placeholder="Correo de contacto" value="<?= $correo_contacto ?? ''; ?>">
        </div>

      <?php } ?>
    <button type="submit" class="btn btn-success">Guardar</button>
  </form>
</div>

<script>
// Enviar el formulario por AJAX
// Enviar el formulario por AJAX con archivos
$('#formCrearNegocio').submit(function(e) {
    e.preventDefault();

    var formData = new FormData(this);
    formData.append('action', 'guardar_config'); // acción adicional

    $.ajax({
        url: '../controllers/config_controller.php',
        type: 'POST',
        data: formData,
        contentType: false,  // evita que jQuery ponga content-type por defecto
        processData: false,  // evita que intente serializar los datos
        success: function(response) {
            console.log(response); 
            location.reload();
        },
        error: function() {
            alert('Error al guardar la configuración.');
        }
    });
});

</script>
