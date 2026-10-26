-- Seed data for Schema.sql
-- Semua password menggunakan bcrypt hash dari "password"
-- Semua file path menggunakan format storage/uploads/<purpose>/<entity_id>_<count>.<ext>

SET NAMES utf8mb4;
START TRANSACTION;

-- Departments
INSERT INTO `Departments` (`DepartmentId`, `Name`) VALUES
('11111111-1111-1111-1111-111111111111', 'Fakultas Teknologi Informasi'),
('22222222-2222-2222-2222-222222222222', 'Fakultas Ekonomi dan Bisnis'),
('33333333-3333-3333-3333-333333333333', 'Fakultas Ilmu Sosial dan Ilmu Politik');

-- StudyPrograms
INSERT INTO `StudyPrograms` (`StudyProgramId`, `DepartmentId`, `Name`) VALUES
('44444444-4444-4444-4444-444444444444', '11111111-1111-1111-1111-111111111111', 'Teknik Informatika'),
('55555555-5555-5555-5555-555555555555', '11111111-1111-1111-1111-111111111111', 'Sistem Informasi'),
('66666666-6666-6666-6666-666666666666', '22222222-2222-2222-2222-222222222222', 'Manajemen'),
('77777777-7777-7777-7777-777777777777', '33333333-3333-3333-3333-333333333333', 'Hubungan Internasional');

-- Programs
INSERT INTO `Programs` (`ProgramId`, `StudyProgramId`, `Name`) VALUES
('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', '44444444-4444-4444-4444-444444444444', 'Reguler'),
('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', '55555555-5555-5555-5555-555555555555', 'International Track'),
('cccccccc-cccc-cccc-cccc-cccccccccccc', '66666666-6666-6666-6666-666666666666', 'Reguler'),
('dddddddd-dddd-dddd-dddd-dddddddddddd', '77777777-7777-7777-7777-777777777777', 'Reguler');

-- DocumentTypes
INSERT INTO `DocumentTypes` (`DocTypeId`, `Name`, `MaxSize`, `IsMandatory`) VALUES
('e0000000-0000-0000-0000-000000000001', 'Surat Permohonan', 2048, 1),
('e0000000-0000-0000-0000-000000000002', 'Surat Persetujuan Dosen Pembimbing', 2048, 1),
('e0000000-0000-0000-0000-000000000003', 'Dokumen Pendukung', 5120, 0),
('e0000000-0000-0000-0000-000000000004', 'Laporan Akhir', 10240, 1),
('e0000000-0000-0000-0000-000000000005', 'Surat Tugas', 2048, 1);

-- Users
INSERT INTO `Users` (
    `UserId`, `Email`, `Password`, `Phone`, `Name`, `Birthdate`, `BirthLocation`,
    `NIK`, `PasporNum`, `KTP`, `Paspor`, `Address`, `Role`, `NIM`, `NIP`,
    `Position`, `AdmissionDate`, `DepartmentId`, `StudyProgramId`, `ProgramId`
) VALUES
(
    '10000000-0000-0000-0000-000000000001', 'superadmin@sitio.ac.id',
    '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
    '081200000001', 'Budi Santoso', '1975-01-15', 'Jakarta',
    '3173011501750001', 'A1000001',
    'storage/uploads/ktp/10000000-0000-0000-0000-000000000001_1.pdf',
    'storage/uploads/paspor/10000000-0000-0000-0000-000000000001_1.pdf',
    'Jl. Merdeka No. 1, Jakarta Pusat', 'SUPERADMIN', NULL, '197501152000011001',
    'Kepala Pusat Layanan Internasional', '2010-01-01', NULL, NULL, NULL
),
(
    '10000000-0000-0000-0000-000000000002', 'admin@sitio.ac.id',
    '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
    '081200000002', 'Siti Aminah', '1985-05-20', 'Bandung',
    '3273012005850002', 'A1000002',
    'storage/uploads/ktp/10000000-0000-0000-0000-000000000002_1.pdf',
    'storage/uploads/paspor/10000000-0000-0000-0000-000000000002_1.pdf',
    'Jl. Asia Afrika No. 12, Bandung', 'ADMIN', NULL, '198505202010012002',
    'Staf Administrasi', '2015-02-01', '11111111-1111-1111-1111-111111111111',
    '44444444-4444-4444-4444-444444444444', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'
),
(
    '10000000-0000-0000-0000-000000000003', 'wakil.dekan@sitio.ac.id',
    '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
    '081200000003', 'Dr. Ahmad Fauzi, M.T.', '1978-09-10', 'Surabaya',
    '3573011009780003', 'A1000003',
    'storage/uploads/ktp/10000000-0000-0000-0000-000000000003_1.pdf',
    'storage/uploads/paspor/10000000-0000-0000-0000-000000000003_1.pdf',
    'Jl. Darmo No. 45, Surabaya', 'STRUKTURAL', NULL, '197809102005011003',
    'Wakil Dekan Bidang Akademik', '2005-03-01', '11111111-1111-1111-1111-111111111111',
    '44444444-4444-4444-4444-444444444444', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'
),
(
    '10000000-0000-0000-0000-000000000004', 'rina.wijaya@sitio.ac.id',
    '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
    '081200000004', 'Dr. Rina Wijaya, M.Kom.', '1982-03-12', 'Yogyakarta',
    '3473011203820004', 'A1000004',
    'storage/uploads/ktp/10000000-0000-0000-0000-000000000004_1.pdf',
    'storage/uploads/paspor/10000000-0000-0000-0000-000000000004_1.pdf',
    'Jl. Kaliurang Km. 5, Sleman', 'DOSEN', NULL, '198203122008012004',
    'Dosen Tetap', '2008-08-01', '11111111-1111-1111-1111-111111111111',
    '44444444-4444-4444-4444-444444444444', 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'
),
(
    '10000000-0000-0000-0000-000000000005', 'dewi.lestari@sitio.ac.id',
    '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
    '081200000005', 'Dewi Lestari, M.M.', '1987-11-25', 'Semarang',
    '3373012511870005', 'A1000005',
    'storage/uploads/ktp/10000000-0000-0000-0000-000000000005_1.pdf',
    'storage/uploads/paspor/10000000-0000-0000-0000-000000000005_1.pdf',
    'Jl. Pandanaran No. 8, Semarang', 'DOSEN', NULL, '198711252012012005',
    'Dosen Tetap', '2012-01-09', '22222222-2222-2222-2222-222222222222',
    '66666666-6666-6666-6666-666666666666', 'cccccccc-cccc-cccc-cccc-cccccccccccc'
),
(
    '10000000-0000-0000-0000-000000000006', 'rizky.pratama@student.sitio.ac.id',
    '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
    '081200000006', 'Rizky Pratama', '2002-04-18', 'Medan',
    '1273011804020006', 'A1000006',
    'storage/uploads/ktp/10000000-0000-0000-0000-000000000006_1.pdf',
    'storage/uploads/paspor/10000000-0000-0000-0000-000000000006_1.pdf',
    'Jl. Gatot Subroto No. 21, Medan', 'MAHASISWA', '2021001001', NULL,
    NULL, '2021-08-16', '11111111-1111-1111-1111-111111111111',
    '44444444-4444-4444-4444-444444444444', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'
),
(
    '10000000-0000-0000-0000-000000000007', 'intan.sari@student.sitio.ac.id',
    '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
    '081200000007', 'Intan Sari', '2003-02-14', 'Palembang',
    '1673011402030007', 'A1000007',
    'storage/uploads/ktp/10000000-0000-0000-0000-000000000007_1.pdf',
    'storage/uploads/paspor/10000000-0000-0000-0000-000000000007_1.pdf',
    'Jl. Sudirman No. 33, Palembang', 'MAHASISWA', '2021001002', NULL,
    NULL, '2021-08-16', '11111111-1111-1111-1111-111111111111',
    '44444444-4444-4444-4444-444444444444', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'
),
(
    '10000000-0000-0000-0000-000000000008', 'fajar.nugroho@student.sitio.ac.id',
    '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
    '081200000008', 'Fajar Nugroho', '2001-12-05', 'Malang',
    '3573010512010008', 'A1000008',
    'storage/uploads/ktp/10000000-0000-0000-0000-000000000008_1.pdf',
    'storage/uploads/paspor/10000000-0000-0000-0000-000000000008_1.pdf',
    'Jl. Ijen No. 17, Malang', 'MAHASISWA', '2020002001', NULL,
    NULL, '2020-08-17', '11111111-1111-1111-1111-111111111111',
    '55555555-5555-5555-5555-555555555555', 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'
),
(
    '10000000-0000-0000-0000-000000000009', 'putri.maharani@student.sitio.ac.id',
    '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
    '081200000009', 'Putri Maharani', '2002-07-30', 'Denpasar',
    '5173013007020009', 'A1000009',
    'storage/uploads/ktp/10000000-0000-0000-0000-000000000009_1.pdf',
    'storage/uploads/paspor/10000000-0000-0000-0000-000000000009_1.pdf',
    'Jl. Sunset Road No. 9, Denpasar', 'MAHASISWA', '2022003001', NULL,
    NULL, '2022-08-15', '22222222-2222-2222-2222-222222222222',
    '66666666-6666-6666-6666-666666666666', 'cccccccc-cccc-cccc-cccc-cccccccccccc'
),
(
    '10000000-0000-0000-0000-000000000010', 'bagas.setiawan@student.sitio.ac.id',
    '$2a$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy',
    '081200000010', 'Bagas Setiawan', '2003-09-09', 'Surakarta',
    '3373010909030010', 'A1000010',
    'storage/uploads/ktp/10000000-0000-0000-0000-000000000010_1.pdf',
    'storage/uploads/paspor/10000000-0000-0000-0000-000000000010_1.pdf',
    'Jl. Slamet Riyadi No. 55, Surakarta', 'MAHASISWA', '2022004001', NULL,
    NULL, '2022-08-15', '33333333-3333-3333-3333-333333333333',
    '77777777-7777-7777-7777-777777777777', 'dddddddd-dddd-dddd-dddd-dddddddddddd'
);

-- PDLNs
INSERT INTO `PDLNs` (
    `PDLNId`, `UserId`, `ActivityName`, `ActivityType`, `Status`, `DestInstitution`,
    `DestCity`, `DestCountry`, `FollowUpPlan`, `StartDate`, `EndDate`, `SPSetneg`
) VALUES
(
    '20000000-0000-0000-0000-000000000001', '10000000-0000-0000-0000-000000000006',
    'Studi Banding Program MBKM ke Universiti Malaya', 'STUDI_BANDING', 'DONE',
    'Universiti Malaya', 'Kuala Lumpur', 'Malaysia',
    'Menyusun laporan studi banding dan mempresentasikan hasilnya pada seminar program studi.',
    '2025-02-10', '2025-02-15', 'SETNEG-2025-0001'
),
(
    '20000000-0000-0000-0000-000000000002', '10000000-0000-0000-0000-000000000007',
    'Pertukaran Mahasiswa Internasional di National University of Singapore', 'PERTUKARAN_MAHASISWA', 'ACCEPTED',
    'National University of Singapore', 'Singapore', 'Singapura',
    'Mengikuti konversi mata kuliah dan melaporkan capaian pembelajaran kepada koordinator program.',
    '2025-08-01', '2025-12-20', 'SETNEG-2025-0002'
),
(
    '20000000-0000-0000-0000-000000000003', '10000000-0000-0000-0000-000000000008',
    'Konferensi Internasional Bidang Sistem Informasi', 'KONFERENSI', 'VERIFICATION',
    'University of Melbourne', 'Melbourne', 'Australia',
    'Mempublikasikan artikel konferensi pada repositori institusi dan menyusun rencana kolaborasi riset.',
    '2025-11-05', '2025-11-08', NULL
),
(
    '20000000-0000-0000-0000-000000000004', '10000000-0000-0000-0000-000000000009',
    'Lokakarya Manajemen Bisnis Internasional', 'LOKAKARYA', 'REVISION',
    'Waseda University', 'Tokyo', 'Jepang',
    'Mengimplementasikan materi lokakarya pada mata kuliah manajemen strategis.',
    '2026-01-12', '2026-01-18', NULL
),
(
    '20000000-0000-0000-0000-000000000005', '10000000-0000-0000-0000-000000000010',
    'Program Pengabdian Masyarakat Lintas Negara', 'PENGABDIAN_MASYARAKAT', 'CANCELLED',
    'Chulalongkorn University', 'Bangkok', 'Thailand',
    'Rencana kegiatan dibatalkan karena penyesuaian jadwal akademik.',
    '2025-10-01', '2025-10-07', NULL
),
(
    '20000000-0000-0000-0000-000000000006', '10000000-0000-0000-0000-000000000006',
    'Penelitian Kolaboratif Keamanan Siber', 'PENELITIAN', 'PENDING',
    'Delft University of Technology', 'Delft', 'Belanda',
    'Menyusun proposal penelitian bersama dan menentukan luaran publikasi.',
    '2026-03-02', '2026-03-14', NULL
);

-- PDLN Participants
INSERT INTO `PDLNParticapants` (`UserId`, `PDLNId`) VALUES
('10000000-0000-0000-0000-000000000006', '20000000-0000-0000-0000-000000000001'),
('10000000-0000-0000-0000-000000000007', '20000000-0000-0000-0000-000000000001'),
('10000000-0000-0000-0000-000000000007', '20000000-0000-0000-0000-000000000002'),
('10000000-0000-0000-0000-000000000010', '20000000-0000-0000-0000-000000000002'),
('10000000-0000-0000-0000-000000000008', '20000000-0000-0000-0000-000000000003'),
('10000000-0000-0000-0000-000000000009', '20000000-0000-0000-0000-000000000004'),
('10000000-0000-0000-0000-000000000010', '20000000-0000-0000-0000-000000000005'),
('10000000-0000-0000-0000-000000000006', '20000000-0000-0000-0000-000000000006');

-- PDLN Documents
INSERT INTO `PDLNDocuments` (`PDLNId`, `DocTypeId`, `Path`) VALUES
('20000000-0000-0000-0000-000000000001', 'e0000000-0000-0000-0000-000000000001', 'storage/uploads/pdln_documents/20000000-0000-0000-0000-000000000001_1.pdf'),
('20000000-0000-0000-0000-000000000001', 'e0000000-0000-0000-0000-000000000002', 'storage/uploads/pdln_documents/20000000-0000-0000-0000-000000000001_2.pdf'),
('20000000-0000-0000-0000-000000000001', 'e0000000-0000-0000-0000-000000000004', 'storage/uploads/pdln_documents/20000000-0000-0000-0000-000000000001_3.pdf'),
('20000000-0000-0000-0000-000000000002', 'e0000000-0000-0000-0000-000000000001', 'storage/uploads/pdln_documents/20000000-0000-0000-0000-000000000002_1.pdf'),
('20000000-0000-0000-0000-000000000002', 'e0000000-0000-0000-0000-000000000002', 'storage/uploads/pdln_documents/20000000-0000-0000-0000-000000000002_2.pdf'),
('20000000-0000-0000-0000-000000000003', 'e0000000-0000-0000-0000-000000000001', 'storage/uploads/pdln_documents/20000000-0000-0000-0000-000000000003_1.pdf'),
('20000000-0000-0000-0000-000000000003', 'e0000000-0000-0000-0000-000000000003', 'storage/uploads/pdln_documents/20000000-0000-0000-0000-000000000003_2.pdf'),
('20000000-0000-0000-0000-000000000004', 'e0000000-0000-0000-0000-000000000001', 'storage/uploads/pdln_documents/20000000-0000-0000-0000-000000000004_1.pdf'),
('20000000-0000-0000-0000-000000000006', 'e0000000-0000-0000-0000-000000000001', 'storage/uploads/pdln_documents/20000000-0000-0000-0000-000000000006_1.pdf');

-- Reports
INSERT INTO `Reports` (`PDLNId`, `FollowUp`, `ImpactRecommendation`, `CostRealization`, `ReportFile`) VALUES
(
    '20000000-0000-0000-0000-000000000001',
    'Hasil studi banding dijadikan bahan penyusunan kurikulum MBKM program studi.',
    'Perlu penambahan mitra universitas di Asia Tenggara dan pendampingan bahasa Inggris akademik.',
    12500000.00,
    'storage/uploads/reports/20000000-0000-0000-0000-000000000001_1.pdf'
);

-- Outbounds
INSERT INTO `Outbounds` (
    `OutboundId`, `UserId`, `Status`, `AcademicYear`, `Semester`, `LetterType`, `Program`,
    `GoAs`, `DestInstituion`, `DestCountry`, `StartDate`, `EndDate`, `DecideBy`, `DecidedAt`,
    `OutboundLetterPath`
) VALUES
(
    '30000000-0000-0000-0000-000000000001', '10000000-0000-0000-0000-000000000006',
    'DONE', 2025, 1, 'ENROLLMENT', 'MBKM Exchange',
    'Mahasiswa', 'Universiti Malaya', 'Malaysia', '2025-02-10', '2025-02-15',
    '10000000-0000-0000-0000-000000000003', '2025-01-20 10:30:00',
    'storage/uploads/outbound_letters/30000000-0000-0000-0000-000000000001_1.pdf'
),
(
    '30000000-0000-0000-0000-000000000002', '10000000-0000-0000-0000-000000000007',
    'ACCEPTED', 2025, 2, 'VISA_RECOMMENDATION', 'Student Exchange',
    'Mahasiswa', 'National University of Singapore', 'Singapura', '2025-08-01', '2025-12-20',
    '10000000-0000-0000-0000-000000000003', '2025-07-05 09:15:00',
    'storage/uploads/outbound_letters/30000000-0000-0000-0000-000000000002_1.pdf'
),
(
    '30000000-0000-0000-0000-000000000003', '10000000-0000-0000-0000-000000000008',
    'VERIFICATION', 2025, 1, 'DIRECTOR_RECOMMENDATION', 'International Conference',
    'Mahasiswa', 'University of Melbourne', 'Australia', '2025-11-05', '2025-11-08',
    NULL, NULL, NULL
),
(
    '30000000-0000-0000-0000-000000000004', '10000000-0000-0000-0000-000000000009',
    'CANCELLED', 2025, 2, 'ENROLLMENT', 'International Workshop',
    'Mahasiswa', 'Waseda University', 'Jepang', '2026-01-12', '2026-01-18',
    '10000000-0000-0000-0000-000000000003', '2025-12-01 14:45:00',
    NULL
),
(
    '30000000-0000-0000-0000-000000000005', '10000000-0000-0000-0000-000000000010',
    'SRIKANDI', 2025, 1, 'DIRECTOR_RECOMMENDATION', 'Community Service',
    'Mahasiswa', 'Chulalongkorn University', 'Thailand', '2025-10-01', '2025-10-07',
    '10000000-0000-0000-0000-000000000003', '2025-09-10 11:00:00',
    'storage/uploads/outbound_letters/30000000-0000-0000-0000-000000000005_1.pdf'
),
(
    '30000000-0000-0000-0000-000000000006', '10000000-0000-0000-0000-000000000006',
    'PENDING', 2025, 2, 'ENROLLMENT', 'Research Collaboration',
    'Mahasiswa', 'Delft University of Technology', 'Belanda', '2026-03-02', '2026-03-14',
    NULL, NULL, NULL
);

-- Tickets
INSERT INTO `Tickets` (`TicketId`, `Email`, `Question`, `Answer`, `AnswerBy`) VALUES
(
    '40000000-0000-0000-0000-000000000001',
    'rizky.pratama@student.sitio.ac.id',
    'Bagaimana cara mengunggah surat persetujuan dosen pembimbing?',
    'Silakan masuk ke menu PDLN, pilih dokumen yang diminta, lalu unggah file PDF maksimal 2 MB.',
    '10000000-0000-0000-0000-000000000002'
),
(
    '40000000-0000-0000-0000-000000000002',
    'fajar.nugroho@student.sitio.ac.id',
    'Apakah status pengajuan dapat diubah setelah masuk tahap verifikasi?',
    NULL,
    '10000000-0000-0000-0000-000000000002'
),
(
    '40000000-0000-0000-0000-000000000003',
    'bagas.setiawan@student.sitio.ac.id',
    'Berapa lama proses penerbitan surat tugas untuk kegiatan internasional?',
    'Proses penerbitan surat tugas memerlukan waktu 3 sampai 5 hari kerja setelah seluruh dokumen lengkap.',
    '10000000-0000-0000-0000-000000000003'
);

-- Ticket Documents
INSERT INTO `TicketDocuments` (`TicketId`, `Path`, `Type`) VALUES
(
    '40000000-0000-0000-0000-000000000001',
    'storage/uploads/ticket_documents/40000000-0000-0000-0000-000000000001_1.png',
    'IMAGE'
),
(
    '40000000-0000-0000-0000-000000000002',
    'storage/uploads/ticket_documents/40000000-0000-0000-0000-000000000002_1.mp4',
    'VIDEO'
);

-- Inbound Programs
INSERT INTO `InboundPrograms` (`InboundProgramId`, `Name`, `StudyProgram`, `StartDate`, `EndDate`) VALUES
(
    '50000000-0000-0000-0000-000000000001',
    'Program Pertukaran Mahasiswa ASEAN',
    'Teknik Informatika',
    '2025-09-01', '2026-01-31'
),
(
    '50000000-0000-0000-0000-000000000002',
    'Summer Course Sustainable Development',
    'Hubungan Internasional',
    '2026-06-15', '2026-07-15'
);

-- Inbounds
INSERT INTO `Inbounds` (
    `InboundId`, `Status`, `Name`, `Birthdate`, `BirthLocation`, `Address`, `PasporNum`,
    `PasporExp`, `LetterType`, `InboundProgramId`, `Duration`, `DurationType`, `DecideBy`,
    `DecidedAt`, `InboundLetterPath`
) VALUES
(
    '60000000-0000-0000-0000-000000000001', 'DONE', 'Nguyen Van An', '2001-03-22',
    'Hanoi', 'No. 15 Nguyen Trai Street, Hanoi, Vietnam', 'B1000001',
    '2030-05-12', 'WARRANTY', '50000000-0000-0000-0000-000000000001',
    6, 'MONTH', '10000000-0000-0000-0000-000000000003', '2025-08-10 09:00:00',
    'storage/uploads/inbound_letters/60000000-0000-0000-0000-000000000001_1.pdf'
),
(
    '60000000-0000-0000-0000-000000000002', 'ACCEPTED', 'Maria Santos', '2002-08-17',
    'Manila', 'Unit 8 Legazpi Street, Makati, Filipina', 'B1000002',
    '2031-01-20', 'PERMIT', '50000000-0000-0000-0000-000000000001',
    5, 'MONTH', '10000000-0000-0000-0000-000000000003', '2025-08-12 10:30:00',
    'storage/uploads/inbound_letters/60000000-0000-0000-0000-000000000002_1.pdf'
),
(
    '60000000-0000-0000-0000-000000000003', 'SRIKANDI', 'Somchai Prasert', '2000-11-02',
    'Bangkok', 'No. 42 Sukhumvit Road, Bangkok, Thailand', 'B1000003',
    '2029-09-30', 'WARRANTY', '50000000-0000-0000-0000-000000000002',
    1, 'MONTH', '10000000-0000-0000-0000-000000000003', '2026-05-20 13:15:00',
    'storage/uploads/inbound_letters/60000000-0000-0000-0000-000000000003_1.pdf'
),
(
    '60000000-0000-0000-0000-000000000004', 'PENDING', 'Lim Wei Jie', '2003-06-11',
    'Kuala Lumpur', 'No. 7 Jalan Ampang, Kuala Lumpur, Malaysia', 'B1000004',
    '2032-02-14', 'PERMIT', '50000000-0000-0000-0000-000000000002',
    30, 'DAY', NULL, NULL, NULL
),
(
    '60000000-0000-0000-0000-000000000005', 'CANCELLED', 'Aung Ko Latt', '2001-01-25',
    'Yangon', 'No. 19 Pyay Road, Yangon, Myanmar', 'B1000005',
    '2028-12-01', 'WARRANTY', '50000000-0000-0000-0000-000000000001',
    4, 'MONTH', '10000000-0000-0000-0000-000000000003', '2025-07-28 15:40:00',
    NULL
);

-- Document Templates
INSERT INTO `DocumentTemplates` (`DocTypeId`, `Path`, `CreatedAt`, `UpdatedAt`) VALUES
('e0000000-0000-0000-0000-000000000001', 'storage/uploads/document_templates/e0000000-0000-0000-0000-000000000001_1.docx', '2025-01-05 08:00:00', '2025-06-10 10:20:00'),
('e0000000-0000-0000-0000-000000000002', 'storage/uploads/document_templates/e0000000-0000-0000-0000-000000000002_1.docx', '2025-01-05 08:00:00', '2025-06-10 10:20:00'),
('e0000000-0000-0000-0000-000000000004', 'storage/uploads/document_templates/e0000000-0000-0000-0000-000000000004_1.docx', '2025-01-05 08:00:00', '2025-07-15 09:30:00'),
('e0000000-0000-0000-0000-000000000005', 'storage/uploads/document_templates/e0000000-0000-0000-0000-000000000005_1.docx', '2025-01-05 08:00:00', '2025-08-01 11:45:00');

-- FAQs
INSERT INTO `FAQs` (`FAQId`, `Question`, `Answer`, `UpdateBy`) VALUES
(
    '70000000-0000-0000-0000-000000000001',
    'Apa itu SiTIO?',
    'SiTIO adalah sistem informasi terintegrasi untuk pengelolaan kegiatan internasional mahasiswa dan dosen.',
    '10000000-0000-0000-0000-000000000002'
),
(
    '70000000-0000-0000-0000-000000000002',
    'Dokumen apa saja yang wajib diunggah pada pengajuan PDLN?',
    'Dokumen wajib meliputi surat permohonan, surat persetujuan dosen pembimbing, dan laporan akhir setelah kegiatan selesai.',
    '10000000-0000-0000-0000-000000000002'
),
(
    '70000000-0000-0000-0000-000000000003',
    'Bagaimana cara mengetahui status pengajuan?',
    'Status pengajuan dapat dilihat pada halaman detail PDLN atau Outbound. Status akan berubah sesuai tahapan verifikasi.',
    '10000000-0000-0000-0000-000000000003'
),
(
    '70000000-0000-0000-0000-000000000004',
    'Apakah pengajuan yang ditolak dapat diajukan ulang?',
    'Pengajuan dengan status REVISION dapat diperbaiki dan diajukan ulang, sedangkan pengajuan CANCELLED harus dibuat baru.',
    '10000000-0000-0000-0000-000000000002'
);

COMMIT;
