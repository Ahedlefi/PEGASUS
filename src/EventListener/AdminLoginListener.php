<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsEventListener(event: LoginSuccessEvent::class)]
final class AdminLoginListener
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private RequestStack $requestStack
    ) {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        // Check if this is an admin login attempt
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/admin')) {
            return;
        }

        $user = $event->getUser();
        
        // Check if user has ROLE_ADMIN
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            // Add error message to session
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add('error', 'Access denied. Admin privileges required.');
            
            // Redirect back to admin login
            $response = new RedirectResponse($this->urlGenerator->generate('admin_login'));
            $event->setResponse($response);
            
            // Log out the user
            $event->getAuthenticator()->onAuthenticationSuccess($request, $event->getAuthenticatedToken(), 'admin');
        }
    }
}
