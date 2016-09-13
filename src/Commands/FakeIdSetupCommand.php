<?php

namespace Propaganistas\LaravelFakeId\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Jenssegers\Optimus\Optimus;

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
        $prime = gmp_intval(gmp_nextprime(mt_rand(1e7, Optimus::MAX_INT)));

        // Calculate the inverse.
        if (!$inverse = gmp_intval(gmp_invert($prime, (Optimus::MAX_INT + 1)))) {
            $this->error("Error during calculation of FakeId settings. Please re-run this command (php artisan fakeid:setup).");

            return;
        }

        // Calculate a random number.
        $rand = hexdec(bin2hex(Str::random(4))) & Optimus::MAX_INT;

        // Write in environment file.
        $path = base_path('.env');

        if (!file_exists($path)) {
            $this->error("Environment file (.env) not found. Aborting FakeId setup!");

            return;
        }

        // Remove existing configuration.
        $fileArray = file($path);
        foreach ($fileArray as $k => $line) {
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
