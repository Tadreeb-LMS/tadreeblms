<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\File;

trait EnvManagerTrait
{
    /**
     * Set or update environment variables in the .env file.
     *
     * @param array $data Key-value pairs of environment variables.
     * @return bool
     */
    protected function setEnv(array $data)
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            return false;
        }

        $envContent = File::get($envPath);

        foreach ($data as $key => $value) {
            $value = (string) $value;

            // Escape backslashes and double quotes so the value can be safely quoted
            $escaped = str_replace(["\\", '"'], ["\\\\", '\\"'], $value);

            // If the value contains whitespace or special characters, save it as a quoted string
            if (preg_match('/[\s"\'#]/', $escaped) || $escaped === '') {
                $valueForEnv = '"' . $escaped . '"';
            } else {
                $valueForEnv = $escaped;
            }

            $pattern = "/^{$key}=.*$/m";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$valueForEnv}", $envContent);
            } else {
                $envContent .= PHP_EOL . "{$key}={$valueForEnv}";
            }
        }

        File::put($envPath, $envContent);

        return true;
    }
}
