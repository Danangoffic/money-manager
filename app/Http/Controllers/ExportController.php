<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(private TransactionService $transactionService) {}

    public function transactionsCsv(Request $request): StreamedResponse
    {
        $householdId = $request->user()->household_id;
        $transactions = $this->transactionService->getByHouseholdFiltered($householdId, array_merge(
            $request->all(),
            ['per_page' => 10000]
        ));

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Tipe', 'Kategori', 'Akun', 'Jumlah', 'Deskripsi']);

            foreach ($transactions->items() as $transaction) {
                fputcsv($handle, [
                    $transaction->date->format('Y-m-d'),
                    $transaction->type,
                    $transaction->category?->name ?? '-',
                    $transaction->account->name,
                    $transaction->amount,
                    $transaction->description ?? '-',
                ]);
            }

            fclose($handle);
        }, 'transactions-'.now()->format('Y-m-d').'.csv');
    }
}
