<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Helpers\ValidationHelper;
use App\Helpers\MailerHelper;
use App\Helpers\GoogleJwtVerifier;
use App\Core\Database as DB;

class AuthController {
    /**
     * GET /login
     */
    public function showLogin(Request $request): void {
        if (isset($_SESSION['Auth'])) {
            Response::redirect('/');
        }
        Response::renderView('login', ['page_title' => 'Sign In']);
    }

    /**
     * POST /login
     */
    public function login(Request $request): void {
        $request->verifyCsrf();
        $usernameEmail = $request->post('username_email', '');

        if (!$request->checkRateLimit('login_' . $usernameEmail, 10, 300)) {
            $_SESSION['error'] = ['field' => 'checkuser', 'msg' => 'Too many login attempts. Please wait 5 minutes.'];
            Response::redirect('/login');
        }

        $response = ValidationHelper::validateLogin($request->all());
        if ($response['status']) {
            session_regenerate_id(true);
            $_SESSION['Auth']     = true;
            $_SESSION['userdata'] = $response['user'];

            if ((int)$response['user']['ac_status'] === 0) {
                $_SESSION['code'] = $code = rand(111111, 999999);
                MailerHelper::sendCode($response['user']['email'], 'Verify Your SamvadHub Account', $code);
            }
            Response::redirect('/');
        } else {
            $_SESSION['error']    = $response;
            $_SESSION['formdata'] = $request->all();
            Response::redirect('/login');
        }
    }

    /**
     * GET /signup
     */
    public function showSignup(Request $request): void {
        if (isset($_SESSION['Auth'])) {
            Response::redirect('/');
        }
        Response::renderView('signup', ['page_title' => 'Create Account']);
    }

    /**
     * POST /signup
     */
    public function signup(Request $request): void {
        $request->verifyCsrf();
        if (!$request->checkRateLimit('signup', 5, 600)) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'Too many signup attempts. Please try again later.'];
            Response::redirect('/signup');
        }

        $response = ValidationHelper::validateSignup($request->all());
        if ($response['status']) {
            if (User::create($request->all())) {
                Response::redirect('/login?newuser');
            } else {
                $_SESSION['error'] = ['field' => 'general', 'msg' => 'Something went wrong. Please try again.'];
                Response::redirect('/signup');
            }
        } else {
            $_SESSION['error']    = $response;
            $_SESSION['formdata'] = $request->all();
            Response::redirect('/signup');
        }
    }

    /**
     * GET /logout
     */
    public function logout(Request $request): void {
        session_destroy();
        Response::redirect('/login');
    }

    /**
     * GET /forgot-password
     */
    public function showForgotPassword(Request $request): void {
        if (isset($_SESSION['Auth'])) {
            Response::redirect('/');
        }
        Response::renderView('forgot_password', ['page_title' => 'Reset Password']);
    }

    /**
     * POST /forgot-password
     */
    public function forgotPassword(Request $request): void {
        $request->verifyCsrf();
        $email = trim(strtolower($request->post('email', '')));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = ['field' => 'email', 'msg' => 'Please enter a valid email address.'];
            Response::redirect('/forgot-password');
        }
        if (!User::isEmailRegistered($email)) {
            $_SESSION['error'] = ['field' => 'email', 'msg' => 'If this email is registered, a code has been sent.'];
            Response::redirect('/forgot-password?resent');
        }

        if (!$request->checkRateLimit('forgot_' . $email, 3, 600)) {
            $_SESSION['error'] = ['field' => 'email', 'msg' => 'Too many attempts. Please wait before requesting another code.'];
            Response::redirect('/forgot-password');
        }

        $_SESSION['forgot_email'] = $email;
        $_SESSION['forgot_code']  = $code = rand(111111, 999999);
        MailerHelper::sendCode($email, 'Reset Your SamvadHub Password', $code);
        Response::redirect('/forgot-password?resent');
    }

    /**
     * POST /verify-forgot-code
     */
    public function verifyForgotCode(Request $request): void {
        $request->verifyCsrf();
        $userCode = trim($request->post('code', ''));
        $code     = $_SESSION['forgot_code'] ?? '';

        if (!empty($userCode) && !empty($code) && $code == $userCode) {
            $_SESSION['auth_temp'] = true;
            Response::redirect('/forgot-password');
        } else {
            $msg = empty($userCode) ? 'Please enter the 6-digit code.' : 'Incorrect verification code.';
            $_SESSION['error'] = ['field' => 'email_verify', 'msg' => $msg];
            Response::redirect('/forgot-password');
        }
    }

    /**
     * POST /reset-password
     */
    public function resetPassword(Request $request): void {
        $request->verifyCsrf();
        if (!isset($_SESSION['auth_temp']) || !isset($_SESSION['forgot_email'])) {
            Response::redirect('/forgot-password');
        }
        $password = trim($request->post('password', ''));
        if (strlen($password) < 6) {
            $_SESSION['error'] = ['field' => 'password', 'msg' => 'Password must be at least 6 characters.'];
            Response::redirect('/forgot-password');
        }
        User::resetPassword($_SESSION['forgot_email'], $password);
        unset($_SESSION['forgot_email'], $_SESSION['forgot_code'], $_SESSION['auth_temp']);
        session_destroy();
        Response::redirect('/login?reseted');
    }

    /**
     * POST /verify-email
     */
    public function verifyEmail(Request $request): void {
        requireAuth();
        $request->verifyCsrf();
        $userCode = trim($request->post('code', ''));
        $code     = $_SESSION['code'] ?? '';

        if (!empty($userCode) && !empty($code) && $code == $userCode) {
            if (User::verifyEmail($_SESSION['userdata']['email'])) {
                $_SESSION['userdata']['ac_status'] = 1;
                Response::redirect('/');
            } else {
                $_SESSION['error'] = ['field' => 'email_verify', 'msg' => 'Verification failed. Please try again.'];
                Response::redirect('/');
            }
        } else {
            $msg = empty($userCode)
                ? 'Please enter the 6-digit code sent to your email.'
                : 'Incorrect verification code. Please try again.';
            $_SESSION['error'] = ['field' => 'email_verify', 'msg' => $msg];
            Response::redirect('/');
        }
    }

    /**
     * GET /resend-verification-code
     */
    public function resendVerification(Request $request): void {
        requireAuth();
        if (!$request->checkRateLimit('resend_code', 3, 600)) {
            Response::redirect('/?resend_blocked');
        }
        $_SESSION['code'] = $code = rand(111111, 999999);
        MailerHelper::sendCode($_SESSION['userdata']['email'], 'Verify Your SamvadHub Account', $code);
        Response::redirect('/?resent');
    }

    /**
     * POST /api/auth/google
     */
    public function googleLogin(Request $request): void {
        $request->verifyCsrf();
        $idToken = trim($request->post('id_token', ''));

        if (empty($idToken)) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'Google authentication token is missing.'];
            Response::redirect('/login');
        }

        // Verify the ID Token with Google
        $payload = GoogleJwtVerifier::verify($idToken);
        if (!$payload) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'Google token verification failed.'];
            Response::redirect('/login');
        }

        $email = strtolower(trim($payload['email'] ?? ''));
        if (empty($email)) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'Email address not provided by Google.'];
            Response::redirect('/login');
        }

        // Retrieve user by email
        $user = User::getByEmail($email);

        if (!$user) {
            // Register a new user account dynamically
            $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $payload['given_name'] ?? 'user'));
            $username = $baseUsername;
            while (User::isUsernameRegistered($username)) {
                $username = $baseUsername . rand(100, 9999);
            }

            // Cryptographically secure random password
            $password = bin2hex(random_bytes(16));

            // Securely download profile picture
            $profilePicUrl = $payload['picture'] ?? '';
            $localProfilePic = 'default_profile.jpg';
            if (!empty($profilePicUrl)) {
                $localProfilePic = $this->downloadOAuthProfilePic($profilePicUrl);
            }

            // Create basic user
            $registered = User::create([
                'first_name' => $payload['given_name'] ?? 'User',
                'last_name' => $payload['family_name'] ?? '',
                'gender' => 0, // Unspecified/Other
                'email' => $email,
                'username' => $username,
                'password' => $password
            ]);

            if ($registered) {
                // Fetch user to obtain ID and set status and profile pic
                $user = User::getByEmail($email);
                if ($user) {
                    DB::execute(
                        "UPDATE users SET ac_status = 1, profile_pic = ? WHERE id = ?",
                        'si', $localProfilePic, (int)$user['id']
                    );
                    // Refresh copy
                    $user = User::getByEmail($email);
                }
            } else {
                $_SESSION['error'] = ['field' => 'general', 'msg' => 'Failed to create user account.'];
                Response::redirect('/login');
            }
        }

        // Account Soft-Deleted check
        if ((int)$user['ac_status'] === 3) {
            Response::redirect('/login?deleted');
        }

        // Account Suspended check
        if ((int)$user['ac_status'] === 2) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'Your account is suspended by the administrator.'];
            Response::redirect('/login');
        }

        // Perform login session initialization
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['Auth'] = true;
        $_SESSION['userdata'] = $user;

        Response::redirect('/');
    }

    /**
     * GET /auth/github
     */
    public function redirectToGithub(Request $request): void {
        if (isset($_SESSION['Auth'])) {
            Response::redirect('/');
        }
        
        $clientId = $_ENV['GITHUB_CLIENT_ID'] ?? '';
        if (empty($clientId)) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'GitHub OAuth Client ID is not configured in .env'];
            Response::redirect('/login');
        }
        
        $state = bin2hex(random_bytes(16));
        $_SESSION['github_oauth_state'] = $state;
        
        $redirectUri = urlencode('http://' . $_SERVER['HTTP_HOST'] . '/api/auth/github/callback');
        $githubUrl = "https://github.com/login/oauth/authorize?client_id={$clientId}&redirect_uri={$redirectUri}&scope=read:user%20user:email&state={$state}";
        
        Response::redirect($githubUrl);
    }

    /**
     * GET /api/auth/github/callback
     */
    public function githubCallback(Request $request): void {
        if (isset($_SESSION['Auth'])) {
            Response::redirect('/');
        }
        
        $state = $request->get('state', '');
        $savedState = $_SESSION['github_oauth_state'] ?? '';
        
        if (empty($state) || empty($savedState) || !hash_equals($savedState, $state)) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'GitHub OAuth CSRF state verification failed.'];
            Response::redirect('/login');
        }
        
        unset($_SESSION['github_oauth_state']);
        
        $code = trim($request->get('code', ''));
        if (empty($code)) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'GitHub OAuth code is missing.'];
            Response::redirect('/login');
        }
        
        $clientId = $_ENV['GITHUB_CLIENT_ID'] ?? '';
        $clientSecret = $_ENV['GITHUB_CLIENT_SECRET'] ?? '';
        
        if (empty($clientId) || empty($clientSecret)) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'GitHub OAuth credentials are not configured in .env'];
            Response::redirect('/login');
        }
        
        // Exchange code for Access Token
        $ch = curl_init('https://github.com/login/oauth/access_token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'state' => $state
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if (!$response) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'Failed to reach GitHub OAuth server.'];
            Response::redirect('/login');
        }
        
        $tokenData = json_decode($response, true);
        $accessToken = $tokenData['access_token'] ?? '';
        
        if (empty($accessToken)) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'Failed to retrieve GitHub access token.'];
            Response::redirect('/login');
        }
        
        // Fetch User Profile
        $ch = curl_init('https://api.github.com/user');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$accessToken}",
            "User-Agent: SamvadHub-OAuth"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $profileRes = curl_exec($ch);
        curl_close($ch);
        
        if (!$profileRes) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'Failed to fetch GitHub profile.'];
            Response::redirect('/login');
        }
        
        $profile = json_decode($profileRes, true);
        
        // Fetch User Emails (to ensure we get the verified primary email)
        $ch = curl_init('https://api.github.com/user/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$accessToken}",
            "User-Agent: SamvadHub-OAuth"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $emailsRes = curl_exec($ch);
        curl_close($ch);
        
        $email = '';
        if ($emailsRes) {
            $emails = json_decode($emailsRes, true);
            if (is_array($emails)) {
                foreach ($emails as $em) {
                    if ($em['primary'] && $em['verified']) {
                        $email = strtolower(trim($em['email']));
                        break;
                    }
                }
            }
        }
        
        // Fallback to profile email if not found in list
        if (empty($email) && !empty($profile['email'])) {
            $email = strtolower(trim($profile['email']));
        }
        
        if (empty($email)) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'Could not retrieve a verified primary email from GitHub.'];
            Response::redirect('/login');
        }
        
        $user = User::getByEmail($email);
        
        if (!$user) {
            // Register a new user dynamically
            $nameParts = explode(' ', $profile['name'] ?? 'GitHub User', 2);
            $firstName = $nameParts[0] ?? 'User';
            $lastName = $nameParts[1] ?? '';
            
            $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $profile['login'] ?? 'user'));
            $username = $baseUsername;
            while (User::isUsernameRegistered($username)) {
                $username = $baseUsername . rand(100, 9999);
            }
            
            $password = bin2hex(random_bytes(16));
            $avatarUrl = $profile['avatar_url'] ?? '';
            $localProfilePic = 'default_profile.jpg';
            if (!empty($avatarUrl)) {
                $localProfilePic = $this->downloadOAuthProfilePic($avatarUrl);
            }
            
            $registered = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => 0,
                'email' => $email,
                'username' => $username,
                'password' => $password
            ]);
            
            if ($registered) {
                $user = User::getByEmail($email);
                if ($user) {
                    DB::execute(
                        "UPDATE users SET ac_status = 1, profile_pic = ? WHERE id = ?",
                        'si', $localProfilePic, (int)$user['id']
                    );
                    $user = User::getByEmail($email);
                }
            } else {
                $_SESSION['error'] = ['field' => 'general', 'msg' => 'Failed to register account via GitHub.'];
                Response::redirect('/login');
            }
        }
        
        if ((int)$user['ac_status'] === 3) {
            Response::redirect('/login?deleted');
        }
        if ((int)$user['ac_status'] === 2) {
            $_SESSION['error'] = ['field' => 'general', 'msg' => 'Your account is suspended by the administrator.'];
            Response::redirect('/login');
        }
        
        session_regenerate_id(true);
        $_SESSION['Auth'] = true;
        $_SESSION['userdata'] = $user;
        Response::redirect('/');
    }

    /**
     * Securely downloads Google's or GitHub's profile photo and stores it locally.
     */
    private function downloadOAuthProfilePic(string $url): string {
        $default = 'default_profile.jpg';
        
        $parsed = parse_url($url);
        if (!$parsed || ($parsed['scheme'] ?? '') !== 'https') {
            return $default;
        }
        
        $host = $parsed['host'] ?? '';
        if (!str_ends_with($host, 'googleusercontent.com') && !str_ends_with($host, 'githubusercontent.com')) {
            return $default;
        }
        
        // Execute curl download with size limit and SSL checks
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_MAXFILESIZE, 2 * 1024 * 1024); // 2MB max
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        if (!$data || $info['http_code'] !== 200) {
            return $default;
        }
        
        $contentType = $info['content_type'] ?? '';
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        
        $mime = explode(';', $contentType)[0];
        if (!isset($allowed[$mime])) {
            return $default;
        }
        
        $ext = $allowed[$mime];
        $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        
        $path = dirname(__DIR__, 2) . '/public/assets/images/profile/' . $filename;
        if (file_put_contents($path, $data)) {
            return $filename;
        }
        
        return $default;
    }
}
