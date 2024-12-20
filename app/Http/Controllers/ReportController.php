<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Models\Report;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function newReport()
    {
        Helper::access('addWorkReport');
        $user = auth()->user();
        $report = Report::whereDate('created_at', Carbon::today())->where('user_id', $user->id)->first();
        if (!$report) {
            $report = new Report();
        }
        return view('report.addReport', [
            'user' => $user,
            'report' => $report,
        ]);
    }

    public function saveReport(Request $req)
    {
        Helper::access('addWorkReport');
        request()->validate([
            'photo' => 'mimes:jpeg,jpg,png,bmp,pdf|max:2048',
        ]);
        $user = auth()->user();
        $report = Report::whereDate('created_at', Carbon::today())->where('user_id', $user->id)->first();
        if (!$report) {
            $report = new Report();
        }
        $report->photo = $req->oldPhoto;
        if ($req->file("photo")) {
            $report->photo = $req->file("photo")->store("", 'report');
        }
        $report->user_id = $user->id;
        $report->description = $req->description;
        if (!$req->description && !$report->photo)
            $report->delete();
        else
            $report->save();
        return redirect()->route('reportList');
    }

    public function list()
    {
        Helper::access(['workReport' , 'addWorkReport']);
        $reports = [];
        for ($day = 0; $day > -60; $day--) {
            $reports[-$day] = Report::with(['user'])->whereDate('created_at', Carbon::today()->addDay($day))->get()->keyBy('user_id');
        }
        return view('report.reportList', [
            'reports' => $reports,
            'users' => User::all()->keyBy('id'),
        ]);
    }

    public function response($id , Request $req)
    {
        Helper::access('workReport');
        $report = Report::findOrFail($id);
        $report->response = $req->response;
        $report->save();
    }
}
