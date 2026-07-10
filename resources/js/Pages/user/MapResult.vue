<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { router, Head, usePage } from '@inertiajs/vue3';
import Footer from '@/Components/Footer.vue';
import axios from 'axios';

// ─── PROPS ───────────────────────────────────────────────────────────────────
const props = defineProps({
    dbStations: { type: Array, default: () => [] },
    filters:    { type: Object, default: () => ({}) },
});

// ─── REAL-TIME CLOCK ─────────────────────────────────────────────────────────
const currentTime = ref(new Date());
let timerInterval = null;
const formattedTime = computed(() =>
    currentTime.value.toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
    })
);

// ─── BATTERY ─────────────────────────────────────────────────────────────────
const userBatteryLevel = ref(65);

// ─── MAP STATE ────────────────────────────────────────────────────────────────
let map          = null;
let markersLayer = null;
let routeLayer   = null;
let userMarker   = null;

// FIX #4: locationWatchId sebelumnya `let` biasa -> tidak reactive, sehingga badge
// "GPS Aktif" di template berpotensi tidak ter-update. Sekarang jadi ref.
const locationWatchId = ref(null);

const userLocation = ref(null);
const isLocating   = ref(false);

// ─── DIRECTION PANEL ─────────────────────────────────────────────────────────
const showDirectionPanel     = ref(false);
const directionInfo          = ref({ distance: '', duration: '', steps: [] });
const isLoadingRoute         = ref(false);
const directionTargetStation = ref(null);

// ─── BOOKING MODAL STATE ──────────────────────────────────────────────────────
const showConfirmationModal = ref(false);
const selectedStationId     = ref(null);
const bookingError          = ref('');
const isProcessingPayment   = ref(false);

// ─── RECEIPT MODAL (fallback jika tidak ada payment link) ────────────────────
const showReceiptModal = ref(false);
const transactionCode  = ref('');
const bookedStation    = ref(null);

// ─── TIME SLOTS ──────────────────────────────────────────────────────────────
const selectedTimeSlot = ref('');
const bookedSlots      = ref({});
const isLoadingSlots   = ref(false);

const timeSlots = [
    '07:00','08:00','09:00','10:00','11:00','12:00',
    '13:00','14:00','15:00','16:00','17:00','18:00',
    '19:00','20:00','21:00',
];

const fetchBookedSlots = async (stationId) => {
    isLoadingSlots.value = true;
    try {
        const res = await axios.get(route('booking.slots', { station_id: stationId }));
        bookedSlots.value[stationId] = res.data.booked_slots || [];
    } catch {
        bookedSlots.value[stationId] = [];
    }
    isLoadingSlots.value = false;
};

const isSlotBooked = (slot) => {
    if (!selectedStation.value) return false;
    return (bookedSlots.value[selectedStation.value.id] || []).includes(slot);
};

// FIX #8: slot dianggap "lewat" kalau kurang dari 15 menit lagi (bukan cuma
// dibandingkan per-jam bulat), supaya user tidak booking slot yang mustahil dikejar.
const SLOT_BUFFER_MS = 15 * 60 * 1000;

const isSlotPast = (slot) => {
    const now = new Date();
    const [slotHour] = slot.split(':').map(Number);
    const slotDate = new Date(now);
    slotDate.setHours(slotHour, 0, 0, 0);
    return slotDate.getTime() - now.getTime() < SLOT_BUFFER_MS;
};

const isSlotDisabled = (slot) => isSlotBooked(slot) || isSlotPast(slot);

// ─── HELPERS ─────────────────────────────────────────────────────────────────
const safeParseArray = (data) => {
    if (!data) return [];
    if (Array.isArray(data)) return data;
    if (typeof data === 'string') {
        try { return JSON.parse(data); } catch { return [data]; }
    }
    return [];
};

const getStationType = (s) => {
    if (s.type) return s.type;
    const d = safeParseArray(s.chargers_detail);
    return d[0]?.tipe ?? 'Fast Charging';
};

const getStationPower = (s) => {
    if (s.power) return s.power;
    const d = safeParseArray(s.chargers_detail);
    return d[0]?.daya ?? '50 kW';
};

const getServiceFee = (s) => Number(s.service_fee) || Number(s.serviceFee) || 0;

// FIX #1: harga dasar kini dibaca dari data stasiun (jika backend mengirimkannya),
// dengan fallback 2000 supaya tidak error kalau field belum ada. Tidak perlu
// perubahan apa pun di backend/controller.
const getBasePrice = (s) => Number(s.price) || Number(s.base_price) || 2000;

const getEstimatedTimeToFull = (powerStr) => {
    const m = (powerStr || '50').match(/([0-9]+)/);
    const kw = m ? Number(m[1]) : 50;
    if (userBatteryLevel.value >= 100) return 'Sudah Penuh';
    const mins = Math.round(((100 - userBatteryLevel.value) / 100 * 60) / kw * 60);
    if (mins < 60) return `${mins} Menit`;
    const h = Math.floor(mins / 60), rem = mins % 60;
    return rem > 0 ? `${h} Jam ${rem} Mnt` : `${h} Jam`;
};

const formatRupiah = (val) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

// FIX #6: escape HTML sebelum disisipkan ke popup Leaflet (innerHTML) untuk
// mencegah XSS lewat nama stasiun yang mungkin berasal dari data admin/DB.
const escapeHtml = (str) => String(str ?? '').replace(/[&<>"']/g, (m) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[m]));

// ─── STATIONS ─────────────────────────────────────────────────────────────────
const formState = ref({ domicile: props.filters.domicile || '' });

const domicileCoordinates = {
    'Batam Center': { lat: 1.1301, lng: 104.0529 },
    'Nagoya':       { lat: 1.1469, lng: 104.0046 },
    'Harbour Bay':  { lat: 1.1517, lng: 103.9976 },
};

const calcDistance = (la1, lo1, la2, lo2) => {
    if (!la1 || !lo1 || !la2 || !lo2) return 999.9;
    const R = 6371, dLat = (la2 - la1) * Math.PI / 180, dLon = (lo2 - lo1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 +
        Math.cos(la1 * Math.PI / 180) * Math.cos(la2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
    return parseFloat((R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))).toFixed(1));
};

// FIX #5: satu sumber kebenaran untuk status/badge stasiun (isBookable + badge
// label/warna dihitung sekali di sini), supaya badge dan tombol booking tidak
// pernah saling kontradiksi lagi.
const getStationBadge = (s, isBookable) => {
    if (!isBookable) {
        return { label: '🔴 Tutup', classes: 'bg-gray-100 text-gray-500' };
    }
    if (s.recommendation_status) {
        return s.is_recommended
            ? { label: '🟢 Sepi', classes: 'bg-green-100 text-green-700' }
            : { label: '🟠 Ramai', classes: 'bg-orange-100 text-orange-700' };
    }
    return null;
};

const nearestStations = computed(() => {
    let center = { lat: 1.1301, lng: 104.0529 };
    if (userLocation.value) center = userLocation.value;
    else if (domicileCoordinates[formState.value.domicile])
        center = domicileCoordinates[formState.value.domicile];

    return (props.dbStations || [])
        .filter(s => String(s.status || '').toLowerCase() !== 'tutup')
        .map(s => {
            const dist = s.lat && s.lng
                ? calcDistance(center.lat, center.lng, parseFloat(s.lat), parseFloat(s.lng))
                : 999.9;
            const isBookable  = s.is_open == 1 || s.is_open === true || s.status === 'Tersedia';
            const power       = getStationPower(s);
            const basePrice   = getBasePrice(s);
            const serviceFee  = getServiceFee(s);
            const badge       = getStationBadge(s, isBookable);
            return {
                ...s,
                realDistance: dist,
                distance: isNaN(dist) ? 'N/A' : dist + ' km',
                isBookable,
                safeType: getStationType(s),
                safePower: power,
                safeServiceFee: serviceFee,
                basePrice,
                displayPrice: formatRupiah(basePrice),
                estimatedTimeToFull: getEstimatedTimeToFull(power),
                badgeLabel: badge?.label ?? null,
                badgeClasses: badge?.classes ?? '',
            };
        })
        .sort((a, b) => a.realDistance - b.realDistance);
});

const selectedStation = computed(() =>
    nearestStations.value.find(s => s.id === selectedStationId.value) ?? null
);

// ─── BOOKING FORM ─────────────────────────────────────────────────────────────
const selectedPort     = ref('');
const selectedDuration = ref('30');
const activeDropdown   = ref(null);

// FIX #9: refs & index aktif untuk navigasi keyboard dropdown Port/Durasi
const portListRef          = ref(null);
const durationListRef      = ref(null);
const portActiveIndex      = ref(-1);
const durationActiveIndex  = ref(-1);

const availablePorts = computed(() => {
    if (!selectedStation.value) return [];
    const details = safeParseArray(selectedStation.value.chargers_detail);
    if (details.length)
        return details.map((c, i) => ({
            id:    `port-${i + 1}`,
            label: `⚡ ${c.tipe || 'Fast'} (${c.daya || 'Unknown'})`,
            value: c.tipe || 'Fast Charging',
        }));
    return [{ id: 'port-1', label: '⚡ DC CCS2 (Fast)', value: 'CCS2' }];
});

const durationOptions = computed(() => {
    if (selectedStation.value?.is_private)
        return [
            { label: 'Darurat (2 Jam)', value: '120' },
            { label: 'Setengah Hari (4 Jam)', value: '240' },
        ];
    return [
        { label: '20 kWh (~20 Menit)', value: '20' },
        { label: '30 kWh (~30 Menit)', value: '30' },
        { label: '50 kWh (~50 Menit)', value: '50' },
    ];
});

// FIX #1: rincian harga sekarang memakai harga & biaya layanan dari stasiun
// yang dipilih (bukan angka hardcode 2000 untuk semua stasiun).
const priceBreakdown = computed(() => {
    if (!selectedStation.value) return { price: 0, service: 0, ppn: 0, total: 0 };
    const price   = selectedStation.value.basePrice;
    const service = selectedStation.value.safeServiceFee;
    const ppn     = Math.round((price + service) * 0.11);
    return { price, service, ppn, total: price + service + ppn };
});

const toggleDropdown = (name) => {
    activeDropdown.value = activeDropdown.value === name ? null : name;
    if (activeDropdown.value === 'port') {
        portActiveIndex.value = availablePorts.value.findIndex(p => p.value === selectedPort.value);
        nextTick(() => focusDropdownItem(portListRef, portActiveIndex.value));
    } else if (activeDropdown.value === 'duration') {
        durationActiveIndex.value = durationOptions.value.findIndex(d => d.value === selectedDuration.value);
        nextTick(() => focusDropdownItem(durationListRef, durationActiveIndex.value));
    }
};

const focusDropdownItem = (listRef, index) => {
    const el = listRef.value?.children?.[Math.max(index, 0)];
    if (el && el.focus) el.focus();
};

const selectOption = (f, v) => {
    if (f === 'port') selectedPort.value = v;
    else if (f === 'duration') selectedDuration.value = v;
    activeDropdown.value = null;
};

// FIX #9: navigasi keyboard (ArrowUp/Down/Enter/Escape) untuk dropdown Port & Durasi
const handleBookingDropdownKeydown = (e, field, options, valueKey = 'value') => {
    const isPort = field === 'port';
    const activeIndex = isPort ? portActiveIndex : durationActiveIndex;
    const listRef = isPort ? portListRef : durationListRef;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex.value = Math.min(activeIndex.value + 1, options.length - 1);
        focusDropdownItem(listRef, activeIndex.value);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex.value = Math.max(activeIndex.value - 1, 0);
        focusDropdownItem(listRef, activeIndex.value);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const opt = options[activeIndex.value];
        if (opt) selectOption(field, opt[valueKey]);
    } else if (e.key === 'Escape') {
        activeDropdown.value = null;
    }
};

const reserveStation = async (id) => {
    selectedStationId.value = id;
    bookingError.value      = '';
    selectedTimeSlot.value  = '';

    await fetchBookedSlots(id);
    await nextTick();

    if (selectedStation.value) {
        selectedDuration.value = selectedStation.value.is_private ? '120' : '30';
        activeDropdown.value   = null;
        if (availablePorts.value.length) selectedPort.value = availablePorts.value[0].value;
        showConfirmationModal.value = true;
    } else {
        alert('Data stasiun tidak ditemukan.');
    }
};

const cancelProcess = () => {
    showConfirmationModal.value = false;
    bookingError.value          = '';
    selectedStationId.value     = null;
    selectedTimeSlot.value      = '';
};

const proceedToPayment = () => {
    if (!selectedTimeSlot.value) {
        bookingError.value = 'Silakan pilih jam booking terlebih dahulu.';
        return;
    }
    bookingError.value = '';
    confirmPayment();
};

// ─── CONFIRM PAYMENT → POST ke backend ────────────────────────────────────────
// FIX #2 & #3: dirapikan jadi satu alur yang jelas -> redirect ke payment_url
// kalau ada, atau tampilkan struk sebagai fallback. Endpoint, payload, dan
// nama field response (payment_url, booking_code) TIDAK diubah sama sekali.
const confirmPayment = async () => {
    if (!selectedStation.value) return;
    isProcessingPayment.value = true;
    bookingError.value        = '';

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res   = await axios.post(route('booking.store'), {
            station_name: selectedStation.value.name,
            booking_time: new Date().toISOString(),
            booking_slot: selectedTimeSlot.value,
            duration:     Number(selectedDuration.value),
            total_price:  priceBreakdown.value.total,
            port_type:    selectedPort.value || 'Regular',
        }, {
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        });

        isProcessingPayment.value   = false;
        showConfirmationModal.value = false;

        const data = res.data;

        if (data.payment_url) {
            // Redirect ke halaman pembayaran resmi (mis. Paymentku)
            window.location.href = data.payment_url;
            return;
        }

        // Fallback: tidak ada link pembayaran -> tampilkan struk biasa
        transactionCode.value  = data.booking_code ?? ('EV-' + Math.floor(Date.now() / 1000));
        bookedStation.value    = selectedStation.value;
        showReceiptModal.value = true;
        drawRouteOnSuccess(selectedStation.value);

    } catch (err) {
        isProcessingPayment.value = false;
        const errors = err.response?.data?.errors;
        if (errors) {
            const first = Object.values(errors)[0];
            bookingError.value = Array.isArray(first) ? first[0] : first;
        } else if (err.response?.status === 409) {
            bookingError.value = 'Slot jam ini baru saja dipesan. Silakan pilih jam lain.';
            if (selectedStation.value) await fetchBookedSlots(selectedStation.value.id);
        } else {
            bookingError.value = err.response?.data?.message ?? 'Gagal memproses booking. Coba lagi.';
        }
    }
};

// ─── RECEIPT ──────────────────────────────────────────────────────────────────
const receiptRouteInfo      = ref({ distance: '', duration: '' });
const isLoadingReceiptRoute = ref(false);

const closeReceiptModal = () => {
    showReceiptModal.value  = false;
    selectedStationId.value = null;
    selectedTimeSlot.value  = '';
    bookedStation.value     = null;
    if (routeLayer && map) { map.removeLayer(routeLayer); routeLayer = null; }
    router.visit(route('profile'));
};

const drawRouteOnSuccess = async (station) => {
    if (!station?.lat || !station?.lng) return;
    isLoadingReceiptRoute.value = true;
    try {
        const loc = await ensureUserLocation().catch(() => null);
        if (!loc) { isLoadingReceiptRoute.value = false; return; }

        const r = await fetchOSRMRoute(loc.lat, loc.lng, parseFloat(station.lat), parseFloat(station.lng));
        if (!r) throw new Error('no route');

        if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }

        window.L.geoJSON(r.geometry, {
            style: { color: '#00C853', weight: 18, opacity: 0.1, lineCap: 'round', lineJoin: 'round' },
        }).addTo(map);

        routeLayer = window.L.geoJSON(r.geometry, {
            style: { color: '#00C853', weight: 6, opacity: 0.95, lineCap: 'round', lineJoin: 'round' },
        }).addTo(map);

        drawUserMarkerIfNeeded(window.L, loc);

        const destIcon = window.L.divIcon({
            className: 'bg-transparent',
            html: `<div style="display:flex;align-items:center;justify-content:center;width:44px;height:44px;background:#00C853;border:4px solid white;border-radius:50%;box-shadow:0 0 0 6px rgba(0,200,83,0.25),0 4px 16px rgba(0,0,0,0.2);font-size:20px;">⚡</div>`,
            iconSize: [44, 44], iconAnchor: [22, 22],
        });
        window.L.marker([parseFloat(station.lat), parseFloat(station.lng)], { icon: destIcon, zIndexOffset: 900 })
            .addTo(map)
            .bindPopup(`<div style="text-align:center;padding:6px 4px;"><b style="font-size:14px;">${escapeHtml(station.name)}</b><br><span style="color:#00C853;font-weight:700;font-size:13px;">✅ Sudah Dipesan</span></div>`, { maxWidth: 200 })
            .openPopup();

        map.fitBounds(routeLayer.getBounds(), { padding: [100, 60], maxZoom: 15 });

        const distKm  = (r.distance / 1000).toFixed(1);
        const durMins = Math.round(r.duration / 60);
        const durText = durMins < 60 ? `${durMins} menit` : `${Math.floor(durMins / 60)} jam ${durMins % 60} mnt`;
        receiptRouteInfo.value = { distance: distKm + ' km', duration: durText };
    } catch (e) {
        console.error('Route on success error:', e);
    }
    isLoadingReceiptRoute.value = false;
};

// ─── OSRM ROUTING ─────────────────────────────────────────────────────────────
const fetchOSRMRoute = async (uLat, uLng, dLat, dLng) => {
    const url = `https://router.project-osrm.org/route/v1/driving/${uLng},${uLat};${dLng},${dLat}?overview=full&geometries=geojson&steps=true`;
    const res  = await fetch(url);
    if (!res.ok) throw new Error('OSRM error');
    const data = await res.json();
    if (data.code !== 'Ok') throw new Error('Route not found');
    return data.routes?.[0] ?? null;
};

const ensureUserLocation = () => new Promise((res, rej) => {
    if (userLocation.value) return res(userLocation.value);
    if (!navigator.geolocation) return rej(new Error('GPS tidak didukung'));
    isLocating.value = true;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            userLocation.value = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            isLocating.value   = false;
            res(userLocation.value);
        },
        (err) => { isLocating.value = false; rej(err); },
        { enableHighAccuracy: true, timeout: 10000 }
    );
});

const drawUserMarkerIfNeeded = (L, loc) => {
    if (userMarker) { userMarker.setLatLng([loc.lat, loc.lng]); return; }
    const icon = L.divIcon({
        className: 'bg-transparent',
        html: `<div style="width:18px;height:18px;background:#3b82f6;border:3px solid white;border-radius:50%;box-shadow:0 0 0 4px rgba(59,130,246,0.3);animation:pulse-blue 1.5s infinite;"></div>`,
        iconSize: [18, 18], iconAnchor: [9, 9],
    });
    userMarker = L.marker([loc.lat, loc.lng], { icon, zIndexOffset: 1000 }).addTo(map);
};

// ─── DIRECTION PANEL ─────────────────────────────────────────────────────────
const parseManeuver = (step) => {
    const type = step.maneuver?.type || '';
    const mod  = step.maneuver?.modifier || '';
    const name = step.name || '';
    const map_ = {
        'depart':            { icon: '🚗', text: 'Mulai perjalanan' },
        'arrive':            { icon: '📍', text: 'Tiba di tujuan' },
        'turn-left':         { icon: '↰',  text: 'Belok kiri' },
        'turn-right':        { icon: '↱',  text: 'Belok kanan' },
        'turn-slight left':  { icon: '↖',  text: 'Sedikit ke kiri' },
        'turn-slight right': { icon: '↗',  text: 'Sedikit ke kanan' },
        'turn-sharp left':   { icon: '⬅',  text: 'Belok tajam kiri' },
        'turn-sharp right':  { icon: '➡',  text: 'Belok tajam kanan' },
        'turn-uturn':        { icon: '↩',  text: 'Putar balik' },
        'merge':             { icon: '↪',  text: 'Gabung jalur' },
        'roundabout':        { icon: '🔄', text: 'Masuk bundaran' },
        'exit roundabout':   { icon: '↗',  text: 'Keluar bundaran' },
        'fork-left':         { icon: '↰',  text: 'Ambil jalur kiri' },
        'fork-right':        { icon: '↱',  text: 'Ambil jalur kanan' },
        'continue':          { icon: '⬆',  text: 'Lurus terus' },
        'new name':          { icon: '⬆',  text: 'Lanjut lurus' },
    };
    const key   = mod ? `${type}-${mod}` : type;
    const match = map_[key] || map_[type] || { icon: '⬆', text: 'Lurus terus' };
    return { icon: match.icon, text: name ? `${match.text} — ${name}` : match.text };
};

const showDirection = async (station) => {
    if (!window.L || !map) { alert('Peta belum siap.'); return; }
    if (!station.lat || !station.lng) { alert('Koordinat stasiun tidak tersedia.'); return; }

    isLoadingRoute.value         = true;
    showDirectionPanel.value     = true;
    directionTargetStation.value = station;
    directionInfo.value          = { distance: '', duration: '', steps: [] };

    try {
        const loc = await ensureUserLocation();
        const r   = await fetchOSRMRoute(loc.lat, loc.lng, parseFloat(station.lat), parseFloat(station.lng));

        if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }

        window.L.geoJSON(r.geometry, {
            style: { color: '#00C853', weight: 14, opacity: 0.15, lineCap: 'round' },
        }).addTo(map);

        routeLayer = window.L.geoJSON(r.geometry, {
            style: { color: '#00C853', weight: 6, opacity: 0.9, lineCap: 'round', lineJoin: 'round' },
        }).addTo(map);

        drawUserMarkerIfNeeded(window.L, loc);

        const destIcon = window.L.divIcon({
            className: 'bg-transparent',
            html: `<div style="display:flex;align-items:center;justify-content:center;width:36px;height:36px;background:#00C853;border:3px solid white;border-radius:50%;box-shadow:0 4px 12px rgba(0,200,83,0.5);font-size:16px;">⚡</div>`,
            iconSize: [36, 36], iconAnchor: [18, 18],
        });
        window.L.marker([parseFloat(station.lat), parseFloat(station.lng)], { icon: destIcon })
            .addTo(map).bindPopup(`<b>${escapeHtml(station.name)}</b>`).openPopup();

        map.fitBounds(routeLayer.getBounds(), { padding: [80, 80] });

        const distKm  = (r.distance / 1000).toFixed(1);
        const durMins = Math.round(r.duration / 60);
        const durText = durMins < 60 ? `${durMins} menit` : `${Math.floor(durMins / 60)} jam ${durMins % 60} mnt`;

        const steps = (r.legs?.[0]?.steps || [])
            .filter(s => s.maneuver?.type !== 'notification')
            .map(s => {
                const { icon, text } = parseManeuver(s);
                return {
                    icon, text,
                    distance: s.distance >= 1000
                        ? (s.distance / 1000).toFixed(1) + ' km'
                        : Math.round(s.distance) + ' m',
                };
            });

        directionInfo.value = { distance: distKm + ' km', duration: durText, steps };
    } catch (err) {
        console.error('Direction error:', err);
        directionInfo.value = {
            distance: 'N/A', duration: 'N/A',
            steps: [{ icon: '⚠️', text: 'Gagal memuat rute. Pastikan GPS aktif.', distance: '' }],
        };
    }
    isLoadingRoute.value = false;
};

const closeDirection = () => {
    showDirectionPanel.value     = false;
    directionTargetStation.value = null;
    directionInfo.value          = { distance: '', duration: '', steps: [] };
    if (routeLayer && map) { map.removeLayer(routeLayer); routeLayer = null; }
    if (map) map.setView([1.126, 104.030], 12);
};

const bookFromDirection = (station) => {
    closeDirection();
    nextTick(() => reserveStation(station.id));
};

// ─── MAP INIT ─────────────────────────────────────────────────────────────────
const loadLeaflet = () => new Promise((res, rej) => {
    if (window.L) return res(window.L);
    const s = document.createElement('script');
    s.src    = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    s.onload  = () => res(window.L);
    s.onerror = () => rej(new Error('Gagal memuat Leaflet'));
    document.body.appendChild(s);
});

const renderMarkers = (L) => {
    if (!map || !markersLayer) return;
    markersLayer.clearLayers();
    nearestStations.value.forEach(s => {
        if (!s.lat || !s.lng) return;
        const safeName = escapeHtml(s.name);
        const icon = L.divIcon({
            className: 'bg-transparent',
            html: `<div style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;background:${s.isBookable ? '#00C853' : '#9CA3AF'};border:2.5px solid white;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,0.25);font-size:14px;cursor:pointer;">⚡</div>`,
            iconSize: [32, 32], iconAnchor: [16, 16],
        });
        const marker = L.marker([s.lat, s.lng], { icon }).addTo(markersLayer);
        marker.bindPopup(`
            <div style="text-align:center;padding:8px 4px;min-width:180px;">
                <b style="font-size:14px;display:block;margin-bottom:4px;">${safeName}</b>
                <span style="color:#666;font-size:12px;">📍 ${s.distance}</span>
                <span style="color:#00C853;font-size:12px;font-weight:600;display:block;margin:2px 0;">⚡ ${escapeHtml(s.safeType)} · ${escapeHtml(s.safePower)}</span>
                <div style="display:flex;gap:6px;margin-top:10px;">
                    <button onclick="window.dispatchEvent(new CustomEvent('ev-direction',{detail:${s.id}}))"
                        style="flex:1;background:#3b82f6;color:#fff;padding:7px 0;border-radius:8px;border:none;font-size:12px;font-weight:700;cursor:pointer;">
                        🗺 Rute
                    </button>
                    ${s.isBookable
                        ? `<button onclick="window.dispatchEvent(new CustomEvent('ev-book',{detail:${s.id}}))"
                            style="flex:1;background:#00C853;color:#fff;padding:7px 0;border-radius:8px;border:none;font-size:12px;font-weight:700;cursor:pointer;">⚡ Pesan</button>`
                        : `<button disabled style="flex:1;background:#e5e7eb;color:#9ca3af;padding:7px 0;border-radius:8px;border:none;font-size:12px;">Penuh</button>`
                    }
                </div>
            </div>`, { maxWidth: 240 });
    });
};

// FIX #4: dijadikan toggle yang benar + tidak pernah menumpuk watcher GPS.
const startRealtimeTracking = () => {
    if (!navigator.geolocation) { alert('Browser tidak support GPS.'); return; }

    // Kalau sudah aktif, klik lagi berarti matikan tracking
    if (locationWatchId.value !== null) {
        navigator.geolocation.clearWatch(locationWatchId.value);
        locationWatchId.value = null;
        return;
    }

    isLocating.value = true;
    locationWatchId.value = navigator.geolocation.watchPosition(
        (pos) => {
            userLocation.value = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            isLocating.value   = false;
            if (map && window.L) {
                drawUserMarkerIfNeeded(window.L, userLocation.value);
                map.flyTo([userLocation.value.lat, userLocation.value.lng], 14);
            }
        },
        (err) => { isLocating.value = false; console.error(err); },
        { enableHighAccuracy: true, maximumAge: 10000, timeout: 5000 }
    );
};

const handleMapBook      = (e) => reserveStation(e.detail);
const handleMapDirection = (e) => {
    const st = nearestStations.value.find(s => s.id === e.detail);
    if (st) showDirection(st);
};

onMounted(async () => {
    const saved = localStorage.getItem('evolt_user_battery');
    if (saved) userBatteryLevel.value = Number(saved);

    timerInterval = setInterval(() => { currentTime.value = new Date(); }, 1000);

    window.addEventListener('ev-book',      handleMapBook);
    window.addEventListener('ev-direction', handleMapDirection);

    try {
        const L = await loadLeaflet();
        await nextTick();
        if (map) map.remove();
        map = L.map('map', { zoomControl: false }).setView([1.126, 104.030], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);
        markersLayer = L.layerGroup().addTo(map);
        renderMarkers(L);
        setTimeout(() => { if (map) map.invalidateSize(); }, 300);
    } catch (e) {
        console.error('Map error:', e);
    }
});

onBeforeUnmount(() => {
    clearInterval(timerInterval);
    window.removeEventListener('ev-book',      handleMapBook);
    window.removeEventListener('ev-direction', handleMapDirection);
    if (locationWatchId.value !== null) navigator.geolocation.clearWatch(locationWatchId.value);
});
</script>

<template>
    <Head title="Cari Stasiun" />
    <div class="min-h-screen flex flex-col bg-gray-50 font-sans text-gray-800">
        <main class="flex-grow pt-6 pb-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

                <!-- Header: Jam -->
                <div class="flex justify-between items-center mb-4">
                    <div class="bg-white px-4 py-2 rounded-full shadow-sm border border-gray-100 flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-bold text-gray-700 font-mono">{{ formattedTime }}</span>
                    </div>
                </div>

                <!-- MAP -->
                <div class="relative w-full mb-8 rounded-3xl shadow-xl overflow-hidden border border-gray-200 h-[520px] md:h-[500px] bg-gray-100">
                    <div id="map" class="absolute inset-0 z-10 w-full h-full"></div>

                    <button @click="startRealtimeTracking"
                        :aria-pressed="locationWatchId !== null"
                        aria-label="Aktifkan atau matikan pelacakan lokasi realtime"
                        class="absolute bottom-4 right-4 z-[400] bg-white p-3 rounded-full shadow-lg border border-gray-200 transition flex items-center justify-center w-12 h-12"
                        :class="locationWatchId !== null ? 'text-blue-600 ring-2 ring-blue-200' : 'text-gray-600 hover:text-blue-600'">
                        <i v-if="!isLocating" class="fas fa-location-arrow text-xl"></i>
                        <i v-else class="fas fa-spinner fa-spin text-xl text-blue-500"></i>
                    </button>

                    <div v-if="locationWatchId !== null"
                        class="absolute top-4 left-1/2 -translate-x-1/2 z-[400] bg-blue-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md flex items-center gap-2">
                        <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div> GPS Aktif (Realtime)
                    </div>

                    <!-- DIRECTION PANEL -->
                <Transition name="slide-right">
                    <div v-if="showDirectionPanel"
                         class="absolute z-[500] bg-white flex flex-col shadow-2xl
                                bottom-0 left-0 right-0 w-full max-h-[42%] rounded-t-3xl
                                sm:top-0 sm:right-auto sm:bottom-auto sm:h-full sm:max-h-full
                                sm:w-[320px] sm:max-w-[85vw] sm:rounded-none sm:rounded-r-[1.5rem]">

                            <div class="bg-[#00C853] text-white px-4 py-4 flex-shrink-0">
                                <div class="flex items-center justify-between mb-3">
                                    <button @click="closeDirection"
                                        class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center hover:bg-white/30 transition">
                                        <i class="fas fa-arrow-left text-sm"></i>
                                    </button>
                                    <span class="font-bold text-sm">Rute Mengemudi</span>
                                    <div class="w-8"></div>
                                </div>

                                <div v-if="isLoadingRoute" class="text-center py-2">
                                    <i class="fas fa-spinner fa-spin text-2xl mb-1"></i>
                                    <p class="text-xs opacity-80">Menghitung rute...</p>
                                </div>

                                <div v-else-if="directionTargetStation" class="space-y-2">
                                    <p class="font-bold text-base leading-tight">{{ directionTargetStation.name }}</p>
                                    <div class="flex items-center gap-3 bg-white/20 rounded-xl px-3 py-2">
                                        <div class="text-center">
                                            <div class="text-xl font-black">{{ directionInfo.distance }}</div>
                                            <div class="text-[10px] opacity-80 uppercase font-semibold">Jarak</div>
                                        </div>
                                        <div class="w-px h-8 bg-white/40"></div>
                                        <div class="text-center">
                                            <div class="text-xl font-black">{{ directionInfo.duration }}</div>
                                            <div class="text-[10px] opacity-80 uppercase font-semibold">Estimasi</div>
                                        </div>
                                    </div>
                                    <button v-if="directionTargetStation.isBookable"
                                        @click="bookFromDirection(directionTargetStation)"
                                        class="w-full py-2.5 bg-white text-[#00C853] font-bold rounded-xl text-sm hover:bg-green-50 transition flex items-center justify-center gap-2 shadow">
                                        <i class="fas fa-ticket-alt"></i> Pesan Stasiun Ini
                                    </button>
                                </div>
                            </div>

                            <div class="flex-1 overflow-y-auto">
                                <div v-if="isLoadingRoute" class="p-4 space-y-3">
                                    <div v-for="n in 5" :key="n" class="flex items-center gap-3 animate-pulse">
                                        <div class="w-8 h-8 bg-gray-200 rounded-full flex-shrink-0"></div>
                                        <div class="flex-1">
                                            <div class="h-3 bg-gray-200 rounded mb-1.5 w-3/4"></div>
                                            <div class="h-2.5 bg-gray-100 rounded w-1/4"></div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="directionInfo.steps.length" class="divide-y divide-gray-100">
                                    <div v-for="(step, i) in directionInfo.steps" :key="i"
                                        class="flex items-start gap-3 px-4 py-3.5 hover:bg-gray-50 transition"
                                        :class="i === 0 ? 'bg-green-50/60' : ''">
                                        <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-base"
                                            :class="i === 0 ? 'bg-green-100 text-green-700'
                                                : i === directionInfo.steps.length - 1 ? 'bg-red-100 text-red-600'
                                                : 'bg-gray-100 text-gray-600'">
                                            {{ step.icon }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-800 leading-snug">{{ step.text }}</p>
                                            <p v-if="step.distance" class="text-xs text-gray-400 mt-0.5 font-medium">{{ step.distance }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="p-6 text-center text-gray-400">
                                    <i class="fas fa-map-marked-alt text-3xl mb-2 block"></i>
                                    <p class="text-sm">Tidak ada langkah navigasi</p>
                                </div>
                            </div>

                            <div v-if="directionTargetStation" class="flex-shrink-0 border-t border-gray-100 px-4 py-3 bg-white">
                                <a :href="`https://www.google.com/maps/dir/?api=1&destination=${directionTargetStation.lat},${directionTargetStation.lng}`"
                                    target="_blank"
                                    class="w-full py-3 border-2 border-gray-200 rounded-xl font-bold text-sm text-gray-700 hover:bg-gray-50 transition flex items-center justify-center gap-2">
                                    <img src="https://www.google.com/favicon.ico" class="w-4 h-4" alt="Google Maps" />
                                    Buka di Google Maps
                                </a>
                            </div>
                        </div>
                    </Transition>
                </div>

                <!-- STATION LIST -->
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">
                            Stasiun <span class="text-[#00C853]">Terdekat</span>
                        </h2>
                        <span class="text-xs sm:text-sm font-medium text-gray-500 bg-white px-3 py-1 rounded-full shadow-sm">
                            {{ nearestStations.length }} Ditemukan
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div v-for="station in nearestStations" :key="station.id"
                            class="bg-white p-5 rounded-3xl shadow-md hover:shadow-xl border-2 transition-all duration-300 flex flex-col relative cursor-pointer"
                            :class="selectedStationId === station.id
                                ? 'border-[#00C853] bg-green-50/30 ring-4 ring-[#00C853]/20'
                                : 'border-transparent'">

                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 leading-tight mb-1">{{ station.name }}</h2>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="fas fa-map-marker-alt mr-1.5 text-gray-400"></i>
                                        <span class="font-bold text-[#00C853]">{{ station.distance }}</span>
                                    </div>
                                </div>
                                <!-- FIX #5: badge tunggal, sinkron dengan isBookable, tidak ada lagi kemungkinan kontradiksi -->
                                <span v-if="station.badgeLabel"
                                    class="text-[10px] font-bold px-2 py-1 rounded-full flex-shrink-0 ml-2"
                                    :class="station.badgeClasses">
                                    {{ station.badgeLabel }}
                                </span>
                            </div>

                            <div class="space-y-3 mb-4 text-sm bg-gray-50 p-4 rounded-2xl border border-gray-100 mt-2">
                                <div class="flex items-center justify-between border-b border-gray-200 pb-2 mb-2">
                                    <span class="text-gray-600">Harga</span>
                                    <!-- FIX #1: harga per stasiun, bukan hardcode -->
                                    <span class="font-semibold text-gray-800">{{ station.displayPrice }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600"><i class="fas fa-bolt w-5 mr-2 text-yellow-500"></i>Tipe</span>
                                    <span class="font-semibold text-gray-800">{{ station.safeType }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600"><i class="fas fa-tachometer-alt w-5 mr-2 text-blue-500"></i>Daya</span>
                                    <span class="font-semibold text-gray-800">{{ station.safePower }}</span>
                                </div>
                                <div class="flex items-center justify-between bg-lime-100/50 p-2 rounded-xl mt-2 border border-lime-200">
                                    <span class="text-lime-800 font-medium text-xs">
                                        <i class="fas fa-clock mr-1 text-lime-600"></i>Est. Penuh (dari {{ userBatteryLevel }}%)
                                    </span>
                                    <span class="font-bold text-lime-900">{{ station.estimatedTimeToFull }}</span>
                                </div>
                            </div>

                            <div class="mt-auto flex gap-2">
                                <button @click.prevent.stop="showDirection(station)"
                                    class="flex-1 py-3 rounded-xl font-bold text-sm transition-all border-2 border-blue-500 text-blue-600 hover:bg-blue-50 active:scale-95 flex items-center justify-center gap-1.5">
                                    <i class="fas fa-route text-sm"></i>
                                    <span>Rute</span>
                                </button>
                                <button @click.prevent.stop="reserveStation(station.id)"
                                    :disabled="!station.isBookable"
                                    :class="['flex-[2] py-3 rounded-xl font-bold text-sm transition-all shadow-md flex items-center justify-center gap-2',
                                        station.isBookable
                                            ? 'bg-[#00C853] text-white hover:bg-[#00A142] hover:shadow-lg active:scale-95'
                                            : 'bg-gray-200 text-gray-400 cursor-not-allowed']">
                                    <i class="fas fa-ticket-alt"></i>
                                    <span>{{ station.isBookable ? 'Pesan' : 'Tutup' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <Footer />

        <!-- ═══════════════════════════════════════════
             MODAL KONFIRMASI BOOKING
        ═══════════════════════════════════════════ -->
        <Transition name="slide-up">
            <div v-if="showConfirmationModal && selectedStation"
                class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm flex items-end sm:items-center justify-center z-[5000] p-0 md:p-8"
                @click.self="cancelProcess">

                <div class="bg-white w-full max-h-[92vh] flex flex-col rounded-t-[2rem] sm:rounded-3xl shadow-2xl md:max-w-2xl relative">

                    <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex-shrink-0">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Konfirmasi Pesanan</h3>
                                <p class="text-gray-500 text-sm mt-0.5">{{ selectedStation.name }}</p>
                            </div>
                            <button @click="cancelProcess"
                                class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition text-gray-500">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto px-6 sm:px-8 py-5 space-y-5">

                        <!-- Port -->
                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Port Charging</label>
                            <div @click="toggleDropdown('port')"
                                @keydown.enter.prevent="toggleDropdown('port')"
                                @keydown.esc="activeDropdown = null"
                                tabindex="0"
                                role="combobox"
                                :aria-expanded="activeDropdown === 'port'"
                                aria-haspopup="listbox"
                                aria-label="Pilih port charging"
                                class="w-full p-3.5 border-2 rounded-xl flex justify-between bg-white cursor-pointer transition focus:outline-none focus:ring-2 focus:ring-[#00C853]/40"
                                :class="activeDropdown === 'port' ? 'border-[#00C853]' : 'border-gray-200'">
                                <span class="font-semibold text-gray-800">{{ selectedPort || 'Pilih Port' }}</span>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="activeDropdown === 'port' ? 'rotate-180' : ''"></i>
                            </div>
                            <div v-if="activeDropdown === 'port'"
                                ref="portListRef"
                                role="listbox"
                                aria-label="Daftar port charging"
                                class="absolute w-full mt-1 bg-white border-2 border-[#00C853] rounded-xl shadow-lg z-20 overflow-hidden">
                                <div v-for="port in availablePorts" :key="port.id"
                                    role="option"
                                    :aria-selected="selectedPort === port.value"
                                    tabindex="0"
                                    @click="selectOption('port', port.value)"
                                    @keydown="handleBookingDropdownKeydown($event, 'port', availablePorts)"
                                    class="p-3.5 hover:bg-green-50 focus:bg-green-50 focus:outline-none cursor-pointer font-medium text-gray-800 border-b border-gray-100 last:border-0">
                                    {{ port.label }}
                                </div>
                            </div>
                        </div>

                        <!-- Durasi -->
                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Target Energi / Durasi</label>
                            <div @click="toggleDropdown('duration')"
                                @keydown.enter.prevent="toggleDropdown('duration')"
                                @keydown.esc="activeDropdown = null"
                                tabindex="0"
                                role="combobox"
                                :aria-expanded="activeDropdown === 'duration'"
                                aria-haspopup="listbox"
                                aria-label="Pilih target energi atau durasi"
                                class="w-full p-3.5 border-2 rounded-xl flex justify-between bg-white cursor-pointer transition focus:outline-none focus:ring-2 focus:ring-[#00C853]/40"
                                :class="activeDropdown === 'duration' ? 'border-[#00C853]' : 'border-gray-200'">
                                <span class="font-semibold text-gray-800">
                                    {{ durationOptions.find(d => d.value === selectedDuration)?.label || 'Pilih Paket' }}
                                </span>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="activeDropdown === 'duration' ? 'rotate-180' : ''"></i>
                            </div>
                            <div v-if="activeDropdown === 'duration'"
                                ref="durationListRef"
                                role="listbox"
                                aria-label="Daftar target energi/durasi"
                                class="absolute w-full mt-1 bg-white border-2 border-[#00C853] rounded-xl shadow-lg z-20 overflow-hidden">
                                <div v-for="dur in durationOptions" :key="dur.value"
                                    role="option"
                                    :aria-selected="selectedDuration === dur.value"
                                    tabindex="0"
                                    @click="selectOption('duration', dur.value)"
                                    @keydown="handleBookingDropdownKeydown($event, 'duration', durationOptions)"
                                    class="p-3.5 hover:bg-green-50 focus:bg-green-50 focus:outline-none cursor-pointer border-b border-gray-100 last:border-0">
                                    <span class="font-bold text-gray-800 block">{{ dur.label }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Pilih Jam -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">
                                Pilih Jam Booking
                                <span class="text-red-400 normal-case font-normal ml-1">(wajib)</span>
                            </label>

                            <div v-if="isLoadingSlots" class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                                <div v-for="n in 10" :key="n" class="h-10 bg-gray-100 animate-pulse rounded-xl"></div>
                            </div>

                            <div v-else class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                                <button v-for="slot in timeSlots" :key="slot"
                                    type="button"
                                    :disabled="isSlotDisabled(slot)"
                                    @click="!isSlotDisabled(slot) && (selectedTimeSlot = slot)"
                                    :class="[
                                        'py-2.5 rounded-xl text-sm font-bold border-2 transition-all duration-150 relative',
                                        isSlotDisabled(slot)
                                            ? 'bg-gray-100 text-gray-300 border-gray-200 cursor-not-allowed ' + (isSlotBooked(slot) ? 'line-through' : 'opacity-60')
                                            : selectedTimeSlot === slot
                                                ? 'bg-[#00C853] text-white border-[#00C853] shadow-md scale-105'
                                                : 'bg-white text-gray-700 border-gray-200 hover:border-[#00C853] hover:text-[#00C853]'
                                    ]">
                                    {{ slot }}

                                    <!-- Badge jika Penuh -->
                                    <span v-if="isSlotBooked(slot)"
                                        class="absolute -top-1.5 -right-1.5 text-[9px] bg-red-400 text-white px-1 py-0.5 rounded-full font-black leading-none shadow-sm">
                                        PENUH
                                    </span>

                                    <!-- Badge jika Waktu Sudah Lewat / Terlalu Mepet -->
                                    <span v-else-if="isSlotPast(slot)"
                                        class="absolute -top-1.5 -right-1.5 text-[9px] bg-gray-400 text-white px-1 py-0.5 rounded-full font-black leading-none shadow-sm">
                                        LEWAT
                                    </span>
                                </button>
                            </div>

                            <p v-if="selectedTimeSlot" class="mt-2 text-xs text-[#00C853] font-semibold flex items-center gap-1">
                                <i class="fas fa-check-circle"></i>
                                Jam dipilih: {{ selectedTimeSlot }} WIB
                            </p>
                        </div>

                        <!-- Error -->
                        <div v-if="bookingError"
                            class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 flex items-start gap-2">
                            <i class="fas fa-exclamation-triangle mt-0.5 flex-shrink-0"></i>
                            <span>{{ bookingError }}</span>
                        </div>

                        <!-- Rincian Harga -->
                        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Rincian Harga</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Harga Charging</span>
                                    <span class="font-medium">{{ formatRupiah(priceBreakdown.price) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Biaya Layanan</span>
                                    <span class="font-medium">{{ priceBreakdown.service === 0 ? 'Gratis' : formatRupiah(priceBreakdown.service) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">PPN (11%)</span>
                                    <span class="font-medium">{{ formatRupiah(priceBreakdown.ppn) }}</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center pt-4 border-t border-gray-200 mt-3">
                                <span class="font-bold text-gray-900">Total Pembayaran</span>
                                <span class="font-black text-2xl text-[#00C853]">{{ formatRupiah(priceBreakdown.total) }}</span>
                            </div>
                        </div>

                        <!-- Info Pembayaran -->
                        <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex items-start gap-3">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i class="fas fa-qrcode text-white text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-blue-800">Pembayaran via QRIS / Paymentku</p>
                                <p class="text-xs text-blue-600 mt-0.5">Setelah konfirmasi, kamu akan diarahkan ke halaman pembayaran resmi.</p>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex gap-3 pb-2">
                            <button @click="cancelProcess"
                                class="flex-1 px-5 py-3.5 border-2 border-gray-200 rounded-xl font-bold text-gray-600 hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button @click="proceedToPayment"
                                :disabled="!selectedTimeSlot || isProcessingPayment"
                                :class="['flex-[2] px-5 py-3.5 rounded-xl font-bold shadow-md transition flex items-center justify-center gap-2',
                                    (selectedTimeSlot && !isProcessingPayment)
                                        ? 'bg-[#00C853] text-white hover:bg-[#00A142]'
                                        : 'bg-gray-200 text-gray-400 cursor-not-allowed']">
                                <template v-if="isProcessingPayment">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    Memproses...
                                </template>
                                <template v-else>
                                    <i class="fas fa-qrcode text-sm"></i>
                                    Lanjut ke Pembayaran
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ═══════════════════════════════════════════
             MODAL STRUK SUKSES (fallback tanpa payment link)
        ═══════════════════════════════════════════ -->
        <Transition name="receipt-pop">
            <div v-if="showReceiptModal"
                class="fixed inset-0 z-[7000] flex flex-col"
                style="pointer-events:none;">

                <!-- FIX #7: backdrop kini bisa diklik untuk menutup modal -->
                <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" style="pointer-events:auto;" @click="closeReceiptModal"></div>

                <div class="relative z-10 flex justify-center pt-5" style="pointer-events:none;">
                    <div class="bg-white/90 backdrop-blur-sm px-4 py-2 rounded-full shadow-lg flex items-center gap-2 border border-green-200">
                        <div class="w-2.5 h-2.5 bg-[#00C853] rounded-full animate-pulse"></div>
                        <span v-if="isLoadingReceiptRoute" class="text-sm font-semibold text-gray-700">
                            <i class="fas fa-spinner fa-spin text-[#00C853] mr-1"></i> Menghitung rute...
                        </span>
                        <span v-else-if="receiptRouteInfo.distance" class="text-sm font-semibold text-gray-700">
                            🗺 Rute:
                            <span class="text-[#00C853] font-black">{{ receiptRouteInfo.distance }}</span>
                            · <span class="text-gray-500">{{ receiptRouteInfo.duration }}</span>
                        </span>
                        <span v-else class="text-sm font-semibold text-gray-700">📍 Rute ditampilkan di peta</span>
                    </div>
                </div>

                <div class="relative z-10 mt-auto pb-6 px-4 flex justify-center" style="pointer-events:auto;" @click.stop>
                    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden relative"
                        style="box-shadow:0 -4px 40px rgba(0,0,0,0.18);">

                        <button @click="closeReceiptModal"
                            type="button"
                            aria-label="Tutup"
                            class="absolute top-3 right-3 z-10 w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition">
                            <i class="fas fa-times text-sm"></i>
                        </button>

                        <div class="bg-[#00C853] px-6 pt-5 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 bg-white rounded-full flex items-center justify-center text-2xl shadow">🎉</div>
                                <div>
                                    <h2 class="text-lg font-extrabold text-white leading-tight">Booking Berhasil!</h2>
                                    <p class="text-green-100 text-xs">Harap selesaikan pembayaran segera</p>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-5 space-y-3">
                            <div class="bg-orange-50 border-2 border-orange-200 rounded-2xl p-4 text-center">
                                <span class="text-[10px] text-gray-400 uppercase font-bold tracking-widest block mb-2">Kode Booking</span>
                                <div class="bg-white p-2 rounded-xl inline-block shadow-sm border border-orange-100 mb-3">
                                    <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${transactionCode}`"
                                        alt="QR Booking" class="w-32 h-32">
                                </div>
                                <div class="text-3xl font-mono font-black text-gray-900 tracking-widest">{{ transactionCode }}</div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-gray-50 rounded-xl px-3 py-2.5 border border-gray-100">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold block mb-0.5">Stasiun</span>
                                    <span class="text-sm font-bold text-gray-800 leading-tight block">{{ bookedStation?.name || '-' }}</span>
                                </div>
                                <div class="bg-gray-50 rounded-xl px-3 py-2.5 border border-gray-100">
                                    <span class="text-[10px] text-gray-400 uppercase font-bold block mb-0.5">Jam Booking</span>
                                    <span class="text-sm font-bold text-gray-800 flex items-center gap-1">
                                        <i class="fas fa-clock text-orange-400 text-xs"></i>
                                        {{ selectedTimeSlot }} WIB
                                    </span>
                                </div>
                            </div>

                            <div class="flex gap-2 pt-1">
                                <button v-if="bookedStation"
                                    @click="() => { showReceiptModal = false; showDirection(bookedStation); }"
                                    class="flex-1 py-3 border-2 border-[#00C853] text-[#00C853] rounded-xl font-bold text-sm hover:bg-green-50 transition flex items-center justify-center gap-1.5 active:scale-95">
                                    <i class="fas fa-route"></i> Lihat Rute
                                </button>
                                <button @click="closeReceiptModal"
                                    class="flex-[2] py-3 bg-[#00C853] text-white rounded-xl font-bold text-sm hover:bg-[#00A142] transition flex items-center justify-center gap-1.5 active:scale-95 shadow-md">
                                    <i class="fas fa-user-circle"></i> Lihat di Profil →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style>
@import url('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
.leaflet-container img  { max-width: none !important; max-height: none !important; }
.leaflet-container      { width: 100% !important; height: 100% !important; z-index: 10 !important; }
.leaflet-popup-content-wrapper { border-radius: 1rem !important; box-shadow: 0 8px 32px rgba(0,0,0,0.12) !important; }
.leaflet-popup-content { margin: 0 !important; }

@keyframes pulse-blue {
    0%   { box-shadow: 0 0 0 0   rgba(59,130,246,0.5); }
    70%  { box-shadow: 0 0 0 10px rgba(59,130,246,0); }
    100% { box-shadow: 0 0 0 0   rgba(59,130,246,0); }
}
</style>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active { transition: transform .3s ease, opacity .3s ease; }
.slide-up-enter-from,  .slide-up-leave-to      { transform: translateY(100%); opacity: 0; }

.slide-right-enter-active, .slide-right-leave-active { transition: transform .35s cubic-bezier(.4,0,.2,1), opacity .35s ease; }
@media (max-width: 639px) {
    .slide-right-enter-from,
    .slide-right-leave-to {
        transform: translateY(100%);
    }
}
.direction-panel { border-radius: 0 1.5rem 1.5rem 0; }

.receipt-pop-enter-active { transition: opacity .25s ease; }
.receipt-pop-leave-active  { transition: opacity .2s ease; }
.receipt-pop-enter-from, .receipt-pop-leave-to { opacity: 0; }
.receipt-pop-enter-active .bg-white { animation: slide-up-card .35s cubic-bezier(.34,1.56,.64,1) both; }
.receipt-pop-leave-active  .bg-white { animation: slide-down-card .2s ease both; }

@keyframes slide-up-card  { from { transform: translateY(60px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes slide-down-card { from { transform: translateY(0); opacity: 1; } to { transform: translateY(60px); opacity: 0; } }
</style>