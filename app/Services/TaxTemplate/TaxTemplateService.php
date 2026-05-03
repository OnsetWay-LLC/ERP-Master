<?php

namespace App\Services\TaxTemplate;

use App\Models\Company;
use App\Models\TaxTemplate;
use Illuminate\Support\Facades\DB;

class TaxTemplateService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $companyId = Company::query()->value('id');

            $template = TaxTemplate::create([
                'company_id' => $companyId,
                'title' => $data['title'],
                'created_by' => auth()->id(),
            ]);

            foreach ($data['lines'] as $line) {
                $template->lines()->create($line);
            }

            return $template->load('lines.account');
        });
    }

    public function update($template, array $data)
    {
        return DB::transaction(function () use ($template, $data) {

            $template->update([
                'title' => $data['title'],
            ]);

            // حذف القديم
            $template->lines()->delete();

            // إعادة إدخال
            foreach ($data['lines'] as $line) {
                $template->lines()->create($line);
            }

            return $template->load('lines.account');
        });
    }

    public function getAll()
    {
        return TaxTemplate::with('lines.account')->get();
    }

    public function getById($id)
    {
        return TaxTemplate::with('lines.account')->findOrFail($id);
    }

    public function delete($template)
    {
        $template->delete();
    }
}