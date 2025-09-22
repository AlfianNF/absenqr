<?php

namespace App\Models;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettingPresensi extends Model
{
    protected $guarded = [];

    protected static $rules = [
        'id_user' => 'required|exists:users,id',
        'hari'  => 'required|date',
        'jam_absen' => 'required|date_format:H:i:s',
        'jam_pulang' => 'required|date_format:H:i:s'
    ];

    protected static $is_add = ['id_user', 'hari', 'jam_absen','jam_pulang'];
    protected static $is_edit = ['id_user', 'hari', 'jam_absen','jam_pulang']; 
    protected static $is_delete = ['hari', 'jam_absen','jam_pulang'];
    protected static $is_filter = ['hari','jam_absen','jam_pulang'];
    protected static $is_search = ['id_user', 'hari'];  

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
     * Get the user that owns the SettingPresensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Get all of the settingPresensi for the SettingPresensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'id_setting', 'id');
    }

    public function getRelations()
    {
        return [
            'presensi' => function ($query) {
                $columns = Schema::getColumnListing('presensis'); 
                $columns = array_diff($columns, ['created_at', 'updated_at']);
                $query->select($columns)->with(['userPresensi' => function ($q) {
                    $columns = Schema::getColumnListing('users');
                    $columns = array_diff($columns, ['created_at', 'updated_at']);
                    $q->select($columns);
                }]);
            },
            'user' => function ($query) {
                $columns = Schema::getColumnListing('users');
                $columns = array_diff($columns, ['created_at', 'updated_at']);
                $query->select($columns);
            },
        ];
    }

}
