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

    // Qat'iy belgilangan ustunlar
    protected $columnsConfig = [
        'devEUI' => ['label' => 'ID устройства', 'unit' => '', 'color' => ''],
        'deviceName' => ['label' => 'Имя устройства', 'unit' => '', 'color' => ''],
        'moisture' => ['label' => 'Влажность', 'unit' => '%', 'color' => ''],
        'electricity' => ['label' => 'Проводимость', 'unit' => 'µS/cm', 'color' => ''],
        'illumination' => ['label' => 'Освещенность', 'unit' => 'Lux', 'color' => ''],
        'temperature' => ['label' => 'Температура', 'unit' => '°C', 'color' => ''],
        'depth' => ['label' => 'Глубина', 'unit' => 'м', 'color' => ''],
    ];

    public function __construct($deviceId, $startDate = null, $endDate = null)
    {
        $this->deviceId = $deviceId;

        $query = Datum::where('device_id', $this->deviceId);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $this->dataCollection = $query->get();

        $keys = [];
        $allowedKeys = array_keys($this->columnsConfig);

        foreach ($this->dataCollection as $item) {
            $jsonData = is_array($item->data) ? $item->data : json_decode($item->data, true);

            if (is_array($jsonData)) {
                foreach ($jsonData as $k => $v) {
                    // 1. Kalit bizning ro'yxatda bormi?
                    // 2. Qiymati null yoki bo'sh emasmi? (0 degan qiymatni o'tkazadi)
                    if (in_array($k, $allowedKeys) && $v !== null && $v !== '') {
                        $keys[] = $k;
                    }
                }
            }
        }

        // Unikal kalitlarni olamiz
        $uniqueKeys = array_unique($keys);

        // Ustunlar tartibi doim $columnsConfig dagi kabi chiroyli bo'lishi uchun tartiblaymiz
        $this->dynamicKeys = array_values(array_intersect($allowedKeys, $uniqueKeys));
    }

    public function collection()
    {
        return $this->dataCollection;
    }

    public function headings(): array
    {
        $headings = ['ID', 'Местоположение устройства', 'Дата информации'];

        foreach ($this->dynamicKeys as $key) {
            $unit = !empty($this->columnsConfig[$key]['unit']) ? ' (' . $this->columnsConfig[$key]['unit'] . ')' : '';
            $headings[] = $this->columnsConfig[$key]['label'] . $unit;
        }

        return $headings;
    }

    public function map($row): array
    {
        $jsonData = is_array($row->data) ? $row->data : json_decode($row->data, true);
        $jsonData = $jsonData ?? [];

        $mapped = [
            $row->id,
            $row->device ? $row->device->location : '',
            $row->created_at ? $row->created_at->format('d.m.Y H:i:s') : '',
        ];

        foreach ($this->dynamicKeys as $key) {
            $mapped[] = $jsonData[$key] ?? null;
        }

        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $colIndex = 4;

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
