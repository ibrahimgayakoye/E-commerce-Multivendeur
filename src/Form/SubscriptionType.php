<?php

namespace App\Form;

use App\Entity\Countries;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       $user  = $options['user'];
       $country = $options['country'];

        $builder
            ->add('country1',EntityType::class,[
                'class'=>Countries::class,
                'label'=>false,
                'required'=>true,
                'expanded'=> true,
                'choices'=> $country,
                'attr'=>[
                    'class'=>'js-example-basic-multiple'
                ]
                

            ])

            ->add('country2',EntityType::class,[
                'class'=>Countries::class,
                'label'=>false,
                'expanded'=> true,
                'choices'=> $country,
                'required'=>true,
                'attr'=>[
                    'class'=>'js-example-basic-multiple'
                ]
                

            ])


            ->add('payment', ChoiceType::class,[
                'choices'=>[
                    'Paypal'=>'paypal',
                    'Stripe'=>'stripe',
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
            'user'=>[],
            'country'=>[]
        ]);
    }
}
