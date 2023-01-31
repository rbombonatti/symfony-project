<?php

namespace App\Form;

use App\Entity\Hashes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HashesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateTimeBatch')
            ->add('blockNumber')
            ->add('entryString')
            ->add('generatedKey')
            ->add('generatedHash')
            ->add('generationAttempts')
            ->add('userIpAddress')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hashes::class,
        ]);
    }
}
