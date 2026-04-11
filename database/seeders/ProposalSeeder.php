<?php

namespace Database\Seeders;

use App\Models\Proposal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProposalSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['ar', 'en'] as $locale) {
            $path = database_path("data/proposals/proposals-$locale.json");
            if (! file_exists($path)) {
                continue;
            }

            $data = json_decode(file_get_contents($path), true);
            foreach ($data['proposals'] ?? [] as $p) {
                Proposal::updateOrCreate(
                    ['proposal_id' => $p['id'], 'locale' => $locale],
                    [
                        'customer_name' => $p['customerName'] ?? '',
                        'description' => $p['description'] ?? null,
                        'password' => Hash::make($p['password']),
                        'content' => collect($p)->except(['id', 'password'])->all(),
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
