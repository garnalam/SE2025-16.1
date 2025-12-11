<?php
namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;

class QuestionsImport implements ToModel, WithHeadingRow
{
    use Importable;

    private $user;
    private $subjectId;
    private $tagIds;

    public function __construct($user, $subjectId, $tagIds = [])
    {
        $this->user = $user;
        $this->subjectId = $subjectId;
        $this->tagIds = $tagIds;
    }

    public function model(array $row)
    {
        // 1. Tạo câu hỏi
        $question = $this->user->questions()->create([
            'subject_id' => $this->subjectId,
            'question_text' => $row['question_text'],
            'type' => 'single_choice',
        ]);

        // 2. Gắn thẻ (Tags)
        if (!empty($this->tagIds)) {
            $question->tags()->sync($this->tagIds);
        }

        // 3. Tạo các lựa chọn (Options)
        $correctIndex = (int) $row['correct_answer_index']; // (Giả sử cột này là 1, 2, 3, 4)

        for ($i = 1; $i <= 4; $i++) { // Giả sử có 4 lựa chọn
            if (isset($row["option_$i"]) && !empty($row["option_$i"])) {
                $question->options()->create([
                    'option_text' => $row["option_$i"],
                    'is_correct' => ($i == $correctIndex),
                ]);
            }
        }

        return null; // Trả về null vì chúng ta đã tự tạo model
    }
}