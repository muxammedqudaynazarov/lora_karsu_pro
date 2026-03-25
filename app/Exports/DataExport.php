<?php

namespace App\Exports;

use App\Models\Datum;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DataExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $deviceId;
    protected $dynamicKeys = [];
    protected $dataCollection;

    protected $columnsConfig = [
        'moisture'     => ['label' => 'Влажность',     'unit' => '%', 'color' => ''],
        'electricity'  => ['label' => 'Проводимость',  'unit' => 'µS/cm', 'color' => ''],
        'illumination' => ['label' => 'Освещенность',  'unit' => 'Lux', 'color' => ''],
        'temperature'  => ['label' => 'Температура',   'unit' => '°C', 'color' => ''],
        'depth'        => ['label' => 'Глубина',       'unit' => 'м', 'color' => ''],
    ];

    public function __construct($deviceId, $startDate = null, $endDate = null)
    {
        $this->deviceId = $deviceId;

        // 1. Sana bo'yicha o'sish tartibida (eng eskisidan yangisiga qarab) tartiblaymiz
        $query = Datum::where('device_id', $this->deviceId)->orderBy('created_at', 'asc');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // 2. get() o'rniga cursor() ishlatamiz! Bu katta datalarda xotira to'lib qolishini oldini oladi.
        $rawCollection = $query->cursor();
        $filteredCollection = collect();

        $keys = [];
        $counter = 0; // Qatorlarni sanash uchun o'zgaruvchi

        foreach ($rawCollection as $item) {
            // Har 10 ta ma'lumotdan faqat bittasini olamiz (0, 10, 20...)
            $isTenth = ($counter % 10 === 0);
            $counter++; // Sanoqni bittaga oshirib qo'yamiz

            // Agar 10-chi ma'lumot bo'lmasa, pastdagi kodlarni o'qimaydi va keyingi ma'lumotga o'tib ketadi
            if (!$isTenth) {
                continue;
            }

            $rowArr = $item->toArray();
            $jsonData = is_array($item->data) ? $item->data : json_decode($item->data, true);
            $jsonData = is_array($jsonData) ? $jsonData : [];

            $combinedData = array_merge($rowArr, $jsonData);

            $hasValidData = false;

            foreach ($combinedData as $k => $v) {
                if (!array_key_exists($k, $this->columnsConfig)) {
                    continue;
                }

                if ($v !== null && trim((string)$v) !== '') {
                    $keys[] = $k;
                    $hasValidData = true;
                }
            }

            if ($hasValidData) {
                $filteredCollection->push($item);
            }
        }

        $this->dataCollection = $filteredCollection;

        $allowedKeys = array_keys($this->columnsConfig);
        $uniqueKeys = array_unique($keys);
        $this->dynamicKeys = array_values(array_intersect($allowedKeys, $uniqueKeys));
    }

    public function collection()
    {
        return $this->dataCollection;
    }

    public function headings(): array
    {
        $headings = ['ID устройства', 'Имя устройства', 'Локация', 'Дата и время'];

        foreach ($this->dynamicKeys as $key) {
            $unit = !empty($this->columnsConfig[$key]['unit']) ? ' (' . $this->columnsConfig[$key]['unit'] . ')' : '';
            $headings[] = $this->columnsConfig[$key]['label'] . $unit;
        }

        return $headings;
    }

    public function map($row): array
    {
        $rowArr = $row->toArray();
        $jsonData = is_array($row->data) ? $row->data : json_decode($row->data, true);
        $jsonData = is_array($jsonData) ? $jsonData : [];

        $combinedData = array_merge($rowArr, $jsonData);

        $mapped = [
            $row->device ? $row->device->devEUI : '',
            $row->device ? $row->device->deviceName : '',
            $row->device ? $row->device->location : '',
            $row->created_at ? $row->created_at->format('d.m.Y H:i:s') : '',
        ];

        foreach ($this->dynamicKeys as $key) {
            if (!array_key_exists($key, $this->columnsConfig)) {
                continue;
            }
            $mapped[] = $combinedData[$key] ?? null;
        }

        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $colIndex = 5;

        foreach ($this->dynamicKeys as $key) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);

            if (!empty($this->columnsConfig[$key]['color'])) {
                $color = str_replace('#', '', $this->columnsConfig[$key]['color']);

                $sheet->getStyle($colLetter . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF' . $color],
                    ],
                ]);
            } else {
                $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);
            }

            $colIndex++;
        }
    }
}
