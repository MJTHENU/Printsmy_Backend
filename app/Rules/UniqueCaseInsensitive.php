namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueCaseInsensitive implements Rule
{
    protected $table;
    protected $column;

    public function __construct($table, $column)
    {
        $this->table = $table;
        $this->column = $column;
    }

    public function passes($attribute, $value)
    {
        $exists = DB::table($this->table)
            ->whereRaw('LOWER(' . $this->column . ') = ?', [strtolower($value)])
            ->exists();

        return !$exists;
    }

    public function message()
    {
        return 'The :attribute has already been taken.';
    }
}
