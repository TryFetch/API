<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\Key;
use Carbon\Carbon;
use PushNotification;
use GuzzleHttp\Exception\ClientException;

class PingEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ping:events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping Put.io to check if there are new events';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $keys = Key::all();
        foreach($keys as $key) {
            $client = new Client();
            try {
                $res = $client->request('GET', "https://api.put.io/v2/events/list?oauth_token={$key->key}");
            } catch (ClientException $e) {
                continue;
            }

            if($res->getStatusCode() == 200) {
                $events = json_decode($res->getBody())->events;

                $devices = $key->devices()->get()->unique('token')->map(function($device) {
                    return PushNotification::Device($device->token);
                });

                $deviceCollection = PushNotification::DeviceCollection($devices->toArray());

                foreach($events as $event) {
                    if(isset($event->type) && isset($event->created_at) && $event->type == 'transfer_completed') {

                        $date = Carbon::parse($event->created_at);
                        if($key->last_run > $date) continue;

                        $message = PushNotification::Message("Transfer Completed: {$event->transfer_name}", ['sound' => 'success.wav']);
                        PushNotification::app('fetch')
                            ->to($deviceCollection)
                            ->send($message);
                    }
                }

                $key->last_run = Carbon::now();
                $key->save();
            }
        }
    }
}
