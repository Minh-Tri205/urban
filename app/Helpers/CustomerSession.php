<?php

namespace App\Helpers;

use App\Models\Customer;

/**
 * Thay cho Session["Customer"] và Session["RoleID"] của bản C#.
 * Session chỉ lưu ID, còn thông tin khách hàng được đọc lại từ DB mỗi request
 * (cache trong request attributes), nên khi Admin đổi quyền hoặc khóa tài khoản thì có hiệu lực ngay.
 */
class CustomerSession
{
    private const KEY_ID = 'CustomerID';
    private const KEY_ROLE = 'RoleID';
    private const REQUEST_CACHE = 'fashionstore.current_customer';

    public static function login(Customer $customer): void
    {
        session()->regenerate();
        session([self::KEY_ID => $customer->CustomerID, self::KEY_ROLE => $customer->RoleID]);
        request()->attributes->set(self::REQUEST_CACHE, $customer);
    }

    public static function logout(): void
    {
        session()->forget([self::KEY_ID, self::KEY_ROLE]);
        session()->regenerate();
        request()->attributes->set(self::REQUEST_CACHE, null);
    }

    public static function user(): ?Customer
    {
        $attributes = request()->attributes;

        if (! $attributes->has(self::REQUEST_CACHE)) {
            $id = session(self::KEY_ID);
            $customer = $id ? Customer::with('customerProfile')->find($id) : null;

            // Tài khoản bị xóa hoặc bị khóa sau khi đăng nhập thì coi như đã đăng xuất
            if ($id && (! $customer || $customer->IsActive === false)) {
                $customer = null;
                session()->forget([self::KEY_ID, self::KEY_ROLE]);
            }

            $attributes->set(self::REQUEST_CACHE, $customer);
        }

        return $attributes->get(self::REQUEST_CACHE);
    }

    public static function id(): ?int
    {
        return self::user()?->CustomerID;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function roleId(): ?int
    {
        return self::user()?->RoleID;
    }

    public static function isAdmin(): bool
    {
        return (bool) self::user()?->isAdmin();
    }
}
