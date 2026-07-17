<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function handleLog(Request $request)
    {
        $logLine = $request->getContent();
        if (empty($logLine)) {
            return response('Empty log', 400);
        }
        Counter::create([
            'data' => $logLine,
        ]);
        // Tizimda loglarni kuzatish uchun (ixtiyoriy, muammo bo'lsa yoqing)
        // Log::info('CS2 Log: ' . $logLine);

        /*try {
            if (str_contains($logLine, 'killed')) {

                // Qotilning Steam ID sini ajratib olish (STEAM_1:X:XXXXXX yoki STEAM_0:X:XXXXXX)
                if (preg_match('/\"[^<]+<\d+><(?<killer_steam>STEAM_[0-1]:[0-1]:\d+)><[^>]+>\"\skilled/', $logLine, $killerMatches)) {
                    $killerSteamId = $killerMatches['killer_steam'];

                    // Qotilning ochkosini oshirish (update or create)
                    Counter::updateOrCreate(
                        ['steam_id' => $killerSteamId],
                        ['kills' => \DB::raw('kills + 1')]
                    );
                }

                // O'lgan o'yinchining Steam ID sini ajratib olish
                if (preg_match('/killed\s\"[^<]+<\d+><(?<victim_steam>STEAM_[0-1]:[0-1]:\d+)><[^>]+>\"/', $logLine, $victimMatches)) {
                    $victimSteamId = $victimMatches['victim_steam'];

                    // O'lgan odamning o'limlar sonini oshirish
                    Counter::updateOrCreate(
                        ['steam_id' => $victimSteamId],
                        ['deaths' => \DB::raw('deaths + 1')]
                    );
                }
            }

            // 2. ASSIST (Yordam berish) holatini tekshirish
            // Log namunasi: "PlayerName<2><STEAM_1:1:123456><CT>" assisted killing "VictimName<3>..."
            if (str_contains($logLine, 'assisted killing')) {
                if (preg_match('/\"[^<]+<\d+><(?<assister_steam>STEAM_[0-1]:[0-1]:\d+)><[^>]+>\"\sassisted\skilling/', $logLine, $assistMatches)) {
                    $assisterSteamId = $assistMatches['assister_steam'];

                    Counter::updateOrCreate(
                        ['steam_id' => $assisterSteamId],
                        ['assists' => \DB::raw('assists + 1')]
                    );
                }
            }

            // 3. MATCH TUGAGAN REJIM (Ixtiyoriy)
            // Log namunasi: Game Over: competitive mg_active de_mirage score 13:10
            if (str_contains($logLine, 'Game Over')) {
                // Bu yerda o'yin tugaganda lobbini yopish yoki boshqa amallarni bajarish mumkin
                Log::info('CS2 o\'yin tugadi! Yakuniy natijalar saqlandi.');
            }

        } catch (\Exception $e) {
            Log::error('CS2 Log parsing error: ' . $e->getMessage());
            return response('Error processing log', 500);
        }

        // CS2 serveriga muvaffaqiyatli qabul qilinganligini bildirish*/
        return response('OK', 200);
    }
}
