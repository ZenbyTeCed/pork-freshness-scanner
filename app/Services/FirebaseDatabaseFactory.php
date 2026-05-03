<?php

namespace App\Services;

use Illuminate\Contracts\Config\Repository as Config;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;
use RuntimeException;

class FirebaseDatabaseFactory
{
    public function __construct(private Config $config)
    {
    }

    public function create(): Database
    {
        $factory = (new Factory)
            ->withServiceAccount($this->credentials())
            ->withDatabaseUri($this->config->get('services.firebase.database_url'));

        return $factory->createDatabase();
    }

    private function credentials(): array|string
    {
        $credentialsJson = $this->config->get('services.firebase.credentials_json');

        if (is_string($credentialsJson) && trim($credentialsJson) !== '') {
            $credentials = json_decode($credentialsJson, true);

            if (! is_array($credentials)) {
                throw new RuntimeException('FIREBASE_CREDENTIALS_JSON is not valid JSON.');
            }

            return $credentials;
        }

        $credentialsPath = $this->config->get('services.firebase.credentials_path');

        if (is_string($credentialsPath) && trim($credentialsPath) !== '') {
            return $credentialsPath;
        }

        throw new RuntimeException('Set FIREBASE_CREDENTIALS_JSON or FIREBASE_CREDENTIALS_PATH in your .env file.');
    }
}
