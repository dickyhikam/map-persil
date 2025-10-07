<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Land Management</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no" />

    <link href="https://unpkg.com/maplibre-gl@3/dist/maplibre-gl.css" rel="stylesheet" />
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }

        #map,
        #map_osm {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
        }

        #map_osm {
            display: none;
        }

        /* .sidebar {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 400px;
            background: rgba(255, 255, 255, 0.82);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1;
        } */

        .sidebar-view {
            display: flex;
            flex-direction: column;
            /* Menata sidebar di bawah satu sama lain */
            gap: 20px;
            /* Jarak antara sidebar */
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1;
            width: 400px;
        }

        /* Sidebar */
        .sidebar {
            width: 100%;
            background: rgba(255, 255, 255, 0.82);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 2;
            overflow-y: auto;
            /* Menambahkan scroll jika isi sidebar melebihi tinggi */
        }

        #results,
        #routing-info {
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
        }

        /* Sidebar untuk Faskes Terdekat */
        #results2 {
            /* margin-top: 15px; */
            /* margin-bottom: 15px; */
            /* Gunakan flex-grow pada #results2 agar menyesuaikan dengan ruang yang tersisa */
            flex-grow: 1;
            /* Mengisi ruang yang tersisa setelah elemen sebelumnya */
            overflow-y: auto;
            /* Menambahkan scroll jika konten terlalu panjang */
            max-height: calc(100vh - 600px);
            /* Mengatur tinggi agar mengisi sisa layar */
        }

        .result-item {
            padding: 5px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
        }

        .result-item:hover {
            background-color: #f0f0f0;
        }

        /* Card Container */
        .card {
            width: 90%;
            height: auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            /* justify-content: center; */
            /* align-items: center; */
            padding: 5px;
            margin: 10px;
        }

        /* Menambahkan efek hover pada card */
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        /* Judul dan alamat */
        .card-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .card-address {
            font-size: 14px;
            color: #777;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
    <!-- menampilkan sidebar -->
    <div class="sidebar-view">
        <div class="sidebar">
            <div class="row" hidden>
                <div class="col-6">
                    <button class="btn btn-outline-primary w-100" id="satelliteBtn" onclick="satelliteBtn()" style="padding: 20px;">
                        <i class="fas fa-satellite fa-xl"></i> <br> Satellite Map
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn btn-outline-primary w-100" id="streetBtn" onclick="streetBtn()" style="padding: 20px;">
                        <i class="fas fa-map-marker-alt fa-xl"></i> <br> Street Map
                    </button>
                </div>
            </div>
            <div id="routing-info" class="info-box">
                <div class="form-group" hidden>
                    <label for="route-id">ID:</label>
                    <input class="form-control" type="text" id="route-id" name="route-id">
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

                <div class="form-group mb-3">
                    <label for="route-lat">Latitude:</label>
                    <input class="form-control" type="text" id="route-lat" name="route-lat">
                </div>

                <div class="form-group mb-3">
                    <label for="route-long">Longitude:</label>
                    <input class="form-control" type="text" id="route-long" name="route-long" readonly>
                </div>

                <div class="d-grid gap-2 d-md-flex">
                    <a href="/" class="btn btn-secondary btn-sm me-2" type="button" id="small-button">
                        <i class="fas fa-map"></i> <!-- Ikon -->
                    </a>
                    <button class="btn btn-primary w-100" type="button" id="save-button">
                        Simpan Perubahan
                    </button>
                </div>

            </div>
        </div>
    </div>


    <div id="map"></div>
    <div id="map_osm"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/maplibre-gl@3/dist/maplibre-gl.js"></script>
    <!-- CDN untuk Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Ambil data konfigurasi dari Blade Laravel
        const region = "{{ env('AWS_REGION') }}";
        const mapName = "{{ env('AWS_MAP_NAME') }}";
        const apiKey = "{{ env('AWS_API_KEY') }}";

        const infoLuasArea = document.getElementById('route-luas-area');
        const infoLat = document.getElementById('route-lat');
        const infoLong = document.getElementById('route-long');
        const infoID = document.getElementById('route-id');
        const saveButton = document.getElementById('save-button');

        $(document).ready(function() {
            mapsAWS();
        });

        // Fungsi untuk menampilkan peta AWS dan menyembunyikan peta OSM
        function satelliteBtn() {
            $('#map').show(); // Menampilkan peta AWS
            $('#map_osm').hide(); // Menyembunyikan peta OSM

            mapsAWS()
        }

        // Fungsi untuk menampilkan peta OSM dan menyembunyikan peta AWS
        function streetBtn() {
            $('#map').hide(); // Menyembunyikan peta AWS
            $('#map_osm').show(); // Menampilkan peta OSM
        }

        function mapsAWS() {
            const mapStyle = `https://maps.geo.${region}.amazonaws.com/maps/v0/maps/${mapName}/style-descriptor?key=${apiKey}`;
            const map = new maplibregl.Map({
                container: "map",
                style: mapStyle,
                center: [106.82715747381171, -6.1755682416875475],
                zoom: 16,
            });
            map.addControl(new maplibregl.NavigationControl(), "top-right");

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

                    // Cek data dan atur pusat peta
                    if (data.features && data.features.length > 0) {
                        const firstFeature = data.features[0];
                        const firstCoords = [firstFeature.properties.longitude, firstFeature.properties.latitude];
                        if (firstCoords[0] && firstCoords[1]) {
                            map.setCenter(firstCoords);
                            map.setZoom(18);
                        } else {
                            map.setCenter([115.8553, -5.1687]);
                            map.setZoom(16);
                            alert('GeoJSON memiliki koordinat tidak valid, menggunakan tampilan default.');
                        }
                    } else {
                        map.setCenter([115.8553, -5.1687]);
                        map.setZoom(16);
                        alert('GeoJSON kosong, menggunakan tampilan default.');
                    }

                    map.on('load', function() {

                        map.addSource('geojson-source', {
                            type: 'geojson',
                            data: data,
                            promoteId: 'no_persil' // <-- PERBAIKAN 1: Gunakan 'no_persil' sebagai ID unik untuk setiap fitur
                        });

                        // Menambahkan layer 'fill' untuk pengisian area
                        map.addLayer({
                            id: 'geojson-fill-layer',
                            type: 'fill',
                            source: 'geojson-source',
                            paint: {
                                // <-- PERBAIKAN 2: Buat warna & opasitas reaktif terhadap status 'highlighted'
                                'fill-color': '#ffffff', // Warna kuning untuk highlight
                                'fill-opacity': [
                                    'case',
                                    ['boolean', ['feature-state', 'highlighted'], false],
                                    0.0, // Opasitas 50% jika 'highlighted' = true
                                    0 // Opasitas 0% (transparan) jika 'highlighted' = false
                                ]
                            }
                        });
                        // Menambahkan layer 'line' untuk garis pinggir
                        map.addLayer({
                            id: 'geojson-line-layer',
                            type: 'line',
                            source: 'geojson-source',
                            paint: {
                                // <-- PERBAIKAN 3: Buat warna garis reaktif terhadap status 'highlighted'
                                'line-color': [
                                    'case',
                                    ['boolean', ['feature-state', 'highlighted'], false],
                                    '#ff0000', // Warna merah jika 'highlighted' = true
                                    '#007bff' // Warna biru default jika 'highlighted' = false
                                ],
                                'line-width': 3
                            }
                        });

                        // <-- PERBAIKAN 4: Logika click handler yang lebih baik
                        let highlightedFeatureId = null;

                        map.on('click', 'geojson-fill-layer', function(e) {
                            // Pastikan ada fitur yang diklik
                            if (e.features.length > 0) {
                                const clickedFeatureId = e.features[0].id; // Ambil ID yang sudah dipromote
                                const properties = e.features[0].properties;

                                // 1. Jika ada fitur yang sebelumnya di-highlight, nonaktifkan highlight-nya
                                if (highlightedFeatureId !== null) {
                                    map.setFeatureState({
                                        source: 'geojson-source',
                                        id: highlightedFeatureId
                                    }, {
                                        highlighted: false
                                    });
                                }

                                // 2. Aktifkan highlight pada fitur yang baru diklik
                                map.setFeatureState({
                                    source: 'geojson-source',
                                    id: clickedFeatureId
                                }, {
                                    highlighted: true
                                });

                                // 3. Simpan ID fitur yang baru di-highlight
                                highlightedFeatureId = clickedFeatureId;

                                // Menampilkan informasi tentang fitur yang dipilih ke dalam input
                                infoLuasArea.value = `${properties.area_in_me.toFixed(2)}`;
                                infoLat.value = properties.latitude;
                                infoLong.value = properties.longitude;
                                infoID.value = highlightedFeatureId;
                            }
                        });
                    });

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
                                    console.log('Data berhasil diperbarui di server:', result);
                                    alert('Data berhasil diperbarui!');
                                })
                                .catch(error => {
                                    console.error('Terjadi kesalahan saat mengirim data:', error);
                                    alert('Gagal memperbarui data!');
                                });
                        } else {
                            // Jika ada input kosong atau tidak valid, tampilkan alert
                            alert('Mohon memilih data land terlebih dahulu.');
                        }
                    });

                })
                .catch(error => {
                    console.error('Error fetching or parsing GeoJSON:', error);
                    alert('Gagal memuat data GeoJSON. Cek console untuk detail error.');
                });
        }
    </script>
</body>

</html>