<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly HttpClientInterface $httpClient,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');

        // reCAPTCHA token can be named 'g-recaptcha-response' or present inside form
        $recaptcha = $request->request->get('g-recaptcha-response', $request->request->get('recaptcha', null));

        if ($recaptcha) {
            $secret = getenv('RECAPTCHA_SECRET') ?: $_ENV['RECAPTCHA_SECRET'] ?? null;
            if ($secret) {
                $resp = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                    'body' => [
                        'secret' => $secret,
                        'response' => $recaptcha,
                        'remoteip' => $request->getClientIp(),
                    ],
                ]);

                $data = $resp->toArray(false);
                if (! isset($data['success']) || $data['success'] !== true) {
                    throw new \RuntimeException('reCAPTCHA verification failed.');
                }
            }
        }

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        // Validate inputs server-side using Symfony Validator
        $violations = $this->validator->validate($email, [new Assert\NotBlank(['message' => 'Please enter your email.']), new Assert\Email(['message' => 'Please enter a valid email address.'])]);
        if (count($violations) > 0) {
            $msg = $violations[0]->getMessage();
            throw new CustomUserMessageAuthenticationException($msg);
        }

        $violations = $this->validator->validate($password, [new Assert\NotBlank(['message' => 'Please enter your password.'])]);
        if (count($violations) > 0) {
            $msg = $violations[0]->getMessage();
            throw new CustomUserMessageAuthenticationException($msg);
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token'))]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // redirect to homepage or intended url
        $targetPath = $request->getSession()->get('_security.' . $firewallName . '.target_path');
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        // Role-based redirection: ADMIN -> admin dashboard, USER -> user dashboard
        $roles = method_exists($token, 'getRoleNames') ? $token->getRoleNames() : $token->getRoles();
        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        if (in_array('ROLE_USER', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('user_dashboard'));
        }

        // fallback
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
