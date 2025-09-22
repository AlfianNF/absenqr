<?php

namespace App\Models;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    protected $guarded = [];

    protected static $rules = [
        'id_setting' => 'required|exists:setting_presensis,id',
        'id_user' => 'required|exists:users,id',
        'jam_masuk' => 'nullable|date_format:H:i:s',
        'jam_keluar' => 'nullable|date_format:H:i:s',
        'status'=> 'nullable',
    ];

    protected static $is_add = ['id_setting', 'id_user', 'jam_masuk', 'jam_keluar', 'latitude','longitude','status'];
    protected static $is_edit = ['jam_masuk', 'jam_keluar','status'];
    protected static $is_delete = ['id_setting', 'id_user', 'jam_masuk', 'jam_keluar', 'latitude','longitude','status'];
    protected static $is_filter = ['id_setting', 'id_user', 'jam_masuk', 'jam_keluar', 'latitude','longitude','status'];
    protected static $is_search = ['id_setting', 'id_user', 'jam_masuk', 'jam_keluar', 'latitude','longitude','status'];


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
     * Get the userPresensi that owns the Presensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userPresensi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Get the presensiSetting that owns the Presensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function presensiSetting(): BelongsTo
    {
        return $this->belongsTo(SettingPresensi::class, 'id_setting');
    }

    public function getRelations(){
        return [
            'presensiSetting' => function ($query) {
                $columns = Schema::getColumnListing('setting_presensis'); 
                $columns = array_diff($columns, ['created_at', 'updated_at']);
                $query->select($columns);
            },
            'userPresensi' => function ($query) {
                $columns = Schema::getColumnListing('users');
                $columns = array_diff($columns, ['created_at', 'updated_at']);
                $query->select($columns);
            },
        ];
    }
}
