<?php

namespace App\Services\TimeSpent;

use App\Models\TimeSpent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TimeSpentService
{
    public function update(array $data)
    {
        try {
            $result = [];
            $userId = $data['user_id'] ?? null;
            
            // Revertido para ler da chave 'user_time_spent' que vem da extensão
            $items = $data['user_time_spent'] ?? [];

            if (empty($items) || !$userId) {
                return $result;
            }

            // 1. Agrupa e calcula o tempo gasto por domínio em memória
            $calculatedTimePerDomain = $this->calculateTimeFromLogs($items);

            // 2. Transação para salvar de forma segura no banco de dados
            return DB::transaction(function () use ($userId, $calculatedTimePerDomain) {
                $result = [];

                foreach ($calculatedTimePerDomain as $url => $secondsToIncrement) {
                    $timeSpent = TimeSpent::where('user_id', $userId)
                        ->where('url', $url)
                        ->first();

                    if ($timeSpent) {
                        // Soma os segundos calculados aos já existentes
                        $timeSpent->time_spent += $secondsToIncrement;
                        $timeSpent->save();
                    } else {
                        // Cria o registro caso seja o primeiro acesso do usuário àquela URL
                        $timeSpent = TimeSpent::create([
                            'user_id'    => $userId,
                            'time_spent' => $secondsToIncrement,
                            'url'        => $url,
                        ]);
                    }

                    $result[] = $timeSpent;
                }

                return $result;
            });

        } catch (\Exception $e) {
            Log::error('Error updating time spent: ' . $e->getMessage());
            throw new \Exception('Error updating time spent: ' . $e->getMessage());
        }
    }

    /**
     * Agrupa os registros por URL e calcula a diferença de tempo baseada nos timestamps.
     */
    private function calculateTimeFromLogs(array $items): array
    {
        $groupedBeats = collect($items)->groupBy('url');
        $timePerDomain = [];
        $limiteInatividadeSegundos = 180; // 3 minutos

        foreach ($groupedBeats as $url => $beats) {
            // Garante a ordenação cronológica dos registros recebidos na fila
            $orderedBeats = $beats->sortBy('timestamp')->values();
            $totalSeconds = 0;
            $totalBeats = count($orderedBeats);

            for ($i = 0; $i < $totalBeats - 1; $i++) {
                $currentBeat = Carbon::parse($orderedBeats[$i]['timestamp']);
                $nextBeat = Carbon::parse($orderedBeats[$i + 1]['timestamp']);

                $diffInSeconds = $currentBeat->diffInSeconds($nextBeat);

                
                if ($diffInSeconds <= $limiteInatividadeSegundos) {
                    $totalSeconds += $diffInSeconds;
                } else {
                    
                    $totalSeconds += 120;
                }
            }

           
            if ($totalBeats === 1) {
                $totalSeconds = 120; // 2 minutos padrão
            }

            $timePerDomain[$url] = $totalSeconds;
        }

        return $timePerDomain;
    }
}