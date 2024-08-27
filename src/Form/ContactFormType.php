<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname',TextType::class,[
                'label'=>'Nom',
                'required'=>true
            ])
            ->add('lastname',TextType::class,options:[
                'label'=>'Prenom',
                'required'=>true
            ])
            ->add('email',EmailType::class,options:[
                'label'=>'Email',
                'required'=>true
            ])
            ->add('subject',TextType::class,options:[
                'label'=>'Sujet',
                'required'=>true
            ])
            ->add('message',TextareaType::class,[
                'label'=>'Message',
                'required'=>true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
