@extends('layouts.app')

@section('title_full', __('LINE').' - '.$mailbox->name)

@section('sidebar')
    @include('partials/sidebar_menu_toggle')
    @include('mailboxes/sidebar_menu')
@endsection

@section('content')

    <div class="section-heading margin-bottom">
        {{ __('LINE') }}
    </div>

    <div class="col-xs-12">
  
        @include('partials/flash_messages')

        <form class="form-horizontal margin-bottom" method="POST" action="" autocomplete="off">
            {{ csrf_field() }}

            <div class="form-group{{ $errors->has('auto_reply_enabled') ? ' has-error' : '' }}">
                <label for="settings_enabled" class="col-sm-2 control-label">{{ __('Enabled') }}</label>

                <div class="col-sm-6">
                    <div class="controls">
                        <div class="onoffswitch-wrap">
                            <div class="onoffswitch">
                                <input type="checkbox" name="settings[enabled]" value="1" id="settings_enabled" class="onoffswitch-checkbox" @if (!empty($settings['enabled']))checked="checked"@endif >
                                <label class="onoffswitch-label" for="settings_enabled"></label>
                            </div>
                            <div class="form-help">
                                <a href="https://freescout.net/module/line-integration/" target="_blank">{{ __('Instruction') }} <small class="glyphicon glyphicon-share"></small></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('WebHook URL') }}</label>

                <div class="col-sm-6">
                    <div class="form-help">
                        {{ __('You have to set this at LINE Developer Console (https only)')}}
                    </div>
                    <div class="form-help">
                        {{ url('/') . '/line/webhook/' . $mailbox['id'] . '/'}}
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Channel ID') }}</label>

                <div class="col-sm-6">
                    <input type="text" class="form-control input-sized-lg" name="settings[id]" value="{{ $settings['id'] ?? '' }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Channel Secret') }}</label>

                <div class="col-sm-6">
                    <input type="text" class="form-control input-sized-lg" name="settings[secret]" value="{{ $settings['secret'] ?? '' }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Channel Token') }}</label>

                <div class="col-sm-6">
                    <input type="text" class="form-control input-sized-lg" name="settings[token]" value="{{ $settings['token'] ?? '' }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __('Auto Reply') }}</label>

                <div class="col-sm-6">
                    <div class="form-help">
                        {{ __('You could set Auto reply at LINE developer console.') }}
                    </div>
                </div>
            </div>

            <div class="form-group margin-top">
                <div class="col-sm-6 col-sm-offset-2">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection