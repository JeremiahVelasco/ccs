<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rubric;
use App\Models\RubricSection;
use App\Models\RubricCriteria;
use App\Models\RubricScaleLevel;

class RubricSeeder extends Seeder
{
    public function run()
    {
        // Create Group Grading Rubric
        $groupRubric = Rubric::create([
            'name' => 'Group Grade Summary',
            'type' => 'group',
            'description' => 'Documentation + Prototype + Presentation',
            'total_points' => 100,
            'is_active' => true
        ]);

        // Documentation Section (30 points)
        $documentationSection = RubricSection::create([
            'rubric_id' => $groupRubric->id,
            'name' => 'Documentation',
            'total_points' => 30,
            'order' => 1
        ]);

        // Chapter 4: Results and Discussion
        $chapter4Criteria = RubricCriteria::create([
            'rubric_section_id' => $documentationSection->id,
            'name' => 'Presentation of Results',
            'description' => 'Chapter 4 (15 pts)',
            'weight' => 2,
            'max_points' => 5,
            'order' => 1
        ]);

        $this->createScaleLevels($chapter4Criteria->id, [
            5 => ['Very Acceptable', 'All results are correct and clearly presented'],
            4 => ['Acceptable', 'All results are clearly presented'],
            3 => ['Fair', 'Most results are clearly presented'],
            2 => ['Needs Improvement', 'Some results are clearly presented'],
            1 => ['Poor', 'The results presented are not correct']
        ]);

        $findingsCriteria = RubricCriteria::create([
            'rubric_section_id' => $documentationSection->id,
            'name' => 'Summary of Findings',
            'description' => 'Quality of findings and support',
            'weight' => 1,
            'max_points' => 5,
            'order' => 2
        ]);

        $this->createScaleLevels($findingsCriteria->id, [
            5 => ['Very Acceptable', 'All findings are valid and well supported'],
            4 => ['Acceptable', 'All findings are supported'],
            3 => ['Fair', 'Most findings are supported'],
            2 => ['Needs Improvement', 'Some findings are supported'],
            1 => ['Poor', 'The findings are not supported']
        ]);

        // Chapter 5: Conclusion
        $conclusionCriteria = RubricCriteria::create([
            'rubric_section_id' => $documentationSection->id,
            'name' => 'Conclusion',
            'description' => 'Chapter 5 (5 pts)',
            'weight' => 1,
            'max_points' => 5,
            'order' => 3
        ]);

        $this->createScaleLevels($conclusionCriteria->id, [
            5 => ['Very Acceptable', 'The conclusions are well supported, restate personal learning and captures the focus of the project'],
            4 => ['Acceptable', 'The conclusions are well supported and restate personal learning'],
            3 => ['Fair', 'The conclusions are clearly stated and supported'],
            2 => ['Needs Improvement', 'The conclusions are clearly stated but not well supported'],
            1 => ['Poor', 'The conclusions are not properly stated']
        ]);

        // Chapter 6: Recommendation
        $recommendationCriteria = RubricCriteria::create([
            'rubric_section_id' => $documentationSection->id,
            'name' => 'Recommendation',
            'description' => 'Chapter 6 (5 pts)',
            'weight' => 1,
            'max_points' => 5,
            'order' => 4
        ]);

        $this->createScaleLevels($recommendationCriteria->id, [
            5 => ['Very Acceptable', 'The recommendations for the project are acceptable and attainable and significant'],
            4 => ['Acceptable', 'The recommendations for the project are acceptable and attainable'],
            3 => ['Fair', 'The recommendations for the project are acceptable'],
            2 => ['Needs Improvement', 'Weak recommendations for the project are provided'],
            1 => ['Poor', 'No clear recommendations for the project are provided']
        ]);

        // Other Content
        $contentCriteria = RubricCriteria::create([
            'rubric_section_id' => $documentationSection->id,
            'name' => 'Content',
            'description' => 'Others (5pts) - Appendices and supporting materials',
            'weight' => 1,
            'max_points' => 5,
            'order' => 5
        ]);

        $this->createScaleLevels($contentCriteria->id, [
            5 => ['Very Acceptable', 'All appendices required and needed are complete and correctly presented'],
            4 => ['Acceptable', 'Almost all required appendices are complete and correctly presented'],
            3 => ['Fair', 'Most appendices are correctly presented'],
            2 => ['Needs Improvement', 'Few appendices are presented but not all are correct'],
            1 => ['Poor', 'Incomplete appendices are presented']
        ]);

        // Prototype Section (40 points)
        $prototypeSection = RubricSection::create([
            'rubric_id' => $groupRubric->id,
            'name' => 'Prototype',
            'total_points' => 40,
            'order' => 2
        ]);

        $projectOutputCriteria = RubricCriteria::create([
            'rubric_section_id' => $prototypeSection->id,
            'name' => 'Project Output',
            'description' => 'Quality and completeness of prototype',
            'weight' => 4,
            'max_points' => 5,
            'order' => 1
        ]);

        $this->createScaleLevels($projectOutputCriteria->id, [
            5 => ['Very Acceptable', 'The prototype meets all objectives of the project'],
            4 => ['Acceptable', 'The prototype meets almost all objectives of the project'],
            3 => ['Fair', 'The prototype meets most objectives of the project'],
            2 => ['Needs Improvement', 'The prototype meets few objectives of the project'],
            1 => ['Poor', 'The prototype does not meet any objectives of the project']
        ]);

        $relevanceCriteria = RubricCriteria::create([
            'rubric_section_id' => $prototypeSection->id,
            'name' => 'Relevance to specialization',
            'description' => 'Alignment with program specialization',
            'weight' => 2,
            'max_points' => 5,
            'order' => 2
        ]);

        $this->createScaleLevels($relevanceCriteria->id, [
            5 => ['Very Acceptable', 'The project is relevant and significant to the program specialization'],
            4 => ['Acceptable', 'The project is relevant to the program specialization'],
            3 => ['Fair', 'The project is sufficiently relevant to the program specialization'],
            2 => ['Needs Improvement', 'The project has minimal relevance to the program specialization'],
            1 => ['Poor', 'The project has poor relevance to program specialization']
        ]);

        $demonstrationCriteria = RubricCriteria::create([
            'rubric_section_id' => $prototypeSection->id,
            'name' => 'Project Demonstration',
            'description' => 'Quality of demonstration and explanation',
            'weight' => 2,
            'max_points' => 5,
            'order' => 3
        ]);

        $this->createScaleLevels($demonstrationCriteria->id, [
            5 => ['Very Acceptable', 'All processes and outcomes are properly and clearly demonstrated'],
            4 => ['Acceptable', 'All processes and outcomes are demonstrated'],
            3 => ['Fair', 'Most processes and outcomes are demonstrated'],
            2 => ['Needs Improvement', 'Few processes and outcomes are demonstrated'],
            1 => ['Poor', 'The project processes and outcomes are not demonstrated']
        ]);

        // Presentation Section (30 points)
        $presentationSection = RubricSection::create([
            'rubric_id' => $groupRubric->id,
            'name' => 'Presentation',
            'total_points' => 30,
            'order' => 3
        ]);

        $consistencyCriteria = RubricCriteria::create([
            'rubric_section_id' => $presentationSection->id,
            'name' => 'Consistency',
            'description' => 'Alignment between presentation and documentation',
            'weight' => 3,
            'max_points' => 5,
            'order' => 1
        ]);

        $this->createScaleLevels($consistencyCriteria->id, [
            5 => ['Very Acceptable', 'The project discussion is consistent and evidently supported based from the project paper'],
            4 => ['Acceptable', 'The project discussion is consistent with the project paper'],
            3 => ['Fair', 'Most of the project discussion is consistent with the project paper'],
            2 => ['Needs Improvement', 'Some parts of the project discussion are consistent with the project paper'],
            1 => ['Poor', 'The project discussion is not consistent with the project paper']
        ]);

        $materialsCriteria = RubricCriteria::create([
            'rubric_section_id' => $presentationSection->id,
            'name' => 'Materials',
            'description' => 'Quality and effectiveness of presentation materials',
            'weight' => 1,
            'max_points' => 5,
            'order' => 2
        ]);

        $this->createScaleLevels($materialsCriteria->id, [
            5 => ['Very Acceptable', 'The materials used exemplary contributed and significantly led to project understanding'],
            4 => ['Acceptable', 'The materials used contributed and significantly led to project understanding'],
            3 => ['Fair', 'The materials used contributed to project understanding'],
            2 => ['Needs Improvement', 'The materials used somehow contributed to project understanding'],
            1 => ['Poor', 'The materials used did not contribute to project understanding']
        ]);

        $mannerCriteria = RubricCriteria::create([
            'rubric_section_id' => $presentationSection->id,
            'name' => 'Manner of Presentation',
            'description' => 'Communication skills and presentation style',
            'weight' => 1,
            'max_points' => 5,
            'order' => 3
        ]);

        $this->createScaleLevels($mannerCriteria->id, [
            5 => ['Very Acceptable', 'The presenters were able to communicate the project ideas in a most appropriate manner'],
            4 => ['Acceptable', 'The presenters were able to communicate the project ideas'],
            3 => ['Fair', 'The presenters were able to communicate most of the project ideas'],
            2 => ['Needs Improvement', 'The presenters were able to communicate some of the project ideas'],
            1 => ['Poor', 'The presenters were not able to communicate project ideas']
        ]);

        $overviewCriteria = RubricCriteria::create([
            'rubric_section_id' => $presentationSection->id,
            'name' => 'Presentation of Project Overview',
            'description' => 'Clarity and completeness of project overview',
            'weight' => 1,
            'max_points' => 5,
            'order' => 4
        ]);

        $this->createScaleLevels($overviewCriteria->id, [
            5 => ['Very Acceptable', 'Project overview was clearly explained'],
            4 => ['Acceptable', 'Project overview was adequately explained'],
            3 => ['Fair', 'Project overview was somehow explained'],
            2 => ['Needs Improvement', 'Project overview is not clearly explained'],
            1 => ['Poor', 'There is no project overview']
        ]);

        // Create Individual Grading Rubric
        $individualRubric = Rubric::create([
            'name' => 'Individual Student Evaluation',
            'type' => 'individual',
            'description' => 'Individual student presentation and performance assessment',
            'total_points' => 100,
            'is_active' => true
        ]);

        $individualSection = RubricSection::create([
            'rubric_id' => $individualRubric->id,
            'name' => 'Individual Performance',
            'total_points' => 100,
            'order' => 1
        ]);

        // Subject Mastery (x8)
        $subjectMasteryCriteria = RubricCriteria::create([
            'rubric_section_id' => $individualSection->id,
            'name' => 'Subject Mastery',
            'description' => 'Knowledge and understanding of the subject matter',
            'weight' => 8,
            'max_points' => 5,
            'order' => 1
        ]);

        $this->createScaleLevels($subjectMasteryCriteria->id, [
            5 => ['Very Acceptable', 'Student discusses the subject with enough information, provides supporting details and gives specific examples'],
            4 => ['Acceptable', 'Student discusses the subject with enough information and supporting details'],
            3 => ['Fair', 'Student discusses the subject with enough information'],
            2 => ['Needs Improvement', 'Student discusses the subject with very minimal details during the discussion'],
            1 => ['Poor', 'Student has no subject mastery at all']
        ]);

        // Ability to Answer Questions (x6)
        $answerQuestionsCriteria = RubricCriteria::create([
            'rubric_section_id' => $individualSection->id,
            'name' => 'Ability to Answer Questions',
            'description' => 'Responsiveness and accuracy in answering questions',
            'weight' => 6,
            'max_points' => 5,
            'order' => 2
        ]);

        $this->createScaleLevels($answerQuestionsCriteria->id, [
            5 => ['Very Acceptable', 'Student can answer all questions about the subject and can explain thoroughly'],
            4 => ['Acceptable', 'Student can answer most questions about the subject'],
            3 => ['Fair', 'Student can answer some questions about the subject'],
            2 => ['Needs Improvement', 'Student can answer few questions about the subject'],
            1 => ['Poor', 'Student cannot answer any question about the subject']
        ]);

        // Delivery (x2)
        $deliveryCriteria = RubricCriteria::create([
            'rubric_section_id' => $individualSection->id,
            'name' => 'Delivery',
            'description' => 'Gestures, expressions, and overall delivery',
            'weight' => 2,
            'max_points' => 5,
            'order' => 3
        ]);

        $this->createScaleLevels($deliveryCriteria->id, [
            5 => ['Very Acceptable', 'Student shows very excellent gestures and expressions to convey ideas'],
            4 => ['Acceptable', 'Student shows very good gestures and expressions to convey ideas'],
            3 => ['Fair', 'Student shows good gestures and expressions to convey ideas'],
            2 => ['Needs Improvement', 'Student shows gestures and expressions that needs improvement to convey ideas'],
            1 => ['Poor', 'Student show poor gestures and expressions to convey ideas']
        ]);

        // Verbal and Non Verbal Ability (x2)
        $verbalCriteria = RubricCriteria::create([
            'rubric_section_id' => $individualSection->id,
            'name' => 'Verbal and Non Verbal Ability',
            'description' => 'Grammar, pronunciation, choice of words and use of English language',
            'weight' => 2,
            'max_points' => 5,
            'order' => 4
        ]);

        $this->createScaleLevels($verbalCriteria->id, [
            5 => ['Very Acceptable', 'Correct grammar, pronunciation, choice of words and use of the English language in general are exceptional'],
            4 => ['Acceptable', 'Correct grammar, pronunciation, choice of words and use of the English language in general are good'],
            3 => ['Fair', 'Correct grammar, pronunciation, choice of words and use of the English language in general are acceptable'],
            2 => ['Needs Improvement', 'Correct grammar, pronunciation, choice of words, and use of the English language are acceptable, with some flaws'],
            1 => ['Poor', 'Correct grammar, pronunciation, choice of words and use of the English language in general are rarely observed']
        ]);

        // Grooming (x2)
        $groomingCriteria = RubricCriteria::create([
            'rubric_section_id' => $individualSection->id,
            'name' => 'Grooming',
            'description' => 'Professional appearance and attire',
            'weight' => 2,
            'max_points' => 5,
            'order' => 5
        ]);

        $this->createScaleLevels($groomingCriteria->id, [
            5 => ['Very Acceptable', 'Student wears formal attire and appears professional, well groomed, and decent'],
            4 => ['Acceptable', 'Student appears professional and decent'],
            3 => ['Fair', 'Student is well-groomed and in corporate attire'],
            2 => ['Needs Improvement', 'Appearance is unprofessional but attempts have been made to look decent'],
            1 => ['Poor', 'Appearance is unprofessional']
        ]);
    }

    private function createScaleLevels($criteriaId, $levels)
    {
        foreach ($levels as $points => $data) {
            RubricScaleLevel::create([
                'rubric_criteria_id' => $criteriaId,
                'points' => $points,
                'level_name' => $data[0],
                'description' => $data[1]
            ]);
        }
    }
}
