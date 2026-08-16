<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings', [
            'prompt'        => Setting::get('edit_prompt', ''),
            'geminiModel'   => Setting::get('gemini_model', 'gemini-3.5-flash-lite'),
            'hasKey'        => filled(Setting::get('gemini_api_key')),
            'hasFdKey'      => filled(Setting::get('football_data_key')),
            'fdTeamId'      => Setting::get('football_data_team_id', '64'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'edit_prompt'          => 'required|string|max:8000',
            'gemini_model'         => 'required|string|max:100',
            'gemini_api_key'       => 'nullable|string|max:200',
            'football_data_key'    => 'nullable|string|max:200',
            'football_data_team_id'=> 'nullable|integer|min:1|max:999999',
        ]);

        Setting::set('edit_prompt', $data['edit_prompt']);
        Setting::set('gemini_model', trim($data['gemini_model']));

        // Keys are only overwritten when a new value is typed (blank = keep).
        if (filled($data['gemini_api_key'])) {
            Setting::set('gemini_api_key', Crypt::encryptString(trim($data['gemini_api_key'])));
        }

        if (filled($data['football_data_key'])) {
            Setting::set('football_data_key', Crypt::encryptString(trim($data['football_data_key'])));
        }

        if (filled($data['football_data_team_id'])) {
            Setting::set('football_data_team_id', (string) $data['football_data_team_id']);
        }

        return back()->with('ok', 'Настройки сохранены.');
    }
}
