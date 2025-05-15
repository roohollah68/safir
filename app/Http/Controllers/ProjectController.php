<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SubProject;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $projects = Project::orderBy('created_at', 'desc')->get();
        if ($user->meta('workReport')) {
            $projects = Project::all();
        } else {
            $projects = Project::where('user_id', $user->id)
            ->orWhere('task_owner_id', $user->id)
            ->get();
        }
        return view('project.list', compact('projects'));
    }

   public function create()
    {
        $project = new Project();
        $edit = false;
        return view('project.addEdit', compact('edit', 'project'));
    }

    public function storeProject(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'image' => 'nullable|mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'location' => 'required|string|max:255',
            'task_owner_id' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
            'report_date' => 'nullable|date',
        ],[
            'image.mimes' => 'فایل با این پسوند قابل قبول نیست!',
            'image.max' => 'حجم فایل نباید از 3 mb بیشتر باشد!',
        ]);
        
        if ($request->deadline) {
            $validated['deadline'] = Carbon::parse($request->deadline);
        }

        if ($request->report_date) {
            $validated['report_date'] = Carbon::parse($request->report_date);
        }

        $validated['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store("", 'project');
            $validated['image'] = $imagePath;
        }

        $project = Project::create($validated);
        if ($request->has('subprojects')) {
            foreach ($request->subprojects as $subprojectData) {
                $project->subProjects()->create($subprojectData);
            }
        }
        return redirect()->route('projectList')->with('success', 'Project added successfully.');
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        
        $edit = true;
        return view('project.addEdit', compact('project', 'edit'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'image' => 'nullable|mimes:jpeg,jpg,png,bmp,pdf,xls,xlsx,doc,docx|max:3048',
            'location' => 'required|string|max:255',
            'task_owner_id' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
        ]);

        if ($request->deadline) {
            $validated['deadline'] = Carbon::parse($request->deadline);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store("", 'project');
            $validated['image'] = $imagePath;
        }

        $existingIds = collect($request->subprojects)
        ->pluck('id')
        ->filter()
        ->toArray();

        $project->subProjects()
            ->whereNotIn('id', $existingIds)
            ->delete();

        if ($request->has('subprojects')) {
            foreach ($request->subprojects as $subproject) {
                $project->subProjects()->updateOrCreate(
                    ['id' => $subproject['id'] ?? null],
                    ['title' => $subproject['title']]
                );
            }
        }

        $project->update($validated);
        return redirect()->route('projectList')->with('success', 'Project updated successfully.');
    }

    public function getComments(Project $project)
    {
        $project->load('comments.user');
        return view('project.comment', ['project' => $project]);
    }

    public function storeComment(Project $project, Request $request)
    {
        $comment = $project->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->input('comment'),
        ]);

        return response()->json([
            'success' => true,
            'comment' => [
                'user_name' => $comment->user->name,
                'timestamp' => verta($comment->created_at)->formatJalaliDatetime(),
                'text' => $comment->comment
            ],
            'count' => $project->comments()->count()
        ]);
    }

    public function storeSubProject(Request $request, Project $project)
    {
        $validated = $request->validate(['title' => 'required|string|max:255']);
        $subProject = $project->subProjects()->create($validated);
        return response()->json(['success' => true, 'subproject' => ['id' => $subProject->id, 'title' => $subProject->title]]);
    }

    public function updateSubProject(Request $request, $id)
    {
        $subProject = SubProject::findOrFail($id);
        $subProject->completed = $request->input('completed');
        $subProject->save();

        return response()->json(['success' => true]);
    }

    public function deleteSubProject(SubProject $subProject)
    {
        $subProject->delete();
        return response()->json(['success' => true]);
    }

    public function AddReport(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        if ($project->task_owner_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'report' => 'required|string'
        ]);
        $project->report = $request->input('report');
        $project->save();
        return response()->json(['success' => true]);
    }

    public function report($id)
    {
        $project = Project::findOrFail($id);
        if ($project->task_owner_id !== auth()->id()) {
            abort(403);
        }
        return view('project.report', compact('project'));
    }
}