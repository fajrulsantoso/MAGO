<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LowonganMagangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar posisi magang yang umum
        $posisiMagang = [
            'Web Developer', 'Mobile Developer', 'UI/UX Designer', 'Graphic Designer',
            'Digital Marketing', 'Content Writer', 'Social Media Specialist', 'Data Analyst',
            'Business Analyst', 'Human Resource', 'Finance', 'Accounting',
            'Customer Service', 'Product Management', 'Project Management', 'Quality Assurance',
            'Network Engineer', 'System Administrator', 'Database Administrator', 'IT Support',
            'Research and Development', 'Software Engineer', 'Frontend Developer', 'Backend Developer',
            'Full Stack Developer', 'DevOps Engineer', 'Cloud Engineer', 'Machine Learning Engineer',
            'Artificial Intelligence', 'Cyber Security', 'SEO Specialist', 'Video Editor',
            'Motion Graphic', 'Public Relations', 'Business Development', 'Sales and Marketing'
        ];

        // Daftar template untuk deskripsi
        $deskripsiTemplate = [
            "Bergabunglah dengan tim kami sebagai %s Intern dan dapatkan pengalaman berharga dalam pengembangan %s. Anda akan bekerja dalam lingkungan yang dinamis dan berkolaborasi dengan profesional berpengalaman.",
            "Kami mencari mahasiswa berbakat untuk posisi %s Intern. Program magang ini akan memberi Anda kesempatan untuk mengaplikasikan pengetahuan akademis Anda dalam lingkungan kerja yang nyata dan mendapatkan keterampilan praktis yang berharga.",
            "Kesempatan magang sebagai %s dengan perusahaan terkemuka di bidangnya. Anda akan mendapatkan pengalaman hands-on dan mentorship dari para profesional industri.",
            "Magang %s dengan fokus pada %s. Program ini dirancang untuk mempersiapkan mahasiswa dengan keterampilan yang dibutuhkan untuk berkarir di industri.",
            "Posisi magang %s tersedia untuk mahasiswa yang antusias untuk belajar dan berkontribusi dalam proyek-proyek yang menantang. Program ini menawarkan lingkungan belajar yang mendukung."
        ];

        // Daftar template kualifikasi
        $kualifikasiTemplate = [
            "• Mahasiswa aktif tingkat akhir dari jurusan %s atau bidang terkait\n• Memiliki pemahaman dasar tentang %s\n• Mampu bekerja dalam tim dan berkomunikasi dengan baik\n• Memiliki inisiatif tinggi dan kemauan belajar yang kuat\n• Menguasai %s",
            "• Sedang menempuh pendidikan di jurusan %s\n• Memiliki ketertarikan yang kuat pada bidang %s\n• Familiar dengan %s\n• Kemampuan analitis yang baik\n• Mampu bekerja dalam tekanan dan deadline",
            "• Mahasiswa S1/D4 jurusan %s semester 5 ke atas\n• Memiliki pengalaman proyek di bidang %s (nilai plus)\n• Terampil menggunakan %s\n• Kreatif dan memiliki kemampuan problem solving yang baik\n• Dapat bekerja secara mandiri maupun tim",
            "• Mahasiswa dari program studi %s\n• Memahami konsep dasar %s\n• Pengalaman dengan %s menjadi nilai tambah\n• Komunikatif dan proaktif\n• Mampu mengatur waktu dengan baik",
            "• Terdaftar sebagai mahasiswa jurusan %s\n• Pengetahuan tentang %s\n• Familiar dengan %s\n• Kemauan belajar hal baru\n• Kemampuan komunikasi yang baik secara lisan dan tulisan"
        ];

        // Daftar template tanggung jawab
        $tanggungJawabTemplate = [
            "• Membantu tim dalam pengembangan dan implementasi %s\n• Berpartisipasi dalam sesi brainstorming dan perencanaan proyek\n• Melakukan %s dan dokumentasi hasil\n• Menyiapkan laporan %s secara berkala\n• Berkolaborasi dengan tim lintas fungsi",
            "• Terlibat dalam proyek %s dari awal hingga implementasi\n• Melakukan riset tentang %s\n• Membantu dalam persiapan dan pelaksanaan %s\n• Menyusun dokumentasi teknis\n• Mengikuti meeting dan training yang diadakan",
            "• Mendukung tim dalam aktivitas %s sehari-hari\n• Menganalisis data %s\n• Membantu pengembangan %s\n• Membuat laporan hasil magang\n• Berkontribusi dalam peningkatan proses",
            "• Membantu mengembangkan strategi %s\n• Melakukan %s dan analisis hasil\n• Membuat konten untuk %s\n• Menyiapkan presentasi untuk stakeholders\n• Mengikuti perkembangan industri terkait",
            "• Terlibat dalam proyek %s dari konsep hingga pelaksanaan\n• Melakukan %s secara rutin\n• Berkoordinasi dengan tim %s\n• Membuat prototype atau model\n• Mendokumentasikan proses dan hasil kerja"
        ];

        // Daftar template benefit
        $benefitTemplate = [
            "• Pengalaman kerja langsung di industri %s\n• Sertifikat magang\n• Kesempatan networking dengan profesional\n• Lingkungan kerja yang supportive\n• %s",
            "• Mentor dedicated selama program magang\n• Pelatihan dan workshop\n• Exposure ke proyek %s nyata\n• Referensi kerja\n• %s",
            "• Lingkungan belajar yang interaktif\n• Jam kerja yang fleksibel\n• Kesempatan berkolaborasi dalam proyek %s\n• Feedback berkala dari supervisor\n• %s",
            "• Program mentoring dari profesional berpengalaman\n• Kesempatan dipekerjakan full-time setelah magang\n• Pengalaman bekerja dalam proyek %s yang berdampak\n• Akses ke resources perusahaan\n• %s",
            "• Exposure ke industri %s\n• Networking dengan profesional dan sesama intern\n• Career coaching\n• Sertifikat penyelesaian\n• %s"
        ];

        // Daftar bidang studi untuk kualifikasi
        $bidangStudi = [
            'Teknik Informatika', 'Sistem Informasi', 'Teknologi Informasi',
            'Ilmu Komputer', 'Teknik Elektro', 'Teknik Industri',
            'Manajemen', 'Akuntansi', 'Ekonomi Bisnis', 'Ilmu Komunikasi',
            'Desain Komunikasi Visual', 'Multimedia', 'Statistika',
            'Matematika', 'Bisnis Digital', 'Marketing'
        ];

        // Daftar teknologi/tools
        $teknologi = [
            'HTML/CSS/JavaScript', 'React.js', 'Vue.js', 'Angular', 'Node.js',
            'PHP', 'Laravel', 'CodeIgniter', 'Django', 'Flask',
            'Java', 'Spring Boot', 'Kotlin', 'Swift', 'Flutter', 'React Native',
            'Python', 'R', 'SPSS', 'SQL', 'NoSQL', 'MongoDB', 'Firebase',
            'AWS', 'GCP', 'Azure', 'Docker', 'Kubernetes', 'Git', 'Jira',
            'Adobe Photoshop', 'Adobe Illustrator', 'Figma', 'Sketch', 'Adobe XD',
            'Microsoft Office Suite', 'Google Analytics', 'SEO tools', 'Social media tools'
        ];

        // Daftar bidang fokus
        $bidangFokus = [
            'pengembangan website', 'pengembangan aplikasi mobile', 'analisis data',
            'desain UI/UX', 'desain grafis', 'digital marketing', 'content creation',
            'customer relationship management', 'human resource development',
            'financial analysis', 'business intelligence', 'networking', 'cybersecurity',
            'sistem informasi', 'sistem manajemen basis data', 'teknologi cloud',
            'artificial intelligence', 'machine learning', 'internet of things',
            'sustainability', 'quality assurance', 'product management', 'project management'
        ];

        // Daftar kegiatan
        $kegiatan = [
            'riset', 'analisis', 'pengujian', 'pengembangan', 'desain',
            'dokumentasi', 'implementasi', 'evaluasi', 'optimisasi',
            'maintenance', 'deployment', 'quality control', 'monitoring',
            'pemrograman', 'debugging', 'troubleshooting', 'review'
        ];

        // Daftar bonus benefit
        $bonusBenefit = [
            'Tunjangan transportasi', 'Tunjangan makan', 'Work from home option',
            'Laptop selama magang', 'Akses ke perpustakaan perusahaan',
            'Outing dan kegiatan team building', 'Kesempatan mengikuti training eksternal',
            'Kunjungan industri', 'Asuransi kesehatan', 'Ruang kerja yang nyaman',
            'Fasilitas olahraga', 'Flexible working hours'
        ];

        // Daftar data untuk lowongan magang
        $data = [];
        
        // Tanggal saat ini - menggunakan tanggal yang diberikan user
        $currentDate = Carbon::parse('2025-05-31 07:38:09');

        // Tahun-tahun untuk lowongan historis dan current
        $years = [2023, 2024, 2025];

        // Jumlah data yang akan dibuat
        $totalData = 200; // Lebih dari 100 data sesuai permintaan
        
        // Mengubah rasio status aktif vs selesai (70% aktif, 30% selesai)
        $activeRatio = 0.7;
        $activeCount = floor($totalData * $activeRatio);
        $completedCount = $totalData - $activeCount;
        
        for ($i = 1; $i <= $totalData; $i++) {
            // Tentukan tahun posting secara acak
            $year = $years[array_rand($years)];
            
            // Generate tanggal posting dan batas akhir yang realistis
            if ($year == 2025) {
                // Untuk tahun 2025, tanggal posting 1-4 bulan sebelum current date
                $postingDate = $currentDate->copy()->subMonths(rand(1, 4))->subDays(rand(0, 30));
                
                // Batas akhir 2-4 minggu setelah posting
                $batasAkhir = $postingDate->copy()->addWeeks(rand(2, 4));
            } else {
                // Untuk tahun lalu, buat lowongan dari tahun tersebut
                $month = rand(1, 12);
                $postingDate = Carbon::createFromDate($year, $month, rand(1, 28));
                $batasAkhir = $postingDate->copy()->addWeeks(rand(2, 4));
            }
            
            // Tentukan status secara acak, tapi pastikan jumlah aktif dan selesai sesuai rasio
            if ($i <= $activeCount) {
                $status = 'Aktif';
                
                // Untuk lowongan aktif, pastikan batas akhir setelah current date
                if ($batasAkhir->lessThan($currentDate)) {
                    $batasAkhir = $currentDate->copy()->addDays(rand(7, 30));
                }
            } else {
                $status = 'Selesai';
                
                // Untuk lowongan selesai, pastikan batas akhir sebelum current date
                if ($batasAkhir->greaterThan($currentDate)) {
                    $batasAkhir = $currentDate->copy()->subDays(rand(1, 30));
                }
            }
            
            // Pilih posisi magang secara acak
            $posisi = $posisiMagang[array_rand($posisiMagang)];
            
            // Generate nama perusahaan yang realistis
            $namaPerusahaan = "Perusahaan #" . ($i % 100 + 1);
            
            // Generate judul lowongan yang realistis
            $judul = "Lowongan Magang " . $posisi . " di " . $namaPerusahaan;
            
            // Generate deskripsi yang realistis
            $bidangAcak = $bidangFokus[array_rand($bidangFokus)];
            $deskripsiAwal = sprintf(
                $deskripsiTemplate[array_rand($deskripsiTemplate)],
                $posisi,
                $bidangAcak
            );
            
            $bidangStudiAcak = $bidangStudi[array_rand($bidangStudi)];
            $teknologiAcak = $teknologi[array_rand($teknologi)];
            $kualifikasi = sprintf(
                $kualifikasiTemplate[array_rand($kualifikasiTemplate)],
                $bidangStudiAcak,
                $bidangAcak,
                $teknologiAcak
            );
            
            $kegiatanAcak1 = $kegiatan[array_rand($kegiatan)];
            $kegiatanAcak2 = $kegiatan[array_rand($kegiatan)];
            $tanggungJawab = sprintf(
                $tanggungJawabTemplate[array_rand($tanggungJawabTemplate)],
                $bidangAcak,
                $kegiatanAcak1,
                $kegiatanAcak2
            );
            
            $bonusAcak = $bonusBenefit[array_rand($bonusBenefit)];
            $benefit = sprintf(
                $benefitTemplate[array_rand($benefitTemplate)],
                $bidangAcak,
                $bonusAcak
            );
            
            $fullDeskripsi = "DESKRIPSI:\n" . $deskripsiAwal . "\n\nKUALIFIKASI:\n" . $kualifikasi . "\n\nTANGGUNG JAWAB:\n" . $tanggungJawab . "\n\nBENEFIT:\n" . $benefit;
            
            // Tentukan sisa field
            $id_jenis_magang = rand(1, 5);
            $id_perusahaan = ($i % 100) + 1; // Memanfaatkan 100 perusahaan yang ada
            $id_daerah_magang = rand(1, 514); // Asumsi ada 514 daerah di Indonesia
            
            // Untuk periode, pilih yang sesuai dengan tanggal posting (recent)
            $periodeYear = $postingDate->year;
            $nextYear = $periodeYear + 1;
            $periodeName = $periodeYear . '/' . $nextYear . ' ';
            $periodeName .= ($postingDate->month > 6) ? 'Ganjil' : 'Genap';
            
            // Asumsi: menggunakan PeriodeSeeder yang sudah dibuat, id periode sesuai dengan urutan
            $baseYear = 1975; // Tahun awal di PeriodeSeeder
            $id_periode = (($periodeYear - $baseYear) * 2) + ($postingDate->month > 6 ? 1 : 2);
            
            $id_waktu_magang = rand(1, 2); // 1 = 3 Bulan, 2 = 6 Bulan
            $id_insentif = rand(1, 2); // 1 = Ada, 2 = Tidak Ada
            
            $data[] = [
                'id_lowongan' => $i,
                'id_jenis_magang' => $id_jenis_magang,
                'id_perusahaan' => $id_perusahaan,
                'id_daerah_magang' => $id_daerah_magang,
                'judul_lowongan' => $judul,
                'deskripsi_lowongan' => $fullDeskripsi,
                'tanggal_posting' => $postingDate->format('Y-m-d'),
                'batas_akhir_lamaran' => $batasAkhir->format('Y-m-d'),
                'status' => $status,
                'id_periode' => $id_periode,
                'id_waktu_magang' => $id_waktu_magang,
                'id_insentif' => $id_insentif,
                'created_at' => $postingDate->format('Y-m-d H:i:s'),
                'updated_at' => $postingDate->format('Y-m-d H:i:s'),
            ];
        }
        
        // Acak data setelah dibuat untuk memastikan distribusi status random
        shuffle($data);
        
        // Reassign ID setelah pengacakan
        for ($index = 0; $index < count($data); $index++) {
            $data[$index]['id_lowongan'] = $index + 1;
        }
        
        // Memasukkan data ke database
        DB::table('t_lowongan_magang')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data lowongan magang');
        $this->command->info('Jumlah status Aktif: ' . $activeCount . ' (' . ($activeRatio*100) . '%)');
        $this->command->info('Jumlah status Selesai: ' . $completedCount . ' (' . ((1-$activeRatio)*100) . '%)');
    }
}