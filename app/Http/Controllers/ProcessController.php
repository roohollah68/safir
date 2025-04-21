<?php
namespace App\Http\Controllers;

use App\Models\Process;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    public function index()
    {
        $processes = Process::orderBy('created_at', 'desc')->get();
        return view('process.list', compact('processes'));
    }

    public function create()
    {
        $process = new Process();
        $edit = false;
        return view('process.addEdit', compact('edit', 'process'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'image' => 'nullable|mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'location' => 'required|string|max:255',
        ],[
            'image.mimes' => 'فایل با این پسوند قابل قبول نیست!',
            'image.max' => 'حجم فایل نباید از 3 mb بیشتر باشد!',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store("", 'process');
            $validated['image'] = $imagePath;
        }

        Process::create($validated);

        return redirect()->route('processList')->with('success', 'Process added successfully.');
    }

    public function edit($id)
    {
        $process = Process::findOrFail($id);
        
        $edit = true;
        return view('process.addEdit', compact('process', 'edit'));
    }

    public function update(Request $request, $id)
    {
        $process = Process::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'image' => 'nullable|mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'location' => 'required|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store("", 'process');
            $validated['image'] = $imagePath;
        }

        $process->update($validated);

        return redirect()->route('processList')->with('success', 'Process updated successfully.');
    }
}
