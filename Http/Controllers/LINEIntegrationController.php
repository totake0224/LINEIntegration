<?php

namespace Modules\LINEIntegration\Http\Controllers;

use App\Conversation;
use App\Customer;
use App\Mailbox;
use App\Thread;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Clients\MessagingApi\Configuration;
use LINE\Constants\HTTPHeader;
use LINE\Parser\EventRequestParser;
use LINE\Webhook\Model\MessageEvent;
use LINE\Parser\Exception\InvalidEventRequestException;
use LINE\Parser\Exception\InvalidSignatureException;
use LINE\Webhook\Model\TextMessageContent;

class LINEIntegrationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('lineintegration::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('lineintegration::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('lineintegration::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        return view('lineintegration::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }

    public function webhooks(Request $request,$mailbox_id)//, $mailbox_secret)
    {

        if (class_exists('Debugbar')) {
            \Debugbar::disable();
        }

        $mailbox = Mailbox::find($mailbox_id);

        $driver_config = $mailbox->meta[\LINEIntegration::DRIVER] ?? [];

        if (empty($driver_config['enabled']) || !(int)$driver_config['enabled'] || empty($driver_config['token'])) {
            \LINEIntegration::log(\LINEIntegration::CHANNEL_NAME.' is not configured for this mailbox.', $mailbox, true);
            return false;
        }

        $request_body = $request->getContent();
        $secret = $driver_config['secret'];
        $token = $driver_config['token'];
        $hash = hash_hmac('sha256', $request_body, $secret, true);
        $signature = base64_encode($hash);

        if($signature === $request->header(HTTPHeader::LINE_SIGNATURE)) { // ここでLINEからの送信を検証してます
            $client = new \GuzzleHttp\Client();
            $config = new \LINE\Clients\MessagingApi\Configuration();
            $config->setAccessToken($token);

            try {
                try {
                    $parsedEvents = EventRequestParser::parseEventRequest($request_body, $secret, $signature);
                } catch (InvalidSignatureException $e) {
                    \LINEIntegration::log('parsedEvent Invalid signature.' , $mailbox, true);
                    return $res->withStatus(400, 'Invalid signature');
                } catch (InvalidEventRequestException $e) {
                    \LINEIntegration::log('parsedEvent Invalid event request.' , $mailbox, true);
                    return $res->withStatus(400, "Invalid event request");
                }
                $bot = new \LINE\Clients\MessagingApi\Api\MessagingApiApi($client,$config);
                $botBlob = new \LINE\Clients\MessagingApi\Api\MessagingApiBlobApi($client,$config);

                foreach($parsedEvents->getEvents()  as $index => $event) {
                    if($event instanceof MessageEvent){
                        \LINEIntegration::processIncomingMessage($bot,$botBlob,$event, $mailbox);
                    }
                }
            } catch (\Exception $e) {
                \LINEIntegration::log('Event error.' . $e , $mailbox, true);
            }
        }
        return;   
    }


        /**
     * Settings.
     */
    public function settings($mailbox_id)
    {
        $mailbox = Mailbox::findOrFail($mailbox_id);
        
        if (!auth()->user()->isAdmin()) {
            \Helper::denyAccess();
        }

        $settings = $mailbox->meta[\LINEIntegration::DRIVER] ?? [];

        return view('lineintegration::settings', [
            'mailbox'   => $mailbox,
            'settings'   => $settings,
        ]);
    }

    /**
     * Settings save.
     */
    public function settingsSave(Request $request, $mailbox_id)
    {
        $mailbox = Mailbox::findOrFail($mailbox_id);
        
        $settings = $request->settings;

        $webhooks_enabled = (int)($mailbox->meta[\LINEIntegration::DRIVER]['enabled'] ?? 0);

        $settings['enabled'] = (int)($settings['enabled'] ?? 0);

        $mailbox->setMetaParam(\LINEIntegration::DRIVER, $settings);
        $mailbox->save();

        \Session::flash('flash_success_floating', __('Settings updated'));

        return redirect()->route('mailboxes.line.settings', ['mailbox_id' => $mailbox_id]);
    }

}
