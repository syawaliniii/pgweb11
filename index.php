<!DOCTYPE html>
<html>
<head>
    <title>Peta Web Sleman</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        #map {
            height: 100%;
            width: 100%;
        }

        /* Style Legend */
        .legend {
            background: white;
            padding: 10px;
            border-radius: 5px;
            line-height: 18px;
            color: #333;
            box-shadow: 0 0 8px rgba(0,0,0,0.3);
            /* Pastikan legenda muncul di atas kontrol layer */
            z-index: 999; 
        }
        .legend h4 {
            margin: 0 0 5px;
            font-size: 14px;
            text-align: left;
        }
        .legend i {
            width: 18px;
            height: 18px;
            float: left;
            margin-right: 8px;
            opacity: 0.7;
        }
        .legend div {
            padding-bottom: 5px;
        }
    </style>
</head>
<body>

<div id="map"></div>

<script>
    // =============================
    // INISIALISASI PETA
    // =============================
    var map = L.map('map').setView([-7.677567, 110.406893], 12);

    // BASEMAP
    var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    // =============================
    // LAYER WMS GEOSEVER LOCAL
    // =============================

    // POLYGON ADMINISTRASI
    var polygonLayer = L.tileLayer.wms("http://localhost:8080/geoserver/ne/wms", {
        layers: "ne:ADMINISTRASIDESA",
        format: "image/png",
        transparent: true
    }).addTo(map);

    // JALAN
    var jalanLayer = L.tileLayer.wms("http://localhost:8080/geoserver/ne/wms", {
        layers: "ne:JALAN_LN_25K",
        format: "image/png",
        transparent: true
    }).addTo(map);

    // TITIK PENDUDUK
    var pointLayer = L.tileLayer.wms("http://localhost:8080/geoserver/ne/wms", {
        layers: "ne:penduduk",
        format: "image/png",
        transparent: true
    }).addTo(map);

    // =============================
    // LAYER WMS EKSTERNAL (RADIUS MERAPI)
    // =============================
    var merapiRadiusLayer = L.tileLayer.wms("https://geoportal.slemankab.go.id/geoserver/wms", {
        layers: "geonode:3404_50k_ln_radius_jarak_merapi",
        format: "image/png",
        transparent: true
    }); // TIDAK di-addTo(map) secara default, akan ditambahkan melalui Layer Control

    // =============================
    // LAYER CONTROL
    // =============================
    var baseMaps = { "OpenStreetMap": osm };

    var overlayMaps = {
        "Polygon Administrasi": polygonLayer,
        "Jalan": jalanLayer,
        "Penduduk (Titik)": pointLayer,
        "Radius Jarak Merapi": merapiRadiusLayer // TAMBAHAN: Layer Merapi
    };

    L.control.layers(baseMaps, overlayMaps).addTo(map);

    // =============================
    // LEGENDA DINAMIS
    // =============================
    var legend = L.control({ position: "bottomright" });
    
    // --- Konten Legenda ---
    var legendContent = {
        // Gunakan KEY (objek layer) untuk cek apakah layer aktif
        [polygonLayer._leaflet_id]: '<div id="legend-administrasi"><span style="background:#ffcc00; width:20px; height:10px; display:inline-block;"></span> Polygon Administrasi</div>',
        [jalanLayer._leaflet_id]: '<div id="legend-jalan"><span style="background:#0000ff; width:20px; height:3px; display:inline-block;"></span> Jalan</div>',
        [pointLayer._leaflet_id]: '<div id="legend-penduduk"><span style="background:#ff0000; width:10px; height:10px; display:inline-block; border-radius:50%;"></span> Titik Penduduk</div>',
        // TAMBAHAN: Item Legenda untuk Layer Radius Merapi
        [merapiRadiusLayer._leaflet_id]: '<div id="legend-merapi"><span style="border: 2px dashed orange; background: transparent; width:20px; height:3px; display:inline-block;"></span> Radius Jarak Merapi</div>'
    };
    
    // --- Fungsi Utama untuk Memperbarui Legenda ---
    function updateLegend() {
        var legendHTML = '<h4>Legenda</h4>';
        var legendEmpty = true;
        
        // Iterasi melalui semua layer di overlayMaps
        for (var key in overlayMaps) {
            var layer = overlayMaps[key];
            
            // Cek apakah layer tersebut saat ini ada di peta (aktif)
            if (map.hasLayer(layer)) {
                // Tambahkan HTML legenda dari objek legendContent
                if (legendContent[layer._leaflet_id]) {
                    legendHTML += legendContent[layer._leaflet_id];
                    legendEmpty = false;
                }
            }
        }
        
        // Jika tidak ada overlay layer yang aktif, tampilkan pesan
        if (legendEmpty) {
             legendHTML += '<div>Tidak ada layer yang aktif.</div>';
        }

        // Perbarui konten div legenda
        var legendDiv = document.querySelector('.legend');
        if (legendDiv) { // Cek apakah elemen ada sebelum diubah
             legendDiv.innerHTML = legendHTML;
        }
    }

    // --- Definisi Kontrol Legenda ---
    legend.onAdd = function(map) {
        // Buat div legenda
        var div = L.DomUtil.create("div", "leaflet-control legend");
        // Hentikan propagasi event klik (Perbaikan untuk legenda yang tidak muncul)
        L.DomEvent.disableClickPropagation(div);
        // Inisialisasi konten legenda saat pertama kali dimuat
        updateLegend(); 
        return div;
    };
    
    legend.addTo(map);

    // --- Tambahkan Event Listener ---
    // Dengarkan saat layer ditambahkan atau dihapus dari peta (melalui Layer Control)
    map.on('overlayadd', updateLegend);
    map.on('overlayremove', updateLegend);
    
    // Tambahan: Panggil updateLegend saat dokumen dimuat untuk memastikan legenda terisi
    setTimeout(updateLegend, 100); 

</script>

</body>
</html>