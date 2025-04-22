<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return User::select('id', 'email', 'name', 'role')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Email', 'Nama', 'Role'];
    }
}