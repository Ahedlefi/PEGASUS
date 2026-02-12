<?php

namespace App\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminEditType extends AdminType
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
