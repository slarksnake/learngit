<?php

namespace App\Models\V1;

use App\Models\BaseModel;
use DB;
use Exception;

/**
 * Class Character
 * @package App\Models\V1
 *
 * 错误码 6000~6999
 */
class Character extends BaseModel
{
    protected $table = 'character';
    public $timestamps = false;

    public static function createCharacter($data=[])
    {
        self::checkCharacterName($data['name']);

        $data['created_at'] = time();
        $id = self::insertGetId($data);

        return $id;
    }

    public static function selectCharacter($id)
    {
        $data = self::where([
                ['character.id', $id],
                ['character.is_del', 0]
            ])
            ->select(
                'character.id',
                'character.name as name',
                'acl',
                'description',
                'number',
                'status',
                'character.created_at'
            )
            ->first();

        if (! $data) {
            throw new Exception('该角色不存在', 6001);
        }

        return $data->toArray();
    }

    public static function listCharacter($p, $n, $all)
    {
        $where = [
            ['character.is_del', 0]
        ];
        // 只显示非停用状态角色
        if (! $all) {
            $where[] = ['character.status', 0];
        }

        $data = self::where($where)
            ->join('account', 'account.id', '=', 'character.founder')
            ->paginate($n, [
                'character.id',
                'character.name',
                'description',
                'number',
                'account.name as founder',
                'status',
                'character.created_at'
            ], 'p', $p);

        return self::getPageData($data);
    }

    public static function updateCharacter($id, $data=[])
    {
        self::checkCharacterName($data['name'], $id);

        // 停用且角色被使用
        if ($data['status'] && self::isCharacterUsed($id)) {
            throw new Exception('该角色已经被用户引用,无法停用@403', 6003);
        }

        self::where('id', $id)->update($data);

        return true;
    }

    public static function deleteCharacter($id)
    {
        if (self::isCharacterUsed($id)) {
            throw new Exception('该角色已经被用户引用,无法删除@403', 6005);
        }

        self::where('id', $id)->update([
            'is_del' => 1
        ]);

        return true;
    }

    public static function countCharacter()
    {
        return self::where('is_del', 0)->count();
    }

    private static function checkCharacterName($name, $id=0)
    {
        if ($id) {
            $data = self::where([
                ['is_del', 0],
                ['name', $name],
                ['id', '<>', $id]
            ])->select('id')->first();
        } else {
            $data = self::where([
                ['is_del', 0],
                ['name', $name]
            ])->select('id')->first();
        }

        if ($data) {
            throw new Exception('该角色名称已经存在@409', 6007);
        }
        return true;
    }

    public static function checkCharacterStatus($id)
    {
        $data = self::where([
                ['id', $id],
                ['is_del', 0],
                ['status', 0]
            ])->select('id')->first();

        if (! $data) {
            throw new Exception('该角色名称已经停用或被删除@422', 6009);
        }
    }


    private static function isCharacterUsed($id)
    {
        $data = DB::table('account')->where([
            ['is_del', 0],
            ['character_id', $id]
        ])->select('id')->first();

        return $data ? true : false;
    }
}