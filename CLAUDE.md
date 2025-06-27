# Claude Code Prompt - Sistem Surat Jalan Management & Tracking Truck

## Context & Project Overview
Saya sedang mengembangkan sistem **Surat Jalan Management dan Tracking Truck** yang bersifat monolitik dengan arsitektur yang terbagi menjadi 2 interface utama:
- `/app` - Interface untuk pihak management 
- `/driver` - Interface khusus untuk sopir truck

## Tech Stack & Requirements
- **Backend**: PHP 8.x + Laravel 12.x
- **Frontend**: Livewire 3.x + Tailwind CSS 4.x
- **UI Components**: Mary UI v2 + Daisy UI v5
- **Real-time Updates**: Livewire Polling System
- **Architecture**: Monolithic Laravel Application

## Dokumentasi Referensi Wajib
Sebelum memberikan solusi atau membuat kode, WAJIB merujuk ke dokumentasi official dan sumber terpercaya berikut:

### Dokumentasi Official:
1. **Laravel 12.x**: https://laravel.com/docs/12.x/
2. **Livewire 3.x**: https://livewire.laravel.com/
3. **Tailwind CSS 4.x**: https://tailwindcss.com/  
4. **Daisy UI 5.x**: https://daisyui.com/
5. **PHP 8.x**: https://www.php.net/
6. **Mary UI 2.x**: https://mary-ui.com/
7. **Leaflet JS**: https://leafletjs.com/reference.html

### Sumber Tambahan Wajib:
- **GitHub Issues & Discussions** dari masing-masing library
- **Stack Overflow** untuk troubleshooting spesifik
- **Laravel Community Forums**: https://laracasts.com/discuss
- **Reddit Communities**: r/laravel, r/PHP, r/webdev
- **MDN Web Docs**: https://developer.mozilla.org/ (untuk JavaScript/Maps)

### Proses Research Wajib:
1. **Cek dokumentasi official terlebih dahulu**
2. **Cari di GitHub Issues** jika ada masalah spesifik
3. **Search di Stack Overflow** untuk solusi yang sudah teruji
4. **Konsultasi forum komunitas** untuk best practices
5. **Verifikasi compatibility** antar library yang digunakan

## Development Workflow Requirements

### 1. Research First Approach
- **SELALU** cek dokumentasi official terlebih dahulu sebelum coding
- Gunakan pattern dan best practices yang sesuai dengan versi yang digunakan
- Verifikasi compatibility antar library yang digunakan

### 2. Livewire 3 Polling Implementation
- Implementasikan real-time tracking menggunakan Livewire 3 polling
- Optimize polling frequency untuk menghindari overload server
- Gunakan wire:poll dengan interval yang tepat sesuai kebutuhan

### 3. Dual Interface Architecture
- Pisahkan routing untuk `/app` (management) dan `/driver` (sopir)
- Implementasikan middleware yang sesuai untuk setiap interface
- Pastikan UI/UX yang berbeda sesuai target user

### 4. Code Quality Standards
- Follow Laravel coding conventions dan best practices
- Gunakan Livewire 3 component patterns yang tepat
- Implementasikan proper error handling dan validation
- Optimize database queries (N+1 problem prevention)

## Instruksi Khusus untuk Claude Code

### WAJIB: Selalu Research Dulu Sebelum Coding
1. **Baca dokumentasi official** untuk pattern yang benar
2. **Cari di GitHub Issues** jika menghadapi error atau bug
3. **Search Stack Overflow** untuk solusi yang sudah terbukti
4. **Konsultasi forum komunitas** untuk best practices terbaru
5. **Verifikasi di MDN** untuk fitur JavaScript/Maps yang digunakan

### Ketika Membuat Components:
1. **Research dokumentasi lengkap** untuk pattern yang benar
2. **Gunakan Livewire 3 syntax yang tepat** (bukan versi lama)
3. **Implementasi Mary UI + Daisy UI** sesuai dokumentasi terbaru
4. **Follow Laravel 12 conventions** untuk routing, middleware, dll
5. **Untuk Maps: Gunakan Leaflet JS** dengan dokumentasi resminya

### Ketika Implementasi Features:
1. **Cek compatibility** di dokumentasi dan GitHub issues
2. **Optimasi real-time updates** dengan Livewire polling terbaru
3. **Pisahkan concerns** antara management dan driver interface
4. **Follow security best practices** sesuai dokumentasi Laravel
5. **Untuk GPS/Maps: Research Leaflet + Laravel integration** di forum

### Ketika Debugging:
1. **Rujuk dokumentasi official** untuk troubleshooting
2. **Cari di Stack Overflow** dengan keyword spesifik
3. **Check GitHub Issues** dari library yang bermasalah
4. **Konsultasi forum Laravel/Livewire** untuk solusi komunitas
5. **Berikan solusi alternatif** jika ada conflict antar library

### Komunikasi dalam Bahasa Indonesia:
- **Selalu gunakan Bahasa Indonesia** dalam semua penjelasan
- **Berikan komentar kode dalam Bahasa Indonesia**
- **Jelaskan langkah-langkah dengan detail** dan mudah dipahami
- **Sertakan contoh implementasi** yang praktis dan aplikatif
- **Berikan alternatif solusi** jika ada beberapa pendekatan

## Role-Based Access Control (7 Roles)

### Role Hierarchy & Permissions:
1. **ROLE_MANAGER** (Highest Authority)
   - Full system access
   - Can create permissions
   - Can manage all users and data
   - Track all driver GPS locations

2. **ROLE_ADMIN** 
   - Can create users (except permissions)  
   - System administration access
   - Cannot create permissions
   - Track all driver GPS locations

3. **ROLE_PETUGAS_LAPANGAN** (Field Officer)
   - Create new surat jalan
   - Fill surat jalan items/details
   - Select drivers (only those with GPS active)
   - Submit for verification

4. **ROLE_PETUGAS_RUANGAN** (Office Staff)
   - Verify surat jalan from field officers
   - Add client data (name, address, destination)
   - Print physical surat jalan
   - Hand over to drivers

5. **ROLE_DRIVER**
   - Must have GPS active to be selectable
   - Cannot leave loading area without physical surat jalan
   - Start tracking after receiving surat jalan
   - Deliver to petugas gudang

6. **ROLE_PETUGAS_GUDANG** (Warehouse Staff)
   - Scan barcode on surat jalan (change status to "arrived")
   - Record unloading completion
   - Note discrepancies (kurang/lebih) if any
   - Change status to "completed"
   - Release driver back to loading area
   - Track driver GPS location

7. **ROLE_CLIENT**
   - View only assigned surat jalan
   - Track delivery progress (GPS location tracking)
   - Read-only access

### Business Workflow Logic:
```
Petugas Lapangan → Create Surat Jalan + Select GPS-Active Driver
    ↓
Petugas Ruangan → Verify + Add Client Data + Print Physical Document  
    ↓
Driver → Receive Physical Surat Jalan (Required to Leave Loading Area)
    ↓  
Driver → Start GPS Tracking + Delivery Process
    ↓
Petugas Gudang → Scan Barcode (Status: Arrived) + Unloading Process
    ↓
Petugas Gudang → Record Discrepancies (if any) + Status: Completed
    ↓
Driver → Return to Loading Area (Available for Next Assignment)
```

### GPS Tracking Access Control:
**Only 4 Roles Can Track Driver Location:**
- ROLE_MANAGER (all drivers)
- ROLE_ADMIN (all drivers) 
- ROLE_CLIENT (only their assigned driver)
- ROLE_PETUGAS_GUDANG (drivers coming to their warehouse)

### Critical Business Rules:
- **No Future Scheduling**: No next week/tomorrow delivery booking
- **Immediate Assignment**: Once selected, driver must deliver immediately  
- **GPS Requirement**: Drivers must have active GPS to be selectable
- **Physical Document Control**: Drivers cannot leave without printed surat jalan
- **Barcode Scanning**: Status changes only via barcode scan at destination
- **Discrepancy Recording**: Mandatory check for quantity differences
- **Real-time Status**: Live tracking throughout entire process

## Key Focus Areas:
- ✅ 7-Role Permission System with strict hierarchy
- ✅ Multi-stage Surat Jalan workflow with verification steps  
- ✅ GPS-based Driver selection and tracking
- ✅ Physical document control with barcode scanning
- ✅ Real-time status updates via Livewire polling
- ✅ Discrepancy management and reporting
- ✅ Loading area access control based on surat jalan status

## Expected Output Quality:
- **Kode yang mengikuti dokumentasi official terbaru**
- **Implementasi optimal untuk real-time tracking dengan Leaflet JS**
- **Pemisahan yang jelas antara interface management dan driver**
- **Error handling yang proper dan user feedback yang informatif**
- **Mobile-first approach untuk interface driver**
- **Penjelasan dalam Bahasa Indonesia yang mudah dipahami**
- **Komentar kode dalam Bahasa Indonesia**
- **Dokumentasi langkah implementasi yang detail**

## Proses Kerja yang Diharapkan:
1. **Research Phase**: Cari informasi dari dokumentasi dan forum
2. **Planning Phase**: Buat rencana implementasi berdasarkan findings
3. **Implementation Phase**: Koding dengan mengikuti best practices
4. **Testing Considerations**: Berikan panduan testing yang sesuai
5. **Documentation**: Jelaskan setiap langkah dalam Bahasa Indonesia

**PENTING**: 
- Jangan membuat asumsi tentang implementasi
- Selalu rujuk ke dokumentasi official dan forum komunitas
- Pastikan compatibility dengan tech stack yang digunakan  
- Berikan penjelasan dalam Bahasa Indonesia yang mudah dipahami
- Sertakan contoh kode yang praktis dan aplikatif
