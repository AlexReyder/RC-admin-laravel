<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncFlatsFromLegacyApartments extends Command
{
    protected $signature = 'flats:sync-from-legacy {--truncate : Очистить flats перед переносом}';

    protected $description = 'Копирует данные из legacy таблицы apartments в новую таблицу flats';

    public function handle(): int
    {
        if (! Schema::hasTable('apartments')) {
            $this->error('Таблица apartments не найдена. Сначала импортируй legacy SQL в текущую БД.');
            return self::FAILURE;
        }

        if (! Schema::hasTable('flats')) {
            $this->error('Таблица flats не найдена. Сначала выполни миграции.');
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            $this->warn('Очистка таблицы flats...');

            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('flats')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $total = DB::table('apartments')->count();

        if ($total === 0) {
            $this->warn('В таблице apartments нет данных для переноса.');
            return self::SUCCESS;
        }

        $this->info("Найдено {$total} записей в apartments. Начинаю перенос...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;

        DB::table('apartments')
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$processed, $bar) {
                $payload = [];

                foreach ($rows as $row) {
                    $payload[] = [
                        'id' => $row->id,
                        'rooms_number' => $row->rooms_number,
                        'rooms_number_true' => $row->rooms_number_true,
                        'floor' => $row->floor,
                        'square' => $row->square,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'entrance_number' => $row->entrance_number,
                        'living_square' => $row->living_square,
                        'ceiling_height' => $row->ceiling_height,
                        'plan' => $row->plan,
                        'sold' => $row->sold,
                        'building' => $row->building,
                        'number' => $row->number,
                        'price' => $row->price,
                        'price_m2' => $row->price_m2,
                        'floor_position' => $row->floor_position,
                        'finish_date' => $row->finish_date,
                        'finishing' => $row->finishing,
                        'action' => $row->action,
                        'action_price_m2' => $row->action_price_m2,
                        'title' => $row->title,
                        'description' => $row->description,
                    ];
                }

                DB::table('flats')->upsert(
                    $payload,
                    ['id'],
                    [
                        'rooms_number',
                        'rooms_number_true',
                        'floor',
                        'square',
                        'created_at',
                        'updated_at',
                        'entrance_number',
                        'living_square',
                        'ceiling_height',
                        'plan',
                        'sold',
                        'building',
                        'number',
                        'price',
                        'price_m2',
                        'floor_position',
                        'finish_date',
                        'finishing',
                        'action',
                        'action_price_m2',
                        'title',
                        'description',
                    ]
                );

                $processed += count($payload);
                $bar->advance(count($payload));
            });

        $bar->finish();
        $this->newLine(2);

        $maxId = DB::table('flats')->max('id');

        if ($maxId) {
            DB::statement('ALTER TABLE flats AUTO_INCREMENT = '.((int) $maxId + 1));
        }

        $this->info("Готово. Перенесено записей: {$processed}.");

        return self::SUCCESS;
    }
}