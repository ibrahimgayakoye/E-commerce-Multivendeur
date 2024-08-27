<?php

namespace App\Form;

use App\Entity\Countries;
use App\Entity\Pack;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SendProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
        ->add('countries',EntityType::class,[
            'class'=>Countries::class,
            'label'=>false,
            'mapped' => false,
            'required'=>true,
            'multiple'=>true,
            'expanded'=>true,
    
            

        ])

        ->add('packs', EntityType::class,[
            'class'=>Pack::class,
            'label'=>false,
            'mapped' => false,
            'required'=>true,
            'multiple'=>true,
            'expanded'=>true,
            
            
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
           'data_class'=>Pack::class,
           'data_class'=>Countries::class
        ]);
    }
}
