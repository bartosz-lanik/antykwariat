<?php

declare(strict_types=1);

namespace App\Core\Language;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LanguageContext
{
    private RequestStack $requestStack;
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->parameterBag = $parameterBag;
    }

    public function currentLanguage(): string
    {
        return $this->requestStack->getCurrentRequest()->getLocale();
    }

    public function languages(): array
    {
        $locales = $this->parameterBag->get('app_locales');

        return explode('|', $locales);
    }
}