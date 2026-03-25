<?php

namespace App\Exports;

use App\Models\Datum; // Yoki model nomi Data bo'lsa shunga o'zgartiring
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

    // Faqat shu ro'yxatdagilar Excelga chiqadi, qolgani bloklanadi!
    protected $columnsConfig = [
        'devEUI'       => ['label' => 'ID устройства', 'unit' => '', 'color' => ''],
        'deviceName'   => ['label' => 'Имя устройства', 'unit' => '', 'color' => ''],
        'temperature'  => ['label' => 'Температура',   'unit' => '°C', 'color' => 'ef4444'],
        'moisture'     => ['label' => 'Влажность',     'unit' => '%', 'color' => '3b82f6'],
        'electricity'  => ['label' => 'Проводимость',  'unit' => 'µS/cm', 'color' => 'f59e0b'],
        'illumination' => ['label' => 'Освещенность',  'unit' => 'Lux', 'color' => '8b5cf6'],
        'depth'        => ['label' => 'Глубина',       'unit' => 'м', 'color' => '06b6d4'],
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
            // 1. Bazadagi asosiy ustunlar (temperature, moisture va hk)
            $rowArr = $item->toArray();

            // 2. JSON ustunidagi ma'lumotlar (devEUI, deviceName)
            $jsonData = is_array($item->data) ? $item->data : json_decode($item->data, true);
            $jsonData = is_array($jsonData) ? $jsonData : [];

            // 3. Ikkala datani bittaga birlashtiramiz!
            $combinedData = array_merge($rowArr, $jsonData);

            foreach ($combinedData as $k => $v) {
                // Kalit ro'yxatda bor bo'lsa va qiymati bo'sh bo'lmasa qabul qilamiz
                if (in_array($k, $allowedKeys) && $v !== null && trim((string)$v) !== '') {
                    $keys[] = $k;
                }
            }
        }

        // Unikal ustunlarni olib, tartibni to'g'rilaymiz
        $uniqueKeys = array_unique($keys);
        $this->dynamicKeys = array_values(array_intersect($allowedKeys, $uniqueKeys));
    }

    public function collection()
    {
        return $this->dataCollection;
    }

    public function headings(): array
    {
        $headings = ['ID', 'Device Location', 'Sana'];

        foreach ($this->dynamicKeys as $key) {
            $unit = !empty($this->columnsConfig[$key]['unit']) ? ' (' . $this->columnsConfig[$key]['unit'] . ')' : '';
            $headings[] = $this->columnsConfig[$key]['label'] . $unit;
        }

        return $headings;
    }

    public function map($row): array
    {
        // Yana datalarni birlashtiramizki qiymatlarni topish kafolatlansin
        $rowArr = $row->toArray();
        $jsonData = is_array($row->data) ? $row->data : json_decode($row->data, true);
        $jsonData = is_array($jsonData) ? $jsonData : [];

        $combinedData = array_merge($rowArr, $jsonData);

        $mapped = [
            $row->id,
            $row->device ? $row->device->location : '',
            $row->created_at ? $row->created_at->format('d.m.Y H:i:s') : '',
        ];

        // Topilgan har bir kalit bo'yicha ma'lumotlarni qatorga teramiz
        foreach ($this->dynamicKeys as $key) {
            $mapped[] = $combinedData[$key] ?? null;
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
