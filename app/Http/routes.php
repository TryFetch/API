<?php

Route::group(['prefix' => 'api'], function() {

    Route::get('faqs', function() {
        return App\FAQ::all();
    });

    Route::post('register-device', function(Illuminate\Http\Request $request) {

        $key = App\Key::where('key', '=', $request->input('api'))
            ->first();

        if(!$key) {
            $key = new App\Key();
            $key->key = $request->input('api');
            $key->last_run = Carbon\Carbon::now();
            $key->save();
        }

        $device = App\Device::where('key_id', '=', $key->id)
            ->where('token', '=', $request->input('device'))
            ->first();

        if(!$device) {
            $device = new App\Device();
            $device->key_id = $key->id;
            $device->token = $request->input('device');
            $device->save();
        }

    });

});

Route::get('test-notification', function() {

    $device = PushNotification::Device("3adaf31025cdb86daef96312ad5daf8548a779a68aa56ecc055b324ac430fe22");
    $deviceCollection = PushNotification::DeviceCollection([$device]);
    $message = PushNotification::Message("Transfer Completed", ['sound' => 'success.wav']);
    PushNotification::app('fetch')
        ->to($deviceCollection)
        ->send($message);

});

Route::resource('request-tv-token', 'TokenGenerationController', ['only' => ['store']]);
Route::resource('exchange-tokens', 'ExchangeTokensController', ['only' => ['show', 'update', 'destroy']]);

Route::get('{token}/complete', function ($token) {
    $code = App\Code::where('token', $token)->first();
    if($code) {
        $code->access_token = $_GET['access_token'];
        $code->save();
    }
    return view('complete');
});

Route::get('{id}', function ($id) {
  $code = App\Code::where('url', $id)->first();
  if(!$code) return app()->abort(404);
  return view('redirecting', ['token' => $code->token]);
});

Route::get('/', function () {
  return redirect('http://getfetchapp.com');
});
