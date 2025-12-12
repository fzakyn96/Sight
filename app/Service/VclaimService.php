<?php 
    namespace App\Service;

    class VclaimService extends BaseBPJSService 
    {
        public function getPesertaByNoKartu(string $nokartu, string $tglPelayanan): array
        {
            $endpoint = "peserta/nokartu/{$nokartu}/tglsep/{$tglPelayanan}";
            return $this->getRequest($endpoint);
        }

        public function getPesertaByNIK(string $nik, string $tglPelayanan): array
        {
            $endpoint = "peserta/nik/{$nik}/tglsep/{$tglPelayanan}";
            return $this->getRequest($endpoint);
        }

        public function referensiDiagnosa(string $diagnosa): array
        {
            $endpoint = "referensi/diagnosa/{$diagnosa}";
            return $this->getRequest($endpoint);
        }

    }
?>