<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Report;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function newReport($id)
    {
        Helper::meta('workReport');
        $warehouse = Warehouse::findOrFail($id);
        $report = Report::whereDate('created_at', Carbon::today())->where('warehouse_id', $id)->first();
        if (!$report) {
            $report = new Report();
        }
        return view('report.addReport', [
            'warehouse2' => $warehouse,
            'report' => $report,
        ]);
    }

    public function saveReport($id, Request $req)
    {
        Helper::meta('workReport');
        request()->validate([
            'photo' => 'mimes:jpeg,jpg,png,bmp,pdf|max:2048',
        ]);
        $report = Report::whereDate('created_at', Carbon::today())->where('warehouse_id', $id)->first();
        if (!$report) {
            $report = new Report();
        }
        $report->photo = $req->oldPhoto;
        if ($req->file("photo")) {
            $report->photo = $req->file("photo")->store("", 'report');
        }
        $report->warehouse_id = $id;
        $report->user_id = auth()->user()->id;
        $report->description = $req->description;
        if (!$req->description && !$report->photo)
            $report->delete();
        else
            $report->save();
        return redirect()->route('reportList');
    }

    public function list()
    {
        Helper::meta('workReport');
        $reports = [];
        for ($day = 0; $day > -60; $day--) {
            $reports[-$day] = Report::with(['warehouse' , 'user'])->whereDate('created_at', Carbon::today()->addDay($day))->get()->keyBy('warehouse_id');
        }
        return view('report.reportList', [
            'reports' => $reports,
            'warehouses' => Warehouse::all()->keyBy('id'),
        ]);
    }

    public function response($id , Request $req)
    {
        $report = Report::findOrFail($id);
        $report->response = $req->response;
        $report->save();
    }
}
