<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ProductCompare;
use App\Models\Wishlist;
use App\Models\User;
use App\Utils\CartManager;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Enums\SessionKey;
use Illuminate\Contracts\View\View;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Enums\ViewPaths\Vendor\Auth;
use App\Http\Requests\Vendor\LoginRequest;
use App\Repositories\VendorWalletRepository;
use App\Services\VendorService;
use App\Traits\RecaptchaTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;



class LoginController extends Controller
{

    use RecaptchaTrait;

    public $company_name;

    public function __construct()
    {
        $this->middleware('guest:customer', ['except' => ['logout']]);
    }

    public function captcha(Request $request, $tmp)
    {

        $phrase = new PhraseBuilder;
        $code = $phrase->build(4);
        $builder = new CaptchaBuilder($code, $phrase);
        $builder->setBackgroundColor(220, 210, 230);
        $builder->setMaxAngle(25);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);
        $builder->build($width = 100, $height = 40, $font = null);
        $phrase = $builder->getPhrase();

        if (Session::has($request->captcha_session_id)) {
            Session::forget($request->captcha_session_id);
        }
        Session::put($request->captcha_session_id, $phrase);
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $builder->output();
    }

    public function generateReCaptcha(): void
    {
        // 👇 DON'T regenerate captcha phrase here
        if (!Session::has(SessionKey::WHOLESALER_RECAPTCHA_KEY)) {
            $recaptchaBuilder = $this->generateDefaultReCaptcha(4);
            Session::put(SessionKey::WHOLESALER_RECAPTCHA_KEY, $recaptchaBuilder->getPhrase());
        } else {
            $recaptchaBuilder = $this->generateDefaultReCaptcha(4);
            // Use existing phrase
            $recaptchaBuilder->setPhrase(Session::get(SessionKey::WHOLESALER_RECAPTCHA_KEY));
        }

        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $recaptchaBuilder->output();
    }


    public function getLoginView(): View
    {
        $recaptchaBuilder = $this->generateDefaultReCaptcha(4);
        $recaptcha = getWebConfig(name: 'recaptcha');
        Session::put(SessionKey::WHOLESALER_RECAPTCHA_KEY, $recaptchaBuilder->getPhrase());
        return view(
            VIEW_FILE_NAMES['wholesale_login_view'],
            compact('recaptchaBuilder', 'recaptcha')
        );
    }
    public function loginSubmit(Request $request)
    {

        $request->validate([
            'user_id' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('phone', $request->user_id)
            ->orWhere('email', $request->user_id)
            ->first();

        $remember = $request->has('remember');

        $max_login_hit = getWebConfig(name: 'maximum_login_hit') ?? 5;
        $temp_block_time = getWebConfig(name: 'temporary_login_block_time') ?? 5;

        if (!$user) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => translate('credentials_doesnt_match'),
                    'redirect_url' => ''
                ]);
            } else {
                Toastr::error(translate('credentials_doesnt_match'));
                return back()->withInput();
            }
        }

        $phone_verification = getLoginConfig(key: 'phone_verification');
        $email_verification = getLoginConfig(key: 'email_verification');

        if ($phone_verification && !$user->is_phone_verified) {
            $redirect = route('customer.auth.check-verification', [
                'identity' => base64_encode($user['phone']),
                'type' => base64_encode('phone_verification')
            ]);

            return $request->ajax()
                ? response()->json(['status' => 'error', 'message' => translate('account_phone_not_verified'), 'redirect_url' => $redirect])
                : redirect($redirect);
        }

        if ($email_verification && !$user->is_email_verified) {
            $redirect = route('customer.auth.check-verification', [
                'identity' => base64_encode($user['email']),
                'type' => base64_encode('email_verification')
            ]);

            return $request->ajax()
                ? response()->json(['status' => 'error', 'message' => translate('account_email_not_verified'), 'redirect_url' => $redirect])
                : redirect($redirect);
        }

        if (isset($user->temp_block_time) && Carbon::parse($user->temp_block_time)->diffInSeconds() <= $temp_block_time) {
            $time = $temp_block_time - Carbon::parse($user->temp_block_time)->diffInSeconds();
            $message = translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();

            return $request->ajax()
                ? response()->json(['status' => 'error', 'message' => $message, 'redirect_url' => ''])
                : back()->withInput()->with(Toastr::error($message));
        }

        if (auth('customer')->attempt(['email' => $user['email'], 'password' => $request['password']], $remember)) {
            $request->session()->regenerate();

            if (!$user->is_active) {
                auth()->guard('customer')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return $request->ajax()
                    ? response()->json(['status' => 'error', 'message' => translate('your_account_is_suspended')])
                    : back()->withInput()->with(Toastr::error(translate('your_account_is_suspended')));
            }

            session()->put('is_wholesaler', $user->user_type == 1 ? true : false);
            session()->put('wholesale_tier', $user->tier);

            $wish_list = Wishlist::whereHas('wishlistProduct', fn($q) => $q)
                ->where('customer_id', $user->id)
                ->pluck('product_id')
                ->toArray();

            $compare_list = ProductCompare::where('user_id', $user->id)->pluck('product_id')->toArray();

            session()->forget(['wish_list', 'compare_list']);
            session()->put('wish_list', $wish_list);
            session()->put('compare_list', $compare_list);

            Toastr::success(translate('welcome_to') . ' ' . getWebConfig(name: 'company_name') . '!');
            CartManager::cartListSessionToDatabase();

            $user->login_hit_count = 0;
            $user->is_temp_blocked = 0;
            $user->temp_block_time = null;
            $user->save();
             $redirect_url = route('store');
            // $redirect_url = 'store';
            $previous_url = url()->previous();

            if (
                strpos($previous_url, 'checkout-complete') !== false ||
                strpos($previous_url, 'offline-payment-checkout-complete') !== false ||
                strpos($previous_url, 'track-order') !== false
            ) {
                $redirect_url = route('store');
            }

            // return $request->ajax()
            //     ? response()->json(['status' => 'success', 'message' => translate('login_successful'), 'redirect_url' => $redirect_url])
            //     : back();
            return $request->ajax()
                ? response()->json(['status' => 'success', 'message' => translate('login_successful'), 'redirect_url' => $redirect_url])
                : redirect()->intended($redirect_url);
        }

        if ($user->is_temp_blocked && Carbon::parse($user->temp_block_time)->diffInSeconds() <= $temp_block_time) {
            $time = $temp_block_time - Carbon::parse($user->temp_block_time)->diffInSeconds();
            $message = translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();

            Toastr::error($message);
            return $request->ajax()
                ? response()->json(['status' => 'error', 'message' => $message, 'redirect_url' => ''])
                : back()->withInput();
        }

        if ($user->is_temp_blocked && Carbon::parse($user->temp_block_time)->diffInSeconds() > $temp_block_time) {
            $user->login_hit_count = 0;
            $user->is_temp_blocked = 0;
            $user->temp_block_time = null;
            $user->save();
        }

        if ($user->login_hit_count >= $max_login_hit && $user->is_temp_blocked == 0) {
            $user->is_temp_blocked = 1;
            $user->temp_block_time = now();
            $user->save();

            $time = $temp_block_time;
            $message = translate('too_many_attempts._please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans();
            Toastr::error($message);

            return $request->ajax()
                ? response()->json(['status' => 'error', 'message' => $message, 'redirect_url' => ''])
                : back()->withInput();
        }

        $user->increment('login_hit_count');

        $message = translate('credentials_doesnt_match');
        Toastr::error($message);

        return $request->ajax()
            ? response()->json(['status' => 'error', 'message' => $message, 'redirect_url' => ''])
            : back()->withInput();
    }


    public function logout(Request $request)
    {
        auth()->guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->forget('wish_list');
        session()->forget('customer_fcm_topic');
        Toastr::success(translate('come_back_soon') . '!');
        return redirect()->route('store');
    }

    public function getLoginModalView(Request $request): JsonResponse
    {
        return response()->json([
            'login_modal' => view(VIEW_FILE_NAMES['get_login_modal_data'])->render(),
            'register_modal' => view(VIEW_FILE_NAMES['get_register_modal_data'])->render(),
        ]);
    }
}
