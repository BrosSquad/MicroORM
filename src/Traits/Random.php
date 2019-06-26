<?php


namespace BrosSquad\MicroORM\Traits;


trait Random
{
    public function randomString(int $length = 10): string {
        return substr(str_shuffle('asdfghjklmnbvcxzqwertyuiop1234567890'), 0, $length);
    }

    public function randomSafeString(int $length = 30): string {
        return openssl_random_pseudo_bytes($length);
    }

    /**
     * @param int $length
     *
     * @return string
     * @throws \Exception
     */
    public function randomBytes(int $length) {
        return random_bytes($length);
    }

    /**
     * @param int $min
     * @param int $max
     *
     * @return int
     * @throws \Exception
     */
    public function randomInteger(int $min, int $max): int {
        return random_int($min, $max);
    }
}
