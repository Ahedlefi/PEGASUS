<?php

namespace App\Form;

use App\Entity\Admin;
use App\Enum\AccountStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
            ]);

        if ($options['include_password']) {
            $constraints = [];
            if ($options['password_required']) {
                $constraints = [new Assert\NotBlank(), new Assert\Length(['min' => 6])];
            }
            $builder->add('password', PasswordType::class, [
                'required' => $options['password_required'],
                'constraints' => $constraints,
            ]);
        }

        $builder
            ->add('username', TextType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 2, 'max' => 180])],
            ])
            ->add('phone', TelType::class, [
                'required' => false,
                'constraints' => [new Assert\Regex(['pattern' => '/^[\\d+\\-\\s]+$/']), new Assert\Length(['max' => 30])],
            ])
            ->add('avatarUrl', UrlType::class, ['required' => false])
            ->add('status', ChoiceType::class, [
                'choices' => AccountStatus::cases(),
                'choice_value' => function (?AccountStatus $status) {
                    return $status?->value;
                },
                'choice_label' => function (AccountStatus $status) {
                    return $status->name;
                },
            ])
            ->add('superAdmin');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Admin::class,
            'include_password' => true,
            'password_required' => true,
        ]);
    }
}
