<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommonResource;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SkillController extends Controller
{
    public function index()
    {
        $skills = Skill::latest()->paginate(5);
        return new CommonResource(true, 'Skill Lists', $skills);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'skill_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $skill = Skill::create([
            'skill_name' => $request->skill_name,
        ]);

        return new CommonResource(true, 'Skill Successfully Added!', $skill);
    }

    public function show(Skill $skill)
    {
        return new CommonResource(true, 'Skill Found!', $skill);
    }

    public function update(Request $request, Skill $skill)
    {
        $validator = Validator::make($request->all(), [
            'skill_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $skill->update([
            'skill_name' => $request->skill_name,
        ]);

        return new CommonResource(true, 'Skill Successfully Updated!', $skill);
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();

        return new CommonResource(true, 'Skill Successfully Deleted!', null);
    }
}
