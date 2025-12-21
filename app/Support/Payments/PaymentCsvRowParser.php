<?php

namespace App\Support\Payments;

use RuntimeException;

class PaymentCsvRowParser
{

    
    public function validateAndNormalize(array $row): array
    {
        $customerId = trim((string)($row['customer_id'] ?? ''));
        $name       = trim((string)($row['customer_name'] ?? ''));
        $email      = trim((string)($row['customer_email'] ?? ''));
        $currency   = strtoupper(trim((string)($row['currency'] ?? '')));
        $reference  = trim((string)($row['reference_no'] ?? ''));
        $dateTime   = trim((string)($row['date_time'] ?? ''));
        $amountRaw  = trim((string)($row['amount'] ?? ''));

        if ($customerId === '' || $name === '' || $reference === '') {
            throw new RuntimeException('Missing required fields (customer_id/customer_name/reference_no).');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid email.');
        }

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new RuntimeException('Invalid currency code.');
        }

        $amount = $this->parseAmount($amountRaw);
        if (bccomp($amount, '0', 6) <= 0) {
            throw new RuntimeException('Amount must be > 0.');
        }

        $paymentAt = $this->parseDateTime($dateTime);
        if (!$paymentAt) {
            throw new RuntimeException('Invalid date_time.');
        }

        return [
            'customer_id'    => $customerId,
            'customer_name'  => $name,
            'customer_email' => $email,
            'amount'         => $amount,
            'currency'       => $currency,
            'reference_no'   => $reference,
            'payment_at'     => $paymentAt,
        ];
    }

    private function parseAmount(string $s): string
    {
        $s = str_replace(' ', '', $s);

        if (str_contains($s, '.') && str_contains($s, ',')) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } elseif (str_contains($s, ',') && !str_contains($s, '.')) {
            $s = str_replace(',', '.', $s);
        }

        $s = preg_replace('/[^0-9.]/', '', $s) ?? '';

        if ($s === '' || !is_numeric($s)) {
            throw new RuntimeException('Invalid amount format.');
        }

        return number_format((float)$s, 6, '.', '');
    }

    private function parseDateTime(string $s): ?string
    {
        $formats = [
            'n/j/Y G:i',
            'm/d/Y G:i',
            'm/d/Y H:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
        ];

        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $s);
            if ($dt && $dt->format($fmt) === $s) {
                return $dt->format('Y-m-d H:i:s');
            }
        }

        try {
            $dt = new \DateTime($s);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}
