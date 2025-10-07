<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maps Persil</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }

        #map {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 100%;
        }

        #info-card {
            position: fixed;
            top: 20px;
            right: -400px;
            width: 350px;
            background: rgba(255, 255, 255, 0.82);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: right 0.5s ease-in-out;
            font-family: Arial, sans-serif;
        }

        #info-card.show {
            right: 20px;
        }

        #info-card h2 {
            margin-top: 0;
        }

        #close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
            color: #888;
        }
    </style>
</head>

<body>

    <div id="map"></div>

    <div class="show" id="info-card">
        <h2 id="info-name"></h2>
        <p id="info-description"></p>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

    <script>
        // Variabel untuk menyimpan layer GeoJSON dan layer yang terakhir diklik
        let geojsonLayer;
        let lastClickedLayer = null;
        // style persil default
        const defaultStyle = {
            weight: 2,
            opacity: 2,
            color: 'blue',
            dashArray: '3',
            fillOpacity: 0
        }
        // style persil yang dipilih (highlight)
        const highlightStyle = {
            weight: 2,
            opacity: 2,
            color: 'red',
            dashArray: '',
            fillOpacity: 0
        };
        //variable untuk detil persil yang dipilih
        const infoCard = document.getElementById('info-card');
        const infoName = document.getElementById('info-name');
        const infoDescription = document.getElementById('info-description');

        // variable layer untuk maps
        const streetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        });
        const satelliteMap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri'
        });

        // Inisialisasi Peta
        const map = L.map('map', {
            layers: [streetMap] // Atur 'streetMap' sebagai layer default saat peta dimuat
        });
        // variable base map
        const baseLayers = {
            "Peta Jalan": streetMap,
            "Satelit": satelliteMap
        };
        //kontrol base map layer
        L.control.layers(baseLayers, null, {
            position: 'bottomleft'
        }).addTo(map);

        //get data geojson
        fetch('/contoh-persil-2.geojson')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                //check apakah terdapat data pada geojson
                if (data.features && data.features.length > 0) {
                    //mengambil array pertama dari data geojson
                    const firstFeature = data.features[0];
                    const firstCoords = [firstFeature.properties.latitude, firstFeature.properties.longitude];
                    map.setView(firstCoords, 18);
                } else {
                    //koordinat default ketika geojson kosong
                    map.setView([-7.2575, 112.7521], 12);
                    alert('GeoJSON is empty, setting to default view.');
                }

                // Buat layer GeoJSON
                geojsonLayer = L.geoJSON(data, {
                    style: defaultStyle, // Terapkan style default
                    onEachFeature: function(feature, layer) {
                        const props = feature.properties;

                        layer.on('click', function(e) {
                            // check untuk reset style layer sebelumnya (jika ada)
                            if (lastClickedLayer) {
                                geojsonLayer.resetStyle(lastClickedLayer);
                            }

                            // menerapkan style yang dipilih
                            layer.setStyle(highlightStyle);
                            // menyimpan layer yang dipilih
                            lastClickedLayer = layer;
                            //menampilkan detil data parsil yang dipilih
                            infoName.textContent = `Persil: ${props.no_persil}`;
                            infoDescription.innerHTML = `Luas Area: ${props.area_in_me.toFixed(2)} m² <br>
                                                 Latitude: ${props.latitude} <br>
                                                 Longitude: ${props.longitude}`;
                            infoCard.classList.add('show');
                        });
                    }
                }).addTo(map);

            })
            .catch(error => {
                console.error('Error fetching or parsing GeoJSON:', error);
                alert('Gagal memuat data GeoJSON. Cek console untuk detail error.');
            });
    </script>
</body>

</html>