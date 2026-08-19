<?php
// 1. Koneksi ke MySQL Laragon & Database 'pasuk'
$koneksi = mysqli_connect("localhost", "root", "");

// Buat database 'pasuk' jika belum ada (khusus untuk folder http://localhost/pasuk)
mysqli_query($koneksi, "CREATE DATABASE IF NOT EXISTS pasuk");
mysqli_select_db($koneksi, "pasuk");

// Buat tabel 'siswa' jika belum ada
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(50),
    nama VARCHAR(100),
    jurusan VARCHAR(100)
)");

// 2. Proses Simpan Data Siswa
if (isset($_POST['tambah'])) {
    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $jurusan = $_POST['jurusan'];

    mysqli_query($koneksi, "INSERT INTO siswa (nis, nama, jurusan) VALUES ('$nis', '$nama', '$jurusan')");
    $hal_sekarang = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
    header("Location: index.php?halaman=" . $hal_sekarang);
    exit;
}

// 3. Proses Hapus Data Siswa
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $hal = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
    mysqli_query($koneksi, "DELETE FROM siswa WHERE id='$id'");
    header("Location: index.php?halaman=" . $hal);
    exit;
}

// 4. Pengaturan Pagination (Halaman)
$limit = 11; // Batas data per halaman (10 data per halaman)
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($halaman < 1) $halaman = 1;
$offset = ($halaman - 1) * $limit;

// Hitung Total Data & Total Halaman
$result_count = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM siswa");
$row_count = mysqli_fetch_assoc($result_count);
$total_data = $row_count['total'];
$total_halaman = ceil($total_data / $limit);
if ($total_halaman < 1) $total_halaman = 1;

if ($halaman > $total_halaman) {
    $halaman = $total_halaman;
    $offset = ($halaman - 1) * $limit;
}

// Ambil Data Siswa Sesuai Halaman
$data_siswa = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Siswa Tugas MK Pak Sukas</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .content {
            flex: 1;
            padding: 20px;
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 8px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            box-sizing: border-box;
        }
        button {
            margin-top: 15px;
            padding: 8px 15px;
            cursor: pointer;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #333;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #eee;
        }
        .btn-hapus {
            color: red;
            text-decoration: none;
        }
        .btn-hapus:hover {
            text-decoration: underline;
        }

        /* Style Navigasi Pagination */
        .pagination {
            margin-top: 15px;
            display: flex;
            gap: 5px;
            align-items: center;
            justify-content: center;
        }
        .pagination a {
            padding: 5px 10px;
            border: 1px solid #333;
            color: #333;
            text-decoration: none;
            font-size: 13px;
        }
        .pagination a.active {
            background-color: #333;
            color: #fff;
            font-weight: bold;
        }
        .pagination a:hover:not(.active) {
            background-color: #eee;
        }

        footer {
            padding: 15px 20px;
            font-size: 100%;
            text-align: right;
            box-sizing: border-box;
        }
    </style>
</head>
<body>

    <div class="content">
        <h2>Tambah Siswa Baru</h2>
        <form method="POST">
            <label>NIS:</label>
            <input type="text" name="nis" required>

            <label>Nama:</label>
            <input type="text" name="nama" required>

            <label>Jurusan:</label>
            <input type="text" name="jurusan" required>

            <button type="submit" name="tambah">Tambah Data</button>
        </form>

        <h3 style="margin-top: 35px">List Data Siswa</h3>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Jurusan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($data_siswa) > 0) {
                    $no = $offset + 1;
                    while ($d = mysqli_fetch_array($data_siswa)) { 
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($d['nis']); ?></td>
                        <td><?= htmlspecialchars($d['nama']); ?></td>
                        <td><?= htmlspecialchars($d['jurusan']); ?></td>
                        <td>
                            <a href="index.php?hapus=<?= $d['id']; ?>&halaman=<?= $halaman; ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php 
                    } 
                } else {
                ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #777;">Belum ada data siswa.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Tombol Navigasi Halaman (Pagination) -->
        <?php if ($total_halaman > 1): ?>
        <div class="pagination">
            <?php if ($halaman > 1): ?>
                <a href="index.php?halaman=<?= $halaman - 1; ?>">&laquo; Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                <a href="index.php?halaman=<?= $i; ?>" class="<?= ($i == $halaman) ? 'active' : ''; ?>"><?= $i; ?></a>
            <?php endfor; ?>

            <?php if ($halaman < $total_halaman): ?>
                <a href="index.php?halaman=<?= $halaman + 1; ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>

    <footer>
        &#169; Evan Arganta. XI RPL 1. No. Absen 10. SMKN 2 Jakarta. Angkatan 2027/2028.
    </footer>

</body>
</html>
