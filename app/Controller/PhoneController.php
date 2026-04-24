<?php
namespace Controller;

use Model\Department;
use Model\Phone;
use Model\Room;
use Model\Subscriber;
use Src\Request;
use Src\View;
use Validators\PhoneValidator;
use ekhidness\PhoneUtils\PhoneFormatter;

class PhoneController
{
    public function index(): string
    {
        return (new View())->render('phone.index', [
            'phones' => Phone::with(['room.department', 'subscriber'])->get()
        ]);
    }

    public function create(Request $request): string
    {
        if ($request->method === 'POST') {
            $data = $request->all();

            /*
            if (!empty($data['Number'])) {
                $clean = preg_replace('/[^0-9]/', '', $data['Number']);
                if (strlen($clean) === 10) {
                    $data['Number'] = '(' . substr($clean, 0, 3) . ') ' . substr($clean, 3, 3) . '-' . substr($clean, 6, 2) . '-' . substr($clean, 8, 2);
                } elseif (strlen($clean) === 11 && substr($clean, 0, 1) === '8') {
                    $clean = '7' . substr($clean, 1);
                    $data['Number'] = '+7 (' . substr($clean, 1, 3) . ') ' . substr($clean, 4, 3) . '-' . substr($clean, 7, 2) . '-' . substr($clean, 9, 2);
                } elseif (strlen($clean) === 11 && substr($clean, 0, 1) === '7') {
                    $data['Number'] = '+7 (' . substr($clean, 1, 3) . ') ' . substr($clean, 4, 3) . '-' . substr($clean, 7, 2) . '-' . substr($clean, 9, 2);
                }

                if (strlen(preg_replace('/[^0-9]/', '', $data['Number'])) < 10) {
                     return (new View())->render('phone.create', [
                        'rooms' => Room::with('department')->get(),
                        'message' => json_encode(['Number' => ['Некорректный формат номера']], JSON_UNESCAPED_UNICODE)
                    ]);
                }
            }
            */

            if (!empty($data['Number'])) {
                $data['Number'] = PhoneFormatter::format($data['Number']);

                if (!PhoneFormatter::isValid($data['Number'])) {
                    return (new View())->render('phone.create', [
                        'rooms' => Room::with('department')->get(),
                        'message' => json_encode(['Number' => ['Некорректный формат номера']], JSON_UNESCAPED_UNICODE)
                    ]);
                }
            }

            $validator = PhoneValidator::make($data);
            if ($validator->fails()) {
                return (new View())->render('phone.create', [
                    'rooms' => Room::with('department')->get(),
                    'message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE)
                ]);
            }

            Phone::create($data);
            app()->route->redirect('/sys/phones');
            return false;
        }
        return (new View())->render('phone.create', ['rooms' => Room::with('department')->get()]);
    }

    public function attach(Request $request): string
    {
        if ($request->method === 'POST') {
            $phone = Phone::find($request->get('phone_id'));

            if ($phone) {
                $subscriberId = $request->get('subscriber_id');
                $phone->SubscriberID = !empty($subscriberId) ? $subscriberId : null;
                $phone->save();
            }

            app()->route->redirect('/sys/phones');
            return false;
        }

        $phones = Phone::with(['room', 'subscriber'])->get();
        $subscribers = Subscriber::with('phones.room.department')->get();

        return (new View())->render('phone.attach', [
            'phones' => $phones,
            'subscribers' => $subscribers
        ]);
    }

    public function byDepartment(Request $request): string
    {
        $phones = Phone::with(['room.department', 'subscriber'])
            ->when($request->get('department_id'), fn($q, $id) => $q->byDepartment($id))
            ->get();

        return (new View())->render('phone.by_department', [
            'phones' => $phones,
            'departments' => Department::all()
        ]);
    }
}