<?php

namespace RodrigoButta\Admin\Auth;

use RodrigoButta\Admin\Facades\Admin;
use RodrigoButta\Admin\Middleware\Pjax;
use Illuminate\Support\Facades\Auth;

// TODO sirve para vuelta de switcher error deny
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class Permission
{
    /**
     * Check permission.
     *
     * @param $permission
     *
     * @return true
     */
    public static function check($permission)
    {
        if (static::isAdministrator()) {
            return true;
        }

        if (is_array($permission)) {
            collect($permission)->each(function ($permission) {
                call_user_func([Permission::class, 'check'], $permission);
            });

            return;
        }

        if (Auth::guard('admin')->user()->cannot($permission)) {
            static::error();
        }
    }

    /**
     * Roles allowed to access.
     *
     * @param $roles
     *
     * @return true
     */
    public static function allow($roles)
    {
        if (static::isAdministrator()) {
            return true;
        }

        if (!Auth::guard('admin')->user()->inRoles($roles)) {
            static::error();
        }
    }

    /**
     * Roles denied to access.
     *
     * @param $roles
     *
     * @return true
     */
    public static function deny($roles)
    {
        if (static::isAdministrator()) {
            return true;
        }

        if (Auth::guard('admin')->user()->inRoles($roles)) {
            static::error();
        }
    }

    /**
     * Send error response page.
     */
    public static function error()
    {

        $request = Request::capture();

        // ajax but not pjax
        if ($request->ajax() && !$request->pjax()) {

            $response = response([
                 'status'  => false,
                 'message' => trans('admin.deny'),
             ]);

        }
        else{

            $response = response(Admin::content()->withError(trans('admin.deny')));

        }

        Pjax::respond($response);

    }

    /**
     * If current user is administrator.
     *
     * @return mixed
     */
    public static function isAdministrator()
    {
        return Auth::guard('admin')->user()->isRole('administrator');
    }


}
