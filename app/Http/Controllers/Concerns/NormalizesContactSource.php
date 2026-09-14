<?php

namespace App\Http\Controllers\Concerns;

trait NormalizesContactSource
{
    /**
     * Normaliza mayúsculas/minúsculas de contact_source/custom_field_2: en producción
     * conviven variantes como "Whatsapp" y "WhatsApp" para el mismo canal (MySQL las
     * trata como iguales por su collation, pero el dashboard las compara en JS de forma
     * exacta) — sin esto, la mayoría de registros de WhatsApp quedaban agrupados como
     * "otras fuentes" en vez de "leads". Compartido por MachineryController (contact_source
     * en loans) y RepuestosController (custom_field_2 en transactions) para que ambos
     * dashboards clasifiquen "Leads" de forma idéntica.
     */
    protected function fuenteLabel($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return 'Sin especificar';
        }

        $canonicas = [
            'contacto directo del vendedor' => 'Contacto directo del vendedor',
            'whatsapp' => 'WhatsApp',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'tiktok' => 'TikTok',
            'web de libra international' => 'Web de Libra International',
        ];

        return $canonicas[mb_strtolower($raw)] ?? $raw;
    }
}
