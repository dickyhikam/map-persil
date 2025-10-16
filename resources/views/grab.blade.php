<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>{{ $nama_menu }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no" />

    <link href="https://unpkg.com/maplibre-gl@3/dist/maplibre-gl.css" rel="stylesheet" />

    <link href="{{ asset('style-map.css') }}" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
    <!-- ====== HEADER / TOPBAR ====== -->
    <header class="topbar">
        <div class="container-fluid px-3">
            <div class="d-flex align-items-center justify-content-between">
                <!-- Kiri: Brand + menu -->
                <div class="d-flex align-items-center gap-3">
                    <!-- <div class="brand d-flex align-items-center gap-2">
                        <i class="fas fa-draw-polygon"></i>
                        <span>Land Management</span>
                    </div> -->
                    <nav class="d-none d-md-flex align-items-center gap-3 ms-3">
                        <a href="{{ route('pageMaps') }}" class="nav-link p-0">Maps</a>
                        <a href="#" class="nav-link p-0">GeoJSON</a>
                        <a href="#" class="nav-link p-0">Image Drone</a>
                    </nav>
                </div>

                <!-- Kanan: tombol Logout -->
                <div class="d-flex align-items-center gap-2">
                    <span class="d-none d-sm-inline">Hello, Admin</span>
                    <button id="btnLogout" class="btn-logout">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- ====== SIDEBAR ====== -->
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
                <div class="form-group mb-3">
                    <label for="land-status">Land Status:</label>
                    <input class="form-control" type="text" id="land-status" name="land-status" readonly placeholder="SHM/HGB/Girik, etc">
                </div>
                <div class="form-group mb-3">
                    <label for="actual-condition">Actual Condition (Photo):</label>
                    <div class="d-flex align-items-center">
                        <input class="form-control" type="text" id="actual-condition" name="actual-condition" readonly placeholder="View photo" readonly>
                        <button type="button" id="viewPhotoBtn" class="btn btn-link ml-2">View</button>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="current-owner">Current Owner:</label>
                    <input class="form-control" type="text" id="current-owner" name="current-owner" readonly>
                </div>
                <div class="form-group mb-3">
                    <label for="previous-owner">Previous Owner:</label>
                    <input class="form-control" type="text" id="previous-owner" name="previous-owner" readonly>
                </div>
                <div class="form-group mb-3">
                    <label for="area-size" class="form-label">Area Size:</label>
                    <div class="input-group">
                        <input class="form-control" id="area-size" name="area-size" readonly>
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">m²</span>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="potential-issue">Potential Issue (Document):</label>
                    <div class="d-flex align-items-center">
                        <input class="form-control" type="text" id="potential-issue" name="potential-issue" readonly placeholder="Download document" readonly>
                        <button type="button" id="downloadIssueBtn" class="btn btn-link ml-2">View</button>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="land-history">Land History (Document):</label>
                    <div class="d-flex align-items-center">
                        <input class="form-control" type="text" id="land-history" name="land-history" readonly placeholder="Download document" readonly>
                        <button type="button" id="downloadHistoryBtn" class="btn btn-link ml-2">View</button>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex">
                    <!-- <a href="/" class="btn btn-secondary btn-sm me-2" type="button" id="small-button">
                        <i class="fas fa-map"></i>
                    </a> -->
                    <button class="btn btn-gradient w-100 py-2 text-white" type="button" id="save-button">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        // Handler reusable
        function showComingSoon(title = 'Coming soon', text = 'This feature is under development.') {
            Swal.fire({
                title,
                text,
                icon: 'info',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0b3a82',
                backdrop: true,
                allowOutsideClick: true
            });
        }

        // Pasang listener untuk link di nav
        document.querySelectorAll('a[href="#"]').forEach(a => {
            a.addEventListener('click', (e) => {
                e.preventDefault();
                const label = a.textContent.trim();
                showComingSoon('Coming soon', `${label} is not available yet.`);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Mengecek apakah token autentikasi ada di sessionStorage atau localStorage
            const authToken = localStorage.getItem('authToken') || sessionStorage.getItem('authToken');

            // Jika token ada, arahkan pengguna ke halaman dashboard
            if (authToken) {
                window.location.href = "{{ route('pageMaps') }}"; // Sesuaikan dengan route dashboard
            }
        });

        // Tombol logout, untuk menghapus session dan mengarahkan kembali ke login
        document.getElementById('btnLogout').addEventListener('click', function() {
            try {
                sessionStorage.clear();
                localStorage.removeItem('authToken');
            } catch (e) {
                console.error('Error during logout:', e);
            }
            window.location.href = "{{ route('pageAuth') }}";
        });


        // Ambil data konfigurasi dari Blade Laravel
        const region = "{{ env('AWS_REGION') }}";
        const mapName = "{{ env('AWS_MAP_NAME') }}";
        const apiKey = "{{ env('AWS_API_KEY') }}";

        const landStatus = document.getElementById('land-status');
        const actualCondition = document.getElementById('actual-condition');
        const currentOwner = document.getElementById('current-owner');
        const previousOwner = document.getElementById('previous-owner');
        const areaSize = document.getElementById('area-size');
        const potentialIssue = document.getElementById('potential-issue');
        const landHistory = document.getElementById('land-history');
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
                zoom: 15,
            });
            map.addControl(new maplibregl.NavigationControl(), "top-right");

            // get data geojson
            fetch('/LAMIPAK-BPN-PERSIL.geojson')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    let geojsonData = data; // Simpan data GeoJSON di variabel
                    console.log(data.features);


                    // Cek data dan atur pusat peta
                    if (data.features && data.features.length > 0) {
                        const firstFeature = data.features[0];
                        const firstCoords = [firstFeature.properties.longitude, firstFeature.properties.latitude];
                        if (firstCoords[0] && firstCoords[1]) {
                            map.setCenter(firstCoords);
                            map.setZoom(15);
                        } else {
                            map.setCenter([115.8553, -5.1687]);
                            map.setZoom(15);
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
                            promoteId: 'Sert_No' // <-- PERBAIKAN 1: Gunakan 'Sert_No' sebagai ID unik untuk setiap fitur
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
                        // 2) Layer garis dasar (semua fitur)
                        map.addLayer({
                            id: 'geojson-line-base',
                            type: 'line',
                            source: 'geojson-source',
                            paint: {
                                'line-color': '#007bff',
                                'line-width': 3,
                                // 'line-join': 'round',
                                // 'line-cap': 'round'
                            }
                        });
                        // 3) Layer highlight (di atas), mulai tidak ada fitur terpilih
                        map.addLayer({
                            id: 'geojson-line-highlight',
                            type: 'line',
                            source: 'geojson-source',
                            filter: ['==', ['to-string', ['get', 'Sert_No']], '__NONE__'],
                            paint: {
                                'line-color': '#ff0000',
                                'line-width': 4,
                                // 'line-join': 'round',
                                // 'line-cap': 'round'
                            }
                        });

                        // Pastikan highlight di atas semua (opsional jika sudah ditambahkan terakhir)
                        map.moveLayer('geojson-line-highlight');

                        // --- Interaksi: klik untuk highlight ---
                        let highlightedFeatureId = null;

                        // Ubah kursor saat hover area yang bisa diklik
                        map.on('mouseenter', 'geojson-fill-layer', () => map.getCanvas().style.cursor = 'pointer');
                        map.on('mouseleave', 'geojson-fill-layer', () => map.getCanvas().style.cursor = '');

                        // Klik pada fitur (fill)
                        map.on('click', 'geojson-fill-layer', (e) => {
                            if (!e.features.length) return;

                            const clickedFeatureId = e.features[0].properties.Sert_No; // hasil dari promoteId
                            const properties = e.features[0].properties;

                            // Set filter layer highlight hanya ke fitur yang dipilih
                            map.setFilter('geojson-line-highlight', [
                                '==',
                                ['to-string', ['get', 'Sert_No']],
                                clickedFeatureId
                            ]);
                            highlightedFeatureId = clickedFeatureId;
                            console.log(properties);


                            // Tampilkan info (pastikan elemen input ini ada di DOM)
                            if (typeof areaSize !== 'undefined') {
                                const luas = Number(properties.land_size);
                                areaSize.value = Number.isFinite(luas) ? luas.toFixed(2) : '';
                            }
                            if (typeof landStatus !== 'undefined') landStatus.value = properties.st_tanah ?? '';
                            if (typeof actualCondition !== 'undefined') actualCondition.value = properties.actual_con ?? '';
                            if (typeof currentOwner !== 'undefined') currentOwner.value = properties.cur_owner ?? '';
                            if (typeof previousOwner !== 'undefined') previousOwner.value = properties.prv_owner ?? '';
                            if (typeof potentialIssue !== 'undefined') potentialIssue.value = properties.Issue ?? '';
                            if (typeof landHistory !== 'undefined') landHistory.value = properties.history ?? '';

                            if (typeof infoID !== 'undefined') infoID.value = highlightedFeatureId;
                        });

                        // Klik area kosong peta → hapus highlight
                        map.on('click', (e) => {
                            // cek apakah klik mengenai layer fill; jika tidak, kosongkan highlight
                            const feats = map.queryRenderedFeatures(e.point, {
                                layers: ['geojson-fill-layer']
                            });
                            if (!feats.length) {
                                map.setFilter('geojson-line-highlight', ['==', ['id'], -1]); // kosong
                                highlightedFeatureId = null;
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
                                if (feature.properties.Sert_No === ID) {
                                    // Perbarui data pada GeoJSON
                                    feature.properties.land_size = parseFloat(luasArea); // Memperbarui luas area
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