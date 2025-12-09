<template>
  <div class="min-h-screen flex flex-col bg-gray-50">
    <Navbar />

    <main class="flex-grow pt-24">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl p-6 shadow-2xl w-full max-w-md mx-auto transform transition-all duration-300">
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Struk Pembayaran</h3>
              <p class="text-sm text-gray-500">E-VOLT Charging</p>
            </div>
            <button @click="goBack" class="text-gray-400 hover:text-gray-600 text-2xl font-light">&times;</button>
          </div>

          <div class="border-t border-gray-100 pt-4 space-y-3 text-sm text-gray-700">
            <div>
              <div class="font-medium">Nomor Booking</div>
              <div class="text-lg font-semibold text-[#00C853]">{{ station.bookingNumber }}</div>
            </div>

            <div>
              <div class="font-medium">Lokasi</div>
              <div>{{ station.name }}</div>
              <div class="text-sm text-gray-500">{{ station.location }}</div>
            </div>

            <div class="space-y-2">
              <div class="flex justify-between">
                <div>
                  <div class="font-medium">Tanggal</div>
                  <div class="text-sm">{{ formatDate(station.bookingTime) }}</div>
                </div>
                <div>
                  <div class="font-medium">Jam</div>
                  <div class="text-sm">{{ formatTime(station.bookingTime) }}</div>
                </div>
              </div>
              <div class="flex justify-between">
                <div>
                  <div class="font-medium">Durasi</div>
                  <div class="text-sm">{{ station.duration }}</div>
                </div>
                <div>
                  <div class="font-medium">Jenis Charger</div>
                  <div class="text-sm">{{ station.chargerType }} • {{ station.power }}</div>
                </div>
              </div>
            </div>

            <div class="flex justify-between items-center bg-[#E6FFB3] p-3 rounded-lg mt-2">
              <div class="text-sm font-medium">Total Harga</div>
              <div class="text-lg font-semibold">{{ total }}</div>
            </div>

            <div class="flex flex-col items-center pt-4">
              <img :src="qrUrl" alt="QR Struk" class="w-40 h-40 rounded-md shadow-inner mb-2">
              <div class="text-xs text-gray-500 text-center">Tunjukkan QR ini saat tiba di lokasi untuk memulai sesi
                charging</div>
            </div>
          </div>

          <div class="pt-6 space-y-3">
            <button @click="doDownload" type="button"
              class="w-full py-3 bg-[#00C853] text-white font-medium rounded-xl hover:bg-[#00A142] transition duration-300 shadow-md">
              Unduh PDF
            </button>
            <button @click="goBack" type="button"
              class="w-full py-3 text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50">
              Kembali
            </button>
          </div>
        </div>
      </div>
    </main>

    <Footer />
  </div>
</template>

<script setup>
import { computed } from 'vue';

// Get data from URL params (passed from MapResults.vue)
const urlParams = new URLSearchParams(window.location.search);
const stationParam = urlParams.get('station');
const totalParam = urlParams.get('total');

const station = stationParam ? JSON.parse(stationParam) : {
  bookingNumber: 'BK1001',
  name: 'SPKLU Nagoya Hill',
  location: 'Nagoya Hill, Batam',
  chargerType: 'DC Fast',
  power: '80 kW',
  bookingTime: '2025-10-22 12:00',
  duration: '60 menit'
};

const total = totalParam || 'Rp 50.000';

const goBack = () => {
  // navigasi kembali ke halaman map-results
  window.location.href = '/map-results';
};



const doDownload = () => {
  const qr = qrUrl.value || '';

  const html = `<!doctype html>
  <html>
  <head>
    <meta charset="utf-8" />
    <title>Struk Pembayaran - E-VOLT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
      @page { margin: 0; }
      :root{
        --bg:#f3f4f6; --card:#ffffff; --muted:#6b7280; --accent:#00C853; --accent-dark:#0b6b3f; --dark:#0f1724;
        --maxw:760px; --radius:10px;
      }
      html,body{height:100%;margin:0;padding:0;background:var(--bg);font-family:Inter,system-ui,Arial;color:#111827}
      body { padding:12mm 12mm; }
      .wrap{min-height:100vh;display:flex;align-items:flex-start;justify-content:center;padding-top:20px}
      .card{width:100%;max-width:var(--maxw);background:var(--card);border-radius:var(--radius);box-shadow:0 6px 18px rgba(15,23,36,0.06);padding:28px 32px;border:1px solid rgba(15,23,36,0.04)}
      .header{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}
      .brand{display:flex;align-items:center;gap:10px}
      .brand .brand-text{font-weight:800;font-size:20px;color:#111827}
      .booking-code{margin-top:18px;font-weight:700;color:#111827;font-size:18px;letter-spacing:0.6px}
      .grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:18px;align-items:start}
      .info-label{font-weight:600;color:#111827;font-size:13px;margin-bottom:6px}
      .info-sub{color:var(--muted);font-size:13px}
      .divider{height:1px;background:linear-gradient(90deg,transparent,#eef2f6,transparent);margin:18px 0;border-radius:2px}

      /* Total Harga: lebih kontras, bar hijau tegas dan teks jelas */
      .total-row{
        display:flex;
        justify-content:space-between;
        align-items:center;
        background: linear-gradient(90deg, rgba(14,165,100,0.10), rgba(230,255,179,0.85));
        border:1px solid rgba(0,200,83,0.12);
        padding:14px 16px;
        border-radius:10px;
        margin-top:14px;
        font-weight:800;
        color:var(--accent-dark);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.6);
      }
      .total-row .label { font-size:14px; color:#064e3a; font-weight:700; }
      .total-row .price { font-size:18px; color:var(--accent-dark); }

      .qr-wrap{display:flex;flex-direction:column;align-items:center;margin-top:18px}
      .qr-wrap img{width:190px;height:190px;object-fit:cover;border-radius:8px;border:1px solid #eee}
      .note{font-size:12px;color:var(--muted);margin-top:10px;text-align:center}

      /* Footer: ubah latar menjadi abu-abu dan teks menjadi gelap agar terbaca */
      .site-footer{
        margin-top:24px;
        background:#e6e9ec;               /* abu-abu terang */
        color:#1f2937;                    /* teks gelap untuk keterbacaan */
        border-radius:8px;
        padding:18px 22px;
        overflow:hidden;
      }
      .footer-sep { height:1px; background:#d1d5db; margin:12px 0 18px; border-radius:1px; } /* thin separator above footer */
      .footer-grid{display:grid;grid-template-columns:1fr 1fr 1fr;grid-template-rows:auto auto;gap:18px;}
      .footer-brand{grid-column:1 / -1;}
      .footer-brand .title{
        font-weight:800;
        font-size:16px;
        color:#0b1220;
      }
      .footer-brand .desc{color:#374151;font-size:13px;max-width:320px}
      .footer-col h4{ color:#111827; margin:0 0 8px 0; font-size:13px; }
      .footer-col ul{list-style:none;padding:0;margin:0}
      .footer-col li{margin:6px 0}
      .footer-col a{color:#0b1220;text-decoration:none;font-size:13px}
      .footer-bottom{
        display:flex;justify-content:space-between;align-items:center;
        border-top:1px solid rgba(15,23,36,0.06);
        padding-top:12px;margin-top:14px;
        color:#475569;font-size:12px;
      }

      @media (max-width:640px){ .grid{grid-template-columns:1fr; } }
      @media print{
        body{background:#fff;padding:6mm}
        .wrap{padding:0}
        .card{box-shadow:none;border:0;padding:10px;max-width:100%}
        .site-footer{background:#e6e9ec;color:#1f2937;padding:12px;border-radius:0}
      }
      @media print { .print-instruction { display:none; } }
      .print-instruction { font-size:12px;color:#7a8690;margin-bottom:8px; }
    </style>
  </head>
  <body>
    <div class="wrap">
      <div class="card" role="article" aria-label="Struk Pembayaran">
        <div class="print-instruction">Jika Anda melihat header/footers (tanggal/judul) di hasil cetak, non‑aktifkan "Headers and footers" di dialog Print browser.</div>

        <div class="header">
          <div class="brand">
            <div class="brand-text" style="color:var(--accent)"><span style="color:var(--accent);font-weight:900">E-</span><span style="color:#111827">VOLT</span></div>
            <div style="margin-left:8px;color:var(--muted);font-size:13px">Struk Pembayaran</div>
          </div>
        </div>

        <div class="booking-code">Kode Booking — ${station.bookingNumber}</div>

        <div class="grid" style="margin-top:12px">
          <div>
            <div class="info-label">Lokasi Charging</div>
            <div class="info-sub" style="font-weight:600">${station.name}</div>
            <div class="info-sub" style="margin-top:6px">${station.location}</div>
          </div>

          <div style="text-align:right">
            <div class="info-label">Durasi</div>
            <div class="info-sub" style="font-weight:600">${station.duration}</div>

            <div style="height:12px"></div>

            <div class="info-label">Jenis Charger</div>
            <div class="info-sub" style="font-weight:600">${station.chargerType} • ${station.power}</div>
          </div>
        </div>

        <div class="divider" aria-hidden="true"></div>

        <div class="total-row" role="status">
          <div class="label">Total Harga</div>
          <div class="price">${total}</div>
        </div>

        <div class="qr-wrap">
          <img src="${qr}" alt="QR Struk" />
          <div class="note">Tunjukkan QR ini saat tiba di lokasi untuk memulai sesi charging</div>
        </div>

        <div style="margin-top:16px;color:var(--muted);font-size:13px">
          Informasi tambahan dan syarat dapat dilihat di aplikasi.
        </div>

        <div class="divider" aria-hidden="true"></div>

        <!-- thin separator above footer -->
        <div class="footer-sep" aria-hidden="true"></div>

        <footer class="site-footer" role="contentinfo" aria-label="Footer">
          <div class="footer-grid">
            <div class="footer-brand">
              <div class="title"><strong>E-</strong>VOLT</div>
              <div class="desc">Platform koneksi cerdas untuk pengguna kendaraan listrik dan stasiun pengisian daya.</div>
            </div>

            <div class="footer-col">
              <h4>Layanan</h4>
              <ul>
                <li><a href="#">Cari Stasiun</a></li>
                <li><a href="#">Reservasi</a></li>
                <li><a href="#">Langganan</a></li>
                <li><a href="#">Aplikasi Mobile</a></li>
              </ul>
            </div>

            <div class="footer-col">
              <h4>Perusahaan</h4>
              <ul>
                <li><a href="#">Tentang Kami</a></li>
                <li><a href="#">Karir</a></li>
                <li><a href="#">Pers</a></li>
                <li><a href="#">Blog</a></li>
              </ul>
            </div>

            <div class="footer-col">
              <h4>Hukum</h4>
              <ul>
                <li><a href="#">Kebijakan Privasi</a></li>
                <li><a href="#">Syarat Penggunaan</a></li>
                <li><a href="#">FAQ</a></li>
                <li><a href="#">Bantuan</a></li>
              </ul>
            </div>
          </div>

          <div class="footer-bottom">
            <div>&copy; ${new Date().getFullYear()} E-VOLT. All rights reserved.</div>
            <!-- versi dihapus sesuai permintaan -->
          </div>
        </footer>
      </div>
    </div>
  </body>
  </html>`;

  const w = window.open('', '_blank');
  if (!w) {
    alert('Popup diblokir. Izinkan pop-up untuk mengunduh struk.');
    return;
  }
  w.document.open();
  w.document.write(html);
  w.document.close();

  // beri waktu agar QR ter-load lalu munculkan print dialog
  setTimeout(() => {
    try { w.focus(); w.print(); } catch (e) { }
  }, 700);
};

// simple qr generator using public API (placeholder)
const qrUrl = computed(() => {
  const data = `STRUK|${station.bookingNumber}|${station.name}|${total}`;
  return `https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=${encodeURIComponent(data)}`;
});

const formatDate = (dateTime) => {
  if (!dateTime) return '';
  const [date] = String(dateTime).split(' ');
  return date;
};

const formatTime = (dateTime) => {
  if (!dateTime) return '';
  const [, time] = String(dateTime).split(' ');
  return time || '';
};
</script>

<style scoped>
/* memastikan modal/komponen cocok untuk cetak */
@media print {
  body * {
    visibility: hidden;
  }

  .print-root,
  .print-root * {
    visibility: visible;
  }

  .print-root {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
