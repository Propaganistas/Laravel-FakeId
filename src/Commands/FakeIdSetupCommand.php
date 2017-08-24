<?php

namespace Propaganistas\LaravelFakeId\Commands;

use Illuminate\Console\Command;
use Jenssegers\Optimus\Energon;

class FakeIdSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fakeid:setup
                            {--o|overwrite : Silently overwrite existing configuration}
                            {--p|preserve : Silently preserves existing configuration}';

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
    public function handle()
    {
        // Write in environment file.
        $path = base_path('.env');
        $env = file($path);

        // Detect existing configuration.
        if ($this->hasExistingConfiguration($env)) {

            if ($this->option('preserve')) {
                return;
            }

            if (! $this->option('overwrite')) {
                if (! $this->confirm("Overwrite existing configuration?")) {
                    return;
                }
            }

            $this->removeExistingConfiguration($env);
        }

        $this->writeNewConfiguration($env, $path);
        $this->info("FakeId configured correctly.");
    }

    /**
     * Checks if the given file array contains existing FakeId configuration.
     */
    protected function hasExistingConfiguration($file)
    {
        return str_contains(implode(' ', $file), 'FAKEID_');
    }

    /**
     * Removes existing FakeId configuration from the given file array.
     *
     * @param array $file
     */
    protected function removeExistingConfiguration(&$file)
    {
        foreach ($file as $k => $line) {
            if (strpos($line, 'FAKEID_') === 0) {
                unset($file[$k]);
            }
        }
    }

    /**
     * Writes new configuration using the provided file array to the given path.
     *
     * @param array $file
     * @param string $path
     */
    protected function writeNewConfiguration($file, $path)
    {
        list($prime, $inverse, $rand) = Energon::generate();

        $file[] = "\nFAKEID_PRIME=" . $prime;
        $file[] = "\nFAKEID_INVERSE=" . $inverse;
        $file[] = "\nFAKEID_RANDOM=" . $rand;

        file_put_contents($path, implode('', $file), LOCK_EX);
    }
}
