<?php

namespace Propaganistas\LaravelFakeId\Commands;

use Illuminate\Console\Command;
use Jenssegers\Optimus\Optimus;
use phpseclib\Crypt\Random;
use phpseclib\Math\BigInteger;

class FakeIdSetupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'fakeid:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configures FakeId for use';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // Get a pseudo-random prime.
        $min = new BigInteger(1e7);
        $max = new BigInteger(Optimus::MAX_INT);
        $prime = $max->randomPrime($min, $max);

        // Calculate the inverse.
        $a = new BigInteger($prime);
        $b = new BigInteger(Optimus::MAX_INT + 1);
        if (!$inverse = $a->modInverse($b)) {
            $this->error("Error during calculation of FakeId settings. Please rerun this command.");
            return;
        }

        // Calculate a random number.
        $rand = hexdec(bin2hex(Random::string(4))) & Optimus::MAX_INT;

        // Write in environment file.
        $path = base_path('.env');

        if (file_exists($path)) {
            // Remove existing configuration.
            $fileArray = file($path);
            foreach($fileArray as $k => $line) {
                if (strpos($line, 'FAKEID_') === 0) {
                    unset($fileArray[$k]);
                }
            }

            // Append new configuration.
            $fileArray[] = "\nFAKEID_PRIME=" . $prime;
            $fileArray[] = "\nFAKEID_INVERSE=" . $inverse;
            $fileArray[] = "\nFAKEID_RANDOM=" . $rand;
            file_put_contents($path, implode('', $fileArray), LOCK_EX);

            $this->info("FakeId configured correctly.");
        }
    }
}
