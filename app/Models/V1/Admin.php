<?php

namespace App\Models\V1;

use App\Models\BaseModel;
use DB;
use Exception;
use App\Services\TOTPService;

/**
 * Class Admin
 * @package App\Models\V1
 *
 * 错误码 1000~1999
 */
class Admin extends BaseModel
{
    protected $table = 'account';
    public $timestamps = false;

    public static function loginAdmin($account, $passwd, $remember=1)
    {
        $data = self::where([
            ['account', $account],
            ['is_del', '<>', 1]
        ])->select('id', 'passwd', 'salt', 'is_closed')->first();

        if (! $data) {
            throw new Exception('账户不存在@401', 1005);
        }

        $data = $data->toArray();

        if (! check_passwd($passwd, $data['passwd'], $data['salt'])) {
            throw new Exception('用户名或密码错误,若忘记密码请联系管理员@401', 1005);
        }

        if ($data['is_closed']) {
            throw new Exception('账户不存在@403', 1007);
        }

        $secret = TOTPService::generate_secret_key();
        $remember = $remember > 7 ? 7 : $remember;
        $expired_at = mktime(3, 0, 0, date('m'), date('d') + $remember, date('Y'));  // 默认第2天凌晨3点过期

        self::where('account', $account)->update([
            'secret' => $secret,
            'expired_at' => $expired_at
        ]);

        return [$data['id'], $secret];
    }

    public static function createAdmin($data=[])
    {
        $account = $data['account'];
        $result = self::where([
                ['account', $account],
                ['is_del', 0]
            ])
            ->select('id')
            ->first();
        if ($result) {
            throw new Exception('该账户已经存在@409', 1001);
        }

        // 检查角色状态
        Character::checkCharacterStatus($data['character_id']);

        list($passwdHash, $salt) = hash_passwd($data['passwd']);

        $data = [
            'salt' => $salt,
            'passwd' => $passwdHash,
            'created_at' => time(),
        ] + $data;

        self::updateCharacterNum($data['character_id']);

        return self::insertGetId($data);
    }

    public static function resetAdminPassword($id, $passwd)
    {
        $data = self::where('id', $id)
            ->select('id')
            ->first();
        if ($data) {
            throw new Exception('账户ID不存在@404', 1003);
        }

        list($passwdHash, $salt) = hash_passwd($passwd);

        self::where('id', $id)->update([
            'passwd' => $passwdHash,
            'salt' => $salt
        ]);

        return true;
    }

    public static function selectAdminById($id, $fields=['id', 'account', 'name', 'character_id', 'is_closed'])
    {
        $data = self::where([
            ['id', $id],
            ['is_del', 0]
        ])->select($fields)->first();

        if (! $data) {
            throw new Exception('该管理员不存在@404', 1009);
        }
        return $data->toArray();
    }

    public static function listAdmin($p, $n)
    {
        $data = self::where('account.is_del', 0)
            ->join('character', 'character_id', '=', 'character.id')
            ->paginate($n, [
                'account.id',
                'account.account',
                'account.name',
                'character.name as character',
                'account.is_closed',
                'account.created_at'
            ], 'p', $p);

        return self::getPageData($data);
    }

    public static function countAdmin()
    {
        return self::where('is_del', 0)->count();
    }

    public static function updateAdmin($id, $data=[])
    {
        $result = self::where('id', $id)
            ->select('character_id')
            ->first();
        if (! $result) {
            throw new Exception('该账户不存在@404', 1005);
        }

        // 检查角色状态
        Character::checkCharacterStatus($data['character_id']);

        // 更换角色
        $result = $result->toArray();
        if ($result['character_id'] != $data['character_id']) {
            self::updateCharacterNum($result['character_id'], false);
            self::updateCharacterNum($data['character_id']);
        }

        if (isset($data['passwd'])) {
            list($passwdHash, $salt) = hash_passwd($data['passwd']);
            $data = [
                'passwd' => $passwdHash,
                'salt' => $salt
            ] + $data;
        }

        // 账户停用之后密钥立刻过期
        if ($data['is_closed']) {
            $data['expired_at'] = 0;
        }

        self::where('id', $id)->update($data);

        return true;
    }

    public static function deleteAdmin($id)
    {
        $result = self::where([
            ['id', $id],
            ['is_del', 0]
        ])->select('character_id')->first();

        if (! $result) {
            throw new Exception('该账户不存在@404', 1011);
        }
        $result = $result->toArray();
        self::updateCharacterNum($result['character_id'], false);

        self::where('id', $id)->update([
            'is_del' => 1
        ]);

        return true;
    }

    public static function selectACL($id)
    {
        $data = self::where([
                ['account.id', $id],
                ['account.is_del', 0]
            ])
            ->join('character', 'character.id', '=', 'account.character_id')
            ->select('character.acl')
            ->first();

        if (! $data) {
            throw new Exception('该账户暂无权限', 1013);
        }

        $data = $data->toArray();
        return explode(',', $data['acl']);
    }

    public static function updateCharacterNum($characterId, $add=true)
    {
        if ($add) {
            DB::table('character')->where('id', $characterId)->increment('number', 1);
        } else {
            DB::table('character')->where('id', $characterId)->decrement('number', 1);
        }
    }
}