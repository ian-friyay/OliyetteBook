<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $errors = [];
        $values = ['name' => '', 'email' => '', 'message' => ''];

        if ($request->isMethod('post')) {
            $values['name'] = trim((string) $request->input('name', ''));
            $values['email'] = strtolower(trim((string) $request->input('email', '')));
            $values['message'] = trim((string) $request->input('message', ''));

            if ($values['name'] === '' || mb_strlen($values['name']) > 100) {
                $errors['name'] = 'Enter your name.';
            }
            if (! filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Enter a valid email address.';
            }
            if (mb_strlen($values['message']) < 10) {
                $errors['message'] = 'Tell us a little more (at least 10 characters).';
            }

            if (! $errors) {
                Inquiry::create([
                    'name' => $values['name'],
                    'email' => $values['email'],
                    'message' => $values['message'],
                ]);
                flash('success', 'Thank you. Your message was sent to the resort team.');

                return redirect('/contact');
            }
        }

        return view('contact', [
            'errors' => $errors,
            'values' => $values,
            'pageTitle' => 'Contact',
        ]);
    }
}
