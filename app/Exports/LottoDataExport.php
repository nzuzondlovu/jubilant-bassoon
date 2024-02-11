<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LottoDataExport implements FromArray, WithMapping, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function map($data): array
    {
        return [
            $data['ticket_id'],
            $data['mainballs'],
            $data['sub1'],
            $data['sub2'],
            $data['comment']
        ];
    }

    public function headings(): array
    {
        return [
            'Ticket ID',
            'Mainballs',
            'Sub 1',
            'Sub 2',
            'Comment'
        ];
    }
}
