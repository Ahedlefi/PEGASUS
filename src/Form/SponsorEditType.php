<?php

namespace App\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SponsorEditType extends SponsorType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'include_password' => false,
            'password_required' => false,
        ]);
    }
}
