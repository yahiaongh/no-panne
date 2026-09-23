<?php

namespace Database\Seeders;

use App\Enums\ServiceType;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * BRD §5.1 — Catalogue des Services MVP (SRV-01 … SRV-10).
     */
    public function run(): void
    {
        $services = [
            ['SRV-01', 'Dépannage sur route', ServiceType::Emergency, 'Panne moteur, batterie à plat, accident mineur'],
            ['SRV-02', 'Vulcanisation / Crevaison', ServiceType::Emergency, 'Changement ou réparation de pneu sur place'],
            ['SRV-03', 'Remorquage', ServiceType::Emergency, 'Transport du véhicule vers un atelier'],
            ['SRV-04', 'Mécanique à domicile', ServiceType::Planned, 'Vidange, freins, courroie, diagnostic OBD'],
            ['SRV-05', 'Électricité auto', ServiceType::Planned, 'Câblage, batterie, alternateur, démarreur'],
            ['SRV-06', 'Carrosserie légère', ServiceType::Planned, 'Petits chocs, rayures, remplacement de pièces'],
            ['SRV-07', 'Vitre & Pare-brise', ServiceType::Planned, 'Remplacement ou réparation de vitres'],
            ['SRV-08', 'Lavage à domicile', ServiceType::Planned, 'Nettoyage intérieur/extérieur au domicile du client'],
            ['SRV-09', 'Diagnostic OBD', ServiceType::Planned, 'Lecture des codes erreurs moteur'],
            ['SRV-10', 'Révision complète', ServiceType::Planned, 'Entretien périodique complet'],
        ];

        foreach ($services as $i => [$code, $name, $type, $description]) {
            Service::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name_fr' => $name,
                    'type' => $type->value,
                    'description_fr' => $description,
                    'active' => true,
                    'sort_order' => $i,
                ]
            );
        }
    }
}