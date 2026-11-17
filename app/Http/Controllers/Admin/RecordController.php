<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        $from = (string) ($request->query('from') ?? now()->startOfMonth()->toDateString());
        $to = (string) ($request->query('to') ?? now()->toDateString());
        $statusFilter = (string) ($request->query('status') ?? 'all');
        $allowedStatuses = ['all', 'pending', 'approved', 'completed', 'declined', 'cancelled'];

        if (! is_valid_date($from)) {
            $from = now()->startOfMonth()->toDateString();
        }
        if (! is_valid_date($to)) {
            $to = now()->toDateString();
        }
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        if (! in_array($statusFilter, $allowedStatuses, true)) {
            $statusFilter = 'all';
        }

        $query = DB::table('bookings as b')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->leftJoin('cottages as c', 'c.id', '=', 'b.cottage_id')
            ->leftJoin('rooms as rm', 'rm.id', '=', 'b.room_id')
            ->where('b.created_at', '>=', $from . ' 00:00:00')
            ->where('b.created_at', '<=', $to . ' 23:59:59')
            ->select('b.*', 'u.first_name', 'u.last_name', 'u.email')
            ->selectRaw('COALESCE(c.name, rm.name) AS cottage_name');

        if ($statusFilter !== 'all') {
            $query->where('b.status', $statusFilter);
        }

        $records = $query->orderByDesc('b.created_at')->orderByDesc('b.id')->get();

        $totalCount = count($records);
        $revenue = 0.0;
        $pendingValue = 0.0;
        $cancelled = 0;

        foreach ($records as $record) {
            if (in_array($record->status, ['approved', 'completed'], true)) {
                $revenue += (float) $record->total_amount;
            }
            if ($record->status === 'pending') {
                $pendingValue += (float) $record->total_amount;
            }
            if (in_array($record->status, ['cancelled', 'declined'], true)) {
                $cancelled++;
            }
        }

        if (($request->query('export') ?? '') === 'csv') {
            $filename = 'oliyettebook-records-' . str_replace('-', '', $from) . '-' . str_replace('-', '', $to) . '.csv';

            return response()->streamDownload(function () use ($records) {
                $out = fopen('php://output', 'wb');
                fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, ['Receipt', 'Guest', 'Email', 'Cottage', 'Check-in', 'Check-out', 'Adults', 'Children', 'Entrance Fee', 'Total', 'Payment', 'Status', 'Booked At']);
                foreach ($records as $record) {
                    fputcsv($out, [
                        $record->receipt_no,
                        $record->first_name . ' ' . $record->last_name,
                        $record->email,
                        $record->cottage_name,
                        $record->check_in,
                        $record->check_out,
                        $record->adults,
                        $record->children,
                        $record->entrance_fee,
                        $record->total_amount,
                        $record->payment_method,
                        $record->status,
                        $record->created_at,
                    ]);
                }
                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $exportParams = ['from' => $from, 'to' => $to, 'status' => $statusFilter, 'export' => 'csv'];
        $exportUrl = '/admin/records?' . http_build_query($exportParams);

        return view('admin.records', [
            'records' => $records,
            'from' => $from,
            'to' => $to,
            'statusFilter' => $statusFilter,
            'totalCount' => $totalCount,
            'revenue' => $revenue,
            'pendingValue' => $pendingValue,
            'cancelled' => $cancelled,
            'exportUrl' => $exportUrl,
            'adminPage' => 'records',
            'pageTitle' => 'Reservation Records',
        ]);
    }
}
