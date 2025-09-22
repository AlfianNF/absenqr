<?php

namespace App\Models;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    protected $guarded = [];

    protected static $rules = [
        'id_user' => 'required|exists:users,id',
        'image'=> 'nullable',
    ];

    protected static $is_add = ['id_user', 'image'];
    protected static $is_edit = ['id_user', 'image'];
    protected static $is_delete = ['id_user', 'image'];
    protected static $is_filter = ['id_user', 'image'];
    protected static $is_search = ['id_user', 'image'];


    public static function getAllowedFields($type)
    {
        return match ($type) {
            'add' => self::$is_add,
            'edit' => self::$is_edit,
            'delete' => self::$is_delete,
            'filter' => self::$is_filter,
            'search' => self::$is_search,
            default => [],
        };
    }

    public static function getValidationRules($type)
    {
        $allowedFields = self::getAllowedFields($type);
        $rules = [];

        foreach ($allowedFields as $field) {
            if (isset(self::$rules[$field])) {
                $rules[$field] = self::$rules[$field];
            }
        }

        return $rules;
    }

    /**
     * Get the userImage that owns the Image
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userImage(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function getRelations(){
        return [
            'userImage' => function ($query) {
                $columns = Schema::getColumnListing('image'); 
                $columns = array_diff($columns, ['created_at', 'updated_at']);
                $query->select($columns);
            },
        ];
    }
}
