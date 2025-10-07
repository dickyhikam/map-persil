<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maps Persil</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>

<body>

    <div id="map"></div>

    <div class="show" id="info-card">
        <div class="form-group" hidden>
            <label for="route-id">ID:</label>
            <input class="form-control" type="text" id="route-id" name="route-id" readonly>
        </div>
        <div class="mb-3">
            <label for="route-luas-area" class="form-label">Luas Area:</label>
            <div class="input-group">
                <input class="form-control" id="route-luas-area" name="route-luas-area">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">m²</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="route-lat">Latitude:</label>
            <input class="form-control" type="text" id="route-lat" name="route-lat">
        </div>

        <div class="form-group">
            <label for="route-long">Longitude:</label>
            <input class="form-control" type="text" id="route-long" name="route-long">
        </div>
        <div class="d-grid gap-2 d-md-flex">
            <a href="/satelit" class="btn btn-secondary btn-sm mr-2" type="button" id="small-button">
                <i class="fas fa-satellite fa-xl"></i>
            </a>
            <button id="save-button" class="btn btn-primary btn-block" type="button">
                Simpan Perubahan
            </button>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

    <script>
        // Variabel untuk menyimpan layer GeoJSON dan layer yang terakhir diklik
        let geojsonLayer;
        let lastClickedLayer = null;
        // style persil default
        const defaultStyle = {
            weight: 2,
            opacity: 2,
            color: '#007bff',
            dashArray: '',
            fillOpacity: 0
        }
        // style persil yang dipilih (highlight)
        const highlightStyle = {
            weight: 2,
            opacity: 2,
            color: '#ff0000',
            dashArray: '',
            fillOpacity: 0
        };
        //variable untuk detil persil yang dipilih
        const infoLuasArea = document.getElementById('route-luas-area');
        const infoLat = document.getElementById('route-lat');
        const infoLong = document.getElementById('route-long');
        const infoID = document.getElementById('route-id');
        const saveButton = document.getElementById('save-button');

        // API Key dan MapName dari AWS Location Service
        const region = "{{ env('AWS_REGION') }}"; // Ganti dengan region peta Anda
        const mapName = "{{ env('AWS_MAP_NAME') }}"; // Ganti dengan nama peta Anda
        const apiKey = "{{ env('AWS_API_KEY') }}"; // Ganti dengan API Key AWS Anda

        // variable layer untuk maps
        const streetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxNativeZoom: 19, // OSM max available zoom is at 19.
            maxZoom: 22, // Match the map maxZoom, or leave map.options.maxZoom undefined.
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
            "Satelit": satelliteMap,
            // "Peta AWS": awsMap
        };
        //kontrol base map layer
        L.control.layers(baseLayers, null, {
            position: 'bottomleft'
        }).addTo(map);

        // get data geojson
        fetch('/contoh-persil-2.geojson')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                let geojsonData = data; // Simpan data GeoJSON di variabel

                //check apakah terdapat data pada geojson
                if (data.features && data.features.length > 0) {
                    //mengambil array pertama dari data geojson
                    const firstFeature = data.features[0];
                    const firstCoords = [firstFeature.properties.latitude, firstFeature.properties.longitude];
                    map.setView(firstCoords, 16);
                } else {
                    //koordinat default ketika geojson kosong
                    map.setView([-7.2575, 112.7521], 16);
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
                            infoLuasArea.value = `${props.area_in_me.toFixed(2)}`;
                            infoLat.value = props.latitude;
                            infoLong.value = props.longitude;
                            infoID.value = props.no_persil;
                        });
                    }
                }).addTo(map);

                // menyimpan data terbaru
                // Fungsi untuk memperbarui GeoJSON berdasarkan input
                saveButton.addEventListener('click', () => {
                    // Ambil data dari input
                    const luasArea = infoLuasArea.value; // luas area input
                    const latitude = parseFloat(infoLat.value); // latitude input
                    const longitude = parseFloat(infoLong.value); // longitude input
                    const ID = infoID.value; // luas area input

                    // Cek apakah semua input sudah terisi
                    if (luasArea && !isNaN(latitude) && !isNaN(longitude) && ID) {
                        // Cari elemen GeoJSON yang sesuai berdasarkan ID
                        geojsonData.features.forEach((feature) => {
                            if (feature.properties.no_persil === ID) {
                                // Perbarui data pada GeoJSON
                                feature.properties.area_in_me = parseFloat(luasArea); // Memperbarui luas area
                                feature.properties.latitude = latitude; // Memperbarui latitude
                                feature.properties.longitude = longitude; // Memperbarui longitude
                            }
                        });


                        // Kirimkan data yang sudah diperbarui ke server Laravel untuk disimpan ulang
                        fetch('/save-updated-geojson', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // CSRF token Laravel
                                },
                                body: JSON.stringify(geojsonData) // Mengirim data yang sudah diperbarui
                            })
                            .then(response => response.json())
                            .then(result => {
                                alert('Data berhasil diperbarui!');
                            })
                            .catch(error => {
                                alert('Gagal memperbarui data!');
                            });
                    } else {
                        // Jika ada input kosong atau tidak valid, tampilkan alert
                        alert('Mohon memilih data land terlebih dahulu.');
                    }
                });

            })
            .catch(error => {
                alert('Gagal memuat data GeoJSON. Cek console untuk detail error.');
            });
    </script>
</body>

</html>