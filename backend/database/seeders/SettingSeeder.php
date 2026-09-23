<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['app.name', 'WrenchGo', 'general', 'Nom public de la plateforme'],
            ['app.locale', 'fr', 'general', 'Langue par défaut'],
            ['otp.ttl_minutes', 5, 'otp', 'Durée de validité d\'un code OTP'],
            ['otp.resend_cooldown_seconds', 60, 'otp', 'Délai minimum entre deux envois'],
            ['otp.max_attempts', 3, 'otp', 'Tentatives de saisie avant blocage'],
            ['otp.lock_minutes', 10, 'otp', 'Durée du premier blocage'],
            ['request.emergency_search_seconds', 45, 'request', 'Temps de recherche des prestataires (urgence)'],
            ['request.planned_validity_hours', 2, 'request', 'Largeur de création d\'une demande planifiée'],
            ['request.collection_max_count', 5, 'request', 'Nombre maximal de demandes en attente par prestataire'],
            ['request.archive_hour', '05:00', 'request', 'Heure d\'archivage (RM-DM-06)'],
            ['request.auto_reject_hours', 24, 'request', 'Rejet auto des demandes non traitées (RM-DM-07)'],
            ['search.initial_radius_km', 10, 'search', 'Rayon de recherche initial'],
            ['search.max_radius_km', 30, 'search', 'Rayon de recherche maximal'],
        ];

        foreach ($settings as [$key, $value, $group, $description]) {
            Setting::set($key, $value, $group, $description);
        }
    }
}