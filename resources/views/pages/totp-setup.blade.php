<x-multi-factor::layout>
    <x-slot name="title">@lang('multi-factor::auth.title')</x-slot>

    <x-multi-factor::auth-card>
        <div id="mfa-setup">
            @if(!$hasStartedTotpSetup)
                <x-multi-factor::form :action="route('two-factor.enable')" id="fortify-totp">
                    <x-multi-factor::button type="submit" class="text-sm" form="fortify-totp">
                        {{ __('Enable Totp') }}
                    </x-multi-factor::button>
                </x-multi-factor::form>

                @if(session('auth.password_confirmed_at'))
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            document.getElementById('fortify-totp')?.submit();
                        });
                    </script>
                @endif
            @endif

            @if (session('status') == 'two-factor-authentication-enabled')
                <div class="mb-4 font-medium text-sm">
                    Please finish configuring two factor authentication below.
                </div>
            @endif

            @if($hasStartedTotpSetup)
                <div class="mfa-column mfa-gap-20">
                    <div class="mfa-column">
                        <p>Scan the QR code below using an authenticator app</p>
                        <div class="mfa-qr-code">
                            {!! $user->twoFactorQrCodeSvg() !!}
                        </div>
                    </div>

                    <div class="mfa-width-full" style="margin-top: 20px;">
                        <a href="{{ route('mfa.method', $mfaMethod) }}">
                            <x-multi-factor::button type="button" class="mfa-width-full">Continue</x-multi-factor::button>
                        </a>
                        <div class="mfa-row" style="margin: 20px 0;">
                            <span class="mfa-separator"></span>
                            <span class="mfa-separator-text">OR enter the code manually</span>
                            <span class="mfa-separator"></span>
                        </div>
                        <div class="mfa-row mfa-gap-10">
                            <x-multi-factor::form.input id="two_factor_secret_input" style="margin: 0;" field="two_factor_secret"
                                                        value="{{ decrypt($user->two_factor_secret) }}" readonly/>
                            <x-multi-factor::button type="button" class="mfa-btn-fit-content"
                                                    onclick="event.preventDefault(); var el=document.getElementById('two_factor_secret_input'); el && (el.select(), document.execCommand('copy'));">
                                <x-multi-factor::svg icon="copy"/>
                            </x-multi-factor::button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-multi-factor::auth-card>
</x-multi-factor::layout>
