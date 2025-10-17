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
        <div class="sidebar" id="page_info" style="display: none;">
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
                    <input class="form-control" type="text" id="land-status" name="land-status" placeholder="SHM/HGB/Girik, etc" readonly>
                </div>
                <div class="form-group mb-3">
                    <label for="actual-condition">Actual Condition (Photo):</label>
                    <div class="d-flex align-items-center">
                        <input class="form-control" type="text" id="actual-condition" name="actual-condition" placeholder="View photo" readonly>
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
                        <input class="form-control" type="text" id="potential-issue" name="potential-issue" placeholder="Download document" readonly>
                        <button type="button" id="downloadIssueBtn" class="btn btn-link ml-2">View</button>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="land-history">Land History (Document):</label>
                    <div class="d-flex align-items-center">
                        <input class="form-control" type="text" id="land-history" name="land-history" placeholder="Download document" readonly>
                        <button type="button" id="downloadHistoryBtn" class="btn btn-link ml-2">View</button>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex">
                    <button id="close-sidebar-btn" class="btn btn-danger">Close</button>
                    <button id="edit-btn" class="btn btn-warning w-100" type="button">Edit</button>
                    <button class="btn btn-gradient w-100 py-2 text-white" type="button" id="save-button" style="display: none;">Save Change</button>
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

        document.getElementById('edit-btn').addEventListener('click', () => {
            removeReadOnly();
        });

        function removeReadOnly() {
            // Enable editing: remove readonly attributes
            document.getElementById('land-status').removeAttribute('readonly');
            document.getElementById('actual-condition').removeAttribute('readonly');
            document.getElementById('current-owner').removeAttribute('readonly');
            document.getElementById('previous-owner').removeAttribute('readonly');
            document.getElementById('area-size').removeAttribute('readonly');
            document.getElementById('potential-issue').removeAttribute('readonly');
            document.getElementById('land-history').removeAttribute('readonly');

            // Change the 'Edit' button to 'Save Change'
            document.getElementById('edit-btn').style.display = 'none';
            document.getElementById('save-button').style.display = 'block';
        }

        function addReadOnly() {
            // After saving, make fields read-only again
            document.getElementById('land-status').setAttribute('readonly', 'true');
            document.getElementById('actual-condition').setAttribute('readonly', 'true');
            document.getElementById('current-owner').setAttribute('readonly', 'true');
            document.getElementById('previous-owner').setAttribute('readonly', 'true');
            document.getElementById('area-size').setAttribute('readonly', 'true');
            document.getElementById('potential-issue').setAttribute('readonly', 'true');
            document.getElementById('land-history').setAttribute('readonly', 'true');

            // Hide 'Save Change' and show 'Edit' again
            document.getElementById('save-button').style.display = 'none';
            document.getElementById('edit-btn').style.display = 'block';
        }

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
        const pageInfo = document.getElementById('page_info');

        $(document).ready(function() {
            mapsAWS();
        });

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
                            map.setCenter([106.8203, -6.9754]);
                            map.setZoom(16);

                            Swal.fire({
                                icon: 'info',
                                title: 'GeoJSON Data is Empty',
                                text: 'The GeoJSON data is empty. Default view has been applied to Istana Presiden.',
                            });
                        }
                    } else {
                        map.setCenter([106.8203, -6.9754]);
                        map.setZoom(16);

                        Swal.fire({
                            icon: 'info',
                            title: 'GeoJSON Data is Empty',
                            text: 'The GeoJSON data is empty. Default view has been applied to Istana Presiden.',
                        });
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
                                'line-color': '#ffff00ff',
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
                            addReadOnly();
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

                            pageInfo.style.display = 'block'; // Show the sidebar

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

                                pageInfo.style.display = 'none';
                            }
                        });
                    });

                    document.getElementById('close-sidebar-btn').addEventListener('click', () => {
                        const pageInfo = document.getElementById('page_info');
                        pageInfo.style.display = 'none'; // Hide the sidebar

                        // Reset the highlight filter on the map
                        map.setFilter('geojson-line-highlight', ['==', ['get', 'Sert_No'], '']); // Remove highlight from the map

                        // Optionally, reset any highlightedFeatureId or other related variables
                        highlightedFeatureId = null; // Reset the highlighted feature ID
                    });


                    // menyimpan data terbaru
                    // Fungsi untuk memperbarui GeoJSON berdasarkan input
                    saveButton.addEventListener('click', () => {
                        // Ambil data dari input
                        const landStatusValue = landStatus.value;
                        const actualConditionValue = actualCondition.value;
                        const currentOwnerValue = currentOwner.value;
                        const previousOwnerValue = previousOwner.value;
                        const areaSizeValue = parseFloat(areaSize.value); // Assuming it's in square meters
                        const potentialIssueValue = potentialIssue.value;
                        const landHistoryValue = landHistory.value;
                        const ID = infoID.value; // luas area input

                        // Cek apakah semua input sudah terisi
                        if (landStatusValue && actualConditionValue && currentOwnerValue && previousOwnerValue && !isNaN(areaSizeValue) && potentialIssueValue && landHistoryValue && ID) {
                            // Cari elemen GeoJSON yang sesuai berdasarkan ID
                            geojsonData.features.forEach((feature) => {
                                if (feature.properties.Sert_No === ID) {
                                    // Perbarui data pada GeoJSON
                                    feature.properties.st_tanah = landStatusValue;
                                    feature.properties.actual_con = actualConditionValue;
                                    feature.properties.cur_owner = currentOwnerValue;
                                    feature.properties.prv_owner = previousOwnerValue;
                                    feature.properties.land_size = areaSizeValue;
                                    feature.properties.Issue = potentialIssueValue;
                                    feature.properties.history = landHistoryValue;
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
                                    addReadOnly()
                                    console.log('Data berhasil diperbarui di server:', result);

                                    // SweetAlert success message
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Data successfully updated!',
                                        text: 'Your data has been successfully saved to the server.',
                                    });
                                })
                                .catch(error => {
                                    addReadOnly()
                                    console.error('Terjadi kesalahan saat mengirim data:', error);

                                    // SweetAlert error message
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: 'Failed to update data! Please try again.',
                                    });
                                });
                        } else {
                            // Jika ada input kosong atau tidak valid, tampilkan alert
                            Swal.fire({
                                icon: 'warning',
                                title: 'Missing Information',
                                text: 'Please fill in all the fields before saving.',
                            });
                        }
                    });

                })
                .catch(error => {
                    console.error('Error fetching or parsing GeoJSON:', error);

                    // SweetAlert error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to load GeoJSON data',
                        text: 'An error occurred while fetching or parsing the data. Please check the console for details.',
                    });
                });
        }
    </script>
</body>

</html>