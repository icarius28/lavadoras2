<?php
// Inicializar $puntos para evitar undefined variable
$puntos = [];

if (isset($_SESSION['negocio']) && $_SESSION['negocio']) {
    $negocio_id = (int) $_SESSION['negocio'];  // Cast a entero para seguridad
    
    $stmt = $conn->prepare("SELECT id, nombre, latitud, longitud FROM usuarios WHERE conductor_negocio = ?");
    $stmt->bind_param("i", $negocio_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $puntos[] = $row;
    }
}
?>


    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Estilos de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
    <h1>Listado de domiciliarios</h1>
    <div id="map"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const mapa = L.map('map').setView([7.8891, -72.4967], 13); // Cambia a coordenadas por defecto de tu ciudad

        // Capa de mapa base (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
        }).addTo(mapa);

        // Marcadores desde PHP
        const puntos = <?php echo json_encode($puntos); ?>;

        puntos.forEach(p => {
            if (p.latitud && p.longitud) {
                L.marker([parseFloat(p.latitud), parseFloat(p.longitud)])
                    .addTo(mapa)
                .bindPopup("Domiciliario " + p.nombre);
            }
        });
    </script>
</body>
</html>
