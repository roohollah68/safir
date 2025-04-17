<?php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('created_at', 'desc')->get();
        return view('project.list', compact('projects'));
    }

   public function create()
    {
        $project = new Project();
        $edit = false;
        return view('project.addEdit', compact('edit', 'project'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'desc' => 'string',
            'image' => 'required|mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'location' => 'required|string|max:255',
        ],[
            'image.mimes' => 'فایل با این پسوند قابل قبول نیست!',
            'image.max' => 'حجم فایل نباید از 3 mb بیشتر باشد!',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store("", 'project');
            $validated['image'] = $imagePath;
        }

        Project::create($validated);

        return redirect()->route('projects.list')->with('success', 'Project added successfully.');
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        
        $edit = true;
        return view('project.addEdit', compact('project', 'edit'));
    }

    public function update(Request $request, $id)
    {
        $project = Projects::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'desc' => 'required|string',
            'image' => 'required|mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'location' => 'required|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store("", 'project');
            $validated['image'] = $imagePath;
        }

        $project->update($validated);

        return redirect()->route('projects.list')->with('success', 'Project updated successfully.');
    }
}