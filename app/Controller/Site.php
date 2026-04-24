<?php
namespace Controller;
use Src\Request;
use Src\View;
use Model\User;
use Validators\UserValidator;

class Site
{
    public function hello(): string
    {
        return (new View())->render('site.hello');
    }

    public function index(): string
    {
        return (new View())->render('site.index');
    }

    public function signup(Request $request): string
    {
        if ($request->method === 'POST') {
            $data = $request->all();

            $prepared = [
                'Login'        => $data['login'] ?? '',
                'PasswordHash' => $data['password'] ?? '',
                'RoleID'       => 3,
                'IsBlocked'    => 0
            ];

            $validator = UserValidator::make($prepared);
            if ($validator->fails()) {
                return (new View())->render('site.signup', [
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE)
                ]);
            }

            User::create($prepared);
            app()->route->redirect('/login');
            return '';
        }

        return (new View())->render('site.signup');
    }
}