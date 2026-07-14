-- =====================================================================
-- Sinkronisasi tabel `locations` dari QR_Rack_Rev02.xlsx (sheet 'Location List')
-- Database: adasi_sto | Tabel: locations
--
-- Mapping plant_id ditentukan dari kolom Warehouse di Excel, divalidasi silang
-- dengan plant_id existing di database untuk name (No. Location Infor) yang sama:
--   Warehouse CWRM1 -> plant_id 2
--   Warehouse SWRM1 -> plant_id 3
--   Warehouse DWRM1 -> plant_id 1
--
-- Total baris Excel (sheet Location List)      : 981
-- Setelah dedupe (baris identik + konflik name) : 937
--   - Baris identik dibuang                    : 10
--   - Konflik No. Location Infor sama tapi No. Location Lama beda
--     (format nol-di-depan vs tidak) -> pilih versi nol-di-depan : 34 baris dibuang
-- UPDATE (name sudah ada di DB, id di-referensi)  : 921 baris
-- INSERT (name belum ada di DB)                   : 16 baris
-- =====================================================================

START TRANSACTION;

-- ---------------------------------------------------------------------
-- UPDATE: 921 baris existing diisi old_location_name, description, warehouse
-- ---------------------------------------------------------------------
UPDATE `locations` SET `old_location_name` = 'CL01', `description` = 'CKG Lantai 001', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 19; -- name=CL001-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL02', `description` = 'CKG Lantai 002', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 20; -- name=CL002-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL03', `description` = 'CKG Lantai 003', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 21; -- name=CL003-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL04', `description` = 'CKG Lantai 004', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 22; -- name=CL004-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL05', `description` = 'CKG Lantai 005', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 23; -- name=CL005-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL06', `description` = 'CKG Lantai 006', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 24; -- name=CL006-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL07', `description` = 'CKG Lantai 007', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 25; -- name=CL007-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL08', `description` = 'CKG Lantai 008', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 26; -- name=CL008-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL09', `description` = 'CKG Lantai 009', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 27; -- name=CL009-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL10', `description` = 'CKG Lantai 010', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 28; -- name=CL010-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL11', `description` = 'CKG Lantai 011', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 29; -- name=CL011-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL12', `description` = 'CKG Lantai 012', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 30; -- name=CL012-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL13', `description` = 'CKG Lantai 013', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 31; -- name=CL013-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL14', `description` = 'CKG Lantai 014', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 32; -- name=CL014-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL15', `description` = 'CKG Lantai 015', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 33; -- name=CL015-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL16', `description` = 'CKG Lantai 016', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 34; -- name=CL016-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL17', `description` = 'CKG Lantai 017', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 35; -- name=CL017-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL18', `description` = 'CKG Lantai 018', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 36; -- name=CL018-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL19', `description` = 'CKG Lantai 019', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 37; -- name=CL019-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL20', `description` = 'CKG Lantai 020', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 38; -- name=CL020-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL21', `description` = 'CKG Lantai 021', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 39; -- name=CL021-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL22', `description` = 'CKG Lantai 022', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 40; -- name=CL022-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL23', `description` = 'CKG Lantai 023', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 41; -- name=CL023-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL24', `description` = 'CKG Lantai 024', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 42; -- name=CL024-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL25', `description` = 'CKG Lantai 025', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 43; -- name=CL025-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL26', `description` = 'CKG Lantai 026', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 44; -- name=CL026-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL27', `description` = 'CKG Lantai 027', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 45; -- name=CL027-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL28', `description` = 'CKG Lantai 028', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 46; -- name=CL028-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR01A', `description` = 'CKG Rak 001 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 47; -- name=CR001-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR01B', `description` = 'CKG Rak 001 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 48; -- name=CR001-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR01C', `description` = 'CKG Rak 001 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 49; -- name=CR001-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR01D', `description` = 'CKG Rak 001 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 50; -- name=CR001-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR01E', `description` = 'CKG Rak 001 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 51; -- name=CR001-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR01F', `description` = 'CKG Rak 001 Cell F00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 52; -- name=CR001-F00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR01G', `description` = 'CKG Rak 001 Cell G00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 53; -- name=CR001-G00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR01H', `description` = 'CKG Rak 001 Cell H00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 54; -- name=CR001-H00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02A1', `description` = 'CKG Rak 002 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 55; -- name=CR002-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02B1', `description` = 'CKG Rak 002 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 56; -- name=CR002-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02C1', `description` = 'CKG Rak 002 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 57; -- name=CR002-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02D1', `description` = 'CKG Rak 002 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 58; -- name=CR002-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02E1', `description` = 'CKG Rak 002 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 59; -- name=CR002-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02F1', `description` = 'CKG Rak 002 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 60; -- name=CR002-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02G1', `description` = 'CKG Rak 002 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 61; -- name=CR002-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02A2', `description` = 'CKG Rak 002 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 62; -- name=CR002-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02B2', `description` = 'CKG Rak 002 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 63; -- name=CR002-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02C2', `description` = 'CKG Rak 002 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 64; -- name=CR002-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02D2', `description` = 'CKG Rak 002 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 65; -- name=CR002-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02E2', `description` = 'CKG Rak 002 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 66; -- name=CR002-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02F2', `description` = 'CKG Rak 002 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 67; -- name=CR002-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02G2', `description` = 'CKG Rak 002 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 68; -- name=CR002-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR03A1', `description` = 'CKG Rak 003 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 69; -- name=CR003-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR03B1', `description` = 'CKG Rak 003 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 70; -- name=CR003-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR03C1', `description` = 'CKG Rak 003 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 71; -- name=CR003-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR03A2', `description` = 'CKG Rak 003 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 72; -- name=CR003-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR03B2', `description` = 'CKG Rak 003 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 73; -- name=CR003-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR03C2', `description` = 'CKG Rak 003 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 74; -- name=CR003-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR04A', `description` = 'CKG Rak 004 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 75; -- name=CR004-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR04B', `description` = 'CKG Rak 004 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 76; -- name=CR004-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR04C', `description` = 'CKG Rak 004 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 77; -- name=CR004-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR04D', `description` = 'CKG Rak 004 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 78; -- name=CR004-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR04E', `description` = 'CKG Rak 004 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 79; -- name=CR004-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR05A', `description` = 'CKG Rak 005 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 80; -- name=CR005-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR05B', `description` = 'CKG Rak 005 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 81; -- name=CR005-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR05C', `description` = 'CKG Rak 005 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 82; -- name=CR005-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR05D', `description` = 'CKG Rak 005 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 83; -- name=CR005-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR05E', `description` = 'CKG Rak 005 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 84; -- name=CR005-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06A1', `description` = 'CKG Rak 006 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 85; -- name=CR006-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06B1', `description` = 'CKG Rak 006 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 86; -- name=CR006-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06C1', `description` = 'CKG Rak 006 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 87; -- name=CR006-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06D1', `description` = 'CKG Rak 006 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 88; -- name=CR006-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06E1', `description` = 'CKG Rak 006 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 89; -- name=CR006-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06F1', `description` = 'CKG Rak 006 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 90; -- name=CR006-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06G1', `description` = 'CKG Rak 006 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 91; -- name=CR006-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06H1', `description` = 'CKG Rak 006 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 92; -- name=CR006-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06A2', `description` = 'CKG Rak 006 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 93; -- name=CR006-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06B2', `description` = 'CKG Rak 006 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 94; -- name=CR006-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06C2', `description` = 'CKG Rak 006 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 95; -- name=CR006-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06D2', `description` = 'CKG Rak 006 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 96; -- name=CR006-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06E2', `description` = 'CKG Rak 006 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 97; -- name=CR006-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06F2', `description` = 'CKG Rak 006 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 98; -- name=CR006-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06G2', `description` = 'CKG Rak 006 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 99; -- name=CR006-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR06H2', `description` = 'CKG Rak 006 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 100; -- name=CR006-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR07A', `description` = 'CKG Rak 007 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 101; -- name=CR007-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR07B', `description` = 'CKG Rak 007 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 102; -- name=CR007-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR07C', `description` = 'CKG Rak 007 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 103; -- name=CR007-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR07D', `description` = 'CKG Rak 007 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 104; -- name=CR007-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR07E', `description` = 'CKG Rak 007 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 105; -- name=CR007-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR07F', `description` = 'CKG Rak 007 Cell F00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 106; -- name=CR007-F00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR07G', `description` = 'CKG Rak 007 Cell G00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 107; -- name=CR007-G00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR07H', `description` = 'CKG Rak 007 Cell H00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 108; -- name=CR007-H00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08A1', `description` = 'CKG Rak 008 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 109; -- name=CR008-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08B1', `description` = 'CKG Rak 008 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 110; -- name=CR008-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08C1', `description` = 'CKG Rak 008 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 111; -- name=CR008-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08D1', `description` = 'CKG Rak 008 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 112; -- name=CR008-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08E1', `description` = 'CKG Rak 008 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 113; -- name=CR008-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08F1', `description` = 'CKG Rak 008 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 114; -- name=CR008-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08G1', `description` = 'CKG Rak 008 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 115; -- name=CR008-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08H1', `description` = 'CKG Rak 008 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 116; -- name=CR008-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08A2', `description` = 'CKG Rak 008 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 117; -- name=CR008-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08B2', `description` = 'CKG Rak 008 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 118; -- name=CR008-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08C2', `description` = 'CKG Rak 008 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 119; -- name=CR008-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08D2', `description` = 'CKG Rak 008 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 120; -- name=CR008-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08E2', `description` = 'CKG Rak 008 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 121; -- name=CR008-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08F2', `description` = 'CKG Rak 008 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 122; -- name=CR008-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08G2', `description` = 'CKG Rak 008 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 123; -- name=CR008-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR08H2', `description` = 'CKG Rak 008 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 124; -- name=CR008-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09A1', `description` = 'CKG Rak 009 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 125; -- name=CR009-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09B1', `description` = 'CKG Rak 009 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 126; -- name=CR009-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09C1', `description` = 'CKG Rak 009 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 127; -- name=CR009-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09D1', `description` = 'CKG Rak 009 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 128; -- name=CR009-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09E1', `description` = 'CKG Rak 009 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 129; -- name=CR009-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09F1', `description` = 'CKG Rak 009 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 130; -- name=CR009-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09G1', `description` = 'CKG Rak 009 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 131; -- name=CR009-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09H1', `description` = 'CKG Rak 009 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 132; -- name=CR009-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09A2', `description` = 'CKG Rak 009 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 133; -- name=CR009-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09B2', `description` = 'CKG Rak 009 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 134; -- name=CR009-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09C2', `description` = 'CKG Rak 009 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 135; -- name=CR009-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09D2', `description` = 'CKG Rak 009 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 136; -- name=CR009-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09E2', `description` = 'CKG Rak 009 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 137; -- name=CR009-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09F2', `description` = 'CKG Rak 009 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 138; -- name=CR009-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09G2', `description` = 'CKG Rak 009 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 139; -- name=CR009-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR09H2', `description` = 'CKG Rak 009 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 140; -- name=CR009-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10A1', `description` = 'CKG Rak 010 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 141; -- name=CR010-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10B1', `description` = 'CKG Rak 010 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 142; -- name=CR010-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10C1', `description` = 'CKG Rak 010 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 143; -- name=CR010-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10D1', `description` = 'CKG Rak 010 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 144; -- name=CR010-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10E1', `description` = 'CKG Rak 010 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 145; -- name=CR010-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10F1', `description` = 'CKG Rak 010 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 146; -- name=CR010-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10G1', `description` = 'CKG Rak 010 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 147; -- name=CR010-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10H1', `description` = 'CKG Rak 010 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 148; -- name=CR010-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10A2', `description` = 'CKG Rak 010 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 149; -- name=CR010-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10B2', `description` = 'CKG Rak 010 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 150; -- name=CR010-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10C2', `description` = 'CKG Rak 010 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 151; -- name=CR010-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10D2', `description` = 'CKG Rak 010 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 152; -- name=CR010-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10E2', `description` = 'CKG Rak 010 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 153; -- name=CR010-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10F2', `description` = 'CKG Rak 010 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 154; -- name=CR010-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10G2', `description` = 'CKG Rak 010 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 155; -- name=CR010-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR10H2', `description` = 'CKG Rak 010 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 156; -- name=CR010-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11A1', `description` = 'CKG Rak 011 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 157; -- name=CR011-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11B1', `description` = 'CKG Rak 011 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 158; -- name=CR011-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11C1', `description` = 'CKG Rak 011 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 159; -- name=CR011-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11D1', `description` = 'CKG Rak 011 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 160; -- name=CR011-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11A2', `description` = 'CKG Rak 011 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 161; -- name=CR011-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11B2', `description` = 'CKG Rak 011 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 162; -- name=CR011-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11C2', `description` = 'CKG Rak 011 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 163; -- name=CR011-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11D2', `description` = 'CKG Rak 011 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 164; -- name=CR011-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11A3', `description` = 'CKG Rak 011 Cell A03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 165; -- name=CR011-A03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11B3', `description` = 'CKG Rak 011 Cell B03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 166; -- name=CR011-B03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11C3', `description` = 'CKG Rak 011 Cell C03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 167; -- name=CR011-C03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11D3', `description` = 'CKG Rak 011 Cell D03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 168; -- name=CR011-D03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11A4', `description` = 'CKG Rak 011 Cell A04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 169; -- name=CR011-A04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11B4', `description` = 'CKG Rak 011 Cell B04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 170; -- name=CR011-B04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11C4', `description` = 'CKG Rak 011 Cell C04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 171; -- name=CR011-C04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR11D4', `description` = 'CKG Rak 011 Cell D04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 172; -- name=CR011-D04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12A1', `description` = 'CKG Rak 012 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 173; -- name=CR012-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12B1', `description` = 'CKG Rak 012 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 174; -- name=CR012-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12C1', `description` = 'CKG Rak 012 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 175; -- name=CR012-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12D1', `description` = 'CKG Rak 012 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 176; -- name=CR012-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12E1', `description` = 'CKG Rak 012 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 177; -- name=CR012-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12F1', `description` = 'CKG Rak 012 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 178; -- name=CR012-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12G1', `description` = 'CKG Rak 012 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 179; -- name=CR012-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12H1', `description` = 'CKG Rak 012 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 180; -- name=CR012-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12A2', `description` = 'CKG Rak 012 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 181; -- name=CR012-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12B2', `description` = 'CKG Rak 012 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 182; -- name=CR012-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12C2', `description` = 'CKG Rak 012 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 183; -- name=CR012-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12D2', `description` = 'CKG Rak 012 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 184; -- name=CR012-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12E2', `description` = 'CKG Rak 012 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 185; -- name=CR012-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12F2', `description` = 'CKG Rak 012 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 186; -- name=CR012-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12G2', `description` = 'CKG Rak 012 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 187; -- name=CR012-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR12H2', `description` = 'CKG Rak 012 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 188; -- name=CR012-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR13A', `description` = 'CKG Rak 013 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 189; -- name=CR013-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR13B', `description` = 'CKG Rak 013 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 190; -- name=CR013-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR13C', `description` = 'CKG Rak 013 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 191; -- name=CR013-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR13D', `description` = 'CKG Rak 013 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 192; -- name=CR013-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR13E', `description` = 'CKG Rak 013 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 193; -- name=CR013-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR13F', `description` = 'CKG Rak 013 Cell F00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 194; -- name=CR013-F00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR13G', `description` = 'CKG Rak 013 Cell G00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 195; -- name=CR013-G00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR13H', `description` = 'CKG Rak 013 Cell H00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 196; -- name=CR013-H00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR14A1', `description` = 'CKG Rak 014 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 197; -- name=CR014-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR14B1', `description` = 'CKG Rak 014 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 198; -- name=CR014-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR14C1', `description` = 'CKG Rak 014 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 199; -- name=CR014-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR14D1', `description` = 'CKG Rak 014 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 200; -- name=CR014-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR14E1', `description` = 'CKG Rak 014 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 201; -- name=CR014-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR14A2', `description` = 'CKG Rak 014 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 202; -- name=CR014-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR14B2', `description` = 'CKG Rak 014 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 203; -- name=CR014-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR14C2', `description` = 'CKG Rak 014 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 204; -- name=CR014-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR14D2', `description` = 'CKG Rak 014 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 205; -- name=CR014-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR14E2', `description` = 'CKG Rak 014 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 206; -- name=CR014-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15A1', `description` = 'CKG Rak 015 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 207; -- name=CR015-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15B1', `description` = 'CKG Rak 015 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 208; -- name=CR015-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15C1', `description` = 'CKG Rak 015 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 209; -- name=CR015-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15D1', `description` = 'CKG Rak 015 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 210; -- name=CR015-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15E1', `description` = 'CKG Rak 015 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 211; -- name=CR015-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15F1', `description` = 'CKG Rak 015 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 212; -- name=CR015-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15G1', `description` = 'CKG Rak 015 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 213; -- name=CR015-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15H1', `description` = 'CKG Rak 015 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 214; -- name=CR015-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15A2', `description` = 'CKG Rak 015 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 215; -- name=CR015-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15B2', `description` = 'CKG Rak 015 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 216; -- name=CR015-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15C2', `description` = 'CKG Rak 015 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 217; -- name=CR015-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15D2', `description` = 'CKG Rak 015 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 218; -- name=CR015-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15E2', `description` = 'CKG Rak 015 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 219; -- name=CR015-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15F2', `description` = 'CKG Rak 015 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 220; -- name=CR015-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15G2', `description` = 'CKG Rak 015 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 221; -- name=CR015-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR15H2', `description` = 'CKG Rak 015 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 222; -- name=CR015-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR16A1', `description` = 'CKG Rak 016 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 223; -- name=CR016-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR16B1', `description` = 'CKG Rak 016 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 224; -- name=CR016-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR16C1', `description` = 'CKG Rak 016 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 225; -- name=CR016-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR16D1', `description` = 'CKG Rak 016 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 226; -- name=CR016-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR16E1', `description` = 'CKG Rak 016 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 227; -- name=CR016-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR16A2', `description` = 'CKG Rak 016 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 228; -- name=CR016-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR16B2', `description` = 'CKG Rak 016 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 229; -- name=CR016-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR16C2', `description` = 'CKG Rak 016 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 230; -- name=CR016-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR16D2', `description` = 'CKG Rak 016 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 231; -- name=CR016-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR16E2', `description` = 'CKG Rak 016 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 232; -- name=CR016-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR17A', `description` = 'CKG Rak 017 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 233; -- name=CR017-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR17B', `description` = 'CKG Rak 017 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 234; -- name=CR017-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR17C', `description` = 'CKG Rak 017 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 235; -- name=CR017-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR17D', `description` = 'CKG Rak 017 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 236; -- name=CR017-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR17E', `description` = 'CKG Rak 017 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 237; -- name=CR017-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR17F', `description` = 'CKG Rak 017 Cell F00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 238; -- name=CR017-F00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR17G', `description` = 'CKG Rak 017 Cell G00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 239; -- name=CR017-G00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR17H', `description` = 'CKG Rak 017 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 240; -- name=CR017-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18A1', `description` = 'CKG Rak 018 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 241; -- name=CR018-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18B1', `description` = 'CKG Rak 018 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 242; -- name=CR018-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18C1', `description` = 'CKG Rak 018 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 243; -- name=CR018-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18D1', `description` = 'CKG Rak 018 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 244; -- name=CR018-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18E1', `description` = 'CKG Rak 018 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 245; -- name=CR018-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18F1', `description` = 'CKG Rak 018 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 246; -- name=CR018-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18G1', `description` = 'CKG Rak 018 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 247; -- name=CR018-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18H1', `description` = 'CKG Rak 018 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 248; -- name=CR018-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18A2', `description` = 'CKG Rak 018 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 249; -- name=CR018-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18B2', `description` = 'CKG Rak 018 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 250; -- name=CR018-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18C2', `description` = 'CKG Rak 018 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 251; -- name=CR018-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18D2', `description` = 'CKG Rak 018 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 252; -- name=CR018-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18E2', `description` = 'CKG Rak 018 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 253; -- name=CR018-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18F2', `description` = 'CKG Rak 018 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 254; -- name=CR018-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18G2', `description` = 'CKG Rak 018 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 255; -- name=CR018-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18H2', `description` = 'CKG Rak 018 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 256; -- name=CR018-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19A1', `description` = 'CKG Rak 019 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 257; -- name=CR019-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19B1', `description` = 'CKG Rak 019 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 258; -- name=CR019-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19C1', `description` = 'CKG Rak 019 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 259; -- name=CR019-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19D1', `description` = 'CKG Rak 019 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 260; -- name=CR019-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19E1', `description` = 'CKG Rak 019 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 261; -- name=CR019-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19F1', `description` = 'CKG Rak 019 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 262; -- name=CR019-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19G1', `description` = 'CKG Rak 019 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 263; -- name=CR019-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19H1', `description` = 'CKG Rak 019 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 264; -- name=CR019-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19A2', `description` = 'CKG Rak 019 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 265; -- name=CR019-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19B2', `description` = 'CKG Rak 019 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 266; -- name=CR019-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19C2', `description` = 'CKG Rak 019 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 267; -- name=CR019-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19D2', `description` = 'CKG Rak 019 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 268; -- name=CR019-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19E2', `description` = 'CKG Rak 019 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 269; -- name=CR019-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19F2', `description` = 'CKG Rak 019 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 270; -- name=CR019-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19G2', `description` = 'CKG Rak 019 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 271; -- name=CR019-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR19H2', `description` = 'CKG Rak 019 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 272; -- name=CR019-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR20A1', `description` = 'CKG Rak 020 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 273; -- name=CR020-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR20B1', `description` = 'CKG Rak 020 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 274; -- name=CR020-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR20C1', `description` = 'CKG Rak 020 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 275; -- name=CR020-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR20D1', `description` = 'CKG Rak 020 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 276; -- name=CR020-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR20E1', `description` = 'CKG Rak 020 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 277; -- name=CR020-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR20A2', `description` = 'CKG Rak 020 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 278; -- name=CR020-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR20B2', `description` = 'CKG Rak 020 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 279; -- name=CR020-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR20C2', `description` = 'CKG Rak 020 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 280; -- name=CR020-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR20D2', `description` = 'CKG Rak 020 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 281; -- name=CR020-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR20E2', `description` = 'CKG Rak 020 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 282; -- name=CR020-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR21A', `description` = 'CKG Rak 021 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 283; -- name=CR021-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR21B', `description` = 'CKG Rak 021 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 284; -- name=CR021-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR21C', `description` = 'CKG Rak 021 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 285; -- name=CR021-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR21D', `description` = 'CKG Rak 021 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 286; -- name=CR021-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR21E', `description` = 'CKG Rak 021 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 287; -- name=CR021-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR22A1', `description` = 'CKG Rak 022 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 288; -- name=CR022-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR22B1', `description` = 'CKG Rak 022 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 289; -- name=CR022-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR22C1', `description` = 'CKG Rak 022 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 290; -- name=CR022-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR22A2', `description` = 'CKG Rak 022 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 291; -- name=CR022-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR22B2', `description` = 'CKG Rak 022 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 292; -- name=CR022-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR22C2', `description` = 'CKG Rak 022 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 293; -- name=CR022-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23A1', `description` = 'CKG Rak 023 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 294; -- name=CR023-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23B1', `description` = 'CKG Rak 023 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 295; -- name=CR023-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23C1', `description` = 'CKG Rak 023 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 296; -- name=CR023-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23D1', `description` = 'CKG Rak 023 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 297; -- name=CR023-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23A2', `description` = 'CKG Rak 023 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 298; -- name=CR023-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23B2', `description` = 'CKG Rak 023 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 299; -- name=CR023-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23C2', `description` = 'CKG Rak 023 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 300; -- name=CR023-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23D2', `description` = 'CKG Rak 023 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 301; -- name=CR023-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23A3', `description` = 'CKG Rak 023 Cell A03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 302; -- name=CR023-A03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23B3', `description` = 'CKG Rak 023 Cell B03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 303; -- name=CR023-B03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23C3', `description` = 'CKG Rak 023 Cell C03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 304; -- name=CR023-C03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23D3', `description` = 'CKG Rak 023 Cell D03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 305; -- name=CR023-D03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23A4', `description` = 'CKG Rak 023 Cell A04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 306; -- name=CR023-A04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23B4', `description` = 'CKG Rak 023 Cell B04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 307; -- name=CR023-B04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23C4', `description` = 'CKG Rak 023 Cell C04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 308; -- name=CR023-C04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23D4', `description` = 'CKG Rak 023 Cell D04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 309; -- name=CR023-D04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR24A', `description` = 'CKG Rak 024 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 310; -- name=CR024-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR24B', `description` = 'CKG Rak 024 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 311; -- name=CR024-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR24C', `description` = 'CKG Rak 024 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 312; -- name=CR024-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR24D', `description` = 'CKG Rak 024 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 313; -- name=CR024-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR24E', `description` = 'CKG Rak 024 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 314; -- name=CR024-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR24F', `description` = 'CKG Rak 024 Cell F00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 315; -- name=CR024-F00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR24G', `description` = 'CKG Rak 024 Cell G00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 316; -- name=CR024-G00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR24H', `description` = 'CKG Rak 024 Cell H00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 317; -- name=CR024-H00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR25A1', `description` = 'CKG Rak 025 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 318; -- name=CR025-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR25B1', `description` = 'CKG Rak 025 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 319; -- name=CR025-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR25C1', `description` = 'CKG Rak 025 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 320; -- name=CR025-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR25D1', `description` = 'CKG Rak 025 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 321; -- name=CR025-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR25E1', `description` = 'CKG Rak 025 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 322; -- name=CR025-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR25A2', `description` = 'CKG Rak 025 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 323; -- name=CR025-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR25B2', `description` = 'CKG Rak 025 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 324; -- name=CR025-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR25C2', `description` = 'CKG Rak 025 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 325; -- name=CR025-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR25D2', `description` = 'CKG Rak 025 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 326; -- name=CR025-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR25E2', `description` = 'CKG Rak 025 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 327; -- name=CR025-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR26A1', `description` = 'CKG Rak 026 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 328; -- name=CR026-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR26B1', `description` = 'CKG Rak 026 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 329; -- name=CR026-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR26C1', `description` = 'CKG Rak 026 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 330; -- name=CR026-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR26D1', `description` = 'CKG Rak 026 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 331; -- name=CR026-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR26E1', `description` = 'CKG Rak 026 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 332; -- name=CR026-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR26A2', `description` = 'CKG Rak 026 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 333; -- name=CR026-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR26B2', `description` = 'CKG Rak 026 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 334; -- name=CR026-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR26C2', `description` = 'CKG Rak 026 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 335; -- name=CR026-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR26D2', `description` = 'CKG Rak 026 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 336; -- name=CR026-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR26E2', `description` = 'CKG Rak 026 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 337; -- name=CR026-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR27A', `description` = 'CKG Rak 027 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 338; -- name=CR027-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR27B', `description` = 'CKG Rak 027 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 339; -- name=CR027-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR27C', `description` = 'CKG Rak 027 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 340; -- name=CR027-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR27D', `description` = 'CKG Rak 027 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 341; -- name=CR027-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR27E', `description` = 'CKG Rak 027 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 342; -- name=CR027-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR27F', `description` = 'CKG Rak 027 Cell F00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 343; -- name=CR027-F00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR27G', `description` = 'CKG Rak 027 Cell G00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 344; -- name=CR027-G00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR27H', `description` = 'CKG Rak 027 Cell H00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 345; -- name=CR027-H00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR28A', `description` = 'CKG Rak 028 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 346; -- name=CR028-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR28B', `description` = 'CKG Rak 028 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 347; -- name=CR028-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR28C', `description` = 'CKG Rak 028 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 348; -- name=CR028-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR28D', `description` = 'CKG Rak 028 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 349; -- name=CR028-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR28E', `description` = 'CKG Rak 028 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 350; -- name=CR028-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR28F', `description` = 'CKG Rak 028 Cell F00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 351; -- name=CR028-F00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR28G', `description` = 'CKG Rak 028 Cell G00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 352; -- name=CR028-G00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR28H', `description` = 'CKG Rak 028 Cell H00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 353; -- name=CR028-H00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR29A1', `description` = 'CKG Rak 029 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 354; -- name=CR029-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR29B1', `description` = 'CKG Rak 029 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 355; -- name=CR029-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR29C1', `description` = 'CKG Rak 029 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 356; -- name=CR029-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR29D1', `description` = 'CKG Rak 029 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 357; -- name=CR029-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR29E1', `description` = 'CKG Rak 029 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 358; -- name=CR029-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR29A2', `description` = 'CKG Rak 029 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 359; -- name=CR029-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR29B2', `description` = 'CKG Rak 029 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 360; -- name=CR029-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR29C2', `description` = 'CKG Rak 029 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 361; -- name=CR029-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR29D2', `description` = 'CKG Rak 029 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 362; -- name=CR029-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR29E2', `description` = 'CKG Rak 029 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 363; -- name=CR029-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR30A', `description` = 'CKG Rak 030 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 364; -- name=CR030-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR30B', `description` = 'CKG Rak 030 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 365; -- name=CR030-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR30C', `description` = 'CKG Rak 030 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 366; -- name=CR030-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR30D', `description` = 'CKG Rak 030 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 367; -- name=CR030-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR31A', `description` = 'CKG Rak 031 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 368; -- name=CR031-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR31B', `description` = 'CKG Rak 031 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 369; -- name=CR031-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR31C', `description` = 'CKG Rak 031 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 370; -- name=CR031-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR31D', `description` = 'CKG Rak 031 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 371; -- name=CR031-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR31E', `description` = 'CKG Rak 031 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 372; -- name=CR031-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR31F', `description` = 'CKG Rak 031 Cell F00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 373; -- name=CR031-F00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR31G', `description` = 'CKG Rak 031 Cell G00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 374; -- name=CR031-G00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR31H', `description` = 'CKG Rak 031 Cell H00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 375; -- name=CR031-H00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32A1', `description` = 'CKG Rak 032 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 376; -- name=CR032-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32B1', `description` = 'CKG Rak 032 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 377; -- name=CR032-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32C1', `description` = 'CKG Rak 032 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 378; -- name=CR032-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32D1', `description` = 'CKG Rak 032 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 379; -- name=CR032-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32E1', `description` = 'CKG Rak 032 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 380; -- name=CR032-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32F1', `description` = 'CKG Rak 032 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 381; -- name=CR032-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32G1', `description` = 'CKG Rak 032 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 382; -- name=CR032-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32H1', `description` = 'CKG Rak 032 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 383; -- name=CR032-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32A2', `description` = 'CKG Rak 032 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 384; -- name=CR032-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32B2', `description` = 'CKG Rak 032 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 385; -- name=CR032-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32C2', `description` = 'CKG Rak 032 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 386; -- name=CR032-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32D2', `description` = 'CKG Rak 032 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 387; -- name=CR032-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32E2', `description` = 'CKG Rak 032 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 388; -- name=CR032-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32F2', `description` = 'CKG Rak 032 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 389; -- name=CR032-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32G2', `description` = 'CKG Rak 032 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 390; -- name=CR032-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR32H2', `description` = 'CKG Rak 032 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 391; -- name=CR032-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33A1', `description` = 'CKG Rak 033 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 392; -- name=CR033-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33B1', `description` = 'CKG Rak 033 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 393; -- name=CR033-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33C1', `description` = 'CKG Rak 033 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 394; -- name=CR033-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33D1', `description` = 'CKG Rak 033 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 395; -- name=CR033-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33E1', `description` = 'CKG Rak 033 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 396; -- name=CR033-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33F1', `description` = 'CKG Rak 033 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 397; -- name=CR033-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33G1', `description` = 'CKG Rak 033 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 398; -- name=CR033-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33H1', `description` = 'CKG Rak 033 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 399; -- name=CR033-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33A2', `description` = 'CKG Rak 033 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 400; -- name=CR033-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33B2', `description` = 'CKG Rak 033 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 401; -- name=CR033-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33C2', `description` = 'CKG Rak 033 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 402; -- name=CR033-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33D2', `description` = 'CKG Rak 033 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 403; -- name=CR033-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33E2', `description` = 'CKG Rak 033 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 404; -- name=CR033-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33F2', `description` = 'CKG Rak 033 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 405; -- name=CR033-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33G2', `description` = 'CKG Rak 033 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 406; -- name=CR033-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR33H2', `description` = 'CKG Rak 033 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 407; -- name=CR033-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR34A', `description` = 'CKG Rak 034 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 408; -- name=CR034-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR34B', `description` = 'CKG Rak 034 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 409; -- name=CR034-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR34C', `description` = 'CKG Rak 034 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 410; -- name=CR034-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR34D', `description` = 'CKG Rak 034 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 411; -- name=CR034-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR34E', `description` = 'CKG Rak 034 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 412; -- name=CR034-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR34F', `description` = 'CKG Rak 034 Cell F00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 413; -- name=CR034-F00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR34G', `description` = 'CKG Rak 034 Cell G00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 414; -- name=CR034-G00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR34H', `description` = 'CKG Rak 034 Cell H00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 415; -- name=CR034-H00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35A1', `description` = 'CKG Rak 035 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 416; -- name=CR035-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35B1', `description` = 'CKG Rak 035 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 417; -- name=CR035-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35C1', `description` = 'CKG Rak 035 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 418; -- name=CR035-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35D1', `description` = 'CKG Rak 035 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 419; -- name=CR035-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35E1', `description` = 'CKG Rak 035 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 420; -- name=CR035-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35A2', `description` = 'CKG Rak 035 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 421; -- name=CR035-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35B2', `description` = 'CKG Rak 035 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 422; -- name=CR035-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35C2', `description` = 'CKG Rak 035 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 423; -- name=CR035-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35D2', `description` = 'CKG Rak 035 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 424; -- name=CR035-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35E2', `description` = 'CKG Rak 035 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 425; -- name=CR035-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR36A', `description` = 'CKG Rak 036 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 426; -- name=CR036-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR36B', `description` = 'CKG Rak 036 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 427; -- name=CR036-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR36C', `description` = 'CKG Rak 036 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 428; -- name=CR036-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR36D', `description` = 'CKG Rak 036 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 429; -- name=CR036-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR37A1', `description` = 'CKG Rak 037 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 430; -- name=CR037-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR37B1', `description` = 'CKG Rak 037 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 431; -- name=CR037-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR37C1', `description` = 'CKG Rak 037 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 432; -- name=CR037-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR37A2', `description` = 'CKG Rak 037 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 433; -- name=CR037-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR37B2', `description` = 'CKG Rak 037 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 434; -- name=CR037-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR37C2', `description` = 'CKG Rak 037 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 435; -- name=CR037-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR38A1', `description` = 'CKG Rak 038 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 436; -- name=CR038-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR38B1', `description` = 'CKG Rak 038 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 437; -- name=CR038-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR38C1', `description` = 'CKG Rak 038 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 438; -- name=CR038-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR38D1', `description` = 'CKG Rak 038 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 439; -- name=CR038-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR38A2', `description` = 'CKG Rak 038 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 440; -- name=CR038-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR38B2', `description` = 'CKG Rak 038 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 441; -- name=CR038-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR38C2', `description` = 'CKG Rak 038 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 442; -- name=CR038-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR38D2', `description` = 'CKG Rak 038 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 443; -- name=CR038-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39A1', `description` = 'CKG Rak 039 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 444; -- name=CR039-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39B1', `description` = 'CKG Rak 039 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 445; -- name=CR039-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39C1', `description` = 'CKG Rak 039 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 446; -- name=CR039-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39D1', `description` = 'CKG Rak 039 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 447; -- name=CR039-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39A2', `description` = 'CKG Rak 039 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 448; -- name=CR039-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39B2', `description` = 'CKG Rak 039 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 449; -- name=CR039-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39C2', `description` = 'CKG Rak 039 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 450; -- name=CR039-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39D2', `description` = 'CKG Rak 039 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 451; -- name=CR039-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39A3', `description` = 'CKG Rak 039 Cell A03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 452; -- name=CR039-A03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39B3', `description` = 'CKG Rak 039 Cell B03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 453; -- name=CR039-B03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39C3', `description` = 'CKG Rak 039 Cell C03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 454; -- name=CR039-C03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39D3', `description` = 'CKG Rak 039 Cell D03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 455; -- name=CR039-D03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR40A1', `description` = 'CKG Rak 040 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 456; -- name=CR040-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR40B1', `description` = 'CKG Rak 040 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 457; -- name=CR040-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR40C1', `description` = 'CKG Rak 040 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 458; -- name=CR040-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR40A2', `description` = 'CKG Rak 040 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 459; -- name=CR040-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR40B2', `description` = 'CKG Rak 040 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 460; -- name=CR040-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR40C2', `description` = 'CKG Rak 040 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 461; -- name=CR040-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41A1', `description` = 'CKG Rak 041 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 462; -- name=CR041-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41B1', `description` = 'CKG Rak 041 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 463; -- name=CR041-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41C1', `description` = 'CKG Rak 041 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 464; -- name=CR041-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41D1', `description` = 'CKG Rak 041 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 465; -- name=CR041-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41E1', `description` = 'CKG Rak 041 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 466; -- name=CR041-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41F1', `description` = 'CKG Rak 041 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 467; -- name=CR041-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41G1', `description` = 'CKG Rak 041 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 468; -- name=CR041-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41H1', `description` = 'CKG Rak 041 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 469; -- name=CR041-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41A2', `description` = 'CKG Rak 041 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 470; -- name=CR041-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41B2', `description` = 'CKG Rak 041 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 471; -- name=CR041-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41C2', `description` = 'CKG Rak 041 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 472; -- name=CR041-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41D2', `description` = 'CKG Rak 041 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 473; -- name=CR041-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41E2', `description` = 'CKG Rak 041 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 474; -- name=CR041-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41F2', `description` = 'CKG Rak 041 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 475; -- name=CR041-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41G2', `description` = 'CKG Rak 041 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 476; -- name=CR041-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR41H2', `description` = 'CKG Rak 041 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 477; -- name=CR041-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR42A', `description` = 'CKG Rak 042 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 478; -- name=CR042-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR42B', `description` = 'CKG Rak 042 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 479; -- name=CR042-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR42C', `description` = 'CKG Rak 042 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 480; -- name=CR042-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR42D', `description` = 'CKG Rak 042 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 481; -- name=CR042-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR42E', `description` = 'CKG Rak 042 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 482; -- name=CR042-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR42F', `description` = 'CKG Rak 042 Cell F00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 483; -- name=CR042-F00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR42G', `description` = 'CKG Rak 042 Cell G00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 484; -- name=CR042-G00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR42H', `description` = 'CKG Rak 042 Cell H00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 485; -- name=CR042-H00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT1A', `description` = 'CKG Rak 043 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 486; -- name=CR043-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT1B', `description` = 'CKG Rak 043 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 487; -- name=CR043-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT1C', `description` = 'CKG Rak 043 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 488; -- name=CR043-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT1D', `description` = 'CKG Rak 043 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 489; -- name=CR043-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT1E', `description` = 'CKG Rak 043 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 490; -- name=CR043-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT2A', `description` = 'CKG Rak 044 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 491; -- name=CR044-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT2B', `description` = 'CKG Rak 044 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 492; -- name=CR044-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT2C', `description` = 'CKG Rak 044 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 493; -- name=CR044-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT2D', `description` = 'CKG Rak 044 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 494; -- name=CR044-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT2E', `description` = 'CKG Rak 044 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 495; -- name=CR044-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT3A', `description` = 'CKG Rak 045 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 496; -- name=CR045-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT3B', `description` = 'CKG Rak 045 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 497; -- name=CR045-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT3C', `description` = 'CKG Rak 045 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 498; -- name=CR045-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT3D', `description` = 'CKG Rak 045 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 499; -- name=CR045-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRT3E', `description` = 'CKG Rak 045 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 500; -- name=CR045-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01A', `description` = 'CKG Tiang 001 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 501; -- name=CT001-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01A1', `description` = 'CKG Tiang 001 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 502; -- name=CT001-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01A2', `description` = 'CKG Tiang 001 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 503; -- name=CT001-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01A3', `description` = 'CKG Tiang 001 Cell A03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 504; -- name=CT001-A03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01A4', `description` = 'CKG Tiang 001 Cell A04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 505; -- name=CT001-A04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01B1', `description` = 'CKG Tiang 001 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 506; -- name=CT001-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01B2', `description` = 'CKG Tiang 001 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 507; -- name=CT001-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01B3', `description` = 'CKG Tiang 001 Cell B03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 508; -- name=CT001-B03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01B4', `description` = 'CKG Tiang 001 Cell B04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 509; -- name=CT001-B04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01C1', `description` = 'CKG Tiang 001 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 510; -- name=CT001-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01C2', `description` = 'CKG Tiang 001 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 511; -- name=CT001-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01C3', `description` = 'CKG Tiang 001 Cell C03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 512; -- name=CT001-C03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01C4', `description` = 'CKG Tiang 001 Cell C04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 513; -- name=CT001-C04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01D1', `description` = 'CKG Tiang 001 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 514; -- name=CT001-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01D2', `description` = 'CKG Tiang 001 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 515; -- name=CT001-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01D3', `description` = 'CKG Tiang 001 Cell D03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 516; -- name=CT001-D03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01D4', `description` = 'CKG Tiang 001 Cell D04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 517; -- name=CT001-D04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01E1', `description` = 'CKG Tiang 001 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 518; -- name=CT001-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01E2', `description` = 'CKG Tiang 001 Cell E02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 519; -- name=CT001-E02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01E3', `description` = 'CKG Tiang 001 Cell E03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 520; -- name=CT001-E03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01E4', `description` = 'CKG Tiang 001 Cell E04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 521; -- name=CT001-E04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01F1', `description` = 'CKG Tiang 001 Cell F01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 522; -- name=CT001-F01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01F2', `description` = 'CKG Tiang 001 Cell F02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 523; -- name=CT001-F02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01F3', `description` = 'CKG Tiang 001 Cell F03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 524; -- name=CT001-F03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01F4', `description` = 'CKG Tiang 001 Cell F04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 525; -- name=CT001-F04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01G1', `description` = 'CKG Tiang 001 Cell G01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 526; -- name=CT001-G01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01G2', `description` = 'CKG Tiang 001 Cell G02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 527; -- name=CT001-G02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01G3', `description` = 'CKG Tiang 001 Cell G03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 528; -- name=CT001-G03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01G4', `description` = 'CKG Tiang 001 Cell G04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 529; -- name=CT001-G04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01H1', `description` = 'CKG Tiang 001 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 530; -- name=CT001-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01H2', `description` = 'CKG Tiang 001 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 531; -- name=CT001-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01H3', `description` = 'CKG Tiang 001 Cell H03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 532; -- name=CT001-H03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01H4', `description` = 'CKG Tiang 001 Cell H04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 533; -- name=CT001-H04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01I1', `description` = 'CKG Tiang 001 Cell I01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 534; -- name=CT001-I01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01I2', `description` = 'CKG Tiang 001 Cell I02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 535; -- name=CT001-I02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01I3', `description` = 'CKG Tiang 001 Cell I03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 536; -- name=CT001-I03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01I4', `description` = 'CKG Tiang 001 Cell I04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 537; -- name=CT001-I04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01J1', `description` = 'CKG Tiang 001 Cell J01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 538; -- name=CT001-J01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01J2', `description` = 'CKG Tiang 001 Cell J02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 539; -- name=CT001-J02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01J3', `description` = 'CKG Tiang 001 Cell J03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 540; -- name=CT001-J03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01J4', `description` = 'CKG Tiang 001 Cell J04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 541; -- name=CT001-J04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01K1', `description` = 'CKG Tiang 001 Cell K01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 542; -- name=CT001-K01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01K2', `description` = 'CKG Tiang 001 Cell K02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 543; -- name=CT001-K02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01K3', `description` = 'CKG Tiang 001 Cell K03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 544; -- name=CT001-K03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01K4', `description` = 'CKG Tiang 001 Cell K04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 545; -- name=CT001-K04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01L1', `description` = 'CKG Tiang 001 Cell L01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 546; -- name=CT001-L01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01L2', `description` = 'CKG Tiang 001 Cell L02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 547; -- name=CT001-L02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01L3', `description` = 'CKG Tiang 001 Cell L03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 548; -- name=CT001-L03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01L4', `description` = 'CKG Tiang 001 Cell L04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 549; -- name=CT001-L04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01M', `description` = 'CKG Tiang 001 Cell M00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 550; -- name=CT001-M00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01N', `description` = 'CKG Tiang 001 Cell N00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 551; -- name=CT001-N00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01O', `description` = 'CKG Tiang 001 Cell O00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 552; -- name=CT001-O00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01P', `description` = 'CKG Tiang 001 Cell P00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 553; -- name=CT001-P00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01Q', `description` = 'CKG Tiang 001 Cell Q00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 554; -- name=CT001-Q00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01R', `description` = 'CKG Tiang 001 Cell R01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 555; -- name=CT001-R01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01S', `description` = 'CKG Tiang 001 Cell S01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 556; -- name=CT001-S01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT02A1', `description` = 'CKG Tiang 002 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 557; -- name=CT002-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT02B1', `description` = 'CKG Tiang 002 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 558; -- name=CT002-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT02C1', `description` = 'CKG Tiang 002 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 559; -- name=CT002-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT02D1', `description` = 'CKG Tiang 002 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 560; -- name=CT002-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT02A2', `description` = 'CKG Tiang 002 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 561; -- name=CT002-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT02B2', `description` = 'CKG Tiang 002 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 562; -- name=CT002-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT02C2', `description` = 'CKG Tiang 002 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 563; -- name=CT002-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT02D2', `description` = 'CKG Tiang 002 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 564; -- name=CT002-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT03A1', `description` = 'CKG Tiang 003 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 565; -- name=CT003-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT03B1', `description` = 'CKG Tiang 003 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 566; -- name=CT003-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT03C1', `description` = 'CKG Tiang 003 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 567; -- name=CT003-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT03D1', `description` = 'CKG Tiang 003 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 568; -- name=CT003-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT04A1', `description` = 'CKG Tiang 004 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 569; -- name=CT004-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT04B1', `description` = 'CKG Tiang 004 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 570; -- name=CT004-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT04C1', `description` = 'CKG Tiang 004 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 571; -- name=CT004-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT04D1', `description` = 'CKG Tiang 004 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 572; -- name=CT004-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT04A2', `description` = 'CKG Tiang 004 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 573; -- name=CT004-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT04B2', `description` = 'CKG Tiang 004 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 574; -- name=CT004-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT04C2', `description` = 'CKG Tiang 004 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 575; -- name=CT004-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT05A1', `description` = 'CKG Tiang 005 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 576; -- name=CT005-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT05B1', `description` = 'CKG Tiang 005 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 577; -- name=CT005-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT05C1', `description` = 'CKG Tiang 005 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 578; -- name=CT005-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT06A1', `description` = 'CKG Tiang 006 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 579; -- name=CT006-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT06B1', `description` = 'CKG Tiang 006 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 580; -- name=CT006-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT06C1', `description` = 'CKG Tiang 006 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 581; -- name=CT006-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT06D1', `description` = 'CKG Tiang 006 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 582; -- name=CT006-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT06B2', `description` = 'CKG Tiang 006 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 583; -- name=CT006-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT06C2', `description` = 'CKG Tiang 006 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 584; -- name=CT006-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT06D2', `description` = 'CKG Tiang 006 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 585; -- name=CT006-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT06B3', `description` = 'CKG Tiang 006 Cell B03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 586; -- name=CT006-B03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT06C3', `description` = 'CKG Tiang 006 Cell C03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 587; -- name=CT006-C03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT06D3', `description` = 'CKG Tiang 006 Cell D03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 588; -- name=CT006-D03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT07A1', `description` = 'CKG Tiang 007 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 589; -- name=CT007-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT07B1', `description` = 'CKG Tiang 007 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 590; -- name=CT007-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT07C1', `description` = 'CKG Tiang 007 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 591; -- name=CT007-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT07D1', `description` = 'CKG Tiang 007 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 592; -- name=CT007-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT07A2', `description` = 'CKG Tiang 007 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 593; -- name=CT007-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT07B2', `description` = 'CKG Tiang 007 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 594; -- name=CT007-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT07C2', `description` = 'CKG Tiang 007 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 595; -- name=CT007-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT08A1', `description` = 'CKG Tiang 008 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 596; -- name=CT008-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT08B1', `description` = 'CKG Tiang 008 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 597; -- name=CT008-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT08C1', `description` = 'CKG Tiang 008 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 598; -- name=CT008-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT08D1', `description` = 'CKG Tiang 008 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 599; -- name=CT008-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT09A1', `description` = 'CKG Tiang 009 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 600; -- name=CT009-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT09B1', `description` = 'CKG Tiang 009 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 601; -- name=CT009-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT09C1', `description` = 'CKG Tiang 009 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 602; -- name=CT009-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT10A1', `description` = 'CKG Tiang 010 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 603; -- name=CT010-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT10B1', `description` = 'CKG Tiang 010 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 604; -- name=CT010-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT10C1', `description` = 'CKG Tiang 010 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 605; -- name=CT010-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT10D1', `description` = 'CKG Tiang 010 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 606; -- name=CT010-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT10A2', `description` = 'CKG Tiang 010 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 607; -- name=CT010-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT10B2', `description` = 'CKG Tiang 010 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 608; -- name=CT010-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT10C2', `description` = 'CKG Tiang 010 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 609; -- name=CT010-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT10D2', `description` = 'CKG Tiang 010 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 610; -- name=CT010-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT11A1', `description` = 'CKG Tiang 011 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 611; -- name=CT011-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT11B1', `description` = 'CKG Tiang 011 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 612; -- name=CT011-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT11C1', `description` = 'CKG Tiang 011 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 613; -- name=CT011-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT11D1', `description` = 'CKG Tiang 011 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 614; -- name=CT011-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT12A1', `description` = 'CKG Tiang 012 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 615; -- name=CT012-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT12B1', `description` = 'CKG Tiang 012 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 616; -- name=CT012-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT12C1', `description` = 'CKG Tiang 012 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 617; -- name=CT012-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT12D1', `description` = 'CKG Tiang 012 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 618; -- name=CT012-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT12A2', `description` = 'CKG Tiang 012 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 619; -- name=CT012-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT12B2', `description` = 'CKG Tiang 012 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 620; -- name=CT012-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT12C2', `description` = 'CKG Tiang 012 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 621; -- name=CT012-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT12D2', `description` = 'CKG Tiang 012 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 622; -- name=CT012-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT13A1', `description` = 'CKG Tiang 013 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 623; -- name=CT013-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT13B1', `description` = 'CKG Tiang 013 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 624; -- name=CT013-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT13C1', `description` = 'CKG Tiang 013 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 625; -- name=CT013-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT13D1', `description` = 'CKG Tiang 013 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 626; -- name=CT013-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT13E1', `description` = 'CKG Tiang 013 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 627; -- name=CT013-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT13A2', `description` = 'CKG Tiang 013 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 628; -- name=CT013-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT13B2', `description` = 'CKG Tiang 013 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 629; -- name=CT013-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT13C2', `description` = 'CKG Tiang 013 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 630; -- name=CT013-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT13D2', `description` = 'CKG Tiang 013 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 631; -- name=CT013-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT14A1', `description` = 'CKG Tiang 014 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 632; -- name=CT014-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT14B1', `description` = 'CKG Tiang 014 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 633; -- name=CT014-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT14C1', `description` = 'CKG Tiang 014 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 634; -- name=CT014-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT14D1', `description` = 'CKG Tiang 014 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 635; -- name=CT014-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT14E1', `description` = 'CKG Tiang 014 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 636; -- name=CT014-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT15A1', `description` = 'CKG Tiang 015 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 637; -- name=CT015-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT15B1', `description` = 'CKG Tiang 015 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 638; -- name=CT015-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT15C1', `description` = 'CKG Tiang 015 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 639; -- name=CT015-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT15D1', `description` = 'CKG Tiang 015 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 640; -- name=CT015-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT15A2', `description` = 'CKG Tiang 015 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 641; -- name=CT015-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT15B2', `description` = 'CKG Tiang 015 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 642; -- name=CT015-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT15C2', `description` = 'CKG Tiang 015 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 643; -- name=CT015-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT16A1', `description` = 'CKG Tiang 016 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 644; -- name=CT016-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT16B1', `description` = 'CKG Tiang 016 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 645; -- name=CT016-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT16C1', `description` = 'CKG Tiang 016 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 646; -- name=CT016-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT16D1', `description` = 'CKG Tiang 016 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 647; -- name=CT016-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT17A1', `description` = 'CKG Tiang 017 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 648; -- name=CT017-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT17B1', `description` = 'CKG Tiang 017 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 649; -- name=CT017-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT17C1', `description` = 'CKG Tiang 017 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 650; -- name=CT017-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT17D1', `description` = 'CKG Tiang 017 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 651; -- name=CT017-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT17A2', `description` = 'CKG Tiang 017 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 652; -- name=CT017-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT17B2', `description` = 'CKG Tiang 017 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 653; -- name=CT017-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT17C2', `description` = 'CKG Tiang 017 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 654; -- name=CT017-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT17D2', `description` = 'CKG Tiang 017 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 655; -- name=CT017-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT18A1', `description` = 'CKG Tiang 018 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 656; -- name=CT018-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT18B1', `description` = 'CKG Tiang 018 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 657; -- name=CT018-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT18C1', `description` = 'CKG Tiang 018 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 658; -- name=CT018-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT18D1', `description` = 'CKG Tiang 018 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 659; -- name=CT018-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRK01', `description` = 'CKG Rak 046 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 660; -- name=CR046-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRK02', `description` = 'CKG Rak 046 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 661; -- name=CR046-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRK03', `description` = 'CKG Rak 046 Cell A03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 662; -- name=CR046-A03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRK04', `description` = 'CKG Rak 046 Cell A04', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 663; -- name=CR046-A04, plant_id=2
UPDATE `locations` SET `old_location_name` = 'SR01A01', `description` = 'SDJ Rak 001 Cell A01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 664; -- name=SR001-A01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR01A02', `description` = 'SDJ Rak 001 Cell A02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 665; -- name=SR001-A02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR01A03', `description` = 'SDJ Rak 001 Cell A03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 666; -- name=SR001-A03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR01B01', `description` = 'SDJ Rak 001 Cell B01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 667; -- name=SR001-B01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR01B02', `description` = 'SDJ Rak 001 Cell B02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 668; -- name=SR001-B02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR01B03', `description` = 'SDJ Rak 001 Cell B03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 669; -- name=SR001-B03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR01C01', `description` = 'SDJ Rak 001 Cell C01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 670; -- name=SR001-C01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR01C02', `description` = 'SDJ Rak 001 Cell C02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 671; -- name=SR001-C02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR01C03', `description` = 'SDJ Rak 001 Cell C03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 672; -- name=SR001-C03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR01D01', `description` = 'SDJ Rak 001 Cell D01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 673; -- name=SR001-D01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR01D02', `description` = 'SDJ Rak 001 Cell D02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 674; -- name=SR001-D02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR01D03', `description` = 'SDJ Rak 001 Cell D03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 675; -- name=SR001-D03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR02A01', `description` = 'SDJ Rak 002 Cell A01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 676; -- name=SR002-A01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR02A02', `description` = 'SDJ Rak 002 Cell A02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 677; -- name=SR002-A02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR02A03', `description` = 'SDJ Rak 002 Cell A03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 678; -- name=SR002-A03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR02B01', `description` = 'SDJ Rak 002 Cell B01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 679; -- name=SR002-B01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR02B02', `description` = 'SDJ Rak 002 Cell B02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 680; -- name=SR002-B02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR02B03', `description` = 'SDJ Rak 002 Cell B03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 681; -- name=SR002-B03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR02C01', `description` = 'SDJ Rak 002 Cell C01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 682; -- name=SR002-C01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR02C02', `description` = 'SDJ Rak 002 Cell C02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 683; -- name=SR002-C02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR02C03', `description` = 'SDJ Rak 002 Cell C03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 684; -- name=SR002-C03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR02D01', `description` = 'SDJ Rak 002 Cell D01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 685; -- name=SR002-D01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR02D02', `description` = 'SDJ Rak 002 Cell D02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 686; -- name=SR002-D02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR02D03', `description` = 'SDJ Rak 002 Cell D03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 687; -- name=SR002-D03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR03A01', `description` = 'SDJ Rak 003 Cell A01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 688; -- name=SR003-A01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR03A02', `description` = 'SDJ Rak 003 Cell A02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 689; -- name=SR003-A02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR03A03', `description` = 'SDJ Rak 003 Cell A03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 690; -- name=SR003-A03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR03B01', `description` = 'SDJ Rak 003 Cell B01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 691; -- name=SR003-B01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR03B02', `description` = 'SDJ Rak 003 Cell B02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 692; -- name=SR003-B02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR03B03', `description` = 'SDJ Rak 003 Cell B03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 693; -- name=SR003-B03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR03C01', `description` = 'SDJ Rak 003 Cell C01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 694; -- name=SR003-C01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR03C02', `description` = 'SDJ Rak 003 Cell C02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 695; -- name=SR003-C02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR03C03', `description` = 'SDJ Rak 003 Cell C03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 696; -- name=SR003-C03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR03D01', `description` = 'SDJ Rak 003 Cell D01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 697; -- name=SR003-D01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR03D02', `description` = 'SDJ Rak 003 Cell D02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 698; -- name=SR003-D02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR03D03', `description` = 'SDJ Rak 003 Cell D03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 699; -- name=SR003-D03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04A01', `description` = 'SDJ Rak 004 Cell A01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 700; -- name=SR004-A01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04A02', `description` = 'SDJ Rak 004 Cell A02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 701; -- name=SR004-A02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04A03', `description` = 'SDJ Rak 004 Cell A03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 702; -- name=SR004-A03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04A04', `description` = 'SDJ Rak 004 Cell A04', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 703; -- name=SR004-A04, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04B01', `description` = 'SDJ Rak 004 Cell B01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 704; -- name=SR004-B01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04B02', `description` = 'SDJ Rak 004 Cell B02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 705; -- name=SR004-B02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04B03', `description` = 'SDJ Rak 004 Cell B03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 706; -- name=SR004-B03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04B04', `description` = 'SDJ Rak 004 Cell B04', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 707; -- name=SR004-B04, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04C01', `description` = 'SDJ Rak 004 Cell C01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 708; -- name=SR004-C01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04C02', `description` = 'SDJ Rak 004 Cell C02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 709; -- name=SR004-C02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04C03', `description` = 'SDJ Rak 004 Cell C03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 710; -- name=SR004-C03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04C04', `description` = 'SDJ Rak 004 Cell C04', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 711; -- name=SR004-C04, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04D01', `description` = 'SDJ Rak 004 Cell D01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 712; -- name=SR004-D01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04D02', `description` = 'SDJ Rak 004 Cell D02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 713; -- name=SR004-D02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04D03', `description` = 'SDJ Rak 004 Cell D03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 714; -- name=SR004-D03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04D04', `description` = 'SDJ Rak 004 Cell D04', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 715; -- name=SR004-D04, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04E01', `description` = 'SDJ Rak 004 Cell E01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 716; -- name=SR004-E01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04E02', `description` = 'SDJ Rak 004 Cell E02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 717; -- name=SR004-E02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04E03', `description` = 'SDJ Rak 004 Cell E03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 718; -- name=SR004-E03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04E04', `description` = 'SDJ Rak 004 Cell E04', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 719; -- name=SR004-E04, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SL01', `description` = 'SDJ Lantai 001', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 720; -- name=SL001-X00, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01A1', `description` = 'SDJ Tiang 001 Cell A01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 721; -- name=ST001-A01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01A2', `description` = 'SDJ Tiang 001 Cell A02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 722; -- name=ST001-A02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01A3', `description` = 'SDJ Tiang 001 Cell A03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 723; -- name=ST001-A03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01A4', `description` = 'SDJ Tiang 001 Cell A04', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 724; -- name=ST001-A04, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01A5', `description` = 'SDJ Tiang 001 Cell A05', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 725; -- name=ST001-A05, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01B1', `description` = 'SDJ Tiang 001 Cell B01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 726; -- name=ST001-B01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01B2', `description` = 'SDJ Tiang 001 Cell B02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 727; -- name=ST001-B02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01B3', `description` = 'SDJ Tiang 001 Cell B03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 728; -- name=ST001-B03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01B4', `description` = 'SDJ Tiang 001 Cell B04', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 729; -- name=ST001-B04, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01B5', `description` = 'SDJ Tiang 001 Cell B05', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 730; -- name=ST001-B05, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01C1', `description` = 'SDJ Tiang 001 Cell C01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 731; -- name=ST001-C01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01C2', `description` = 'SDJ Tiang 001 Cell C02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 732; -- name=ST001-C02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01C3', `description` = 'SDJ Tiang 001 Cell C03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 733; -- name=ST001-C03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01C4', `description` = 'SDJ Tiang 001 Cell C04', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 734; -- name=ST001-C04, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01C5', `description` = 'SDJ Tiang 001 Cell C05', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 735; -- name=ST001-C05, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01D1', `description` = 'SDJ Tiang 001 Cell D01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 736; -- name=ST001-D01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01D2', `description` = 'SDJ Tiang 001 Cell D02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 737; -- name=ST001-D02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01D3', `description` = 'SDJ Tiang 001 Cell D03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 738; -- name=ST001-D03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01D4', `description` = 'SDJ Tiang 001 Cell D04', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 739; -- name=ST001-D04, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01D5', `description` = 'SDJ Tiang 001 Cell D05', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 740; -- name=ST001-D05, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01E1', `description` = 'SDJ Tiang 001 Cell E01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 741; -- name=ST001-E01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01E2', `description` = 'SDJ Tiang 001 Cell E02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 742; -- name=ST001-E02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01E3', `description` = 'SDJ Tiang 001 Cell E03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 743; -- name=ST001-E03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'DL01', `description` = 'DS8 Lantai 001', `warehouse` = 'DWRM1', `updated_at` = NOW() WHERE `id` = 744; -- name=DL001-X00, plant_id=1
UPDATE `locations` SET `old_location_name` = 'DL02', `description` = 'DS8 Lantai 002', `warehouse` = 'DWRM1', `updated_at` = NOW() WHERE `id` = 745; -- name=DL002-X00, plant_id=1
UPDATE `locations` SET `old_location_name` = 'CT01T', `description` = 'CKG Tiang 001 Cell T00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 746; -- name=CT001-T00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01U', `description` = 'CKG Tiang 001 Cell U00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 747; -- name=CT001-U00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT01V', `description` = 'CKG Tiang 001 Cell V00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 748; -- name=CT001-V00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT19A1', `description` = 'CKG Tiang 019 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 749; -- name=CT019-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT19B1', `description` = 'CKG Tiang 019 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 750; -- name=CT019-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT19C1', `description` = 'CKG Tiang 019 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 751; -- name=CT019-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT19D1', `description` = 'CKG Tiang 019 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 752; -- name=CT019-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT19A2', `description` = 'CKG Tiang 019 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 753; -- name=CT019-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT19B2', `description` = 'CKG Tiang 019 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 754; -- name=CT019-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT19C2', `description` = 'CKG Tiang 019 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 755; -- name=CT019-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT19D2', `description` = 'CKG Tiang 019 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 756; -- name=CT019-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT20A1', `description` = 'CKG Tiang 020 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 757; -- name=CT020-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT20B1', `description` = 'CKG Tiang 020 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 758; -- name=CT020-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT20C1', `description` = 'CKG Tiang 020 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 759; -- name=CT020-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT20D1', `description` = 'CKG Tiang 020 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 760; -- name=CT020-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT21A1', `description` = 'CKG Tiang 021 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 761; -- name=CT021-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT21B1', `description` = 'CKG Tiang 021 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 762; -- name=CT021-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT21C1', `description` = 'CKG Tiang 021 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 763; -- name=CT021-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT21A2', `description` = 'CKG Tiang 021 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 764; -- name=CT021-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT21B2', `description` = 'CKG Tiang 021 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 765; -- name=CT021-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT21C2', `description` = 'CKG Tiang 021 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 766; -- name=CT021-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT21D2', `description` = 'CKG Tiang 021 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 767; -- name=CT021-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT22A1', `description` = 'CKG Tiang 022 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 768; -- name=CT022-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT22B1', `description` = 'CKG Tiang 022 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 769; -- name=CT022-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT22C1', `description` = 'CKG Tiang 022 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 770; -- name=CT022-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT22D1', `description` = 'CKG Tiang 022 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 771; -- name=CT022-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT23A1', `description` = 'CKG Tiang 023 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 772; -- name=CT023-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT23B1', `description` = 'CKG Tiang 023 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 773; -- name=CT023-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT23C1', `description` = 'CKG Tiang 023 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 774; -- name=CT023-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT24A1', `description` = 'CKG Tiang 024 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 775; -- name=CT024-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT24B1', `description` = 'CKG Tiang 024 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 776; -- name=CT024-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT24C1', `description` = 'CKG Tiang 024 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 777; -- name=CT024-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT24D1', `description` = 'CKG Tiang 024 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 778; -- name=CT024-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT24A2', `description` = 'CKG Tiang 024 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 779; -- name=CT024-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT24B2', `description` = 'CKG Tiang 024 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 780; -- name=CT024-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT24C2', `description` = 'CKG Tiang 024 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 781; -- name=CT024-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT25A1', `description` = 'CKG Tiang 025 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 782; -- name=CT025-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT25B1', `description` = 'CKG Tiang 025 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 783; -- name=CT025-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT25C1', `description` = 'CKG Tiang 025 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 784; -- name=CT025-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT25D1', `description` = 'CKG Tiang 025 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 785; -- name=CT025-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT25A2', `description` = 'CKG Tiang 025 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 786; -- name=CT025-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT25B2', `description` = 'CKG Tiang 025 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 787; -- name=CT025-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT25C2', `description` = 'CKG Tiang 025 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 788; -- name=CT025-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT26A1', `description` = 'CKG Tiang 026 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 789; -- name=CT026-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT26B1', `description` = 'CKG Tiang 026 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 790; -- name=CT026-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT26C1', `description` = 'CKG Tiang 026 Cell C01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 791; -- name=CT026-C01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT26D1', `description` = 'CKG Tiang 026 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 792; -- name=CT026-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT26A2', `description` = 'CKG Tiang 026 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 793; -- name=CT026-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT26B2', `description` = 'CKG Tiang 026 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 794; -- name=CT026-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT26C2', `description` = 'CKG Tiang 026 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 795; -- name=CT026-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT26D2', `description` = 'CKG Tiang 026 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 796; -- name=CT026-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP1', `description` = 'CKG Palet 001', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 797; -- name=CP001-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP2', `description` = 'CKG Palet 002', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 798; -- name=CP002-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP3', `description` = 'CKG Palet 003', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 799; -- name=CP003-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP4', `description` = 'CKG Palet 004', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 800; -- name=CP004-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP5', `description` = 'CKG Palet 005', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 801; -- name=CP005-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP6', `description` = 'CKG Palet 006', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 802; -- name=CP006-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP7', `description` = 'CKG Palet 007', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 803; -- name=CP007-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP8', `description` = 'CKG Palet 008', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 804; -- name=CP008-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP9', `description` = 'CKG Palet 009', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 805; -- name=CP009-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP10', `description` = 'CKG Palet 010', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 806; -- name=CP010-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP11', `description` = 'CKG Palet 011', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 807; -- name=CP011-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP12', `description` = 'CKG Palet 012', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 808; -- name=CP012-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP13', `description` = 'CKG Palet 013', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 809; -- name=CP013-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP14', `description` = 'CKG Palet 014', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 810; -- name=CP014-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP15', `description` = 'CKG Palet 015', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 811; -- name=CP015-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP16', `description` = 'CKG Palet 016', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 812; -- name=CP016-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP17', `description` = 'CKG Palet 017', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 813; -- name=CP017-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP18', `description` = 'CKG Palet 018', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 814; -- name=CP018-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP19', `description` = 'CKG Palet 019', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 815; -- name=CP019-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP20', `description` = 'CKG Palet 020', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 816; -- name=CP020-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP21', `description` = 'CKG Palet 021', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 817; -- name=CP021-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP22', `description` = 'CKG Palet 022', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 818; -- name=CP022-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP23', `description` = 'CKG Palet 023', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 819; -- name=CP023-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP24', `description` = 'CKG Palet 024', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 820; -- name=CP024-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP25', `description` = 'CKG Palet 025', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 821; -- name=CP025-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP26', `description` = 'CKG Palet 026', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 822; -- name=CP026-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP27', `description` = 'CKG Palet 027', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 823; -- name=CP027-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP28', `description` = 'CKG Palet 028', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 824; -- name=CP028-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP29', `description` = 'CKG Palet 029', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 825; -- name=CP029-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP30', `description` = 'CKG Palet 030', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 826; -- name=CP030-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP31', `description` = 'CKG Palet 031', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 827; -- name=CP031-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP32', `description` = 'CKG Palet 032', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 828; -- name=CP032-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP33', `description` = 'CKG Palet 033', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 829; -- name=CP033-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP34', `description` = 'CKG Palet 034', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 830; -- name=CP034-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP35', `description` = 'CKG Palet 035', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 831; -- name=CP035-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP36', `description` = 'CKG Palet 036', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 832; -- name=CP036-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP37', `description` = 'CKG Palet 037', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 833; -- name=CP037-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP38', `description` = 'CKG Palet 038', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 834; -- name=CP038-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP39', `description` = 'CKG Palet 039', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 835; -- name=CP039-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP40', `description` = 'CKG Palet 040', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 836; -- name=CP040-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP41', `description` = 'CKG Palet 041', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 837; -- name=CP041-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP42', `description` = 'CKG Palet 042', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 838; -- name=CP042-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP43', `description` = 'CKG Palet 043', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 839; -- name=CP043-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP44', `description` = 'CKG Palet 044', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 840; -- name=CP044-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP45', `description` = 'CKG Palet 045', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 841; -- name=CP045-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP46', `description` = 'CKG Palet 046', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 842; -- name=CP046-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP47', `description` = 'CKG Palet 047', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 843; -- name=CP047-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP48', `description` = 'CKG Palet 048', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 844; -- name=CP048-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP49', `description` = 'CKG Palet 049', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 845; -- name=CP049-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP50', `description` = 'CKG Palet 050', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 846; -- name=CP050-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP51', `description` = 'CKG Palet 051', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 847; -- name=CP051-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP52', `description` = 'CKG Palet 052', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 848; -- name=CP052-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP53', `description` = 'CKG Palet 053', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 849; -- name=CP053-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP54', `description` = 'CKG Palet 054', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 850; -- name=CP054-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP55', `description` = 'CKG Palet 055', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 851; -- name=CP055-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP56', `description` = 'CKG Palet 056', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 852; -- name=CP056-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP57', `description` = 'CKG Palet 057', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 853; -- name=CP057-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP58', `description` = 'CKG Palet 058', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 854; -- name=CP058-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP59', `description` = 'CKG Palet 059', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 855; -- name=CP059-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP60', `description` = 'CKG Palet 060', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 856; -- name=CP060-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP61', `description` = 'CKG Palet 061', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 857; -- name=CP061-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP62', `description` = 'CKG Palet 062', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 858; -- name=CP062-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CP63', `description` = 'CKG Palet 063', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 859; -- name=CP063-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT1', `description` = 'CKG Palet 064 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 860; -- name=CP064-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT2', `description` = 'CKG Palet 065 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 861; -- name=CP065-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT3', `description` = 'CKG Palet 066 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 862; -- name=CP066-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT4', `description` = 'CKG Palet 067 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 863; -- name=CP067-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT5', `description` = 'CKG Palet 068 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 864; -- name=CP068-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT6', `description` = 'CKG Palet 069 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 865; -- name=CP069-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT7', `description` = 'CKG Palet 070 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 866; -- name=CP070-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT8', `description` = 'CKG Palet 071 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 867; -- name=CP071-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT9', `description` = 'CKG Palet 072 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 868; -- name=CP072-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT10', `description` = 'CKG Palet 073 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 869; -- name=CP073-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT11', `description` = 'CKG Palet 074 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 870; -- name=CP074-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT12', `description` = 'CKG Palet 075 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 871; -- name=CP075-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT13', `description` = 'CKG Palet 076 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 872; -- name=CP076-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT14', `description` = 'CKG Palet 077 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 873; -- name=CP077-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT15', `description` = 'CKG Palet 078 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 874; -- name=CP078-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CL32', `description` = 'CKG Lantai 032', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 875; -- name=CL032-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CLMAI', `description` = 'CKG Lantai 000 (CLMAI)', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 876; -- name=CL000-X01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT16', `description` = 'CKG Palet 079 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 877; -- name=CP079-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT17', `description` = 'CKG Palet 080 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 878; -- name=CP080-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT18', `description` = 'CKG Palet 081 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 879; -- name=CP081-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT19', `description` = 'CKG Palet 082 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 880; -- name=CP082-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT20', `description` = 'CKG Palet 083 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 881; -- name=CP083-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT21', `description` = 'CKG Palet 084 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 882; -- name=CP084-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CPT22', `description` = 'CKG Palet 085 EXTGR', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 883; -- name=CP085-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02H1', `description` = 'CKG Rak 002 Cell H01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 884; -- name=CR002-H01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR02H2', `description` = 'CKG Rak 002 Cell H02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 885; -- name=CR002-H02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR05E1', `description` = 'CKG Rak 005 Cell E01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 886; -- name=CR005-E01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR07D2', `description` = 'CKG Rak 007 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 887; -- name=CR007-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR101A', `description` = 'CKG Rak 101 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 888; -- name=CR101-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR101A1', `description` = 'CKG Rak 101 Cell A01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 889; -- name=CR101-A01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR17B1', `description` = 'CKG Rak 017 Cell B01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 890; -- name=CR017-B01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR18A', `description` = 'CKG Rak 018 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 891; -- name=CR018-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR22B', `description` = 'CKG Rak 022 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 892; -- name=CR022-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR23C', `description` = 'CKG Rak 023 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 893; -- name=CR023-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR25B', `description` = 'CKG Rak 025 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 894; -- name=CR025-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR26A', `description` = 'CKG Rak 026 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 895; -- name=CR026-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR26G', `description` = 'CKG Rak 026 Cell G00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 896; -- name=CR026-G00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35A', `description` = 'CKG Rak 035 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 897; -- name=CR035-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35B', `description` = 'CKG Rak 035 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 898; -- name=CR035-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35C', `description` = 'CKG Rak 035 Cell C00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 899; -- name=CR035-C00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35D', `description` = 'CKG Rak 035 Cell D00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 900; -- name=CR035-D00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR35E', `description` = 'CKG Rak 035 Cell E00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 901; -- name=CR035-E00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR38B', `description` = 'CKG Rak 038 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 902; -- name=CR038-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39A', `description` = 'CKG Rak 039 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 903; -- name=CR039-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR39B', `description` = 'CKG Rak 039 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 904; -- name=CR039-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CR40B', `description` = 'CKG Rak 040 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 905; -- name=CR040-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRD16', `description` = 'CKG Rak 047 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 906; -- name=CR047-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRE2', `description` = 'CKG Rak 048 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 907; -- name=CR048-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRF52', `description` = 'CKG Rak 049 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 908; -- name=CR049-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRF70', `description` = 'CKG Rak 050 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 909; -- name=CR050-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRS01', `description` = 'CKG Rak 051 Cell A00 EXSDJ', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 910; -- name=CR051-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CRS02', `description` = 'CKG Rak 052 Cell A00 EXSDJ', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 911; -- name=CR052-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT03A2', `description` = 'CKG Tiang 003 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 912; -- name=CT003-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT03B2', `description` = 'CKG Tiang 003 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 913; -- name=CT003-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT05A2', `description` = 'CKG Tiang 005 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 914; -- name=CT005-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT05D1', `description` = 'CKG Tiang 005 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 915; -- name=CT005-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT08A2', `description` = 'CKG Tiang 008 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 916; -- name=CT008-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT08B2', `description` = 'CKG Tiang 008 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 917; -- name=CT008-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT09A2', `description` = 'CKG Tiang 009 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 918; -- name=CT009-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT09B2', `description` = 'CKG Tiang 009 Cell B02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 919; -- name=CT009-B02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT09C2', `description` = 'CKG Tiang 009 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 920; -- name=CT009-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT09D1', `description` = 'CKG Tiang 009 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 921; -- name=CT009-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT09D2', `description` = 'CKG Tiang 009 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 922; -- name=CT009-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT11A2', `description` = 'CKG Tiang 011 Cell A02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 923; -- name=CT011-A02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT11C2', `description` = 'CKG Tiang 011 Cell C02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 924; -- name=CT011-C02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT1201', `description` = 'CKG Tiang 012 Cell X01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 925; -- name=CT012-X01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT12B3', `description` = 'CKG Tiang 012 Cell B03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 926; -- name=CT012-B03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT12C3', `description` = 'CKG Tiang 012 Cell C03', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 927; -- name=CT012-C03, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT23D1', `description` = 'CKG Tiang 023 Cell D01', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 928; -- name=CT023-D01, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT24A', `description` = 'CKG Tiang 024 Cell A00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 929; -- name=CT024-A00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT24B', `description` = 'CKG Tiang 024 Cell B00', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 930; -- name=CT024-B00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CT24D2', `description` = 'CKG Tiang 024 Cell D02', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 931; -- name=CT024-D02, plant_id=2
UPDATE `locations` SET `old_location_name` = 'CTEMP', `description` = 'CKG Lantai 000 (CTEMP)', `warehouse` = 'CWRM1', `updated_at` = NOW() WHERE `id` = 932; -- name=CL000-X00, plant_id=2
UPDATE `locations` SET `old_location_name` = 'DS8', `description` = 'DS8 Lantai 000 (DS8)', `warehouse` = 'DWRM1', `updated_at` = NOW() WHERE `id` = 933; -- name=DL000-X00, plant_id=1
UPDATE `locations` SET `old_location_name` = 'SR04F2', `description` = 'SDJ Rak 004 Cell F02', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 934; -- name=SR004-F02, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04F3', `description` = 'SDJ Rak 004 Cell F03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 935; -- name=SR004-F03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04G1', `description` = 'SDJ Rak 004 Cell G01', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 936; -- name=SR004-G01, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04G3', `description` = 'SDJ Rak 004 Cell G03', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 937; -- name=SR004-G03, plant_id=3
UPDATE `locations` SET `old_location_name` = 'SR04G4', `description` = 'SDJ Rak 004 Cell G04', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 938; -- name=SR004-G04, plant_id=3
UPDATE `locations` SET `old_location_name` = 'ST01E4', `description` = 'SDJ Tiang 001 Cell E04', `warehouse` = 'SWRM1', `updated_at` = NOW() WHERE `id` = 939; -- name=ST001-E04, plant_id=3

-- ---------------------------------------------------------------------
-- INSERT: 16 baris baru (name belum ada di database)
-- ---------------------------------------------------------------------
INSERT INTO `locations` (`plant_id`, `name`, `old_location_name`, `description`, `warehouse`, `is_active`, `is_confirmed`, `created_at`, `updated_at`) VALUES
(2, 'CT003-C02', 'CT03C2', 'CKG Tiang 003 Cell C02', 'CWRM1', 1, 0, NOW(), NOW()),
(2, 'CT005-B02', 'CT05B2', 'CKG Tiang 005 Cell B02', 'CWRM1', 1, 0, NOW(), NOW()),
(2, 'CT005-B03', 'CT05B3', 'CKG Tiang 005 Cell B03', 'CWRM1', 1, 0, NOW(), NOW()),
(2, 'CT006-A02', 'CT06A2', 'CKG Tiang 006 Cell A02', 'CWRM1', 1, 0, NOW(), NOW()),
(2, 'CT008-C02', 'CT08C1', 'CKG Tiang 008 Cell C02', 'CWRM1', 1, 0, NOW(), NOW()),
(2, 'CT011-B02', 'CT11B2', 'CKG Tiang 011 Cell B02', 'CWRM1', 1, 0, NOW(), NOW()),
(1, 'DR001-X00', 'DR01', 'DS8 Rak 001', 'DWRM1', 1, 0, NOW(), NOW()),
(1, 'DR002-X00', 'DR02', 'DS8 Rak 002', 'DWRM1', 1, 0, NOW(), NOW()),
(1, 'DR003-X00', 'DR03', 'DS8 Rak 003', 'DWRM1', 1, 0, NOW(), NOW()),
(1, 'DR004-X00', 'DR04', 'DS8 Rak 004', 'DWRM1', 1, 0, NOW(), NOW()),
(1, 'DR005-X00', 'DR05', 'DS8 Rak 005', 'DWRM1', 1, 0, NOW(), NOW()),
(1, 'DR006-X00', 'DR06', 'DS8 Rak 006', 'DWRM1', 1, 0, NOW(), NOW()),
(1, 'DR007-X00', 'DR07', 'DS8 Rak 007', 'DWRM1', 1, 0, NOW(), NOW()),
(1, 'DR008-X00', 'DR08', 'DS8 Rak 008', 'DWRM1', 1, 0, NOW(), NOW()),
(1, 'DR009-X00', 'DR09', 'DS8 Rak 009', 'DWRM1', 1, 0, NOW(), NOW()),
(1, 'DR010-X00', 'DR10', 'DS8 Rak 010', 'DWRM1', 1, 0, NOW(), NOW());

COMMIT;