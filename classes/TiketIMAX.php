<?php
require_once 'Tiket.php';

class TiketIMAX extends Tiket {
    private $kacamata3dId;
    private $efekGerakFitur;

    public function __construct($id_tiket, $nama_film, $jadwal_tayang, $jumlah_kursi, $hargaDasarTiket, $kacamata3dId, $efekGerakFitur) {
        parent::__construct($id_tiket, $nama_film, $jadwal_tayang, $jumlah_kursi, $hargaDasarTiket);
        $this->kacamata3dId = $kacamata3dId;
        $this->efekGerakFitur = $efekGerakFitur;
    }

    public function hitungTotalHarga() {
        return ($this->jumlah_kursi * $this->hargaDasarTiket) + 35000;
    }

    public function tampilkanInfoFasilitas() {
        $info = "Kacamata 3D: " . ($this->kacamata3dId ?? 'Tidak tersedia');
        $info .= " | Efek Gerak: " . ($this->efekGerakFitur ?? 'Tidak tersedia');
        return $info;
    }

    public function getKacamata3dId() { return $this->kacamata3dId; }
    public function getEfekGerakFitur() { return $this->efekGerakFitur; }
}
?>