<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class QRCodeService
{
    /**
     * Generate an encrypted payload for the QR code.
     * Payload contains: division_id, verification_code_id, and a timestamp.
     */
    public function generatePayload(int $divisionId, int $codeId): string
    {
        $data = [
            'd' => $divisionId,
            'v' => $codeId,
            't' => floor(time() / 30) * 30, // stable for 30 seconds
        ];

        return Crypt::encryptString(json_encode($data));
    }

    /**
     * Decrypt and validate the payload.
     */
    public function decryptPayload(string $payload): array
    {
        try {
            $decrypted = Crypt::decryptString($payload);
            $data = json_decode($decrypted, true);

            if (!isset($data['d'], $data['v'], $data['t'])) {
                throw new \Exception('Invalid QR payload structure.');
            }

            // rolling code validation: check if timestamp is within 60 seconds (with some tolerance)
            $diff = time() - $data['t'];
            if ($diff < -30 || $diff > 90) { // allows 30s future (clock sync) and 90s past
                throw new \Exception('QR Code has expired. Please refresh the scanner.');
            }

            return $data;
        } catch (\Exception $e) {
            throw new \Exception('Failed to decrypt or validate QR code: ' . $e->getMessage());
        }
    }
}
