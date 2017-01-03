<?php

namespace Propaganistas\LaravelFakeId\Commands;

use Illuminate\Console\Command;
use Jenssegers\Optimus\Energon;

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
        // Write in environment file.
        $path = base_path('.env');

        if (! file_exists($path)) {
            $this->error("Environment file (.env) not found. Aborting FakeId setup!");

            return;
        }

        $env = file($path);

        // Detect existing configuration.
        if (str_contains(implode(' ', $env), 'FAKEID_')) {
            if (! $this->confirm("Overwrite existing configuration?")) {
                return;
            }

            foreach ($env as $k => $line) {
                if (strpos($line, 'FAKEID_') === 0) {
                    unset($env[$k]);
                }
            }
        }

        list($prime, $inverse, $rand) = Energon::generate();

        // Append new configuration.
        $env[] = "\nFAKEID_PRIME=" . $prime;
        $env[] = "\nFAKEID_INVERSE=" . $inverse;
        $env[] = "\nFAKEID_RANDOM=" . $rand;
        file_put_contents($path, implode('', $env), LOCK_EX);

        $this->info("FakeId configured correctly.");
    }
}
