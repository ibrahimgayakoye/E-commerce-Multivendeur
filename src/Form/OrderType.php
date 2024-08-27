<?php

namespace App\Form;

use App\Entity\Addresses;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       $user  = $options['user'];

        $builder
            ->add('addresses',EntityType::class,[
                'class'=>Addresses::class,
                'label'=>false,
                'required'=>true,
                'multiple'=>false,
                'choices'=> $user->getAddresses(),
                'expanded'=>true,

            ])

            ->add('payment', ChoiceType::class,[
                'choices'=>[
                    'Payer par Paypal'=>'paypal ',
                    'Carte visa'=>'stripe',
                ],
                'label'=>false,
                'required'=>true,
                'multiple'=>false,
                'expanded'=>true,
            ])
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user'=>[]
        ]);
    }
}
