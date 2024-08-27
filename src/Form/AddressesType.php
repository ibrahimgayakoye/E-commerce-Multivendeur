<?php

namespace App\Form;

use App\Entity\Addresses;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',options:[
                'label'=>'Titre',
                'required'=>true
            ])
            ->add('firstname',options:[
                'label'=>'Nom',
                'required'=>true

            ])
            ->add('lastname',options:[
                'label'=>'Prenom',
                'required'=>true
                ])
            ->add('company',options:[
                'label'=>'Entreprise',
                'required'=>false
            ])
            ->add('address',options:[
                'label'=>'Addresse',
                'required'=>true
            ])
            ->add('zipcode',options:[
                'label'=>'Code postal',
                'required'=>true
            ])
            ->add('country',options:[
                'label'=>'Pays',
                'required'=>true
            ])
            ->add('city',options:[
                'label'=>'Ville',
                'required'=>true
            ])
            ->add('phone',options:[
                'label'=>'Telephone',
                'required'=>true
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Addresses::class,
        ]);
    }
}
