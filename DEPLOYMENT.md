# Panduan Deployment: Laravel 12 (Vue/Inertia/Vite) dengan Docker/Podman

Panduan ini berisi langkah-langkah untuk mendeploy project **Evolt-App** menggunakan Docker/Podman ke VPS.
Arsitektur yang digunakan adalah **Single Container (PHP-FPM + Nginx di dalam container)** dengan reverse proxy Nginx Host dan Database MySQL berjalan langsung di Host VPS Anda (di luar container).

---

## Prasyarat di VPS

Sebelum memulai, pastikan VPS Anda sudah terpasang:

1. **Nginx** (di VPS Host)
2. **MySQL/MariaDB** (di VPS Host)
3. **Docker** atau **Podman**

---

## Langkah Deployment (Setelah Clone)

### Langkah 1: Masuk ke Folder Project

```bash
cd evolt-app
```

### Langkah 2: Setup File `.env`

1. Salin template file `.env`:
    ```bash
    cp .env.example .env
    ```
2. Edit file `.env` (misal menggunakan `nano .env`):
    - Ubah `APP_ENV=production` and `APP_DEBUG=false`.
    - Atur `APP_URL=https://evolt.my.id`.
    - **Database:** Hubungkan ke MySQL VPS Anda. Karena kita akan menggunakan mode `--network host`, atur host ke `127.0.0.1`:
        ```env
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=nama_database_vps
        DB_USERNAME=username_database_vps
        DB_PASSWORD=password_database_vps
        ```

### Langkah 3: Generate Laravel APP_KEY

Laravel membutuhkan key unik untuk keamanan enkripsi data.

1. Terlebih dahulu build image aplikasi (Langkah 4 di bawah).
2. Setelah build sukses, jalankan perintah ini untuk menghasilkan key:
    ```bash
    podman run --rm evolt-app php artisan key:generate --show
    # Catatan: ganti 'podman' dengan 'docker' jika Anda menggunakan Docker
    ```
3. Salin kode `base64:...` yang tampil di layar, lalu masukkan ke `.env` pada baris `APP_KEY=`.

### Langkah 4: Build Docker/Podman Image

Jalankan perintah build untuk mengompilasi frontend asset (Vite/Vue) dan menginstal dependencies PHP ke dalam image:

```bash
docker build -t evolt-app .
# Ganti dengan 'docker build -t evolt-app .' jika memakai Docker
```

### Langkah 5: Jalankan Container Aplikasi

Jalankan container menggunakan mode **host network** agar dapat langsung mengakses database lokal VPS (`127.0.0.1`) dan meneruskan port `8081` langsung ke host:

```bash
docker run -d \
  --name evolt-app \
  --network host \
  --restart unless-stopped \
  --env-file .env \
  evolt-app
# Ganti dengan 'docker run ...' jika memakai Docker
```

> **⚠️ Catatan Penting Tentang Migrasi Database:**
>
> - Perintah di atas menjalankan aplikasi tanpa migrasi otomatis (direkomendasikan jika database sudah berisi tabel/data).
> - Jika ini adalah **database baru (fresh)** dan Anda ingin migrasi berjalan otomatis saat container start, tambahkan flag `-e RUN_MIGRATIONS=true` saat menjalankan container.
> - Jika database sudah ada tapi gagal migrasi karena kolom duplikat, **jangan** gunakan flag `RUN_MIGRATIONS=true` agar container tidak crash-loop. Jalankan migrasi secara manual via CLI container jika diperlukan (lihat bagian bawah).

---

## Konfigurasi Nginx di VPS Host (Reverse Proxy)

Sekarang hubungkan Nginx di VPS Anda dengan Nginx di dalam container (yang berjalan di port `8081` localhost).

1. Buat file konfigurasi block server baru:
    ```bash
    sudo nano /etc/nginx/sites-available/evolt
    ```
2. Isi dengan konfigurasi berikut:

    ```nginx
    server {
        listen 80;
        server_name evolt.my.id www.evolt.my.id;

        location / {
            proxy_pass http://127.0.0.1:8081;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    }
    ```

3. Aktifkan konfigurasi dan muat ulang Nginx:
    ```bash
    sudo ln -s /etc/nginx/sites-available/evolt /etc/nginx/sites-enabled/
    sudo nginx -t
    sudo systemctl reload nginx
    ```

---

## Konfigurasi SSL HTTPS (Opsional - Sangat Direkomendasikan)

Karena Nginx berjalan langsung di VPS Host, Anda bisa menggunakan **Certbot** untuk memasang Let's Encrypt secara gratis:

```bash
sudo apt update && sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d evolt.my.id -d www.evolt.my.id
```

Certbot akan mendeteksi block server Anda, mendaftarkan sertifikat SSL, dan otomatis mengalihkan semua traffic HTTP (port 80) ke HTTPS (port 443).

---

## Perintah Manajemen Tambahan

### Menjalankan Migrasi Secara Manual

Jika sewaktu-waktu ada perubahan database baru dan Anda tidak ingin merestart container, jalankan perintah:

```bash
podman exec -it evolt-app php artisan migrate --force
```

### Melihat Log Container

Untuk memantau log aplikasi (Nginx & PHP-FPM di dalam container):

```bash
podman logs -f evolt-app
```

### Menghentikan Aplikasi

```bash
podman stop evolt-app
podman rm evolt-app
```

```
Log Laravel (detail error internal Laravel):

bash
docker exec -it evolt-app tail -n 100 storage/logs/laravel.log
Tambahkan -f untuk memantau langsung (real-time):

bash
docker exec -it evolt-app tail -f storage/logs/laravel.log
Log Container (Nginx & PHP-FPM stdout/stderr):

bash
docker logs -f evolt-app
```
