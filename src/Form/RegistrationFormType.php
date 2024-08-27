<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('roles', ChoiceType::class, [
            'choices' =>[
                'Customer'=>'ROLE_USER',
                'Seller'=>'ROLE_SELLER',
                
            ],
            'expanded'=>true,
            'multiple'=>false,
            'label'=>'Roles'
        ])
            ->add('email', EmailType::class,[
                'attr'=>[
                   'class' => 'form-control',
                ],
                'label'=> 'Email'
            ])
            ->add("lastname", TextType::class,[
                'attr'=>[
                   'class' => 'form-control'
                ],
                'label'=> 'Nom'
            ])
            ->add("firstname", TextType::class,[
                'attr'=>[
                   'class' => 'form-control'
                ],
                'label'=> 'Prenom'
            ])
            ->add("phone", TextType::class,[
                'attr'=>[
                   'class' => 'form-control'
                ],
                'label'=> 'Phone'
            ])
           
            
            
            

            ->add('RGPDConsent', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;

        
    $builder->get('roles')
    ->addModelTransformer(new CallbackTransformer(
        function ($rolesArray) {
            // transform the array to a string
            return count($rolesArray)? $rolesArray[0]: null;
        },
        function ($rolesString) {
            // transform the string back to an array
            return [$rolesString];
        }
    ));
    
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
