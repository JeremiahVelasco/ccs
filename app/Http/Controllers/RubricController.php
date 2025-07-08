<?php

namespace App\Http\Controllers;

use App\Models\Rubric;
use App\Models\RubricSection;
use App\Models\RubricCriteria;
use App\Models\RubricScaleLevel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RubricController extends Controller
{
    public function index()
    {
        $rubrics = Rubric::with(['sections.criteria.scaleLevels'])
            ->where('is_active', true)
            ->get();

        return response()->json($rubrics);
    }

    public function show($id)
    {
        $rubric = Rubric::with(['sections.criteria.scaleLevels'])
            ->findOrFail($id);

        return response()->json($rubric);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:group,individual',
            'description' => 'nullable|string',
            'sections' => 'required|array',
            'sections.*.name' => 'required|string',
            'sections.*.criteria' => 'required|array',
            'sections.*.criteria.*.name' => 'required|string',
            'sections.*.criteria.*.weight' => 'required|integer|min:1',
            'sections.*.criteria.*.max_points' => 'required|integer|min:1',
        ]);

        $rubric = Rubric::create($request->only(['name', 'type', 'description']));

        foreach ($request->sections as $sectionIndex => $sectionData) {
            $section = RubricSection::create([
                'rubric_id' => $rubric->id,
                'name' => $sectionData['name'],
                'total_points' => collect($sectionData['criteria'])->sum(function ($criteria) {
                    return $criteria['weight'] * $criteria['max_points'];
                }),
                'order' => $sectionIndex + 1
            ]);

            foreach ($sectionData['criteria'] as $criteriaIndex => $criteriaData) {
                $criteria = RubricCriteria::create([
                    'rubric_section_id' => $section->id,
                    'name' => $criteriaData['name'],
                    'description' => $criteriaData['description'] ?? null,
                    'weight' => $criteriaData['weight'],
                    'max_points' => $criteriaData['max_points'],
                    'order' => $criteriaIndex + 1
                ]);

                if (isset($criteriaData['scale_levels'])) {
                    foreach ($criteriaData['scale_levels'] as $level) {
                        RubricScaleLevel::create([
                            'rubric_criteria_id' => $criteria->id,
                            'points' => $level['points'],
                            'level_name' => $level['level_name'],
                            'description' => $level['description']
                        ]);
                    }
                }
            }
        }

        $rubric->update(['total_points' => $rubric->sections->sum('total_points')]);

        return response()->json($rubric->load('sections.criteria.scaleLevels'), 201);
    }

    public function getByType($type)
    {
        $rubrics = Rubric::with(['sections.criteria.scaleLevels'])
            ->where('type', $type)
            ->where('is_active', true)
            ->get();

        return response()->json($rubrics);
    }
}
