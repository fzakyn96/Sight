<?php 
    
    namespace App\Service;

    class BPJSService 
    {
        protected $vclaimService;

        public function __construct()
        {
            $config = config('services.bpjs.vclaim');
            $this->vclaimService = new VclaimService($config);
        }

        public function vclaim(): VclaimService
        {
            return $this->vclaimService;
        }
    }

?>