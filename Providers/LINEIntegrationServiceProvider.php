<?php

namespace Modules\LINEIntegration\Providers;

use App\Attachment;
use App\Conversation;
use App\Customer;
use App\Mailbox;
use App\Thread;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\PushMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Clients\MessagingApi\Model\ImageMessage;
use LINE\Constants\HTTPHeader;
use LINE\Parser\EventRequestParser;
use LINE\Webhook\Model\MessageEvent;
use LINE\Parser\Exception\InvalidEventRequestException;
use LINE\Parser\Exception\InvalidSignatureException;
use LINE\Constants\MessageContentProviderType;

use LINE\LINEBot\Event\MessageEvent\UnknownMessageContent;
use LINE\Webhook\Model\AudioMessageContent;
use LINE\Webhook\Model\ImageMessageContent;
use LINE\Webhook\Model\LocationMessageContent;
use LINE\Webhook\Model\StickerMessageContent;
use LINE\Webhook\Model\TextMessageContent;
use LINE\Webhook\Model\VideoMessageContent;

use LINE\Clients\MessagingApi\Model\AltUri;
use LINE\Clients\MessagingApi\Model\FlexBox;
use LINE\Clients\MessagingApi\Model\FlexBubble;
use LINE\Clients\MessagingApi\Model\FlexButton;
use LINE\Clients\MessagingApi\Model\FlexComponent;
use LINE\Clients\MessagingApi\Model\FlexIcon;
use LINE\Clients\MessagingApi\Model\FlexImage;
use LINE\Clients\MessagingApi\Model\FlexMessage;
use LINE\Clients\MessagingApi\Model\FlexSpacer;
use LINE\Clients\MessagingApi\Model\FlexSpan;
use LINE\Clients\MessagingApi\Model\FlexText;
use LINE\Clients\MessagingApi\Model\URIAction;
use LINE\Constants\ActionType;
use LINE\Constants\Flex\BubbleContainerSize;
use LINE\Constants\Flex\ComponentButtonHeight;
use LINE\Constants\Flex\ComponentButtonStyle;
use LINE\Constants\Flex\ComponentFontSize;
use LINE\Constants\Flex\ComponentFontWeight;
use LINE\Constants\Flex\ComponentIconSize;
use LINE\Constants\Flex\ComponentImageAspectMode;
use LINE\Constants\Flex\ComponentImageAspectRatio;
use LINE\Constants\Flex\ComponentImageSize;
use LINE\Constants\Flex\ComponentLayout;
use LINE\Constants\Flex\ComponentMargin;
use LINE\Constants\Flex\ComponentSpaceSize;
use LINE\Constants\Flex\ComponentSpacing;
use LINE\Constants\Flex\ComponentType;
use LINE\Constants\Flex\ContainerType;
use LINE\Constants\MessageType;

require_once __DIR__.'/../vendor/autoload.php';

class LINEIntegrationServiceProvider extends ServiceProvider
{
    const DRIVER = 'line';
    const CHANNEL = 22884;
    const CHANNEL_NAME = 'LINE';

    const LOG_NAME = 'line_errors';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        // Add item to the mailbox menu
        \Eventy::addAction('mailboxes.settings.menu', function($mailbox) {
            if (auth()->user()->isAdmin()) {
                echo \View::make('lineintegration::partials/settings_menu', ['mailbox' => $mailbox])->render();
            }
        }, 35);

        \Eventy::addFilter('menu.selected', function($menu) {
            $menu['line'] = [
                'mailboxes.line.settings',
            ];
            return $menu;
        });

        \Eventy::addFilter('channel.name', function($name, $channel) {
            if ($name) {
                return $name;
            }
            if ($channel == self::CHANNEL) {
                return self::CHANNEL_NAME;
            } else {
                return $name;
            }
        }, 20, 2);

        \Eventy::addAction('chat_conversation.send_reply', function($conversation, $replies, $customer) {
            if ($conversation->channel != self::CHANNEL) {
                return;
            }

            if (!$customer->channel_id) {
                \LINEIntegration::log('Can not send a reply to the customer ('.$customer->id.': '.$customer->getFullName().'): customer has no messenger ID.', $conversation->mailbox,true);
                return;
            }

            $mailbox = Mailbox::find($conversation->mailbox_id);

            $driver_config = $mailbox->meta[\LINEIntegration::DRIVER] ?? [];

            $client = new \GuzzleHttp\Client();
            $config = new \LINE\Clients\MessagingApi\Configuration();
            $token = $driver_config['token'];
            $config->setAccessToken($token);
            $bot = new \LINE\Clients\MessagingApi\Api\MessagingApiApi($client,$config);


            if (!$bot) {
                return;
            }

            // We send only the last reply.
            $replies = $replies->sortByDesc(function ($item, $key) {
                return $item->id;
            });
            $thread = $replies[0];

            // If thread is draft, it means it has been undone
            $thread = $thread->fresh();
            
            if ($thread->isDraft()) {
                return;
            }

            //$botman->typesAndWaits(2);
            
            $text = rtrim($thread->getBodyAsText());//なぜか改行されてしまうため

            $attachments=[];
            if ($thread->has_attachments) {
                //all attachment will be url link.
                //because freescout can't send message without message body.
                //line imagemessage could not contain message body.
                foreach ($thread->attachments as $attachment) {
                    $button = new FlexButton([
                        'type' => ComponentType::BUTTON,
                        'style' => ComponentButtonStyle::LINK,
                        'action' => new URIAction([
                            'type' => ActionType::URI,
                            'uri' => str_replace($attachment->file_name,urlencode($attachment->file_name),$attachment->url()),
//                                'uri' => Storage::url(Attachment::DIRECTORY . DIRECTORY_SEPARATOR . $attachment->file_dir . urlencode($attachment->file_name)) . '?id='.$attachment->id . '&token=' . $attachment->getToken(),
                            'label' => $attachment->file_name,
                        ]),
                    ]);
                    $attachments[]=$button;
                }
            }
            $attachmentBox= new FlexBox([
                'type' => ComponentType::BOX,
                'layout' => ComponentLayout::VERTICAL,
                'alignItems' =>  FlexBox::ALIGN_ITEMS_FLEX_START,
                    'contents' => $attachments,
            ]);    

            
            $body= new FlexBox([
                'type' => ComponentType::BOX,
                'layout' => ComponentLayout::VERTICAL,
                'contents' => 
                    [
                        // Title
                        new FlexText([
                            'type' => ComponentType::TEXT,
                            'text' => rtrim($thread->getBodyAsText()),//なぜか改行されてしまうため
                            'size' => ComponentFontSize::SM,
                        ]),
                        $attachmentBox
                    ],
            ]);

            $message = new FlexMessage([
                'type' => MessageType::FLEX,
                'altText' => $text,
                'contents' => new FlexBubble([
                    'type' => ContainerType::BUBBLE,
                    'body' => $body,
                    'size' => BubbleContainerSize::KILO
                ])
            ]);

            $request = new PushMessageRequest([
                        'to' => $customer->channel_id,
                        'messages' => [$message],
                        ]);
            $bot->pushMessage($request);    

// attachment is added to downward of body text as a link.
// I choose email like style.
/*
            if ($thread->has_attachments) {//send attchment as independent message.
                foreach ($thread->attachments as $attachment) {
                    switch ($attachment->type) {
                        case Attachment::TYPE_IMAGE:
                            $message = new ImageMessage([
                                'type' => MessageType::IMAGE,
                                'originalContentUrl' => $attachment->url(),
                                'previewImageUrl' => $attachment->url(),
                            ]);
                            $request = new PushMessageRequest([
                                'to' => $customer->channel_id,
                                'messages' => [$message],
                                ]);
                            $bot->pushMessage($request);
                            break;
                        case Attachment::TYPE_VIDEO:
                            $message = new VideoMessage([
                                'type' => MessageType::VIDEO,
                                'originalContentUrl' => $attachment->url(),
                                'previewImageUrl' => $attachment->url(),
                            ]);
                            $request = new PushMessageRequest([
                                'to' => $customer->channel_id,
                                'messages' => [$message],
                                ]);
                            $bot->pushMessage($request);
                            break;
                        case Attachment::TYPE_AUDIO:
                            $message = new AudioMessage([
                                'type' => MessageType::AUDIO,
                                'originalContentUrl' => $attachment->url(),
                                'duration' => 0,//it difficult get duration. I will reconsider this afterward.
                            ]);
                            $request = new PushMessageRequest([
                                'to' => $customer->channel_id,
                                'messages' => [$message],
                                ]);
                            $bot->pushMessage($request);
                            break;
                    }
                }
            }
            */
        }, 20, 3);

    }

    //get sticker image from this uarl
    //https://stickershop.line-scdn.net/stickershop/v1/sticker/ [sticker id] /iPhone/sticker_key@2x.png
    //I don't know what url I can get animated sticker.
    public static function HandleStickerMessage($customer,$mailbox,$message){
        $channel = \LINEIntegration::CHANNEL;
        $conversation = Conversation::where('mailbox_id', $mailbox->id)
        ->where('customer_id', $customer->id)
        ->where('channel', $channel)
        ->orderBy('created_at', 'desc')
        ->first();

        $attachments = [];

        //sticker is always external image;
        $file_url = "https://stickershop.line-scdn.net/stickershop/v1/sticker/" . $message->getStickerId() . "/iPhone/sticker_key@2x.png";
        $attachments[] = [
            'file_name' => $message->getStickerId() . ".png",
            'file_url' => $file_url,
        ];
        $text = '<div style="display: flex; gap:5px;flex-wrap:wrap">';
        $text .= '<img loading="lazy" height="200" src="cid:image">';
        $text .='</div>';

        if ($conversation) {//LINEの場合は同じユーザーとのやり取りは常に同じ会話として取り扱われるのでほぼほぼこちらがわで処理される。
            // Create thread in existing conversation.
            $thread=Thread::createExtended([
                    'type' => Thread::TYPE_CUSTOMER,
                    'customer_id' => $customer->id,
                    'body' => $text,
                    'attachments' => $attachments,
                ],
                $conversation,
                $customer
            );
            foreach($thread->attachments as $attachment){
                $thread->body = str_replace('cid:image', $attachment->url(), $thread->body);
            }
            $thread->save();            
        } 
    }


    public static function HandleTextMessage($customer,$mailbox,$message){
        $channel = \LINEIntegration::CHANNEL;
        $conversation = Conversation::where('mailbox_id', $mailbox->id)
        ->where('customer_id', $customer->id)
        ->where('channel', $channel)
        ->orderBy('created_at', 'desc')
        ->first();

        $attachments = [];

        $text = nl2br($message->getText());

        if ($conversation) {
            // Create thread in existing conversation.
            Thread::createExtended([
                    'type' => Thread::TYPE_CUSTOMER,
                    'customer_id' => $customer->id,
                    'body' => $text,
                    'attachments' => $attachments,
                ],
                $conversation,
                $customer
            );
        } 
    }

    public static function CreateAttachment($attachments,$thread_id){
        foreach($attachments as $attachment){
            $content = null;
            $uploaded_file = null;

            if (is_object($attachment) && get_class($attachment) == 'Illuminate\Http\UploadedFile') {

                $uploaded_file = $attachment;
                $attachment = [];
                $attachment['file_name'] = $uploaded_file->getClientOriginalName();
                $attachment['mime_type'] = $uploaded_file->getMimeType();

            } else {

                if (empty($attachment['file_name'])
                    //|| empty($attachment['mime_type'])
                    || (empty($attachment['data']) && empty($attachment['file_url']))
                ) {
                    continue;
                }
                if (!empty($attachment['data'])) {
                    // BASE64 string.
                    $content = base64_decode($attachment['data']);
                    if (!$content) {
                        return;
                    }
                    if (empty($attachment['mime_type'])) {
                        $f = finfo_open();
                        $attachment['mime_type'] = finfo_buffer($f, $content, FILEINFO_MIME_TYPE);
                    }
                } else {
                    // URL.
                    $file_path = \Helper::downloadRemoteFileAsTmp($attachment['file_url']);
                    if (!$file_path) {
                        return;
                    }
                    $uploaded_file = new \Illuminate\Http\UploadedFile(
                        $file_path, basename($file_path),
                        null, null, true
                    );
                    if (empty($attachment['mime_type'])) {
                        $attachment['mime_type'] = mime_content_type($file_path);
                        if (empty($attachment['mime_type'])) {
                            $attachment['mime_type'] = $uploaded_file->getMimeType();
                        }
                    }
                }
            }
            $attachment = Attachment::create(
                $attachment['file_name'],
                $attachment['mime_type'],
                null,
                $content,
                $uploaded_file,
                $embedded = false,
                $thread_id,
                null
            );
        }
        return;
    }

    //use ifram and google map
    //https://maps.google.com/maps?q=36.879676,-111.512351
    public static function HandleLocationMessage($customer,$mailbox,$message){
        $channel = \LINEIntegration::CHANNEL;
        $conversation = Conversation::where('mailbox_id', $mailbox->id)
        ->where('customer_id', $customer->id)
        ->where('channel', $channel)
        ->orderBy('created_at', 'desc')
        ->first();

        $attachments = [];

        $text = 'LINEから位置情報送信<br>';
        $text .= $message->getTitle() . '<br>';
        $text .= $message->getAddress() . '<br>';
        $text .= 'https://maps.google.com/maps?q=' . $message->getLatitude() . ',' . $message->getLongitude() ;
//        $text = nl2br($text);

        if ($conversation) {
            // Create thread in existing conversation.
            Thread::createExtended([
                    'type' => Thread::TYPE_CUSTOMER,
                    'customer_id' => $customer->id,
                    'body' => $text,
                    'attachments' => $attachments,
                ],
                $conversation,
                $customer
            );
        } 
    }

    public static function HandleAudioMessage($botBlob,$customer,$mailbox,$message){
        $channel = \LINEIntegration::CHANNEL;
        $conversation = Conversation::where('mailbox_id', $mailbox->id)
        ->where('customer_id', $customer->id)
        ->where('channel', $channel)
        ->orderBy('created_at', 'desc')
        ->first();

        $attachments = [];

        $contentProvider = $message->getContentProvider();
        $url=$contentProvider->getOriginalContentUrl();
        if ($contentProvider->getType() == MessageContentProviderType::EXTERNAL) {
            $file_url = $contentProvider->getOriginalContentUrl();
            $attachments[] = [
                'file_name' => \Helper::remoteFileName($file_url),
                'file_url' => $file_url,
            ];
        }else{
            $contentId = $message->getId();
            $sfo = $botBlob->getMessageContent($contentId);
            $image = $sfo->fread($sfo->getSize());
            $attachments[] = [
                'file_name' => \Helper::remoteFileName("audio-" . $message->getId().".m4a"),
                'data' => base64_encode($image),//thread only handle base64 encoded image or image url or file path;
            ];
        }

        if ($conversation) {//LINEの場合は同じユーザーとのやり取りは常に同じ会話として取り扱われるのでほぼほぼこちらがわで処理される。
            // Create thread in existing conversation.
            //単独の動画
            $text = '<a href="cid:audio-' . $message->getId().'.m4a" target="_blank">' . __('Audio from LINE') . '</a>';//LINEから音声送信
/*            $text = '<div style="display: flex; gap:5px;flex-wrap:wrap">';
            $text .= '<video loading="lazy" height="200" src=cid:movie.mp4"></video>';
            $text .='</div>';
            */
            $thread=Thread::createExtended([
                    'type' => Thread::TYPE_CUSTOMER,
                    'customer_id' => $customer->id,
                    'body' => $text,
                    'attachments' => $attachments,
                ],
                $conversation,
                $customer
            );
            foreach($thread->attachments as $attachment){
                $thread->body = str_replace('cid:' . $attachment->file_name, $attachment->url(), $thread->body);
            }
            $thread->save();
        } 
    }

    
    public static function HandleVideoMessage($botBlob,$customer,$mailbox,$message){
        $channel = \LINEIntegration::CHANNEL;
        $conversation = Conversation::where('mailbox_id', $mailbox->id)
        ->where('customer_id', $customer->id)
        ->where('channel', $channel)
        ->orderBy('created_at', 'desc')
        ->first();

        $attachments = [];

        $contentProvider = $message->getContentProvider();
        $url=$contentProvider->getOriginalContentUrl();
        if ($contentProvider->getType() == MessageContentProviderType::EXTERNAL) {
            $file_url = $contentProvider->getOriginalContentUrl();
            $attachments[] = [
                'file_name' => \Helper::remoteFileName($file_url),
                'file_url' => $file_url,
            ];
        }else{
            $contentId = $message->getId();
            $sfo = $botBlob->getMessageContent($contentId);
            $image = $sfo->fread($sfo->getSize());
            $attachments[] = [
                'file_name' => \Helper::remoteFileName("video-" . $message->getId().".mp4"),
                'data' => base64_encode($image),//thread only handle base64 encoded image or image url or file path;
            ];
        }

        if ($conversation) {//LINEの場合は同じユーザーとのやり取りは常に同じ会話として取り扱われるのでほぼほぼこちらがわで処理される。
            // Create thread in existing conversation.
            //単独の動画
            $text = '<a href="cid:video-' . $message->getId().'.mp4" target="_blank">' . __('Movie from LINE') . '</a>';//LINEから動画送信
            $thread=Thread::createExtended([
                    'type' => Thread::TYPE_CUSTOMER,
                    'customer_id' => $customer->id,
                    'body' => $text,
                    'attachments' => $attachments,
                ],
                $conversation,
                $customer
            );
            foreach($thread->attachments as $attachment){
                $thread->body = str_replace('cid:' . $attachment->file_name, $attachment->url(), $thread->body);
            }
            $thread->save();

        } 
    }

    //multiple images arrived separated webhook.
    //it's need bind them one thread by manually.
    //set imageset id to thread's message id.
    //imageset id is not identical at all the time. but we sort thread by created_at and use first. so I think it ok.
    //I can not match path of images of same imageset. 
    //but it seems freescout standard.
    
    public static function HandleImageMessage($botBlob,$customer,$mailbox,$message){
        $channel = \LINEIntegration::CHANNEL;
        $conversation = Conversation::where('mailbox_id', $mailbox->id)
        ->where('customer_id', $customer->id)
        ->where('channel', $channel)
        ->orderBy('created_at', 'desc')
        ->first();

        $attachments = [];

        $contentProvider = $message->getContentProvider();
        $url=$contentProvider->getOriginalContentUrl();
        if ($contentProvider->getType() == MessageContentProviderType::EXTERNAL) {
            $file_url = $contentProvider->getOriginalContentUrl();

            $attachments[] = [
                'file_name' => \Helper::remoteFileName($file_url),
                'file_url' => $file_url,
            ];
        }else{
            $contentId = $message->getId();
            $sfo = $botBlob->getMessageContent($contentId);
            $image = $sfo->fread($sfo->getSize());
            $imageset = $message->getImageSet();
            if($imageset){
                $attachments[] = [
                    'file_name' => \Helper::remoteFileName("image" . $imageset->getIndex() .".jpg"),
                    'data' => base64_encode($image),//thread only handle base64 encoded image or image url or file path;
                ];
            }else{
                $attachments[] = [
                    'file_name' => \Helper::remoteFileName("image1.jpg"),
                    'data' => base64_encode($image),//thread only handle base64 encoded image or image url or file path;
                ];
            }
        }

        if ($conversation) {//LINEの場合は同じユーザーとのやり取りは常に同じ会話として取り扱われるのでほぼほぼこちらがわで処理される。
            // Create thread in existing conversation.
            $imageset = $message->getImageSet();
            if($imageset){
                //freescout allow limited tag.so image size is fixed.and insert $idx between images as a spacer.
                $text = '<div style="display: flex; gap:5px;flex-wrap:wrap">';
                        for ($idx = 1; $idx <= $imageset->getTotal();$idx++){
                            $text .= ' <a href="cid:image' . $idx .'.jpg" target="_blank"><img loading="lazy" height="200" src="cid:image' . $idx .'.jpg"></a>';
                        }
                $text .='</div>';

                $thread = Thread::where('conversation_id', $conversation->id)
                ->where('customer_id', $customer->id)
                ->where('message_id',$imageset->getId())
                ->orderBy('created_at', 'desc')
                ->first();
                if(!$thread){
                    $thread = Thread::createExtended([
                            'type' => Thread::TYPE_CUSTOMER,
                            'customer_id' => $customer->id,
                            'body' => $text,
                            'attachments' => $attachments,
                        ],
                        $conversation,
                        $customer
                    );
                    $thread->message_id=$imageset->getId();
                    $thread->save();
                }else{
                    \LINEIntegration::CreateAttachment($attachments,$thread->id);
                }
                foreach($thread->attachments as $attachment){
                    $thread->body = str_replace('cid:' . $attachment->file_name, $attachment->url(), $thread->body);
                }
                $thread->save();
            }else{
                //単独の写真
                $text = '<div style="display: flex; gap:5px;flex-wrap:wrap">';
                $text .= ' <a href="cid:image.jpg" target="_blank"><img loading="lazy" height="200" src="cid:image.jpg"></a>';
                $text .='</div>';
                $thread=Thread::createExtended([
                        'type' => Thread::TYPE_CUSTOMER,
                        'customer_id' => $customer->id,
                        'body' => $text,
                        'attachments' => $attachments,
                    ],
                    $conversation,
                    $customer
                );
                foreach($thread->attachments as $attachment){
                    $thread->body = str_replace('cid:image.jpg', $attachment->url(), $thread->body);
                }
                $thread->save();            
            }
        } 
    }

    public static function processIncomingMessage($bot,$botBlob,$event,$mailbox)
    {

        $message = $event->getMessage();
        if (!($message instanceof TextMessageContent || $message instanceof ImageMessageContent || $message instanceof StickerMessageContent || $message instanceof VideoMessageContent || $message instanceof LocationMessageContent || $message instanceof AudioMessageContent)) {
            \LINEIntegration::log('Does not support this message type:' . get_class($message), $mailbox, true);
            return;
        }

        $source = $event->getSource();
        $user_id = $source["userId"];
        $profile = $bot->getProfile($user_id);

        $channel = \LINEIntegration::CHANNEL;
        //ユーザーの存在確認
        $customer = Customer::where('channel', $channel)
        ->where('channel_id', $user_id)
        ->first();

        if (!$customer) {//ユーザー生成
            $customer_data = [
                'channel' => $channel,
                'channel_id' => $user_id,
                'first_name' => __('LINE User'),
                'last_name' => $profile["displayName"],
                'social_profiles' => Customer::formatSocialProfiles([[
                    'type' => Customer::SOCIAL_TYPE_OTHER,
                    'value' => $profile["displayName"],
                ]])
            ];

            $customer = Customer::createWithoutEmail($customer_data);

            if (!$customer) {
                \LINEIntegration::log('Could not create a customer.', $mailbox, true);
                return;
            }
            // Get Telegram user photo.
            $photo_url = '';
            try {
                $photo_url = $profile["pictureUrl"];
            } catch (\Exception $e) {
                // Do nothing.
            }
            
            if ($photo_url) {
                if ($customer->setPhotoFromRemoteFile($photo_url)) {
                    $customer->save();
                }
            }
        }
        $conversation = Conversation::where('mailbox_id', $mailbox->id)
        ->where('customer_id', $customer->id)
        ->where('channel', $channel)
        ->orderBy('created_at', 'desc')
        ->first();
        if(!$conversation){
            // Create conversation. one customer has only one conversation
            //conversation text is important for freescout.
            //so I set LINE ユーザーとのやり取りを開始しました。
            Conversation::create([
                'type' => Conversation::TYPE_CHAT,
                'subject' => Conversation::subjectFromText(__('Talk to LINE User :name', ['name' => $profile["displayName"]])),
                'mailbox_id' => $mailbox->id,
                'source_type' => Conversation::SOURCE_TYPE_WEB,
                'channel' => $channel,
            ], [[
                'type' => Thread::TYPE_CUSTOMER,
                'customer_id' => $customer->id,
                'body' =>  Conversation::subjectFromText(__('Start Talking to LINE User :name', ['name' => $profile["displayName"]])),
                'attachments' => [],
            ]],
            $customer
            );
        }

        if ($message instanceof TextMessageContent) {
            \LINEIntegration::HandleTextMessage($customer,$mailbox,$message);
        } elseif ($message instanceof StickerMessageContent) {
            \LINEIntegration::HandleStickerMessage($customer,$mailbox,$message);
//            $handler = new StickerMessageHandler($bot, $logger, $event);
        } elseif ($message instanceof LocationMessageContent) {
            \LINEIntegration::HandleLocationMessage($customer,$mailbox,$message);
//            $handler = new LocationMessageHandler($bot, $logger, $event);
        } elseif ($message instanceof ImageMessageContent) {
            \LINEIntegration::HandleImageMessage($botBlob,$customer,$mailbox,$message);
        } elseif ($message instanceof AudioMessageContent) {
            \LINEIntegration::HandleAudioMessage($botBlob,$customer,$mailbox,$message);
//            $handler = new AudioMessageHandler($bot, $botBlob, $logger, $req, $event);
        } elseif ($message instanceof VideoMessageContent) {
            \LINEIntegration::HandleVideoMessage($botBlob,$customer,$mailbox,$message);
//            $handler = new VideoMessageHandler($bot, $botBlob, $logger, $req, $event);
        } elseif ($message instanceof AudioMessageContent) {
            \LINEIntegration::HandleAudioMessage($botBlob,$customer,$mailbox,$message);
        } elseif ($message instanceof UnknownMessageContent) {
            \LINEIntegration::log('Unknown message content.' . get_class($message), $mailbox, true);
        } else {
            \LINEIntegration::log('wrong message content.' . get_class($message), $mailbox, true);
            // Unexpected behavior (just in case)
            // something wrong if reach here
        }
    }

    public static function log($text, $mailbox = null, $is_webhook = true)
    {
        \Helper::log(\LINEIntegration::LOG_NAME, '['.self::CHANNEL_NAME.($is_webhook ? ' Webhook' : '').'] '.($mailbox ? '('.$mailbox->name.') ' : '').$text);
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTranslations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('lineintegration.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'lineintegration'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/lineintegration');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/lineintegration';
        }, \Config::get('view.paths')), [$sourcePath]), 'lineintegration');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
